<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::create('/purchasing/purchase-orders', 'POST', [
    'po_date' => '2026-07-24',
    'supplier_id' => 1,
    'target_date' => '2026-08-01',
    'items' => [
        [
            'product_id' => 1,
            'unit_id' => 1,
            'qty' => 10,
            'price' => 1000
        ]
    ]
]);

$response = $kernel->handle($request);
echo "Status: " . $response->getStatusCode() . "\n";
echo $response->getContent();
