<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $result = DB::select('REPAIR TABLE activity_log');
    print_r($result);
} catch (\Exception $e) {
    echo "Failed to repair: " . $e->getMessage() . "\n";
}

try {
    $result = DB::select('CHECK TABLE activity_log');
    print_r($result);
} catch (\Exception $e) {
    echo "Failed to check: " . $e->getMessage() . "\n";
}

