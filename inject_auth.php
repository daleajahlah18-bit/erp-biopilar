<?php
$map = [
    'Master/ProjectController.php' => 'App\Models\Project',
    'UserManagement/UserController.php' => 'App\Models\User',
    'UserManagement/RoleController.php' => 'Spatie\Permission\Models\Role',
    'Master/CustomerController.php' => 'App\Models\Customer',
    'Master/SupplierController.php' => 'App\Models\Supplier',
    'Master/ProductController.php' => 'App\Models\Product',
    'Purchasing/PurchaseOrderController.php' => 'App\Models\PurchaseOrder',
    'Purchasing/GoodsReceiptController.php' => 'App\Models\GoodsReceipt',
    'Purchasing/PurchasePaymentController.php' => 'App\Models\PurchasePayment',
    'Sales/SalesOrderController.php' => 'App\Models\SalesOrder',
    'Sales/SalesInvoiceController.php' => 'App\Models\SalesInvoice',
    'Sales/SalesPaymentController.php' => 'App\Models\SalesPayment',
    'AssetManagement/AssetController.php' => 'App\Models\Asset',
    'ProjectReport/DailyReportController.php' => 'App\Models\DailyReport',
    'Inventory/StockOpnameController.php' => 'App\Models\StockOpname',
    'Inventory/InventoryTransferController.php' => 'App\Models\InventoryTransfer'
];

foreach ($map as $file => $model) {
    $path = 'app/Http/Controllers/' . $file;
    if (file_exists($path)) {
        $content = file_get_contents($path);
        
        $parts = explode('\\', $model);
        $modelName = end($parts);
        
        if (strpos($content, 'authorizeResource') === false) {
            $constructCall = "\$this->authorizeResource($modelName::class, strtolower('$modelName'));";
            
            if (strpos($content, '__construct') !== false) {
                // Insert into existing construct
                $content = preg_replace('/public function __construct\([^)]*\)\s*\{/', "$0\n        $constructCall", $content);
            } else {
                // Create construct
                $construct = "\n    public function __construct()\n    {\n        $constructCall\n    }\n";
                $content = preg_replace('/class [^{]+{\n/', "$0" . $construct, $content, 1);
            }
            
            file_put_contents($path, $content);
            echo "Updated $file\n";
        }
    }
}
