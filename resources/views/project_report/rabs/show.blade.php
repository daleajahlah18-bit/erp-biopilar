@extends('layouts.app')
@section('title', 'View RAB')
@section('page_title', 'RAB: ' . $rab->rab_name)

@section('header_actions')
<div class="d-flex gap-2">
    <a href="{{ route('rabs.index') }}" class="btn-outline-custom text-decoration-none">
        <i class="bi bi-arrow-left"></i> Back
    </a>
    @can('rab.export')
    <a href="{{ route('rabs.export', $rab->id) }}" class="btn-success-custom text-decoration-none">
        <i class="bi bi-file-earmark-spreadsheet"></i> Export Excel
    </a>
    @endcan
    @can('rab.edit')
    <a href="{{ route('rabs.edit', $rab->id) }}" class="btn-warning-custom text-decoration-none">
        <i class="bi bi-pencil"></i> Edit
    </a>
    @endcan
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4 h-auto">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">RAB Details</h6>
                <span class="badge-status badge-{{ strtolower($rab->status) }}">{{ $rab->status }}</span>
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm w-auto mb-0">
                    <tr>
                        <td class="fw-bold pe-4">Project Name</td>
                        <td>: {{ $rab->project->project_name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold pe-4">RAB Name</td>
                        <td>: {{ $rab->rab_name }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold pe-4">Created By</td>
                        <td>: {{ $rab->creator->name ?? 'System' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold pe-4">Grand Total</td>
                        <td class="fw-bold text-success fs-5">: Rp {{ number_format($rab->total_amount, 2, ',', '.') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card shadow mb-4 h-auto">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">RAB Structure</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="5%" class="text-center">No</th>
                                <th width="45%">Work Description</th>
                                <th width="10%" class="text-center">Qty</th>
                                <th width="10%" class="text-center">Unit</th>
                                <th width="15%" class="text-end">Unit Price (Rp)</th>
                                <th width="15%" class="text-end">Total Price (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php 
                                $secIndex = 1;
                            @endphp
                            @forelse($tree as $section)
                                <!-- SECTION -->
                                <tr class="table-secondary fw-bold">
                                    <td class="text-start">{{ $secIndex++ }}.</td>
                                    <td colspan="4">{{ $section->title }}</td>
                                    <td class="text-end">{{ number_format($section->total_price, 2, ',', '.') }}</td>
                                </tr>
                                
                                @php
                                    $grpNumber = 1;
                                @endphp
                                @foreach($section->children_groups as $group)
                                    <!-- GROUP -->
                                    <tr class="table-light fw-bold">
                                        <td class="text-center">{{ $grpNumber }}</td>
                                        <td style="padding-left: 20px;">
                                            {{ $group->title }}
                                            @if($group->specification)
                                                <br><small class="text-muted fw-normal">{{ $group->specification }}</small>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $group->qty > 0 ? $group->qty : '' }}</td>
                                        <td class="text-center">{{ $group->unit }}</td>
                                        <td class="text-end">{{ $group->qty > 0 ? number_format($group->unit_price, 2, ',', '.') : '' }}</td>
                                        <td class="text-end">{{ number_format($group->total_price, 2, ',', '.') }}</td>
                                    </tr>

                                    <!-- ITEMS -->
                                    @php $itmNumber = 1; @endphp
                                    @foreach($group->children_items as $item)
                                        <tr>
                                            <td class="text-end">{{ $itmNumber++ }}</td>
                                            <td style="padding-left: 40px;">
                                                {{ $item->title }}
                                                @if($item->specification)
                                                    <br><small class="text-muted">{{ $item->specification }}</small>
                                                @endif
                                            </td>
                                            <td class="text-center">{{ $item->qty }}</td>
                                            <td class="text-center">{{ $item->unit }}</td>
                                            <td class="text-end">{{ number_format($item->unit_price, 2, ',', '.') }}</td>
                                            <td class="text-end">{{ number_format($item->total_price, 2, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                    @php $grpNumber++; @endphp
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">No items found in this RAB.</td>
                                </tr>
                            @endforelse
                            
                            @if(count($tree) > 0)
                            <tr class="table-primary fw-bold">
                                <td colspan="5" class="text-end">GRAND TOTAL</td>
                                <td class="text-end">Rp {{ number_format($rab->total_amount, 2, ',', '.') }}</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
