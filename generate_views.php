<?php

function getViewTemplate($title, $type) {
    if ($type === 'index') {
        return "@extends('layouts.app')\n@section('page_title', '{$title}')\n@section('content')\n<div class=\"card\">\n    <div class=\"card-body\">\n        <div class=\"d-flex justify-content-between mb-3\">\n            <h5 class=\"card-title\">Daftar {$title}</h5>\n            <a href=\"#\" class=\"btn-primary-custom\">Tambah Data</a>\n        </div>\n        <table class=\"table-custom\">\n            <thead><tr><th>ID</th><th>Keterangan</th><th>Aksi</th></tr></thead>\n            <tbody><tr><td colspan=\"3\" class=\"text-center\">Belum ada data</td></tr></tbody>\n        </table>\n    </div>\n</div>\n@endsection";
    }
    if ($type === 'create') {
        return "@extends('layouts.app')\n@section('page_title', 'Tambah {$title}')\n@section('content')\n<div class=\"card\" style=\"max-width: 600px;\">\n    <div class=\"card-body\">\n        <form method=\"POST\" action=\"#\">\n            @csrf\n            <div class=\"mb-3\">\n                <label class=\"form-label\">Input Data</label>\n                <input type=\"text\" class=\"form-control\" name=\"data\">\n            </div>\n            <div class=\"d-flex justify-content-end gap-2\">\n                <button type=\"button\" class=\"btn-outline-custom\">Batal</button>\n                <button type=\"submit\" class=\"btn-primary-custom\">Simpan</button>\n            </div>\n        </form>\n    </div>\n</div>\n@endsection";
    }
    if ($type === 'edit') {
        return "@extends('layouts.app')\n@section('page_title', 'Edit {$title}')\n@section('content')\n<div class=\"card\" style=\"max-width: 600px;\">\n    <div class=\"card-body\">\n        <form method=\"POST\" action=\"#\">\n            @csrf @method('PUT')\n            <div class=\"mb-3\">\n                <label class=\"form-label\">Update Data</label>\n                <input type=\"text\" class=\"form-control\" name=\"data\">\n            </div>\n            <div class=\"d-flex justify-content-end gap-2\">\n                <button type=\"button\" class=\"btn-outline-custom\">Batal</button>\n                <button type=\"submit\" class=\"btn-primary-custom\">Update</button>\n            </div>\n        </form>\n    </div>\n</div>\n@endsection";
    }
    if ($type === 'show') {
        return "@extends('layouts.app')\n@section('page_title', 'Detail {$title}')\n@section('content')\n<div class=\"card\">\n    <div class=\"card-body\">\n        Detail lengkap {$title}.\n    </div>\n</div>\n@endsection";
    }
}

$views = [
    // Purchasing
    'purchasing/purchase_orders/index.blade.php' => getViewTemplate('Purchase Order', 'index'),
    'purchasing/purchase_orders/create.blade.php' => getViewTemplate('Purchase Order', 'create'),
    'purchasing/purchase_orders/edit.blade.php' => getViewTemplate('Purchase Order', 'edit'),
    'purchasing/purchase_orders/show.blade.php' => getViewTemplate('Purchase Order', 'show'),
    'purchasing/goods_receipts/index.blade.php' => getViewTemplate('Goods Receipt', 'index'),
    'purchasing/goods_receipts/create.blade.php' => getViewTemplate('Goods Receipt', 'create'),
    'purchasing/goods_receipts/edit.blade.php' => getViewTemplate('Goods Receipt', 'edit'),
    'purchasing/goods_receipts/show.blade.php' => getViewTemplate('Goods Receipt', 'show'),
    
    // Production
    'production/bom/index.blade.php' => getViewTemplate('Bill Of Material', 'index'),
    'production/bom/create.blade.php' => getViewTemplate('Bill Of Material', 'create'),
    'production/bom/edit.blade.php' => getViewTemplate('Bill Of Material', 'edit'),
    'production/bom/show.blade.php' => getViewTemplate('Bill Of Material', 'show'),
    'production/orders/index.blade.php' => getViewTemplate('Production Order', 'index'),
    'production/orders/create.blade.php' => getViewTemplate('Production Order', 'create'),
    'production/orders/edit.blade.php' => getViewTemplate('Production Order', 'edit'),
    'production/orders/show.blade.php' => getViewTemplate('Production Order', 'show'),
    
    // Inventory
    'inventory/stocks/index.blade.php' => getViewTemplate('Stok Produk', 'index'),
    'inventory/transfers/index.blade.php' => getViewTemplate('Transfer Stok', 'index'),
    'inventory/transfers/create.blade.php' => getViewTemplate('Transfer Stok', 'create'),
    'inventory/transfers/edit.blade.php' => getViewTemplate('Transfer Stok', 'edit'),
    'inventory/transfers/show.blade.php' => getViewTemplate('Transfer Stok', 'show'),
    
    // Master
    'master/suppliers/create.blade.php' => getViewTemplate('Supplier', 'create'),
    'master/suppliers/edit.blade.php' => getViewTemplate('Supplier', 'edit'),
    'master/units/create.blade.php' => getViewTemplate('Unit', 'create'),
    'master/units/edit.blade.php' => getViewTemplate('Unit', 'edit'),
    'master/projects/create.blade.php' => getViewTemplate('Project', 'create'),
    'master/projects/edit.blade.php' => getViewTemplate('Project', 'edit'),
    'master/warehouses/create.blade.php' => getViewTemplate('Warehouse', 'create'),
    'master/warehouses/edit.blade.php' => getViewTemplate('Warehouse', 'edit'),
];

foreach ($views as $path => $content) {
    $dir = dirname(__DIR__ . "/resources/views/{$path}");
    if (!is_dir($dir)) mkdir($dir, 0777, true);
    file_put_contents(__DIR__ . "/resources/views/{$path}", $content);
    echo "Updated view: $path\n";
}
