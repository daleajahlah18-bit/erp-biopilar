<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

print_r(DB::select('DESCRIBE bill_of_materials'));
print_r(DB::select('DESCRIBE bill_of_material_details'));
