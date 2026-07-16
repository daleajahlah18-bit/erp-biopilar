@extends('layouts.app')
@section('title', 'Input Production Result')
@section('page_title', 'Production Result')
@section('page_subtitle', 'Catat hasil produksi aktual dan kalkulasi waste')

@section('content')
<div class="card mb-4" style="max-width: 800px; margin: 0 auto;">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0">Form Hasil Produksi</h5>
    </div>
    <div class="card-body">
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row mb-4  p-3 rounded">
            <div class="col-md-6">
                <p class="mb-1"><strong>No Produksi:</strong> <br> {{ $order->production_number }}</p>
            </div>
            <div class="col-md-6">
                <p class="mb-1"><strong>Bill of Material:</strong> <br> {{ $order->billOfMaterial->bom_number ?? '-' }} ({{ $order->billOfMaterial->product->product_name ?? 'Tanpa Produk Jadi' }})</p>
            </div>
        </div>

        <form action="{{ route('production.orders.save-production-result', $order->id) }}" method="POST">
            @csrf

            <div class="mb-3">
                <label class="form-label fw-bold">Target Quantity</label>
                <input type="text" class="form-control" id="targetQty" value="{{ rtrim(rtrim(number_format($order->target_quantity, 4, ',', '.'), '0'), ',') }}" readonly style="background-color: #e9ecef; cursor: not-allowed;">
                <input type="hidden" id="targetQtyValue" value="{{ $order->target_quantity }}">
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold text-success">Hasil Produksi Aktual <span class="text-danger">*</span></label>
                <input type="number" name="actual_quantity" id="actualQty" class="form-control" min="0" step="0.0001" required placeholder="Contoh: 95">
                <small class="text-muted">Masukkan jumlah barang yang benar-benar berhasil diproduksi.</small>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold text-danger">Waste / Susut (Auto Calculate)</label>
                <input type="text" class="form-control text-danger fw-bold" id="wasteQty" value="0" readonly style="background-color: #fdf2f2;">
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">Keterangan Tambahan / Notes</label>
                <textarea name="production_result_notes" class="form-control" rows="3" placeholder="Contoh: Terdapat ayam yang rusak sebelum dibakar sehingga susut 5 pcs."></textarea>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('production.orders.show', $order->id) }}" class="btn-outline-custom text-decoration-none">Batal</a>
                <button type="submit" class="btn-success-custom" id="btnSimpan" onclick="event.preventDefault(); confirmAction(() => this.closest('form').submit(), 'Konfirmasi', 'Apakah Anda yakin data hasil produksi sudah benar? Stok produk jadi akan ditambahkan dan transaksi ini tidak dapat diubah lagi.')">
                    <i class="bi bi-check2-circle"></i> Simpan Hasil Produksi
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@stack('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const targetQtyValue = parseFloat(document.getElementById('targetQtyValue').value) || 0;
    const actualQtyInput = document.getElementById('actualQty');
    const wasteQtyInput = document.getElementById('wasteQty');

    actualQtyInput.addEventListener('input', function() {
        const actual = parseFloat(this.value) || 0;
        const waste = targetQtyValue - actual;
        
        wasteQtyInput.value = parseFloat(waste).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 4 });
        
        if (waste < 0) {
            wasteQtyInput.classList.remove('text-danger');
            wasteQtyInput.classList.add('text-primary'); // Overproduction
        } else {
            wasteQtyInput.classList.add('text-danger');
            wasteQtyInput.classList.remove('text-primary'); // Waste
        }
    });
});
</script>
