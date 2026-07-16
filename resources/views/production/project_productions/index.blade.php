@extends('layouts.app')
@section('title', 'Data Project Fabrication')
@section('page_title', 'Project Fabrication')
@section('page_subtitle', 'Pemakaian Barang Project')

@section('content')
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center  py-3">
        <h6 class="m-0 font-weight-bold text-primary">Daftar Project Fabrication</h6>
        <a href="{{ route('production.project-productions.create') }}" class="btn-primary-custom py-2 px-3 text-decoration-none">
            <i class="bi bi-plus-lg"></i> Buat Dokumen
        </a>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-hover table-custom align-middle">
                <thead class="">
                    <tr>
                        <th>No</th>
                        <th>No Dokumen</th>
                        <th>@sortablelink('created_at', 'Tanggal')</th>
                        <th>@sortablelink('project', 'Project')</th>
                        <th>@sortablelink('gudang_asal', 'Gudang Asal')</th>
                        <th>@sortablelink('status', 'Status')</th>
                        <th>@sortablelink('catatan', 'Catatan')</th>
                        <th>@sortablelink('dibuat_oleh', 'Dibuat Oleh')</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($projectProductions as $index => $pp)
                    <tr>
                        <td>{{ $projectProductions->firstItem() + $index }}</td>
                        <td><span class="badge bg-secondary">{{ $pp->project_production_number }}</span></td>
                        <td>{{ \Carbon\Carbon::parse($pp->production_date)->format('d M Y') }}</td>
                        <td class="fw-bold">{{ $pp->project->project_name ?? '-' }}</td>
                        <td>{{ $pp->warehouse->warehouse_name ?? '-' }}</td>
                        <td>
                            @if($pp->status === 'Finalized')
                                <span class="badge bg-success">Finalized</span>
                            @else
                                <span class="badge bg-warning text-dark">Draft</span>
                            @endif
                        </td>
                        <td>{{ Str::limit($pp->notes, 30) ?: '-' }}</td>
                        <td>{{ $pp->creator->name ?? '-' }}</td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('production.project-productions.show', $pp->id) }}" class="btn btn-sm btn-info text-white" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if($pp->status !== 'Finalized')
                                    <a href="{{ route('production.project-productions.edit', $pp->id) }}" class="btn btn-sm btn-warning text-dark" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('production.project-productions.finalize', $pp->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Apakah Anda yakin ingin melakukan Finalisasi? Setelah difinalisasi, dokumen tidak dapat di-edit dan STOK AKAN DIPOTONG.');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" title="Finalisasi">
                                            <i class="bi bi-check2-circle"></i>
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('production.project-productions.pdf', $pp->id) }}" class="btn btn-sm btn-danger" title="Download PDF">
                                    <i class="bi bi-file-earmark-pdf"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">Belum ada data Project Fabrication.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-3">
            {{ $projectProductions->links() }}
        </div>
    </div>
</div>
@endsection
