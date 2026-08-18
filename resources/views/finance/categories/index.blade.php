@extends('layouts.app')

@section('page_title', 'Expense Categories')
@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Finance</a></li>
            <li class="breadcrumb-item active" aria-current="page">Categories</li>
        </ol>
    </nav>
@endsection

@section('header_actions')
    @can('finance.create')
        <a href="{{ route('finance.categories.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Add Category
        </a>
    @endcan
@endsection

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>@sortablelink('code', 'Code')</th>
                        <th>@sortablelink('name', 'Name')</th>
                        <th>Description</th>
                        <th>@sortablelink('is_active', 'Status')</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                        <tr>
                            <td>{{ $category->code ?? '-' }}</td>
                            <td>{{ $category->name }}</td>
                            <td>{{ Str::limit($category->description, 50) }}</td>
                            <td>
                                @if($category->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @can('finance.edit')
                                    <a href="{{ route('finance.categories.edit', $category) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                @endcan
                                @can('finance.delete')
                                    <form action="{{ route('finance.categories.destroy', $category) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this category?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No expense categories found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-top">
            {{ $categories->appends(request()->except('page'))->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection
