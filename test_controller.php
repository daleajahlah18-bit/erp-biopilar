<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$project = \App\Models\Project::first();
$file = new \Illuminate\Http\UploadedFile(__DIR__.'/dummy.pdf', 'dummy.pdf', 'application/pdf', null, true);

$request = \Illuminate\Http\Request::create('/project-documents', 'POST', [
    'project_id' => $project->id,
    'document_name' => 'Test Dummy Document',
    'document_category' => 'Drawing',
    'version' => '1.0',
    'remarks' => 'Test upload'
], [], ['file' => $file], ['HTTP_ACCEPT' => 'application/json']);

// Login as user 1
\Auth::loginUsingId(1);

$controller = $app->make(\App\Http\Controllers\Reports\ProjectDocumentController::class);
$response = $controller->store($request);
echo "Content: " . $response->getContent() . "\n";


