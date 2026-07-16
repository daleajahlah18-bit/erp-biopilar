<?php

$models = glob('app/Models/*.php');

$method = "\n    public function getActivitylogOptions(): LogOptions\n    {\n        return LogOptions::defaults()->logAll()->logOnlyDirty()->dontSubmitEmptyLogs();\n    }\n";

foreach ($models as $file) {
    $content = file_get_contents($file);
    
    // Check if it appears twice
    if (substr_count($content, 'getActivitylogOptions') > 1) {
        // Remove the first occurrence (which is right after the class opening brace)
        // Or just replace the exact method string once
        $content = preg_replace('/' . preg_quote($method, '/') . '/', '', $content, 1);
        file_put_contents($file, $content);
        echo "Fixed duplicate in $file\n";
    }
}
