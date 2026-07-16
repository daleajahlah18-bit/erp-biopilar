<?php
$directories = ['app', 'database', 'resources', 'routes', 'config'];

foreach ($directories as $dir) {
    if (!is_dir($dir)) continue;
    
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $f) {
        if ($f->isFile() && str_ends_with($f->getFilename(), '.php')) {
            $path = $f->getPathname();
            $c = file_get_contents($path);
            if (substr($c, 0, 3) === pack('CCC', 0xef, 0xbb, 0xbf)) {
                file_put_contents($path, substr($c, 3));
                echo "Removed BOM from $path\n";
            }
        }
    }
}

