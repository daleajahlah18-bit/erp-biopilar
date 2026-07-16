<?php
require __DIR__.'/vendor/autoload.php';

$x = new Google\Service\Drive\DriveFile();
echo "Success: " . get_class($x);
