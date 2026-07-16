@extends(auth()->check() ? 'layouts.app' : 'layouts.guest')

@section('content')
<div class="d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <!-- The actual alert will be handled by SweetAlert2 automatically -->
</div>

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        Swal.fire({
            icon: 'error',
            title: 'Akses Ditolak',
            text: 'Maaf, Anda tidak memiliki hak akses untuk membuka menu ini.',
            confirmButtonText: 'Kembali',
            confirmButtonColor: '#ef4444',
            allowOutsideClick: false,
            allowEscapeKey: false
        }).then((result) => {
            if (result.isConfirmed) {
                if (window.history.length > 1) {
                    window.history.back();
                } else {
                    window.location.href = "{{ url('/dashboard') }}";
                }
            }
        });
    });
</script>
@endpush
@endsection
