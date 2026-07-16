<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\SalesPayment;
use App\Models\SalesInvoice;
use App\Services\NumberGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class SalesPaymentController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(SalesPayment::class, strtolower('SalesPayment'));
    }
    public function index()
    {
        $payments = SalesPayment::with(["salesInvoice.salesOrder.customer", "salesInvoice.salesOrder.project", "projectPaymentTerm", "creator"])->sortable()->latest()->sortable()->paginate(10);
        return view("sales.payments.index", compact("payments"));
    }

    public function create()
    {
        // Invoice yang sudah Approved
        $invoices = SalesInvoice::with(['salesOrder.customer', 'salesOrder.project'])
                                ->where("status", "Approved")
                                ->orWhere("status", "Paid")
                                ->get();
        return view("sales.payments.create", compact("invoices"));
    }

    public function store(Request $request, NumberGeneratorService $numGen)
    {
        $request->validate([
            "sales_invoice_id" => "required|exists:sales_invoices,id",
            "project_payment_term_id" => "required|exists:project_payment_terms,id",
            "payment_date" => "required|date",
            "payment_amount" => "required|numeric|min:1",
            "payment_method" => "required|string",
            "notes" => "nullable|string",
        ]);

        try {
            DB::beginTransaction();

            $term = \App\Models\ProjectPaymentTerm::lockForUpdate()->find($request->project_payment_term_id);
            $sisaTagihan = $term->remaining_amount;

            if ($request->payment_amount > $sisaTagihan) {
                throw new \Exception("Nominal pembayaran melebih sisa tagihan termin! Sisa: Rp " . number_format($sisaTagihan, 2));
            }

            $payment = SalesPayment::create([
                "payment_number" => $numGen->generate("PAY-SO", SalesPayment::class, "payment_number"),
                "sales_invoice_id" => $request->sales_invoice_id,
                "project_payment_term_id" => $request->project_payment_term_id,
                "payment_date" => $request->payment_date,
                "payment_amount" => $request->payment_amount,
                "payment_method" => $request->payment_method,
                "notes" => $request->notes,
                "created_by" => auth()->id()
            ]);

            // Update status pembayaran invoice (optional/legacy)
            $invoice = SalesInvoice::find($request->sales_invoice_id);
            $alreadyPaid = $invoice->payments()->sum("payment_amount");
            if ($alreadyPaid >= $invoice->total_amount) {
                $invoice->update(["payment_status" => "Paid", "status" => "Paid"]); 
            } else {
                $invoice->update(["payment_status" => "Partially Paid"]);
            }

            DB::commit();
            return redirect()->route("sales.payments.index")->with("success", "Pembayaran Termin berhasil dicatat.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with("error", "Gagal: " . $e->getMessage());
        }
    }

    public function show(SalesPayment $payment)
    {
        $payment->load(["salesInvoice.salesOrder.customer", "salesInvoice.salesOrder.project", "projectPaymentTerm", "creator"]);
        return view("sales.payments.show", compact("payment"));
    }

    public function printPdf(SalesPayment $payment)
    {
        $payment->load(["salesInvoice.salesOrder.customer", "salesInvoice.salesOrder.project", "projectPaymentTerm", "creator"]);
        $pdf = Pdf::loadView("sales.payments.pdf", compact("payment"));
        return $pdf->download($payment->payment_number . ".pdf");
    }

    public function getInvoiceInfo($id)
    {
        $invoice = SalesInvoice::with(["salesOrder.customer", "salesOrder.project.projectPaymentTerms.salesPayments"])->find($id);
        if (!$invoice) return response()->json(["error" => "Invoice not found"], 404);

        $project = $invoice->salesOrder->project;
        $customerName = $project ? $project->client_name : ($invoice->salesOrder->customer ? $invoice->salesOrder->customer->customer_name : '-');

        $terms = [];
        if ($project && $project->projectPaymentTerms) {
            foreach ($project->projectPaymentTerms as $term) {
                // Calculate due date
                $dueDate = \Carbon\Carbon::parse($invoice->invoice_date);
                if (strtolower($term->term_unit) == 'days') {
                    $dueDate->addDays($term->term_value);
                } else {
                    $dueDate->addMonths($term->term_value);
                }

                $terms[] = [
                    'id' => $term->id,
                    'top_type' => $term->top_type,
                    'percentage' => $term->percentage,
                    'term_value' => $term->term_value,
                    'term_unit' => $term->term_unit,
                    'nominal' => $term->nominal,
                    'total_paid' => $term->total_paid,
                    'remaining_amount' => $term->remaining_amount,
                    'status' => $term->payment_status,
                    'due_date' => $dueDate->format('Y-m-d')
                ];
            }
        }

        return response()->json([
            "customer_name" => $customerName,
            "project_name" => $project ? $project->project_name : '-',
            "sales_order_number" => $invoice->salesOrder->so_number,
            "invoice_number" => $invoice->invoice_number,
            "project_value" => $project ? $project->project_value : 0,
            "terms" => $terms
        ]);
    }
}
