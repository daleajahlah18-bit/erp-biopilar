<?php

$controllers = [
    'Purchasing/PurchaseOrderController' => <<<'EOT'
<?php
namespace App\Http\Controllers\Purchasing;
use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    public function index() { $orders = PurchaseOrder::latest()->paginate(10); return view('purchasing.purchase_orders.index', compact('orders')); }
    public function create() { return view('purchasing.purchase_orders.create'); }
    public function store(Request $request) {}
    public function show(PurchaseOrder $purchase_order) { return view('purchasing.purchase_orders.show', compact('purchase_order')); }
    public function edit(PurchaseOrder $purchase_order) { return view('purchasing.purchase_orders.edit', compact('purchase_order')); }
    public function update(Request $request, PurchaseOrder $purchase_order) {}
    public function destroy(PurchaseOrder $purchase_order) { $purchase_order->delete(); return redirect()->route('purchasing.purchase-orders.index')->with('success', 'Deleted'); }
}
EOT,
    'Purchasing/GoodsReceiptController' => <<<'EOT'
<?php
namespace App\Http\Controllers\Purchasing;
use App\Http\Controllers\Controller;
use App\Models\GoodsReceipt;
use Illuminate\Http\Request;

class GoodsReceiptController extends Controller
{
    public function index() { $receipts = GoodsReceipt::latest()->paginate(10); return view('purchasing.goods_receipts.index', compact('receipts')); }
    public function create() { return view('purchasing.goods_receipts.create'); }
    public function store(Request $request) {}
    public function show(GoodsReceipt $goods_receipt) { return view('purchasing.goods_receipts.show', compact('goods_receipt')); }
    public function edit(GoodsReceipt $goods_receipt) { return view('purchasing.goods_receipts.edit', compact('goods_receipt')); }
    public function update(Request $request, GoodsReceipt $goods_receipt) {}
    public function destroy(GoodsReceipt $goods_receipt) { $goods_receipt->delete(); return redirect()->route('purchasing.goods-receipts.index')->with('success', 'Deleted'); }
}
EOT,
    'Production/BillOfMaterialController' => <<<'EOT'
<?php
namespace App\Http\Controllers\Production;
use App\Http\Controllers\Controller;
use App\Models\BillOfMaterial;
use Illuminate\Http\Request;

class BillOfMaterialController extends Controller
{
    public function index() { $boms = BillOfMaterial::latest()->paginate(10); return view('production.bom.index', compact('boms')); }
    public function create() { return view('production.bom.create'); }
    public function store(Request $request) {}
    public function show(BillOfMaterial $bom) { return view('production.bom.show', compact('bom')); }
    public function edit(BillOfMaterial $bom) { return view('production.bom.edit', compact('bom')); }
    public function update(Request $request, BillOfMaterial $bom) {}
    public function destroy(BillOfMaterial $bom) { $bom->delete(); return redirect()->route('production.bom.index')->with('success', 'Deleted'); }
}
EOT,
    'Production/ProductionOrderController' => <<<'EOT'
<?php
namespace App\Http\Controllers\Production;
use App\Http\Controllers\Controller;
use App\Models\ProductionOrder;
use Illuminate\Http\Request;

class ProductionOrderController extends Controller
{
    public function index() { $orders = ProductionOrder::latest()->paginate(10); return view('production.orders.index', compact('orders')); }
    public function create() { return view('production.orders.create'); }
    public function store(Request $request) {}
    public function show(ProductionOrder $order) { return view('production.orders.show', compact('order')); }
    public function edit(ProductionOrder $order) { return view('production.orders.edit', compact('order')); }
    public function update(Request $request, ProductionOrder $order) {}
    public function destroy(ProductionOrder $order) { $order->delete(); return redirect()->route('production.orders.index')->with('success', 'Deleted'); }
}
EOT,
    'Inventory/StockController' => <<<'EOT'
<?php
namespace App\Http\Controllers\Inventory;
use App\Http\Controllers\Controller;
use App\Models\Stock;

class StockController extends Controller
{
    public function index() { $stocks = Stock::latest()->paginate(10); return view('inventory.stocks.index', compact('stocks')); }
}
EOT,
    'Inventory/InventoryTransferController' => <<<'EOT'
<?php
namespace App\Http\Controllers\Inventory;
use App\Http\Controllers\Controller;
use App\Models\InventoryTransfer;
use Illuminate\Http\Request;

class InventoryTransferController extends Controller
{
    public function index() { $transfers = InventoryTransfer::latest()->paginate(10); return view('inventory.transfers.index', compact('transfers')); }
    public function create() { return view('inventory.transfers.create'); }
    public function store(Request $request) {}
    public function show(InventoryTransfer $transfer) { return view('inventory.transfers.show', compact('transfer')); }
    public function edit(InventoryTransfer $transfer) { return view('inventory.transfers.edit', compact('transfer')); }
    public function update(Request $request, InventoryTransfer $transfer) {}
    public function destroy(InventoryTransfer $transfer) { $transfer->delete(); return redirect()->route('inventory.transfers.index')->with('success', 'Deleted'); }
}
EOT,
];

foreach ($controllers as $name => $content) {
    $dir = dirname(__DIR__ . "/app/Http/Controllers/{$name}.php");
    if (!is_dir($dir)) mkdir($dir, 0777, true);
    file_put_contents(__DIR__ . "/app/Http/Controllers/{$name}.php", $content);
    echo "Updated Controller: $name\n";
}
