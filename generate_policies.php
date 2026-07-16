<?php
$policies = [
    'Project' => 'projects',
    'Customer' => 'customers',
    'Supplier' => 'suppliers',
    'Product' => 'products',
    'AssetCategory' => 'master_categories',
    'PurchaseOrder' => 'purchase_order',
    'GoodsReceipt' => 'goods_receipt',
    'PurchasePayable' => 'accounts_payable',
    'PurchasePayment' => 'purchase_payment',
    'SalesOrder' => 'sales_order',
    'SalesInvoice' => 'sales_invoice',
    'SalesPayment' => 'sales_payment',
    'Stock' => 'product_stock',
    'InventoryTransfer' => 'stock_transfer',
    'StockOpname' => 'stock_opname',
    'Asset' => 'master_assets',
    'DailyReport' => 'daily_report',
    'ProjectReport' => 'project_report',
    'User' => 'users',
    'Role' => 'roles',
];

foreach ($policies as $model => $module) {
    $content = "<?php\n\nnamespace App\Policies;\n\nclass {$model}Policy extends BasePolicy\n{\n    protected \$module = '{$module}';\n}\n";
    file_put_contents('app/Policies/' . $model . 'Policy.php', $content);
    echo "Created {$model}Policy\n";
}
