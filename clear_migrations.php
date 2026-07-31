<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    DB::table('migrations')->where('migration', 'like', '%activity_log%')->delete();
    echo "Removed migrations from table.\n";
} catch (\Exception $e) {
    echo "Failed to remove migrations: " . $e->getMessage() . "\n";
}
