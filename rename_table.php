<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    DB::statement('RENAME TABLE activity_log TO activity_log_broken');
    echo "Renamed successfully.\n";
} catch (\Exception $e) {
    echo "Failed to rename: " . $e->getMessage() . "\n";
}
