<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$project = \App\Models\Project::first();
if (!$project) {
    echo "No project found.\n";
    exit;
}

// Create dummy PDF
file_put_contents(__DIR__.'/test_upload.pdf', 'Dummy PDF content for testing OAuth upload');

$file = new \Illuminate\Http\UploadedFile(
    __DIR__.'/test_upload.pdf', 
    'test_upload.pdf', 
    'application/pdf', 
    null, 
    true
);

$request = \Illuminate\Http\Request::create('/project-documents', 'POST', [
    'project_id' => $project->id,
    'document_name' => 'OAuth E2E Test Document',
    'document_category' => 'Drawing',
    'version' => '1.0',
    'remarks' => 'Testing OAuth 2.0 Upload'
], [], ['file' => $file], ['HTTP_ACCEPT' => 'application/json']);

// Login as user 1
\Auth::loginUsingId(1);

$controller = $app->make(\App\Http\Controllers\Reports\ProjectDocumentController::class);
echo "Uploading...\n";
$response = $controller->store($request);
$content = $response->getContent();
echo "Upload Response: " . $content . "\n";

$json = json_decode($content, true);
if (isset($json['success']) && $json['success']) {
    $docId = $json['document']['id'];
    echo "Document ID: $docId\n";
    
    echo "\nDeleting...\n";
    $deleteResponse = $controller->destroy($docId);
    echo "Delete Response: " . $deleteResponse->getContent() . "\n";
}
