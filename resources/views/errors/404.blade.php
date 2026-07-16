@extends(auth()->check() ? 'layouts.app' : 'layouts.guest')

@section('content')
<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="text-center">
        <!-- 404 CSS Art -->
        <div class="mb-4">
            <h1 style="font-size: 8rem; font-weight: 800; color: var(--color-primary, #6D4CFF); text-shadow: 4px 4px 0px rgba(109,76,255,0.2);">
                4<span class="text-danger">0</span>4
            </h1>
        </div>
        <h3 class="mb-3 fw-bold" style="color: var(--color-text-primary, #333)">Halaman Tidak Ditemukan</h3>
        <p class="text-muted mb-4" style="max-width: 400px; margin: 0 auto;">
            Maaf, halaman yang Anda cari tidak ada, sudah dihapus, atau Anda salah mengetikkan URL.
        </p>
        <a href="{{ url('/dashboard') }}" class="btn btn-primary px-4 py-2" style="border-radius: 8px; background-color: var(--color-primary, #6D4CFF); border-color: var(--color-primary, #6D4CFF);">
            <i class="bi bi-arrow-left me-2"></i> Kembali ke Dashboard
        </a>
    </div>
</div>
@endsection
