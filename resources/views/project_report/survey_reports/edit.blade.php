@extends('layouts.app')
@section('title', 'Edit Survey Report')
@section('page_title', 'Edit Survey Report')

@section('content')
<div class="row">
    <div class="col-12">
        <form action="{{ route('survey-reports.update', $report->id) }}" method="POST" id="surveyReportForm" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <!-- General Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">General Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Client Name <span class="text-danger">*</span></label>
                            <input type="text" name="client_name" class="form-control" value="{{ $report->client_name }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Survey Location <span class="text-danger">*</span></label>
                            <input type="text" name="survey_location" class="form-control" value="{{ $report->survey_location }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">PIC Client</label>
                            <input type="text" name="pic_client" class="form-control" value="{{ $report->pic_client }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone_number" class="form-control" value="{{ $report->phone_number }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Survey Date <span class="text-danger">*</span></label>
                            <input type="date" name="survey_date" class="form-control" value="{{ $report->survey_date->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Surveyor <span class="text-danger">*</span></label>
                            <input type="text" name="surveyor" class="form-control" value="{{ $report->surveyor }}" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Client Address</label>
                            <textarea name="client_address" class="form-control" rows="2">{{ $report->client_address }}</textarea>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Opening Description <span class="text-danger">*</span></label>
                            <textarea name="opening_description" class="form-control" rows="3" required>{{ $report->opening_description }}</textarea>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Closing Description</label>
                            <textarea name="closing_description" class="form-control" rows="3">{{ $report->closing_description }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tree Builder -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Survey Items (Tree Builder)</h6>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddCategory">
                            <i class="bi bi-plus-lg"></i> Add Category
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="tree-container" class="list-group list-group-flush sortable-tree">
                        <!-- Categories will be added here -->
                    </div>
                    <div id="empty-state" class="text-center py-5 text-muted">
                        <i class="bi bi-diagram-3 fs-1 text-light mb-3 d-block"></i>
                        <p>No items added yet. Click "Add Category" to start building your survey report.</p>
                    </div>
                    
                    <!-- Hidden input to store JSON data -->
                    <input type="hidden" name="tree_data" id="tree_data">
                </div>
            </div>

            <div class="d-flex justify-content-end mb-5">
                <a href="{{ route('survey-reports.index') }}" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-success" id="btnSave">Update Survey Report</button>
            </div>
        </form>
    </div>
</div>

<!-- Item Modal -->
<div class="modal fade" id="addItemModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="itemParentId">
                <div class="mb-3">
                    <label class="form-label">Item Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="itemTitle">
                </div>
                <div class="mb-3">
                    <label class="form-label">Quantity</label>
                    <input type="text" class="form-control" id="itemQty" placeholder="e.g. 1 Unit, 2 Lot, 5 Meter">
                </div>
                <div class="mb-3">
                    <label class="form-label">Remark</label>
                    <textarea class="form-control" id="itemRemark" rows="2" placeholder="Notes for PDF"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Attachments (Images)</label>
                    <input type="file" class="form-control" id="itemAttachments" multiple accept="image/*">
                    <div id="previewContainer" class="mt-2 d-flex flex-wrap"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveItemNode()">Save Item</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    window.existingTreeData = {!! json_encode($treeData ?? []) !!};
</script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script src="{{ asset('js/survey_report_tree.js') }}"></script>
<style>
    .sortable-tree { min-height: 50px; }
    .tree-node { border: 1px solid #ddd; margin-bottom: 5px; border-radius: 4px; background: #fff; }
    .tree-node-header { padding: 10px; cursor: move; display: flex; justify-content: space-between; align-items: center; background: #f8f9fc; }
    .tree-node-content { padding: 10px; border-top: 1px solid #ddd; }
    .node-category > .tree-node-header { background: #e3e6f0; font-weight: bold; }
    .node-group > .tree-node-header { background: #f8f9fc; font-weight: 600; margin-left: 20px; }
    .node-item > .tree-node-header { background: #fff; margin-left: 40px; cursor: default; }
    .drag-handle { cursor: move; }
</style>
@endpush
