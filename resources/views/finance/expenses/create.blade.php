@extends('layouts.app')

@section('page_title', 'Record Expense')
@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Finance</a></li>
            <li class="breadcrumb-item"><a href="{{ route('finance.expenses.index') }}">Expenses</a></li>
            <li class="breadcrumb-item active" aria-current="page">Record</li>
        </ol>
    </nav>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <form action="{{ route('finance.expenses.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="row">
                        <!-- Left Column -->
                        <div class="col-md-6 border-end pe-4">
                            <h5 class="mb-4 text-primary">Expense Details</h5>
                            
                            <div class="mb-3">
                                <label for="project_id" class="form-label">Project <span class="text-danger">*</span></label>
                                <select class="form-select @error('project_id') is-invalid @enderror" id="project_id" name="project_id" required>
                                    <option value="">Search Project...</option>
                                </select>
                                @error('project_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                                <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="expense_date" class="form-label">Expense Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('expense_date') is-invalid @enderror" id="expense_date" name="expense_date" value="{{ old('expense_date', date('Y-m-d')) }}" required>
                                @error('expense_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="amount" class="form-label">Amount (Rp) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control @error('amount') is-invalid @enderror" id="amount" name="amount" value="{{ old('amount') }}" required>
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" required>{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="col-md-6 ps-4">
                            <h5 class="mb-4 text-primary">Payment & Attachments</h5>
                            
                            <div class="mb-3">
                                <label for="payment_method" class="form-label">Payment Method <span class="text-danger">*</span></label>
                                <select class="form-select @error('payment_method') is-invalid @enderror" id="payment_method" name="payment_method" required>
                                    <option value="">Select Method</option>
                                    @foreach($paymentMethods as $method)
                                        <option value="{{ $method }}" {{ old('payment_method') == $method ? 'selected' : '' }}>{{ $method }}</option>
                                    @endforeach
                                </select>
                                @error('payment_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="paid_to" class="form-label">Paid To (Vendor/Person)</label>
                                <input type="text" class="form-control @error('paid_to') is-invalid @enderror" id="paid_to" name="paid_to" value="{{ old('paid_to') }}">
                                @error('paid_to')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="reference_number" class="form-label">Reference Number (Receipt/Invoice No)</label>
                                <input type="text" class="form-control @error('reference_number') is-invalid @enderror" id="reference_number" name="reference_number" value="{{ old('reference_number') }}">
                                @error('reference_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="notes" class="form-label">Additional Notes</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="2">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="attachments" class="form-label">Attachments (Receipts, Proof of Payment)</label>
                                <input class="form-control @error('attachments.*') is-invalid @enderror" type="file" id="attachments" name="attachments[]" multiple>
                                <div class="form-text">Max 5MB per file. You can select multiple files.</div>
                                @error('attachments.*')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="{{ route('finance.expenses.index') }}" class="btn btn-light">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Expense</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#project_id').select2({
        theme: 'default',
        placeholder: 'Search Project...',
        ajax: {
            url: '{{ route("finance.expenses.projects.search") }}',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { q: params.term };
            },
            processResults: function (data) {
                return { results: data.results };
            },
            cache: true
        }
    });
});
</script>
@endpush
