@extends('layouts.app')
@section('title', 'Project')
@section('page_title', 'Master Project')
@section('header_actions')
<a href="{{ route('master.projects.create') }}" class="btn-primary-custom"><i class="bi bi-plus-lg"></i> Tambah Project</a>
@endsection

@section('content')
@if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
@if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table-custom table table-hover mb-0">
                <thead class="">
                    <tr>
                        <th>No</th>
                        <th>@sortablelink('project_name', 'Project Name')</th>
                        <th>@sortablelink('client', 'Client')</th>
                        <th>@sortablelink('status', 'Status')</th>
                        <th>@sortablelink('project_value', 'Project Value')</th>
                        <th>@sortablelink('hpp', 'HPP')</th>
                        <th>@sortablelink('margin', 'Margin')</th>
                        <th>@sortablelink('pic', 'PIC')</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($projects as $project)
                    <tr>
                        <td>{{ $loop->iteration + $projects->firstItem() - 1 }}</td>
                        <td>
                            <strong><a href="{{ route('master.projects.show', $project->id) }}" class="text-decoration-none">{{ $project->project_name }}</a></strong>
                            <div class="small text-muted">{{ $project->project_start_date ? \Carbon\Carbon::parse($project->project_start_date)->format('d M Y') : '-' }} s/d {{ $project->project_end_date ? \Carbon\Carbon::parse($project->project_end_date)->format('d M Y') : '-' }}</div>
                        </td>
                        <td>{{ $project->client_name }}</td>
                        <td>
                            @if($project->project_status == 'Completed')
                                <span class="badge bg-success">Completed</span>
                            @elseif($project->project_status == 'On Going')
                                <span class="badge bg-primary">On Going</span>
                            @elseif($project->project_status == 'Cancelled')
                                <span class="badge bg-danger">Cancelled</span>
                            @else
                                <span class="badge bg-secondary">Draft</span>
                            @endif
                        </td>
                        <td class="text-end">Rp {{ number_format($project->project_value, 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($project->hpp, 0, ',', '.') }}</td>
                        <td class="text-end">
                            @php $margin = $project->margin; @endphp
                            @if($margin > 0)
                                <span class="badge bg-success text-white px-2 py-1">Rp {{ number_format($margin, 0, ',', '.') }}</span>
                            @elseif($margin == 0)
                                <span class="badge bg-warning  px-2 py-1">Rp 0</span>
                            @else
                                <span class="badge bg-danger text-white px-2 py-1">Rp {{ number_format($margin, 0, ',', '.') }}</span>
                            @endif
                        </td>
                        <td>{{ $project->person_in_charge }}</td>
                        <td class="text-end">
                            <a href="{{ route('master.projects.pdf', $project) }}" class="btn btn-sm btn-outline-danger me-1" target="_blank" title="Print PDF"><i class="bi bi-file-earmark-pdf"></i></a>
                            <a href="{{ route('master.projects.show', $project) }}" class="btn btn-sm btn-outline-info me-1"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('master.projects.edit', $project) }}" class="btn btn-sm btn-outline-secondary me-1"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('master.projects.destroy', $project) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="event.preventDefault(); confirmDelete(() => this.closest('form').submit(), 'Hapus Data?', 'Yakin hapus project ini?')"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center py-4 text-secondary">Belum ada data project.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($projects->hasPages()) 
        <div class="card-footer  border-top">
            {{ $projects->links() }}
        </div> 
    @endif
</div>
@endsection