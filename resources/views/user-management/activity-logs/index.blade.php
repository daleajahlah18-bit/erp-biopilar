@extends('layouts.app')
@section('title', 'Audit Trail')
@section('page_title', 'Enterprise Audit Trail')

@push('styles')
<style>
    .timeline {
        position: relative;
        padding-left: 3rem;
        margin-bottom: 2rem;
    }
    .timeline::before {
        content: '';
        position: absolute;
        top: 0;
        left: 15px;
        height: 100%;
        width: 2px;
        background: #e9ecef;
    }
    .timeline-item {
        position: relative;
        margin-bottom: 1.5rem;
    }
    .timeline-icon {
        position: absolute;
        left: -3rem;
        top: 0;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        z-index: 1;
        box-shadow: 0 0 0 4px #fff;
    }
    .timeline-content {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 0.5rem;
        padding: 1rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
    }
    .timeline-content:hover {
        border-color: #dee2e6;
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
    }
    .change-table th { background: #f8f9fa; }
    .old-value { background-color: #ffeef0; color: #dc3545; padding: 4px 8px; border-radius: 4px; display: inline-block; margin-bottom: 4px; }
    .new-value { background-color: #e6f8f0; color: #198754; padding: 4px 8px; border-radius: 4px; display: inline-block; }
    .change-arrow { text-align: center; color: #6c757d; font-size: 1.2rem; margin: 4px 0; }
</style>
@endpush

@section('content')
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white py-3 d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
        <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-shield-check"></i> Enterprise Audit Trail</h6>
        <div class="d-flex gap-2">
            <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}" class="btn btn-success btn-sm"><i class="bi bi-file-excel"></i> Export Excel</a>
            <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" class="btn btn-danger btn-sm"><i class="bi bi-file-pdf"></i> Export PDF</a>
        </div>
    </div>
    <div class="card-body">
        
        <!-- Filter Form -->
        <form method="GET" action="{{ route('activity-logs.index') }}" class="row g-3 mb-4 bg-light p-3 rounded">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Search description, doc number..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="module" class="form-select">
                    <option value="">All Modules</option>
                    @foreach($modules as $mod)
                        <option value="{{ $mod }}" {{ request('module') == $mod ? 'selected' : '' }}>{{ $mod }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="action" class="form-select">
                    <option value="">All Actions</option>
                    <option value="created" {{ request('action') == 'created' ? 'selected' : '' }}>Created</option>
                    <option value="updated" {{ request('action') == 'updated' ? 'selected' : '' }}>Updated</option>
                    <option value="deleted" {{ request('action') == 'deleted' ? 'selected' : '' }}>Deleted</option>
                    <option value="login" {{ request('action') == 'login' ? 'selected' : '' }}>Login</option>
                    <option value="logout" {{ request('action') == 'logout' ? 'selected' : '' }}>Logout</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="user_id" class="form-select">
                    <option value="">All Users</option>
                    @foreach($users as $usr)
                        <option value="{{ $usr->id }}" {{ request('user_id') == $usr->id ? 'selected' : '' }}>{{ $usr->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Search</button>
                <a href="{{ route('activity-logs.index') }}" class="btn btn-secondary w-100">Reset</a>
            </div>
        </form>

        <!-- Timeline -->
        <div class="timeline">
            @forelse($logs as $log)
                @php
                    $icon = 'bi-info-circle';
                    $bg = 'bg-primary';
                    if($log->event == 'created') { $icon = 'bi-plus-circle'; $bg = 'bg-success'; }
                    elseif($log->event == 'updated') { $icon = 'bi-pencil-square'; $bg = 'bg-warning text-dark'; }
                    elseif($log->event == 'deleted') { $icon = 'bi-trash'; $bg = 'bg-danger'; }
                    elseif(in_array($log->event, ['login', 'logout'])) { $icon = 'bi-door-open'; $bg = 'bg-info'; }
                @endphp
                <div class="timeline-item">
                    <div class="timeline-icon {{ $bg }}">
                        <i class="bi {{ $icon }}"></i>
                    </div>
                    <div class="timeline-content">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <span class="badge {{ $bg }} me-2">{{ ucfirst($log->event) }}</span>
                                <span class="fw-bold text-dark">{{ $log->description }}</span>
                            </div>
                            <small class="text-muted"><i class="bi bi-clock"></i> {{ $log->created_at->format('d M Y, H:i') }} WIB</small>
                        </div>
                        <div class="d-flex justify-content-between align-items-end">
                            <div class="text-muted small">
                                <div><i class="bi bi-person"></i> <strong>{{ $log->causer->name ?? 'System' }}</strong> <span class="text-muted">({{ $log->causer?->roles?->first()?->name ?? 'System Role' }})</span></div>
                                <div><i class="bi bi-box"></i> Module: <strong>{{ $log->log_name ?? 'System' }}</strong></div>
                            </div>
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-primary btn-view-detail" 
                                    data-log="{{ json_encode([
                                        'id' => $log->id,
                                        'date' => $log->created_at->format('d M Y, H:i') . ' WIB',
                                        'user' => $log->causer->name ?? 'System',
                                        'role' => $log->causer?->roles?->first()?->name ?? '-',
                                        'module' => $log->log_name,
                                        'action' => ucfirst($log->event),
                                        'description' => $log->description,
                                        'ip_address' => $log->ip_address,
                                        'browser' => $log->user_agent,
                                        'url' => $log->url,
                                        'properties' => $log->properties
                                    ]) }}">
                                    <i class="bi bi-eye"></i> View Details
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center text-muted py-5">
                    <i class="bi bi-inbox fs-1"></i>
                    <p class="mt-2">No activity logs found.</p>
                </div>
            @endforelse
        </div>
        
        <div class="d-flex justify-content-end mt-3">
            {{ $logs->links() }}
        </div>
    </div>
</div>

<!-- Detail Modal -->
<div class="modal fade" id="auditDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold"><i class="bi bi-shield-lock text-primary"></i> Audit Trail Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <h6 class="fw-bold mb-3 border-bottom pb-2">General Information</h6>
                <div class="row mb-4">
                    <div class="col-md-6 mb-2">
                        <small class="text-muted d-block">Performed By</small>
                        <strong id="m_user"></strong> <span id="m_role" class="badge bg-secondary ms-1"></span>
                    </div>
                    <div class="col-md-6 mb-2">
                        <small class="text-muted d-block">Date & Time</small>
                        <strong id="m_date"></strong>
                    </div>
                    <div class="col-md-6 mb-2">
                        <small class="text-muted d-block">Module & Action</small>
                        <strong id="m_module"></strong> - <span id="m_action" class="badge bg-primary"></span>
                    </div>
                    <div class="col-md-6 mb-2">
                        <small class="text-muted d-block">Document</small>
                        <strong id="m_description"></strong>
                    </div>
                    <div class="col-md-6 mb-2">
                        <small class="text-muted d-block">IP Address</small>
                        <code id="m_ip"></code>
                    </div>
                    <div class="col-md-6 mb-2">
                        <small class="text-muted d-block">URL Accessed</small>
                        <small id="m_url" class="text-primary text-break"></small>
                    </div>
                    <div class="col-12">
                        <small class="text-muted d-block">Browser / User Agent</small>
                        <small id="m_browser" class="text-muted"></small>
                    </div>
                </div>

                <div id="changes_section" style="display: none;">
                    <h6 class="fw-bold mb-3 border-bottom pb-2">Record Changes</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm change-table">
                            <thead>
                    <tr>
                        <th>@sortablelink('field', 'Field')</th>
                                    <th>@sortablelink('changes_(old_&rarr;_new)', 'Changes (Old &rarr; New)')</th>
                                </tr>
                            </thead>
                            <tbody id="m_changes_body">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = new bootstrap.Modal(document.getElementById('auditDetailModal'));
    
    document.querySelectorAll('.btn-view-detail').forEach(btn => {
        btn.addEventListener('click', function() {
            const data = JSON.parse(this.getAttribute('data-log'));
            
            // Populate General Info
            document.getElementById('m_user').textContent = data.user;
            document.getElementById('m_role').textContent = data.role;
            document.getElementById('m_date').textContent = data.date;
            document.getElementById('m_module').textContent = data.module;
            document.getElementById('m_action').textContent = data.action;
            document.getElementById('m_description').textContent = data.description;
            document.getElementById('m_ip').textContent = data.ip_address || '-';
            document.getElementById('m_url').textContent = data.url || '-';
            document.getElementById('m_browser').textContent = data.browser || '-';

            // Populate Changes
            const changesSection = document.getElementById('changes_section');
            const changesBody = document.getElementById('m_changes_body');
            changesBody.innerHTML = '';
            
            if (data.properties && data.properties.resolved) {
                const attrs = data.properties.resolved.attributes || {};
                const olds = data.properties.resolved.old || {};
                
                let hasChanges = false;
                
                Object.keys(attrs).forEach(key => {
                    hasChanges = true;
                    const fieldLabel = attrs[key].label || key;
                    const newValue = attrs[key].value;
                    const oldValue = olds[key] !== undefined ? olds[key] : null;
                    
                    let tr = document.createElement('tr');
                    
                    if (data.action.toLowerCase() === 'updated' && oldValue !== null) {
                        tr.innerHTML = `
                            <td class="fw-bold align-middle">${fieldLabel}</td>
                            <td>
                                <div class="d-flex flex-column align-items-start">
                                    <div class="old-value"><del>${oldValue}</del></div>
                                    <div class="change-arrow"><i class="bi bi-arrow-down"></i></div>
                                    <div class="new-value">${newValue}</div>
                                </div>
                            </td>
                        `;
                    } else {
                        // For Created or Deleted
                        let colorClass = data.action.toLowerCase() === 'deleted' ? 'old-value' : 'new-value';
                        tr.innerHTML = `
                            <td class="fw-bold align-middle">${fieldLabel}</td>
                            <td>
                                <div class="${colorClass}">${newValue}</div>
                            </td>
                        `;
                    }
                    
                    changesBody.appendChild(tr);
                });
                
                if (hasChanges) {
                    changesSection.style.display = 'block';
                } else {
                    changesSection.style.display = 'none';
                }
            } else {
                changesSection.style.display = 'none';
            }
            
            modal.show();
        });
    });
});
</script>
@endpush
