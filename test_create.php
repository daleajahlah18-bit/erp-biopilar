<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$client = new Google\Client();
$client->setAuthConfig(storage_path('app/' . config('google-drive.credentials')));
$client->addScope(Google\Service\Drive::DRIVE);
$service = new Google\Service\Drive($client);

try {
    $fileMetadata = new \Google\Service\Drive\DriveFile([
        'name' => 'test_folder',
        'mimeType' => 'application/vnd.google-apps.folder'
    ]);
    $folder = $service->files->create($fileMetadata, [
        'fields' => 'id'
    ]);
    echo "create success: " . $folder->id . "\n";
} catch (\Throwable $e) {
    echo "create Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine() . "\n";
}
