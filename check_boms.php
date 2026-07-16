<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$boms = App\Models\BillOfMaterial::all();
foreach($boms as $bom) {
    echo "ID: {$bom->id}, Name: {$bom->bom_name}, Product ID: {$bom->product_id}\n";
}
