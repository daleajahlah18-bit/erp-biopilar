<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$client = new Google\Client();
$client->setAuthConfig(storage_path('app/' . config('google-drive.credentials')));
$client->addScope(Google\Service\Drive::DRIVE);
$service = new Google\Service\Drive($client);
$optParams = [
    'q' => "name = 'test'",
    'spaces' => 'drive',
    'fields' => 'files(id, name)'
];

try {
    $results = $service->files->listFiles($optParams);
    echo "listFiles success!\n";
} catch (\Throwable $e) {
    echo "listFiles Error: " . $e->getMessage() . "\n";
}

try {
    $x = new Google\Service\Drive\DriveFile(['name' => 'test']);
    echo "new DriveFile success!\n";
} catch (\Throwable $e) {
    echo "new DriveFile Error: " . $e->getMessage() . "\n";
}
