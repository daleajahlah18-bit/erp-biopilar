@extends('layouts.app')
@section('title', 'Preview RAB Import')
@section('page_title', 'Preview RAB Import')
@section('page_subtitle', 'Review imported data before saving')

@section('header_actions')
<div class="d-flex gap-2">
    <a href="{{ route('rabs.index') }}" class="btn-outline-custom text-decoration-none">
        <i class="bi bi-x-circle"></i> Cancel
    </a>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="alert alert-info border-0 shadow-sm d-flex align-items-center">
                    <i class="bi bi-info-circle-fill fs-4 me-3 text-info"></i>
                    <div>
                        <strong>Preview Mode</strong><br>
                        Please review the data below. If everything is correct, click the "Confirm & Save" button at the bottom of the page.
                    </div>
                </div>

                <table class="table table-borderless table-sm w-auto mb-0">
                    <tr>
                        <td class="fw-bold pe-4">Project Name</td>
                        <td>: {{ $project->project_name }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold pe-4">RAB Name</td>
                        <td>: {{ $rab_name }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 font-weight-bold text-primary">Imported Structure</h6>
            </div>
            <div class="card-body p-0">
                <form action="{{ route('rabs.excel.import') }}" method="POST">
                    @csrf
                    <input type="hidden" name="project_id" value="{{ $project->id }}">
                    <input type="hidden" name="rab_name" value="{{ $rab_name }}">
                    <input type="hidden" name="temp_file" value="{{ $tempFile }}">

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
                                    $grpNumber = 1;
                                    $itmNumber = 1;
                                @endphp
                                @foreach($previewData as $row)
                                    @if($row['type'] == 'Section')
                                    @php 
                                        $secLetter = $secIndex++ . '.';
                                        $grpNumber = 1; 
                                    @endphp
                                    <tr class="table-secondary fw-bold">
                                        <td class="text-start">{{ $secLetter }}</td>
                                        <td colspan="5">{{ $row['title'] }}</td>
                                    </tr>
                                    @elseif($row['type'] == 'Group')
                                    @php 
                                        $itmNumber = 1; 
                                    @endphp
                                    <tr class="table-light fw-bold">
                                        <td class="text-center">{{ $grpNumber }}</td>
                                        <td style="padding-left: 20px;">
                                            {{ $row['title'] }}
                                            @if(!empty($row['specification']))
                                                <br><small class="text-muted fw-normal">{{ $row['specification'] }}</small>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $row['qty'] > 0 ? $row['qty'] : '' }}</td>
                                        <td class="text-center">{{ $row['unit'] }}</td>
                                        <td class="text-end">{{ $row['qty'] > 0 ? number_format((float)$row['unit_price'], 2, ',', '.') : '' }}</td>
                                        <td class="text-end">{{ $row['total_price'] > 0 ? number_format((float)$row['total_price'], 2, ',', '.') : '' }}</td>
                                    </tr>
                                    @php $grpNumber++; @endphp
                                    @elseif($row['type'] == 'Item')
                                    <tr>
                                        <td class="text-end">{{ $itmNumber++ }}</td>
                                        <td style="padding-left: 40px;">
                                            {{ $row['title'] }}
                                            @if(!empty($row['specification']))
                                                <br><small class="text-muted">{{ $row['specification'] }}</small>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $row['qty'] }}</td>
                                        <td class="text-center">{{ $row['unit'] }}</td>
                                        <td class="text-end">{{ number_format((float)$row['unit_price'], 2, ',', '.') }}</td>
                                        <td class="text-end">{{ number_format((float)$row['total_price'], 2, ',', '.') }}</td>
                                    </tr>
                                    @endif
                                @endforeach
                                
                                @if(count($previewData) > 0)
                                <tr class="table-primary fw-bold">
                                    <td colspan="5" class="text-end">GRAND TOTAL (Calculated by system)</td>
                                    <td class="text-end">Rp {{ number_format($grandTotal, 2, ',', '.') }}</td>
                                </tr>
                                @else
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-danger">
                                        <i class="bi bi-exclamation-triangle fs-3 d-block mb-2"></i>
                                        No valid RAB items found in the Excel file. Please ensure you are using the correct template format.
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <div class="card-footer bg-white py-3 text-end">
                        <button type="submit" class="btn btn-success" {{ count($previewData) == 0 ? 'disabled' : '' }}>
                            <i class="bi bi-check-circle"></i> Confirm & Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
