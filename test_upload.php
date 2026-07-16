<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

var_dump(class_exists('Google\Service\Drive\DriveFile'));

$project = \App\Models\Project::first();
if (!$project) {
    echo "No project found.";
    exit;
}

$file = new \Illuminate\Http\UploadedFile(__DIR__.'/dummy.txt', 'dummy.txt', 'text/plain', null, true);

$request = \Illuminate\Http\Request::create('/project-documents', 'POST', [
    'project_id' => $project->id,
    'document_name' => 'Test Dummy Document',
    'document_category' => 'Drawing',
    'version' => '1.0',
    'remarks' => 'Test upload'
], [], ['file' => $file], ['HTTP_ACCEPT' => 'application/json']);

$response = app()->handle($request);
echo "Status: " . $response->getStatusCode() . "\n";
echo "Content: " . $response->getContent() . "\n";
