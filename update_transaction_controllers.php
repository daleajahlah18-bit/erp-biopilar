<?php

$controllers = [
    'Purchasing/PurchaseOrderController' => <<<'EOT'
<?php
namespace App\Http\Controllers\Purchasing;
use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    public function index() { $orders = PurchaseOrder::with('supplier')->latest()->paginate(10); return view('purchasing.purchase_orders.index', compact('orders')); }
    public function create() { $suppliers = Supplier::all(); return view('purchasing.purchase_orders.create', compact('suppliers')); }
    public function store(Request $request) {}
    public function show(PurchaseOrder $purchase_order) {}
    public function edit(PurchaseOrder $purchase_order) {}
    public function update(Request $request, PurchaseOrder $purchase_order) {}
    public function destroy(PurchaseOrder $purchase_order) {}
}
EOT,
    'Purchasing/GoodsReceiptController' => <<<'EOT'
<?php
namespace App\Http\Controllers\Purchasing;
use App\Http\Controllers\Controller;
use App\Models\GoodsReceipt;
use App\Models\PurchaseOrder;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class GoodsReceiptController extends Controller
{
    public function index() { $receipts = GoodsReceipt::with(['purchaseOrder', 'warehouse'])->latest()->paginate(10); return view('purchasing.goods_receipts.index', compact('receipts')); }
    public function create() { 
        $orders = PurchaseOrder::where('status', 'Approved')->get();
        $warehouses = Warehouse::all();
        return view('purchasing.goods_receipts.create', compact('orders', 'warehouses')); 
    }
    public function store(Request $request) {}
    public function show(GoodsReceipt $goods_receipt) {}
    public function edit(GoodsReceipt $goods_receipt) {}
    public function update(Request $request, GoodsReceipt $goods_receipt) {}
    public function destroy(GoodsReceipt $goods_receipt) {}
}
EOT,
    'Production/BillOfMaterialController' => <<<'EOT'
<?php
namespace App\Http\Controllers\Production;
use App\Http\Controllers\Controller;
use App\Models\BillOfMaterial;
use App\Models\Product;
use Illuminate\Http\Request;

class BillOfMaterialController extends Controller
{
    public function index() { $boms = BillOfMaterial::with('product')->latest()->paginate(10); return view('production.bom.index', compact('boms')); }
    public function create() { $products = Product::where('product_type', 'Bahan Jadi')->get(); return view('production.bom.create', compact('products')); }
    public function store(Request $request) {}
    public function show(BillOfMaterial $bom) {}
    public function edit(BillOfMaterial $bom) {}
    public function update(Request $request, BillOfMaterial $bom) {}
    public function destroy(BillOfMaterial $bom) {}
}
EOT,
    'Production/ProductionOrderController' => <<<'EOT'
<?php
namespace App\Http\Controllers\Production;
use App\Http\Controllers\Controller;
use App\Models\ProductionOrder;
use App\Models\Product;
use App\Models\BillOfMaterial;
use Illuminate\Http\Request;

class ProductionOrderController extends Controller
{
    public function index() { $orders = ProductionOrder::with('product')->latest()->paginate(10); return view('production.orders.index', compact('orders')); }
    public function create() { 
        $products = Product::where('product_type', 'Bahan Jadi')->get();
        $boms = BillOfMaterial::all();
        return view('production.orders.create', compact('products', 'boms')); 
    }
    public function store(Request $request) {}
    public function show(ProductionOrder $order) {}
    public function edit(ProductionOrder $order) {}
    public function update(Request $request, ProductionOrder $order) {}
    public function destroy(ProductionOrder $order) {}
}
EOT,
    'Inventory/StockController' => <<<'EOT'
<?php
namespace App\Http\Controllers\Inventory;
use App\Http\Controllers\Controller;
use App\Models\Stock;

class StockController extends Controller
{
    public function index() { $stocks = Stock::with(['product', 'warehouse'])->latest()->paginate(20); return view('inventory.stocks.index', compact('stocks')); }
}
EOT,
    'Inventory/InventoryTransferController' => <<<'EOT'
<?php
namespace App\Http\Controllers\Inventory;
use App\Http\Controllers\Controller;
use App\Models\InventoryTransfer;
use App\Models\Warehouse;
use App\Models\Product;
use Illuminate\Http\Request;

class InventoryTransferController extends Controller
{
    public function index() { $transfers = InventoryTransfer::with(['sourceWarehouse', 'destinationWarehouse'])->latest()->paginate(10); return view('inventory.transfers.index', compact('transfers')); }
    public function create() { 
        $warehouses = Warehouse::all();
        $products = Product::all();
        return view('inventory.transfers.create', compact('warehouses', 'products')); 
    }
    public function store(Request $request) {}
    public function show(InventoryTransfer $transfer) {}
    public function edit(InventoryTransfer $transfer) {}
    public function update(Request $request, InventoryTransfer $transfer) {}
    public function destroy(InventoryTransfer $transfer) {}
}
EOT,
];

foreach ($controllers as $name => $content) {
    file_put_contents(__DIR__ . "/app/Http/Controllers/{$name}.php", $content);
    echo "Updated Controller: $name\n";
}
