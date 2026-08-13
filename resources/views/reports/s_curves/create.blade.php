@extends('layouts.app')
@section('title', 'Create S-Curve')
@section('page_title', 'Buat S-Curve Baru')

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 font-weight-bold text-primary">Data S-Curve</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('s-curves.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Project <span class="text-danger">*</span></label>
                        <select name="project_id" class="form-select select2" required>
                            <option value="">-- Pilih Project --</option>
                            @foreach($projects as $p)
                                <option value="{{ $p->id }}" {{ old('project_id') == $p->id ? 'selected' : '' }}>{{ $p->project_name }}</option>
                            @endforeach
                        </select>
                        @error('project_id') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Nama S-Curve <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="Contoh: S-Curve Tahap 1" required>
                        @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label font-weight-bold">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" id="start_date" class="form-control" value="{{ old('start_date') }}" required>
                            @error('start_date') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-weight-bold">Tanggal Selesai <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" id="end_date" class="form-control" value="{{ old('end_date') }}" required>
                            @error('end_date') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="text-end">
                        <a href="{{ route('s-curves.index') }}" class="btn btn-outline-secondary me-2">Batal</a>
                        <button type="submit" class="btn btn-primary" onclick="return validateDates()"><i class="bi bi-save"></i> Simpan & Lanjutkan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function validateDates() {
    const start = document.getElementById('start_date').value;
    const end = document.getElementById('end_date').value;
    
    if (start && end) {
        if (new Date(start) > new Date(end)) {
            alert('Tanggal selesai tidak boleh lebih kecil dari tanggal mulai.');
            return false;
        }
    }
    return true;
}
</script>
@endsection
