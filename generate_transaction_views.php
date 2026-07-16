<?php

$views = [
    // --- Purchase Orders ---
    'purchasing/purchase_orders/index.blade.php' => <<<'EOT'
@extends('layouts.app')
@section('title', 'Purchase Order')
@section('page_title', 'Purchase Order')
@section('header_actions')
<a href="{{ route('purchasing.purchase-orders.create') }}" class="btn-primary-custom"><i class="bi bi-plus-lg"></i> Buat PO</a>
@endsection
@section('content')
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table-custom">
                <thead><tr><th>No. PO</th><th>Supplier</th><th>Tanggal</th><th>Total</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr>
                        <td><strong>{{ $order->po_number }}</strong></td>
                        <td>{{ $order->supplier->supplier_name ?? '-' }}</td>
                        <td>{{ $order->po_date }}</td>
                        <td>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                        <td><span class="badge-status {{ $order->status == 'Draft' ? 'badge-draft' : 'badge-approved' }}">{{ $order->status }}</span></td>
                        <td>
                            <a href="#" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-4 text-secondary">Belum ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
EOT,
    'purchasing/purchase_orders/create.blade.php' => <<<'EOT'
@extends('layouts.app')
@section('title', 'Buat Purchase Order')
@section('page_title', 'Buat Purchase Order')
@section('content')
<div class="card" style="max-width: 800px;">
    <div class="card-body">
        <form action="#" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nomor PO (Auto)</label>
                    <input type="text" class="form-control" placeholder="PO-..." readonly>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tanggal PO</label>
                    <input type="date" name="po_date" class="form-control" required>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Supplier</label>
                    <select name="supplier_id" class="form-select" required>
                        <option value="">-- Pilih Supplier --</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->supplier_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('purchasing.purchase-orders.index') }}" class="btn-outline-custom text-decoration-none">Batal</a>
                <button type="submit" class="btn-primary-custom">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
EOT,

    // --- Goods Receipts ---
    'purchasing/goods_receipts/index.blade.php' => <<<'EOT'
@extends('layouts.app')
@section('title', 'Goods Receipt')
@section('page_title', 'Goods Receipt')
@section('header_actions')
<a href="{{ route('purchasing.goods-receipts.create') }}" class="btn-primary-custom"><i class="bi bi-plus-lg"></i> Terima Barang</a>
@endsection
@section('content')
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table-custom">
                <thead><tr><th>No. GR</th><th>No. PO</th><th>Gudang</th><th>Penerima</th><th>Tanggal</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse($receipts as $receipt)
                    <tr>
                        <td><strong>{{ $receipt->gr_number }}</strong></td>
                        <td>{{ $receipt->purchaseOrder->po_number ?? '-' }}</td>
                        <td>{{ $receipt->warehouse->warehouse_name ?? '-' }}</td>
                        <td>{{ $receipt->received_by }}</td>
                        <td>{{ $receipt->receipt_date }}</td>
                        <td><a href="#" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a></td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-4 text-secondary">Belum ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
EOT,
    'purchasing/goods_receipts/create.blade.php' => <<<'EOT'
@extends('layouts.app')
@section('title', 'Terima Barang')
@section('page_title', 'Terima Barang (Goods Receipt)')
@section('content')
<div class="card" style="max-width: 800px;">
    <div class="card-body">
        <form action="#" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nomor GR (Auto)</label>
                    <input type="text" class="form-control" placeholder="GR-..." readonly>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tanggal Terima</label>
                    <input type="date" name="receipt_date" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nomor PO</label>
                    <select name="purchase_order_id" class="form-select" required>
                        <option value="">-- Pilih PO --</option>
                        @foreach($orders as $order)
                            <option value="{{ $order->id }}">{{ $order->po_number }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Gudang Tujuan</label>
                    <select name="warehouse_id" class="form-select" required>
                        <option value="">-- Pilih Gudang --</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->warehouse_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('purchasing.goods-receipts.index') }}" class="btn-outline-custom text-decoration-none">Batal</a>
                <button type="submit" class="btn-primary-custom">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
EOT,

    // --- BOM ---
    'production/bom/index.blade.php' => <<<'EOT'
@extends('layouts.app')
@section('title', 'Bill Of Material')
@section('page_title', 'Bill Of Material (BOM)')
@section('header_actions')
<a href="{{ route('production.bom.create') }}" class="btn-primary-custom"><i class="bi bi-plus-lg"></i> Buat BOM</a>
@endsection
@section('content')
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table-custom">
                <thead><tr><th>No. BOM</th><th>Produk Target</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse($boms as $bom)
                    <tr>
                        <td><strong>{{ $bom->bom_number }}</strong></td>
                        <td>{{ $bom->product->product_name ?? '-' }}</td>
                        <td><a href="#" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a></td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="text-center py-4 text-secondary">Belum ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
EOT,
    'production/bom/create.blade.php' => <<<'EOT'
@extends('layouts.app')
@section('title', 'Buat BOM')
@section('page_title', 'Buat Bill Of Material')
@section('content')
<div class="card" style="max-width: 600px;">
    <div class="card-body">
        <form action="#" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label">Produk Jadi Target</label>
                <select name="product_id" class="form-select" required>
                    <option value="">-- Pilih Produk Jadi --</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->product_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('production.bom.index') }}" class="btn-outline-custom text-decoration-none">Batal</a>
                <button type="submit" class="btn-primary-custom">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
EOT,

    // --- Production Order ---
    'production/orders/index.blade.php' => <<<'EOT'
@extends('layouts.app')
@section('title', 'Production Order')
@section('page_title', 'Production Order')
@section('header_actions')
<a href="{{ route('production.orders.create') }}" class="btn-primary-custom"><i class="bi bi-plus-lg"></i> Buat Order Produksi</a>
@endsection
@section('content')
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table-custom">
                <thead><tr><th>No. Produksi</th><th>Produk Target</th><th>Target Qty</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr>
                        <td><strong>{{ $order->production_number }}</strong></td>
                        <td>{{ $order->product->product_name ?? '-' }}</td>
                        <td>{{ number_format($order->quantity_target, 0) }}</td>
                        <td><span class="badge-status badge-inprogress">{{ $order->status }}</span></td>
                        <td><a href="#" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a></td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-4 text-secondary">Belum ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
EOT,
    'production/orders/create.blade.php' => <<<'EOT'
@extends('layouts.app')
@section('title', 'Buat Production Order')
@section('page_title', 'Buat Production Order')
@section('content')
<div class="card" style="max-width: 800px;">
    <div class="card-body">
        <form action="#" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">No. Produksi (Auto)</label>
                    <input type="text" class="form-control" readonly placeholder="PRD-...">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Produk Jadi</label>
                    <select name="product_id" class="form-select" required>
                        <option value="">-- Pilih Produk --</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->product_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Bill of Material (BOM)</label>
                    <select name="bill_of_material_id" class="form-select" required>
                        <option value="">-- Pilih BOM --</option>
                        @foreach($boms as $bom)
                            <option value="{{ $bom->id }}">{{ $bom->bom_number }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Target Quantity</label>
                    <input type="number" class="form-control" name="quantity_target" required>
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('production.orders.index') }}" class="btn-outline-custom text-decoration-none">Batal</a>
                <button type="submit" class="btn-primary-custom">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
EOT,

    // --- Stok ---
    'inventory/stocks/index.blade.php' => <<<'EOT'
@extends('layouts.app')
@section('title', 'Stok Produk')
@section('page_title', 'Stok Produk')
@section('content')
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table-custom">
                <thead><tr><th>Kode</th><th>Nama Produk</th><th>Gudang</th><th>Kuantitas</th></tr></thead>
                <tbody>
                    @forelse($stocks as $stock)
                    <tr>
                        <td><strong>{{ $stock->product->product_code ?? '-' }}</strong></td>
                        <td>{{ $stock->product->product_name ?? '-' }}</td>
                        <td>{{ $stock->warehouse->warehouse_name ?? '-' }}</td>
                        <td>{{ number_format($stock->quantity, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center py-4 text-secondary">Belum ada data stok.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
EOT,

    // --- Inventory Transfers ---
    'inventory/transfers/index.blade.php' => <<<'EOT'
@extends('layouts.app')
@section('title', 'Transfer Stok')
@section('page_title', 'Transfer Stok')
@section('header_actions')
<a href="{{ route('inventory.transfers.create') }}" class="btn-primary-custom"><i class="bi bi-arrow-left-right"></i> Transfer Baru</a>
@endsection
@section('content')
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table-custom">
                <thead><tr><th>No. Transfer</th><th>Gudang Asal</th><th>Gudang Tujuan</th><th>Tanggal</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse($transfers as $transfer)
                    <tr>
                        <td><strong>{{ $transfer->transfer_number }}</strong></td>
                        <td>{{ $transfer->sourceWarehouse->warehouse_name ?? '-' }}</td>
                        <td>{{ $transfer->destinationWarehouse->warehouse_name ?? '-' }}</td>
                        <td>{{ $transfer->transfer_date }}</td>
                        <td><a href="#" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a></td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-4 text-secondary">Belum ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
EOT,
    'inventory/transfers/create.blade.php' => <<<'EOT'
@extends('layouts.app')
@section('title', 'Buat Transfer Stok')
@section('page_title', 'Buat Transfer Stok')
@section('content')
<div class="card" style="max-width: 800px;">
    <div class="card-body">
        <form action="#" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">No. Transfer (Auto)</label>
                    <input type="text" class="form-control" readonly placeholder="TRF-...">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tanggal Transfer</label>
                    <input type="date" name="transfer_date" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Gudang Asal</label>
                    <select name="source_warehouse_id" class="form-select" required>
                        <option value="">-- Pilih Gudang --</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->warehouse_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Gudang Tujuan</label>
                    <select name="destination_warehouse_id" class="form-select" required>
                        <option value="">-- Pilih Gudang --</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->warehouse_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('inventory.transfers.index') }}" class="btn-outline-custom text-decoration-none">Batal</a>
                <button type="submit" class="btn-primary-custom">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
EOT,
];

foreach ($views as $path => $content) {
    file_put_contents(__DIR__ . "/resources/views/{$path}", $content);
    echo "Updated transaction view: $path\n";
}
