<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    DB::statement('TRUNCATE TABLE activity_log');
    echo "Activity log truncated successfully.\n";
} catch (\Exception $e) {
    echo "Failed to truncate: " . $e->getMessage() . "\n";
    echo "Attempting to drop and recreate...\n";
    DB::statement('DROP TABLE IF EXISTS activity_log');
}
