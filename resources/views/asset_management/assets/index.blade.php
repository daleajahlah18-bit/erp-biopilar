@extends('layouts.app')
@section('title', 'Master Assets')
@section('page_title', 'Master Assets')

@section('header_actions')
<a href="{{ route('asset-management.assets.create') }}" class="btn btn-primary">
    <i class="bi bi-plus"></i> New Asset
</a>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover datatable">
                <thead>
                    <tr>
                        <th>@sortablelink('asset_code', 'Asset Code')</th>
                        <th>@sortablelink('name', 'Name')</th>
                        <th>@sortablelink('category', 'Category')</th>
                        <th>@sortablelink('location', 'Location')</th>
                        <th>@sortablelink('acq._cost', 'Acq. Cost')</th>
                        <th>@sortablelink('status', 'Status')</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($assets as $asset)
                    <tr>
                        <td>{{ $asset->asset_code }}</td>
                        <td>{{ $asset->asset_name }}</td>
                        <td>{{ $asset->category->category_name ?? '-' }}</td>
                        <td>{{ $asset->location }}</td>
                        <td>Rp {{ number_format($asset->acquisition_cost, 0, ',', '.') }}</td>
                        <td>
                            @if($asset->status == 'Active')
                                <span class="badge bg-success bg-opacity-10 text-success border border-success">Active</span>
                            @elseif($asset->status == 'Under Maintenance')
                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning">Maintenance</span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary">{{ $asset->status }}</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('asset-management.assets.show', $asset->id) }}" class="btn btn-sm btn-info text-white">Detail</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
