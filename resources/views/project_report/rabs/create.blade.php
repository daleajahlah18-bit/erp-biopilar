@extends('layouts.app')
@section('title', 'Create RAB')
@section('page_title', 'Create RAB')

@section('content')
<div class="row">
    <div class="col-12">
        <form action="{{ route('rabs.store') }}" method="POST" id="rabForm">
            @csrf
            
            <!-- General Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">General Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Project <span class="text-danger">*</span></label>
                            <select name="project_id" class="form-select select2" required>
                                <option value="">Select Project...</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}">{{ $project->project_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">RAB Name <span class="text-danger">*</span></label>
                            <input type="text" name="rab_name" class="form-control" required placeholder="e.g. RAB Sipil Tahap 1">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tree Builder -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">RAB Items (Tree Builder)</h6>
                    <div>
                        <span class="me-4 fw-bold fs-5 text-success">Grand Total: Rp <span id="grandTotalDisplay">0</span></span>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddSection">
                            <i class="bi bi-plus-lg"></i> Add Section
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="tree-container" class="list-group list-group-flush sortable-tree">
                        <!-- Sections will be added here -->
                    </div>
                    <div id="empty-state" class="text-center py-5 text-muted">
                        <i class="bi bi-diagram-3 fs-1 text-light mb-3 d-block"></i>
                        <p>No items added yet. Click "Add Section" to start building your RAB.</p>
                    </div>
                    
                    <!-- Hidden input to store JSON data -->
                    <input type="hidden" name="tree_data" id="tree_data">
                </div>
            </div>

            <div class="d-flex justify-content-end mb-5">
                <a href="{{ route('rabs.index') }}" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-success" id="btnSave">Save RAB</button>
            </div>
        </form>
    </div>
</div>

<!-- Item Modal -->
<div class="modal fade" id="addItemModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add / Edit Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="itemParentId">
                <input type="hidden" id="itemExistingId">
                <div class="mb-3">
                    <label class="form-label">Work Description <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="itemTitle">
                </div>
                <div class="mb-3">
                    <label class="form-label">Specification</label>
                    <input type="text" class="form-control" id="itemSpec">
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Quantity</label>
                        <input type="number" step="0.01" class="form-control" id="itemQty" placeholder="0">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Unit</label>
                        <input type="text" class="form-control" id="itemUnit" placeholder="e.g. M3, LS, Unit">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Unit Cost (Rp)</label>
                    <input type="number" step="0.01" class="form-control" id="itemPrice" placeholder="0">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Total Price: Rp <span id="itemTotalDisplay">0</span></label>
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

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .sortable-tree { min-height: 50px; }
    .tree-node { border: 1px solid #ddd; margin-bottom: 5px; border-radius: 4px; background: #fff; }
    .tree-node-header { padding: 10px; cursor: move; display: flex; justify-content: space-between; align-items: center; background: #f8f9fc; }
    .tree-node-content { padding: 10px; border-top: 1px solid #ddd; }
    .node-section > .tree-node-header { background: #e3e6f0; font-weight: bold; }
    .node-group > .tree-node-header { background: #f8f9fc; font-weight: 600; margin-left: 20px; }
    .node-item > .tree-node-header { background: #fff; margin-left: 40px; cursor: default; }
    .drag-handle { cursor: move; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
    window.existingTreeData = [];
</script>
<script src="{{ asset('js/rab_tree_builder.js') }}"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2();
    });
</script>
@endpush
