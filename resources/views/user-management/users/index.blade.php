@extends('layouts.app')
@section('title', 'Users Management')
@section('page_title', 'Users')

@section('content')
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">User List</h6>
        <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-person-plus"></i> Add User</a>
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
                        <th>@sortablelink('photo', 'Photo')</th>
                        <th>@sortablelink('employee_id', 'Employee ID')</th>
                        <th>@sortablelink('full_name', 'Full Name')</th>
                        <th>@sortablelink('email', 'Email')</th>
                        <th>@sortablelink('role', 'Role')</th>
                        <th>@sortablelink('department', 'Department')</th>
                        <th>@sortablelink('status', 'Status')</th>
                        <th width="15%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td>
                                @if($user->photo)
                                    <img src="{{ route('display', ['module' => 'users', 'id' => $user->id, 'field' => 'photo']) }}" class="rounded-circle" width="40" height="40" alt="Photo">
                                @else
                                    <div class="bg-secondary rounded-circle d-flex justify-content-center align-items-center text-white" style="width: 40px; height: 40px;">
                                        <i class="bi bi-person"></i>
                                    </div>
                                @endif
                            </td>
                            <td>{{ $user->employee_id }}</td>
                            <td class="fw-bold">{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td><span class="badge bg-primary">{{ $user->roles->first()->name ?? '-' }}</span></td>
                            <td>{{ $user->department ?? '-' }}</td>
                            <td>
                                @if($user->status == 'Active')
                                    <span class="badge bg-success">Active</span>
                                @elseif($user->status == 'Inactive')
                                    <span class="badge bg-secondary">Inactive</span>
                                @else
                                    <span class="badge bg-danger">{{ $user->status }}</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning btn-sm"><i class="bi bi-pencil"></i></a>
                                @if($user->id !== auth()->id())
                                    <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline-block" onsubmit="event.preventDefault(); confirmDelete(() => this.submit(), 'Hapus Data?', 'Are you sure you want to delete this user?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
