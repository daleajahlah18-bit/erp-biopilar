@extends('layouts.app')
@section('title', 'Edit Role: ' . $role->name)
@section('page_title', 'Edit Role')

@section('content')
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <h6 class="m-0 font-weight-bold text-primary">Role Details</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('roles.update', $role->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group mb-4">
                <label for="name">Role Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $role->name) }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <h5 class="mb-3 font-weight-bold text-dark border-bottom pb-2">Permission Matrix</h5>
            
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="bg-light text-center">
                        <tr>
                            <th class="text-start">Menu</th>
                            <th>Visible</th>
                            <th>View</th>
                            <th>Create</th>
                            <th>Edit</th>
                            <th>Delete</th>
                            <th>Approve</th>
                            <th>Export</th>
                            <th>Print</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($menus as $parent => $menuItems)
                            <tr class="table-secondary">
                                <td colspan="9" class="fw-bold">{{ $parent }}</td>
                            </tr>
                            @foreach($menuItems as $menu)
                                @php
                                    $slug = Str::slug($menu->name, '_');
                                    $actions = ['visible', 'view', 'create', 'edit', 'delete', 'approve', 'export', 'print'];
                                @endphp
                                <tr>
                                    <td class="ps-4">{{ $menu->name }}</td>
                                    @foreach($actions as $action)
                                        @php
                                            $permName = $slug . '.' . $action;
                                            $isChecked = in_array($permName, $rolePermissions);
                                        @endphp
                                        <td class="text-center">
                                            <div class="form-check form-switch d-flex justify-content-center">
                                                <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permName }}" {{ $isChecked ? 'checked' : '' }}>
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Update Role</button>
                <a href="{{ route('roles.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
