<?php

$models = glob('app/Models/*.php');

foreach ($models as $file) {
    $content = file_get_contents($file);
    
    // Skip if already has LogsActivity
    if (strpos($content, 'LogsActivity') !== false) {
        continue;
    }
    
    // Add use statements
    if (strpos($content, 'use Spatie\Activitylog\Traits\LogsActivity;') === false) {
        $content = preg_replace('/namespace App\\\Models;\n/s', "namespace App\Models;\n\nuse Spatie\Activitylog\Traits\LogsActivity;\nuse Spatie\Activitylog\LogOptions;", $content, 1);
    }
    
    // Add trait inside class
    $content = preg_replace('/class\s+([A-Za-z0-9_]+)\s+extends\s+Model\s*\{/s', "class $1 extends Model\n{\n    use LogsActivity;", $content, 1);
    $content = preg_replace('/class\s+([A-Za-z0-9_]+)\s+extends\s+Authenticatable\s*\{/s', "class $1 extends Authenticatable\n{\n    use LogsActivity;", $content, 1);
    
    // Add method
    $method = "\n    public function getActivitylogOptions(): LogOptions\n    {\n        return LogOptions::defaults()->logAll()->logOnlyDirty()->dontSubmitEmptyLogs();\n    }\n";
    $content = preg_replace('/\{/', "{\n" . $method, $content, 1); // Not safe to just replace first {
    
    // Better way to add method at the end of class
    $content = preg_replace('/\}\s*$/s', $method . "\n}", $content);
    
    file_put_contents($file, $content);
    echo "Added LogsActivity to $file\n";
}
