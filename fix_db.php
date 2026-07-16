<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    DB::statement('ALTER TABLE bill_of_material_details DROP FOREIGN KEY bill_of_material_details_raw_material_id_foreign');
    echo "Dropped foreign key.\n";
} catch (\Exception $e) {
    echo "Error dropping foreign key: " . $e->getMessage() . "\n";
}

try {
    DB::statement('ALTER TABLE bill_of_material_details CHANGE raw_material_id product_id BIGINT UNSIGNED NOT NULL;');
    echo "Renamed column.\n";
} catch (\Exception $e) {
    echo "Error renaming column: " . $e->getMessage() . "\n";
}

try {
    DB::statement('ALTER TABLE bill_of_material_details ADD CONSTRAINT bill_of_material_details_product_id_foreign FOREIGN KEY (product_id) REFERENCES products(id);');
    echo "Added foreign key.\n";
} catch (\Exception $e) {
    echo "Error adding foreign key: " . $e->getMessage() . "\n";
}
