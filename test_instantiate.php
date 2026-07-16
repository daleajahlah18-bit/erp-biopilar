<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $x = new Google\Service\Drive\DriveFile(['name' => 'test']);
    echo "Success!";
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine();
}
