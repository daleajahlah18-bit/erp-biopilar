@extends('layouts.app')
@section('title', 'Role Management')
@section('page_title', 'Roles')

@section('content')
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white py-3 d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
        <h6 class="m-0 font-weight-bold text-primary">Role List</h6>
        <a href="{{ route('roles.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus"></i> Add Role</a>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="bg-light">
                    <tr>
                        <th width="5%">No</th>
                        <th>@sortablelink('role_name', 'Role Name')</th>
                        <th width="15%">@sortablelink('users_count', 'Users Count')</th>
                        <th width="20%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($roles as $index => $role)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td class="fw-bold">{{ $role->name }}</td>
                            <td><span class="badge bg-info">{{ $role->users()->count() }} Users</span></td>
                            <td>
                                <div class="d-flex gap-2 flex-wrap">
                                    <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-warning btn-sm"><i class="bi bi-pencil"></i> Edit</a>
                                    @if($role->name !== 'Administrator')
                                        <form action="{{ route('roles.destroy', $role->id) }}" method="POST" class="d-inline-block" onsubmit="event.preventDefault(); confirmDelete(() => this.submit(), 'Hapus Data?', 'Are you sure you want to delete this role?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i> Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
