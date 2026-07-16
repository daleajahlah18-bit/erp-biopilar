<?php

$dir = __DIR__ . '/app/Models';
$files = glob($dir . '/*.php');

foreach ($files as $file) {
    $content = file_get_contents($file);

    // 1. Replace Imports
    $content = str_replace(
        'use Spatie\Activitylog\Traits\LogsActivity;',
        'use App\Traits\EnterpriseAuditTrail;',
        $content
    );
    // Remove LogOptions import if it's there to clean up
    $content = str_replace("use Spatie\Activitylog\LogOptions;\n", "", $content);
    $content = str_replace("use Spatie\Activitylog\LogOptions;\r\n", "", $content);

    // 2. Replace Trait usage inside class (handle different spacings or multiple uses)
    $content = preg_replace('/use\s+([^;\{]*)(LogsActivity)([^;]*);/', 'use $1EnterpriseAuditTrail$3;', $content);

    // 3. Remove the entire getActivitylogOptions method
    $content = preg_replace('/public function getActivitylogOptions\(\).*?\{.*?\}/s', '', $content);

    file_put_contents($file, $content);
    echo "Refactored: " . basename($file) . "\n";
}
echo "Done!\n";
