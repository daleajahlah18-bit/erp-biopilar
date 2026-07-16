<?php
$dir = __DIR__ . '/database/migrations/';
$files = glob($dir . '*.php');

$schemas = [
    'create_products_table' => <<<'EOT'
            $table->id();
            $table->string('product_code')->unique();
            $table->string('product_name');
            $table->enum('product_type', ['Bahan Baku', 'Bahan Jadi', 'Bill of Material']);
            $table->foreignId('unit_id')->constrained('units');
            $table->text('description')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
EOT,
    'create_suppliers_table' => <<<'EOT'
            $table->id();
            $table->string('supplier_name');
            $table->text('supplier_address')->nullable();
            $table->string('supplier_phone')->nullable();
            $table->string('supplier_email')->nullable();
            $table->string('bank_account')->nullable();
            $table->timestamps();
            $table->softDeletes();
EOT,
    'create_units_table' => <<<'EOT'
            $table->id();
            $table->string('unit_name')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
EOT,
    'create_projects_table' => <<<'EOT'
            $table->id();
            $table->string('project_name');
            $table->text('project_address')->nullable();
            $table->string('person_in_charge')->nullable();
            $table->timestamps();
            $table->softDeletes();
EOT,
    'create_warehouses_table' => <<<'EOT'
            $table->id();
            $table->string('warehouse_name');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
EOT,
    'create_purchase_orders_table' => <<<'EOT'
            $table->id();
            $table->string('po_number')->unique();
            $table->foreignId('supplier_id')->constrained('suppliers');
            $table->date('po_date');
            $table->text('project_note')->nullable();
            $table->decimal('total_amount', 18, 2)->default(0);
            $table->enum('status', ['Draft', 'Approved', 'Closed'])->default('Draft');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
EOT,
    'create_purchase_order_details_table' => <<<'EOT'
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('unit_id')->constrained('units');
            $table->decimal('quantity', 15, 4);
            $table->decimal('unit_price', 18, 2);
            $table->decimal('subtotal', 18, 2);
            $table->timestamps();
EOT,
    'create_goods_receipts_table' => <<<'EOT'
            $table->id();
            $table->string('gr_number')->unique();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders');
            $table->foreignId('warehouse_id')->constrained('warehouses');
            $table->string('received_by');
            $table->date('receipt_date');
            $table->timestamps();
EOT,
    'create_goods_receipt_details_table' => <<<'EOT'
            $table->id();
            $table->foreignId('goods_receipt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');
            $table->decimal('quantity_order', 15, 4);
            $table->decimal('quantity_received', 15, 4);
            $table->timestamps();
EOT,
    'create_purchase_payments_table' => <<<'EOT'
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders');
            $table->decimal('total_purchase', 18, 2);
            $table->decimal('total_paid', 18, 2)->default(0);
            $table->decimal('remaining_payment', 18, 2)->default(0);
            $table->enum('payment_status', ['Belum Dibayar', 'Setengah Dibayar', 'Lunas'])->default('Belum Dibayar');
            $table->date('payment_date')->nullable();
            $table->timestamps();
EOT,
    'create_bill_of_materials_table' => <<<'EOT'
            $table->id();
            $table->string('bom_number')->unique();
            $table->foreignId('product_id')->constrained('products');
            $table->text('description')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
EOT,
    'create_bill_of_material_details_table' => <<<'EOT'
            $table->id();
            $table->foreignId('bill_of_material_id')->constrained()->cascadeOnDelete();
            $table->foreignId('raw_material_id')->constrained('products');
            $table->decimal('quantity', 15, 4);
            $table->foreignId('unit_id')->constrained('units');
            $table->timestamps();
EOT,
    'create_production_orders_table' => <<<'EOT'
            $table->id();
            $table->string('production_number')->unique();
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('bill_of_material_id')->constrained('bill_of_materials');
            $table->decimal('quantity_target', 15, 4);
            $table->date('production_date');
            $table->string('person_in_charge');
            $table->enum('status', ['Draft','Released','In Progress','Finished','Cancelled'])->default('Draft');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
EOT,
    'create_material_issues_table' => <<<'EOT'
            $table->id();
            $table->string('issue_number')->unique();
            $table->foreignId('production_order_id')->constrained('production_orders');
            $table->foreignId('warehouse_id')->constrained('warehouses');
            $table->date('issue_date');
            $table->foreignId('issued_by')->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamps();
EOT,
    'create_material_issue_details_table' => <<<'EOT'
            $table->id();
            $table->foreignId('material_issue_id')->constrained()->cascadeOnDelete();
            $table->foreignId('raw_material_id')->constrained('products');
            $table->decimal('quantity_required', 15, 4);
            $table->decimal('quantity_issued', 15, 4);
            $table->foreignId('unit_id')->constrained('units');
            $table->timestamps();
EOT,
    'create_production_results_table' => <<<'EOT'
            $table->id();
            $table->string('result_number')->unique();
            $table->foreignId('production_order_id')->constrained('production_orders');
            $table->foreignId('warehouse_id')->constrained('warehouses');
            $table->date('result_date');
            $table->decimal('quantity_target', 15, 4);
            $table->decimal('quantity_finished', 15, 4);
            $table->decimal('quantity_reject', 15, 4)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
EOT,
    'create_stocks_table' => <<<'EOT'
            $table->id();
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('warehouse_id')->constrained('warehouses');
            $table->decimal('quantity', 15, 4)->default(0);
            $table->timestamps();
            $table->unique(['product_id', 'warehouse_id']);
EOT,
    'create_stock_opnames_table' => <<<'EOT'
            $table->id();
            $table->foreignId('warehouse_id')->constrained('warehouses');
            $table->foreignId('product_id')->constrained('products');
            $table->decimal('system_stock', 15, 4)->default(0);
            $table->decimal('physical_stock', 15, 4)->default(0);
            $table->decimal('difference', 15, 4)->default(0);
            $table->text('notes')->nullable();
            $table->date('opname_date');
            $table->timestamps();
EOT,
    'create_inventory_transfers_table' => <<<'EOT'
            $table->id();
            $table->string('transfer_number')->unique();
            $table->foreignId('source_warehouse_id')->constrained('warehouses');
            $table->foreignId('destination_warehouse_id')->constrained('warehouses');
            $table->date('transfer_date');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
EOT,
    'create_inventory_transfer_details_table' => <<<'EOT'
            $table->id();
            $table->foreignId('inventory_transfer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');
            $table->decimal('quantity', 15, 4);
            $table->timestamps();
EOT,
    'create_item_journals_table' => <<<'EOT'
            $table->id();
            $table->string('journal_number')->unique();
            $table->enum('transaction_type', ['Stock In', 'Stock Out', 'Transfer In', 'Transfer Out', 'Adjustment']);
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('warehouse_id')->constrained('warehouses');
            $table->decimal('quantity', 15, 4);
            $table->text('description');
            $table->string('reference_number')->nullable();
            $table->date('transaction_date');
            $table->timestamps();
EOT,
];

foreach ($files as $file) {
    foreach ($schemas as $key => $schema) {
        if (strpos($file, $key) !== false) {
            $content = file_get_contents($file);
            $pattern = '/Schema::create\([\'"][a-zA-Z_]+[\'"], function \(Blueprint \$table\) \{.*?\}\);/s';
            
            // Extract table name
            preg_match('/Schema::create\([\'"]([a-zA-Z_]+)[\'"]/', $content, $m);
            $tableName = $m[1] ?? '';
            
            $replacement = "Schema::create('{$tableName}', function (Blueprint \$table) {\n" . $schema . "\n        });";
            $content = preg_replace($pattern, $replacement, $content);
            file_put_contents($file, $content);
            echo "Updated $file\n";
        }
    }
}
