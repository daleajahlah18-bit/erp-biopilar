@extends('layouts.app')
@section('title', 'New Asset')
@section('page_title', 'New Asset')

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('asset-management.assets.store') }}" method="POST">
            @csrf
            <h5 class="mb-3 text-primary border-bottom pb-2">General Information</h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Asset Name</label>
                    <input type="text" name="asset_name" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Category</label>
                    <select name="category_id" class="form-select" required>
                        <option value="">Select Category</option>
                        @foreach($categories as $c)
                            <option value="{{ $c->id }}">{{ $c->category_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Brand</label>
                    <input type="text" name="brand" class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label>Model</label>
                    <input type="text" name="model" class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label>Serial Number</label>
                    <input type="text" name="serial_number" class="form-control">
                </div>
            </div>

            <h5 class="mb-3 text-primary border-bottom pb-2 mt-4">Location & Assignment</h5>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label>Location</label>
                    <input type="text" name="location" class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label>Department</label>
                    <input type="text" name="department" class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label>Responsible Person (PIC)</label>
                    <input type="text" name="responsible_person" class="form-control">
                </div>
            </div>

            <h5 class="mb-3 text-primary border-bottom pb-2 mt-4">Financial Details</h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Purchase Date</label>
                    <input type="date" name="purchase_date" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Start Depreciation Date</label>
                    <input type="date" name="start_depreciation_date" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Acquisition Cost</label>
                    <input type="number" name="acquisition_cost" class="form-control" required min="0">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Residual Value</label>
                    <input type="number" name="residual_value" class="form-control" value="0" min="0">
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card bg-light border-0">
                        <div class="card-body">
                            <h6 class="text-primary"><i class="bi bi-book"></i> Commercial Book</h6>
                            <div class="mb-3">
                                <label>Method</label>
                                <select name="commercial_method" class="form-select" required>
                                    <option value="Straight Line">Straight Line</option>
                                    <option value="Double Declining Balance">Double Declining Balance</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Useful Life (Months)</label>
                                <input type="number" name="commercial_useful_life" class="form-control" required min="1">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-light border-0">
                        <div class="card-body">
                            <h6 class="text-success"><i class="bi bi-building"></i> Fiscal Book</h6>
                            <div class="mb-3">
                                <label>Method</label>
                                <select name="fiscal_method" class="form-select" required>
                                    <option value="Straight Line">Straight Line</option>
                                    <option value="Double Declining Balance">Double Declining Balance</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Useful Life (Months)</label>
                                <input type="number" name="fiscal_useful_life" class="form-control" required min="1">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('asset-management.assets.index') }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Asset</button>
            </div>
        </form>
    </div>
</div>
@endsection
