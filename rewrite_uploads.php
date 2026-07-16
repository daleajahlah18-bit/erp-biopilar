<?php
$files = [
    'app/Http/Controllers/Master/ProjectController.php',
    'app/Http/Controllers/UserManagement/UserController.php',
    'app/Http/Controllers/ProjectReport/DailyReportController.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        $content = preg_replace_callback('/(\$data\[\'([^\']+)\'\]|\$reportData\[\'([^\']+)\'\]|\$photoPath)\s*=\s*\$file->storeAs\(\'([^\']+)\',\s*\$filename,\s*\'public\'\);/', function($m) {
            $assign = $m[1];
            $dir = $m[4];
            return "\\Intervention\\Image\\ImageManagerStatic::make(\$file)->encode(\$file->extension(), 90)->save(storage_path('app/public/{$dir}/' . \$filename));\n                {$assign} = '{$dir}/' . \$filename;";
        }, $content);
        
        file_put_contents($file, $content);
        echo "Updated $file\n";
    }
}
