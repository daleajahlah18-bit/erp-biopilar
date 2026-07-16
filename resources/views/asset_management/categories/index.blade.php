@extends('layouts.app')
@section('title', 'Master Asset Categories')
@section('page_title', 'Master Asset Categories')

@section('header_actions')
<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
    <i class="bi bi-plus"></i> New Category
</button>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover datatable">
                <thead>
                    <tr>
                        <th>@sortablelink('code', 'Code')</th>
                        <th>@sortablelink('name', 'Name')</th>
                        <th>@sortablelink('comm._life_(mths)', 'Comm. Life (Mths)')</th>
                        <th>@sortablelink('comm._method', 'Comm. Method')</th>
                        <th>@sortablelink('fisc._life_(mths)', 'Fisc. Life (Mths)')</th>
                        <th>@sortablelink('fisc._method', 'Fisc. Method')</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categories as $category)
                    <tr>
                        <td>{{ $category->category_code }}</td>
                        <td>{{ $category->category_name }}</td>
                        <td>{{ $category->default_useful_life_commercial }}</td>
                        <td>{{ $category->default_method_commercial }}</td>
                        <td>{{ $category->default_useful_life_fiscal }}</td>
                        <td>{{ $category->default_method_fiscal }}</td>
                        <td>
                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editCategoryModal{{ $category->id }}">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteCategoryModal{{ $category->id }}">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>

                    <!-- Edit Modal -->
                    <div class="modal fade" id="editCategoryModal{{ $category->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <form action="{{ route('asset-management.categories.update', $category->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Asset Category</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label>Category Name</label>
                                            <input type="text" name="category_name" class="form-control" value="{{ $category->category_name }}" required>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label>Commercial Life (Months)</label>
                                                <input type="number" name="default_useful_life_commercial" class="form-control" value="{{ $category->default_useful_life_commercial }}" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label>Commercial Method</label>
                                                <select name="default_method_commercial" class="form-select" required>
                                                    <option value="Straight Line" {{ $category->default_method_commercial == 'Straight Line' ? 'selected' : '' }}>Straight Line</option>
                                                    <option value="Double Declining Balance" {{ $category->default_method_commercial == 'Double Declining Balance' ? 'selected' : '' }}>Double Declining Balance</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label>Fiscal Life (Months)</label>
                                                <input type="number" name="default_useful_life_fiscal" class="form-control" value="{{ $category->default_useful_life_fiscal }}" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label>Fiscal Method</label>
                                                <select name="default_method_fiscal" class="form-select" required>
                                                    <option value="Straight Line" {{ $category->default_method_fiscal == 'Straight Line' ? 'selected' : '' }}>Straight Line</option>
                                                    <option value="Double Declining Balance" {{ $category->default_method_fiscal == 'Double Declining Balance' ? 'selected' : '' }}>Double Declining Balance</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label>Residual Value (%)</label>
                                            <input type="number" name="default_residual_value_percent" class="form-control" value="{{ $category->default_residual_value_percent }}" min="0" max="100">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Update</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Delete Modal -->
                    <div class="modal fade" id="deleteCategoryModal{{ $category->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <form action="{{ route('asset-management.categories.destroy', $category->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Delete Category</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        Are you sure you want to delete category <strong>{{ $category->category_name }}</strong>?
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('asset-management.categories.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">New Asset Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Category Name</label>
                        <input type="text" name="category_name" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Commercial Life (Months)</label>
                            <input type="number" name="default_useful_life_commercial" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Commercial Method</label>
                            <select name="default_method_commercial" class="form-select" required>
                                <option value="Straight Line">Straight Line</option>
                                <option value="Double Declining Balance">Double Declining Balance</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Fiscal Life (Months)</label>
                            <input type="number" name="default_useful_life_fiscal" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Fiscal Method</label>
                            <select name="default_method_fiscal" class="form-select" required>
                                <option value="Straight Line">Straight Line</option>
                                <option value="Double Declining Balance">Double Declining Balance</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Residual Value (%)</label>
                        <input type="number" name="default_residual_value_percent" class="form-control" value="0" min="0" max="100">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
