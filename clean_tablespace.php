<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    DB::statement('DROP TABLESPACE activity_log');
    echo "Dropped tablespace successfully.\n";
} catch (\Exception $e) {
    echo "Failed to drop tablespace: " . $e->getMessage() . "\n";
}
