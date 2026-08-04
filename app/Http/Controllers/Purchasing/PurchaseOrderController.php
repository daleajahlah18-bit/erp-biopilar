<?php
namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Project;
use App\Models\Unit;
use App\Services\NumberGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class PurchaseOrderController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(PurchaseOrder::class, strtolower('PurchaseOrder'));
    }
    public function index() {
        $orders = PurchaseOrder::with('supplier')->sortable()->latest()->sortable()->paginate(10);
        return view('purchasing.purchase_orders.index', compact('orders'));
    }

    public function create() {
        $suppliers = Supplier::all();
        $products = Product::all();
        $units = Unit::all();
        $projects = Project::all();
        return view('purchasing.purchase_orders.create', compact('suppliers', 'products', 'units', 'projects'));
    }

    public function store(Request $request, NumberGeneratorService $numberGenerator) {
        $request->validate([
            'po_date' => 'required|date',
            'supplier_id' => 'required|exists:suppliers,id',
            'project_id' => 'nullable|exists:projects,id',
            'project_note' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.is_new_product' => 'nullable|boolean',
            'items.*.product_id' => 'exclude_if:items.*.is_new_product,1|required|exists:products,id',
            'items.*.product_code' => 'required_if:items.*.is_new_product,1|nullable|distinct|unique:products,product_code',
            'items.*.product_name' => 'required_if:items.*.is_new_product,1|nullable',
            'items.*.product_type' => 'required_if:items.*.is_new_product,1|nullable|in:Bahan Baku,Bahan Jadi,Bill of Material',
            'items.*.engineering_category' => 'required_if:items.*.is_new_product,1|nullable|in:Civil,Mechanical,Electrical',
            'items.*.unit_id' => 'required|exists:units,id',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.price' => 'required|numeric|min:0.01',
        ]);

        try {
            DB::beginTransaction();

            $totalAmount = 0;
            $itemsData = [];

            foreach ($request->items as $item) {
                if (!empty($item['is_new_product'])) {
                    $product = Product::create([
                        'product_code' => $item['product_code'],
                        'product_name' => $item['product_name'],
                        'product_type' => $item['product_type'],
                        'engineering_category' => $item['engineering_category'],
                        'unit_id' => $item['unit_id'],
                        'created_by' => auth()->id(),
                    ]);
                    $productId = $product->id;
                } else {
                    $productId = $item['product_id'];
                }

                $subtotal = $item['qty'] * $item['price'];
                $totalAmount += $subtotal;
                $itemsData[] = [
                    'product_id' => $productId,
                    'unit_id' => $item['unit_id'],
                    'quantity' => $item['qty'],
                    'unit_price' => $item['price'],
                    'subtotal' => $subtotal,
                ];
            }

            $is_ppn = $request->has('is_ppn') ? true : false;
            $ppn_percentage = $is_ppn ? 11 : 0;
            $ppn_amount = $is_ppn ? ($totalAmount * ($ppn_percentage / 100)) : 0;
            $grand_total = $totalAmount + $ppn_amount;

            $po = PurchaseOrder::create([
                'po_number' => $numberGenerator->generate('PO', PurchaseOrder::class, 'po_number'),
                'po_date' => $request->po_date,
                'supplier_id' => $request->supplier_id,
                'project_id' => $request->project_id,
                'project_note' => $request->project_note,
                'total_amount' => $totalAmount,
                'is_ppn' => $is_ppn,
                'ppn_percentage' => $ppn_percentage,
                'ppn_amount' => $ppn_amount,
                'grand_total' => $grand_total,
                'status' => 'Approved',
                'created_by' => auth()->id(),
            ]);

            foreach ($itemsData as $data) {
                $po->details()->create($data);
            }

            DB::commit();

            return redirect()->route('purchasing.purchase-orders.show', $po->id)
                             ->with('success', 'Purchase Release berhasil dibuat.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal membuat PO: ' . $e->getMessage());
        }
    }

    public function show(PurchaseOrder $purchase_order) {
        $purchase_order->load(['supplier', 'details.product', 'details.unit', 'creator']);
        return view('purchasing.purchase_orders.show', compact('purchase_order'));
    }

    public function printPdf(PurchaseOrder $purchase_order) {
        $purchase_order->load(['supplier', 'details.product', 'details.unit', 'creator']);
        
        $pdf = Pdf::loadView('purchasing.purchase_orders.pdf', ['purchaseOrder' => $purchase_order])->setPaper('A4');
        return $pdf->download("PO_{$purchase_order->po_number}.pdf");
    }

    public function edit(PurchaseOrder $purchase_order) {
        if ($purchase_order->goodsReceipts()->exists()) {
            return redirect()->route('purchasing.purchase-orders.show', $purchase_order->id)
                             ->with('error', 'Purchase Release tidak dapat diedit karena sudah memiliki Goods Receipt atau Pembayaran.');
        }

        $purchase_order->load('details');
        $suppliers = Supplier::all();
        $products = Product::all();
        $units = Unit::all();
        $projects = Project::all();

        return view('purchasing.purchase_orders.edit', compact('purchase_order', 'suppliers', 'products', 'units', 'projects'));
    }

    public function update(Request $request, PurchaseOrder $purchase_order) {
        if ($purchase_order->goodsReceipts()->exists()) {
            return redirect()->route('purchasing.purchase-orders.show', $purchase_order->id)
                             ->with('error', 'Purchase Release tidak dapat diedit karena sudah memiliki Goods Receipt atau Pembayaran.');
        }

        $request->validate([
            'po_date' => 'required|date',
            'supplier_id' => 'required|exists:suppliers,id',
            'project_id' => 'nullable|exists:projects,id',
            'project_note' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.is_new_product' => 'nullable|boolean',
            'items.*.product_id' => 'exclude_if:items.*.is_new_product,1|required|exists:products,id',
            'items.*.product_code' => 'required_if:items.*.is_new_product,1|nullable|distinct|unique:products,product_code',
            'items.*.product_name' => 'required_if:items.*.is_new_product,1|nullable',
            'items.*.product_type' => 'required_if:items.*.is_new_product,1|nullable|in:Bahan Baku,Bahan Jadi,Bill of Material',
            'items.*.engineering_category' => 'required_if:items.*.is_new_product,1|nullable|in:Civil,Mechanical,Electrical',
            'items.*.unit_id' => 'required|exists:units,id',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.price' => 'required|numeric|min:0.01',
        ]);

        try {
            DB::beginTransaction();

            $totalAmount = 0;
            $itemsData = [];

            foreach ($request->items as $item) {
                if (!empty($item['is_new_product'])) {
                    $product = Product::create([
                        'product_code' => $item['product_code'],
                        'product_name' => $item['product_name'],
                        'product_type' => $item['product_type'],
                        'engineering_category' => $item['engineering_category'],
                        'unit_id' => $item['unit_id'],
                        'created_by' => auth()->id(),
                    ]);
                    $productId = $product->id;
                } else {
                    $productId = $item['product_id'];
                }

                $subtotal = $item['qty'] * $item['price'];
                $totalAmount += $subtotal;
                $itemsData[] = [
                    'product_id' => $productId,
                    'unit_id' => $item['unit_id'],
                    'quantity' => $item['qty'],
                    'unit_price' => $item['price'],
                    'subtotal' => $subtotal,
                ];
            }

            $is_ppn = $request->has('is_ppn') ? true : false;
            $ppn_percentage = $is_ppn ? 11 : 0;
            $ppn_amount = $is_ppn ? ($totalAmount * ($ppn_percentage / 100)) : 0;
            $grand_total = $totalAmount + $ppn_amount;

            $purchase_order->update([
                'po_date' => $request->po_date,
                'supplier_id' => $request->supplier_id,
                'project_id' => $request->project_id,
                'project_note' => $request->project_note,
                'total_amount' => $totalAmount,
                'is_ppn' => $is_ppn,
                'ppn_percentage' => $ppn_percentage,
                'ppn_amount' => $ppn_amount,
                'grand_total' => $grand_total,
            ]);

            // Delete old details and insert new ones
            $purchase_order->details()->delete();
            foreach ($itemsData as $data) {
                $purchase_order->details()->create($data);
            }

            DB::commit();

            return redirect()->route('purchasing.purchase-orders.show', $purchase_order->id)
                             ->with('success', 'Purchase Release berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal memperbarui PO: ' . $e->getMessage());
        }
    }
    public function destroy(PurchaseOrder $purchase_order) {
        $purchase_order->delete();
        return redirect()->route('purchasing.purchase-orders.index')->with('success', 'Deleted');
    }
}
