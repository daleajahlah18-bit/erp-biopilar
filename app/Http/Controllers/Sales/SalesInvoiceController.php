<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceDetail;
use App\Models\SalesOrder;
use App\Models\Product;
use App\Models\ItemJournal;
use App\Services\NumberGeneratorService;
use App\Services\StockMovementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class SalesInvoiceController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(SalesInvoice::class, strtolower('SalesInvoice'));
    }
    public function index()
    {
        $invoices = SalesInvoice::with(["salesOrder.customer", "salesOrder.project", "creator"])->sortable()->latest()->sortable()->paginate(10);
        return view("sales.invoices.index", compact("invoices"));
    }

    public function create()
    {
        $orders = SalesOrder::where("status", "Confirmed")->get();
        return view("sales.invoices.create", compact("orders"));
    }

    public function store(Request $request, NumberGeneratorService $numGen)
    {
        $request->validate([
            "sales_order_id" => "required|exists:sales_orders,id",
            "invoice_date" => "required|date",
            "terms_of_payment_days" => "required|integer|min:0",
            "notes" => "nullable|string",
            "products" => "nullable|array",
            "products.*.product_id" => "required_with:products|exists:products,id|distinct",
            "products.*.quantity" => "required_with:products|numeric|min:0.01",
            "products.*.unit_price" => "required_with:products|numeric|min:0",
            "products.*.subtotal" => "required_with:products|numeric|min:0",
            "services" => "nullable|array",
            "services.*.service_name" => "required_with:services|string",
            "services.*.quantity" => "required_with:services|numeric|min:0.01",
            "services.*.unit_price" => "required_with:services|numeric|min:0",
            "services.*.subtotal" => "required_with:services|numeric|min:0",
            "total_amount" => "required|numeric|min:0",
        ]);

        if (empty($request->products) && empty($request->services)) {
            return back()->withInput()->with("error", "Minimal harus ada satu Produk atau Jasa.");
        }

        try {
            DB::beginTransaction();

            $invoice = SalesInvoice::create([
                "invoice_number" => $numGen->generate("INV", SalesInvoice::class, "invoice_number"),
                "sales_order_id" => $request->sales_order_id,
                "invoice_date" => $request->invoice_date,
                "terms_of_payment_days" => $request->terms_of_payment_days,
                "total_amount" => $request->total_amount,
                "notes" => $request->notes,
                "status" => "Draft", // Status default
                "payment_status" => "Unpaid",
                "created_by" => auth()->id()
            ]);

            if ($request->has('products') && is_array($request->products)) {
                foreach ($request->products as $item) {
                    $product = Product::find($item["product_id"]);
                    SalesInvoiceDetail::create([
                        "sales_invoice_id" => $invoice->id,
                        "product_id" => $item["product_id"],
                        "unit_id" => $product->unit_id,
                        "quantity" => $item["quantity"],
                        "unit_price" => $item["unit_price"],
                        "subtotal" => $item["subtotal"],
                    ]);
                }
            }

            if ($request->has('services') && is_array($request->services)) {
                foreach ($request->services as $service) {
                    \App\Models\SalesInvoiceService::create([
                        "sales_invoice_id" => $invoice->id,
                        "service_name" => $service["service_name"],
                        "quantity" => $service["quantity"],
                        "unit_price" => $service["unit_price"],
                        "subtotal" => $service["subtotal"],
                        "notes" => $service["notes"] ?? null,
                    ]);
                }
            }

            // Update SO Status
            $so = SalesOrder::find($request->sales_order_id);
            $so->update(["status" => "Invoiced"]);

            DB::commit();
            return redirect()->route("sales.invoices.index")->with("success", "Sales Invoice berhasil disimpan (Draft).");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with("error", "Gagal: " . $e->getMessage());
        }
    }

    public function show(SalesInvoice $invoice)
    {
        $invoice->load(["salesOrder.customer", "salesOrder.project", "details.product.unit", "services", "creator"]);
        return view("sales.invoices.show", compact("invoice"));
    }

    public function approve(SalesInvoice $invoice)
    {
        if ($invoice->status !== "Draft") {
            return back()->with("error", "Hanya invoice draft yang bisa di-approve.");
        }

        try {
            DB::beginTransaction();

            // 1. Update status invoice (tanpa memotong stok)
            $invoice->update([
                "status" => "Approved"
            ]);

            DB::commit();
            return back()->with("success", "Invoice berhasil di-Approve. Stok project sudah ditangani pada tahap Project Production.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with("error", $e->getMessage());
        }
    }

    public function printPdf(SalesInvoice $invoice)
    {
        $invoice->load(["salesOrder.customer", "salesOrder.project", "details.product.unit", "services", "creator"]);
        $pdf = Pdf::loadView("sales.invoices.pdf", compact("invoice"));
        return $pdf->download($invoice->invoice_number . ".pdf");
    }

    public function getOrderDetails($id)
    {
        $order = SalesOrder::with(["details.product.unit", "services", "customer", "project.projectPaymentTerms"])->find($id);
        if (!$order) return response()->json(["error" => "Order not found"], 404);

        $customerName = $order->project ? $order->project->client_name : ($order->customer ? $order->customer->customer_name : '-');
        
        $paymentTermsList = [];
        if ($order->project && $order->project->projectPaymentTerms) {
            foreach ($order->project->projectPaymentTerms as $term) {
                $days = $term->term_unit === 'Months' ? $term->term_value * 30 : $term->term_value;
                $paymentTermsList[] = [
                    "id" => $term->id,
                    "name" => $term->top_type . " (" . $term->percentage . "%) - " . $term->term_value . " " . $term->term_unit,
                    "days" => $days
                ];
            }
        }

        return response()->json([
            "customer" => [
                "customer_name" => $customerName,
                "payment_terms_list" => $paymentTermsList,
            ],
            "details" => $order->details->map(function($d) {
                // Get Stock
                $stock = DB::table("stocks")->where("product_id", $d->product_id)->sum("quantity");
                return [
                    "product_id" => $d->product_id,
                    "product_name" => $d->product->product_name,
                    "unit_name" => $d->unit->unit_name,
                    "quantity" => $d->quantity,
                    "unit_price" => $d->unit_price,
                    "available_stock" => $stock
                ];
            }),
            "services" => $order->services->map(function($s) {
                return [
                    "service_name" => $s->service_name,
                    "quantity" => $s->quantity,
                    "unit_price" => $s->unit_price,
                    "subtotal" => $s->subtotal,
                    "notes" => $s->notes
                ];
            })
        ]);
    }
}
