<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\SalesOrder;
use App\Models\SalesOrderDetail;
use App\Models\Project;
use App\Models\Product;
use App\Services\NumberGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class SalesOrderController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(SalesOrder::class, strtolower('SalesOrder'));
    }
    public function index()
    {
        // Changed customer to project
        $orders = SalesOrder::with(["project", "creator"])->sortable()->latest()->sortable()->paginate(10);
        return view("sales.orders.index", compact("orders"));
    }

    public function create()
    {
        // Changed Customer to Project
        $projects = Project::where('project_status', '!=', 'Completed')->get();
        $products = Product::with("unit")->get();
        return view("sales.orders.create", compact("projects", "products"));
    }

    public function store(Request $request, NumberGeneratorService $numGen)
    {
        $request->validate([
            "project_id" => "required|exists:projects,id",
            "sales_order_date" => "required|date",
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

            $order = SalesOrder::create([
                "sales_order_number" => $numGen->generate("SO", SalesOrder::class, "sales_order_number"),
                "project_id" => $request->project_id,
                "sales_order_date" => $request->sales_order_date,
                "total_amount" => $request->total_amount,
                "notes" => $request->notes,
                "status" => "Confirmed", // Langsung Confirmed
                "created_by" => auth()->id()
            ]);

            if ($request->has('products') && is_array($request->products)) {
                foreach ($request->products as $item) {
                    $product = Product::find($item["product_id"]);
                    SalesOrderDetail::create([
                        "sales_order_id" => $order->id,
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
                    \App\Models\SalesOrderService::create([
                        "sales_order_id" => $order->id,
                        "service_name" => $service["service_name"],
                        "quantity" => $service["quantity"],
                        "unit_price" => $service["unit_price"],
                        "subtotal" => $service["subtotal"],
                        "notes" => $service["notes"] ?? null,
                    ]);
                }
            }

            DB::commit();

            // Recalculate Project Value
            $costingService = new \App\Services\ProjectCostingService();
            $costingService->recalculateProject($order->project);

            return redirect()->route("sales.orders.index")->with("success", "Sales Order berhasil dibuat.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with("error", "Gagal: " . $e->getMessage());
        }
    }

    public function show(SalesOrder $order)
    {
        $order->load(["project", "details.product.unit", "services", "creator"]);
        return view("sales.orders.show", compact("order"));
    }

    public function printPdf(SalesOrder $order)
    {
        $order->load(["project", "details.product.unit", "creator"]);
        $pdf = Pdf::loadView("sales.orders.pdf", compact("order"));
        return $pdf->download($order->sales_order_number . ".pdf");
    }

    public function getProductInfo($id)
    {
        $product = Product::with("unit")->find($id);
        if (!$product) return response()->json(["error" => "Product not found"], 404);

        // Get Stock
        $stock = DB::table("stocks")
            ->where("product_id", $id)
            ->sum("quantity");

        return response()->json([
            "unit_name" => $product->unit->unit_name ?? "-",
            "unit_price" => 0, // Default 0
            "available_stock" => $stock
        ]);
    }

    public function getProjectInfo($id)
    {
        $project = Project::find($id);
        if (!$project) return response()->json(["error" => "Project not found"], 404);

        return response()->json([
            "client_name" => $project->client_name,
            "client_po_date" => $project->client_po_date ? \Carbon\Carbon::parse($project->client_po_date)->format('Y-m-d') : null,
            "project_value" => $project->project_value,
            "terms_of_payment" => $project->terms_of_payment_value ? $project->terms_of_payment_value . ' ' . $project->terms_of_payment_type : '-',
            "project_status" => $project->project_status
        ]);
    }
}
