@extends('layouts.app')
@section('title', 'Unit')
@section('page_title', 'Master Unit')
@section('header_actions')
<a href="{{ route('master.units.create') }}" class="btn-primary-custom"><i class="bi bi-plus-lg"></i> Tambah Unit</a>
@endsection
@section('content')
@if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>@sortablelink('name', 'Nama Unit')</th><th>@sortablelink('deskripsi', 'Deskripsi')</th><th class="text-end">Aksi</th></tr></thead>
                <tbody>
                    @forelse($units as $unit)
                    <tr>
                        <td>{{ $unit->unit_name }}</td><td>{{ $unit->description }}</td>
                        <td class="text-end">
                            <a href="{{ route('master.units.edit', $unit) }}" class="btn btn-sm btn-outline-secondary me-1"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('master.units.destroy', $unit) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="event.preventDefault(); confirmDelete(() => this.closest('form').submit(), 'Hapus Data?', 'Yakin hapus?')"><i class="bi bi-trash"></i></button>
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
    @if($units->hasPages()) <div class="card-footer  border-top">{{ $units->links() }}</div> @endif
</div>
@endsection