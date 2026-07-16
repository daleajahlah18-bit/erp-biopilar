<?php

$modules = [
    'suppliers' => [
        'title' => 'Supplier',
        'route' => 'master.suppliers',
        'var' => 'supplier',
        'fields' => [
            'supplier_name' => ['label' => 'Nama Supplier', 'type' => 'text'],
            'supplier_address' => ['label' => 'Alamat', 'type' => 'textarea'],
            'supplier_phone' => ['label' => 'No. Telepon', 'type' => 'text'],
            'supplier_email' => ['label' => 'Email', 'type' => 'email'],
            'bank_account' => ['label' => 'Rekening Bank', 'type' => 'text'],
        ]
    ],
    'units' => [
        'title' => 'Unit',
        'route' => 'master.units',
        'var' => 'unit',
        'fields' => [
            'unit_name' => ['label' => 'Nama Unit', 'type' => 'text'],
            'description' => ['label' => 'Deskripsi', 'type' => 'textarea'],
        ]
    ],
    'projects' => [
        'title' => 'Project',
        'route' => 'master.projects',
        'var' => 'project',
        'fields' => [
            'project_name' => ['label' => 'Nama Project', 'type' => 'text'],
            'project_address' => ['label' => 'Alamat Project', 'type' => 'textarea'],
            'person_in_charge' => ['label' => 'Penanggung Jawab', 'type' => 'text'],
        ]
    ],
    'warehouses' => [
        'title' => 'Gudang',
        'route' => 'master.warehouses',
        'var' => 'warehouse',
        'fields' => [
            'warehouse_name' => ['label' => 'Nama Gudang', 'type' => 'text'],
            'description' => ['label' => 'Deskripsi', 'type' => 'textarea'],
        ]
    ],
];

foreach ($modules as $folder => $config) {
    $title = $config['title'];
    $route = $config['route'];
    $var = $config['var'];
    $fields = $config['fields'];
    
    // Generate Index
    $th = "";
    $td = "";
    foreach ($fields as $field => $info) {
        $th .= "<th>{$info['label']}</th>";
        $td .= "<td>{{ \${$var}->{$field} }}</td>";
    }
    
    $index = <<<EOT
@extends('layouts.app')
@section('title', '{$title}')
@section('page_title', 'Master {$title}')
@section('header_actions')
<a href="{{ route('{$route}.create') }}" class="btn-primary-custom"><i class="bi bi-plus-lg"></i> Tambah {$title}</a>
@endsection
@section('content')
@if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table-custom">
                <thead><tr>{$th}<th class="text-end">Aksi</th></tr></thead>
                <tbody>
                    @forelse(\${$folder} as \${$var})
                    <tr>
                        {$td}
                        <td class="text-end">
                            <a href="{{ route('{$route}.edit', \${$var}) }}" class="btn btn-sm btn-outline-secondary me-1"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('{$route}.destroy', \${$var}) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Yakin hapus?')"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="10" class="text-center py-4 text-secondary">Belum ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if(\${$folder}->hasPages()) <div class="card-footer bg-white border-top">{{ \${$folder}->links() }}</div> @endif
</div>
@endsection
EOT;

    // Generate Create
    $formInputs = "";
    foreach ($fields as $field => $info) {
        if ($info['type'] === 'textarea') {
            $formInputs .= <<<HTML
            <div class="mb-3">
                <label class="form-label">{$info['label']}</label>
                <textarea name="{$field}" class="form-control" rows="3">{{ old('{$field}') }}</textarea>
            </div>
HTML;
        } else {
            $formInputs .= <<<HTML
            <div class="mb-3">
                <label class="form-label">{$info['label']}</label>
                <input type="{$info['type']}" name="{$field}" class="form-control" value="{{ old('{$field}') }}" required>
            </div>
HTML;
        }
    }

    $create = <<<EOT
@extends('layouts.app')
@section('title', 'Tambah {$title}')
@section('page_title', 'Tambah {$title}')
@section('content')
<div class="card" style="max-width: 600px;">
    <div class="card-body">
        <form action="{{ route('{$route}.store') }}" method="POST">
            @csrf
            {$formInputs}
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('{$route}.index') }}" class="btn-outline-custom text-decoration-none">Batal</a>
                <button type="submit" class="btn-primary-custom">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
EOT;

    // Generate Edit
    $editInputs = "";
    foreach ($fields as $field => $info) {
        if ($info['type'] === 'textarea') {
            $editInputs .= <<<HTML
            <div class="mb-3">
                <label class="form-label">{$info['label']}</label>
                <textarea name="{$field}" class="form-control" rows="3">{{ old('{$field}', \${$var}->{$field}) }}</textarea>
            </div>
HTML;
        } else {
            $editInputs .= <<<HTML
            <div class="mb-3">
                <label class="form-label">{$info['label']}</label>
                <input type="{$info['type']}" name="{$field}" class="form-control" value="{{ old('{$field}', \${$var}->{$field}) }}" required>
            </div>
HTML;
        }
    }

    $edit = <<<EOT
@extends('layouts.app')
@section('title', 'Edit {$title}')
@section('page_title', 'Edit {$title}')
@section('content')
<div class="card" style="max-width: 600px;">
    <div class="card-body">
        <form action="{{ route('{$route}.update', \${$var}) }}" method="POST">
            @csrf @method('PUT')
            {$editInputs}
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('{$route}.index') }}" class="btn-outline-custom text-decoration-none">Batal</a>
                <button type="submit" class="btn-primary-custom">Update</button>
            </div>
        </form>
    </div>
</div>
@endsection
EOT;

    // Save files
    file_put_contents(__DIR__ . "/resources/views/master/{$folder}/index.blade.php", $index);
    file_put_contents(__DIR__ . "/resources/views/master/{$folder}/create.blade.php", $create);
    file_put_contents(__DIR__ . "/resources/views/master/{$folder}/edit.blade.php", $edit);
    echo "Generated Master: {$title}\n";
}

