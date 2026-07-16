document.addEventListener('DOMContentLoaded', function() {
    let nodeIdCounter = 0;
    
    const treeContainer = document.getElementById('tree-container');
    const emptyState = document.getElementById('empty-state');
    const btnAddCategory = document.getElementById('btnAddCategory');
    const form = document.getElementById('surveyReportForm');
    
    // (Existing tree data rendering moved to bottom)

    // Init Sortable for root (Categories)
    new Sortable(treeContainer, {
        group: 'categories',
        animation: 150,
        fallbackOnBody: true,
        swapThreshold: 0.65,
        handle: '.drag-handle'
    });

    btnAddCategory.addEventListener('click', function() {
        Swal.fire({
            title: 'Enter Category Name',
            input: 'text',
            inputPlaceholder: 'e.g. Mechanical',
            showCancelButton: true,
            confirmButtonText: 'Add Category',
            inputValidator: (value) => {
                if (!value) {
                    return 'Category name cannot be empty!'
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                addCategoryNode(result.value);
                updateEmptyState();
            }
        });
    });

    function generateId() {
        nodeIdCounter++;
        return 'node_' + Date.now() + '_' + nodeIdCounter;
    }

    function addCategoryNode(title, existingId = null) {
        const id = existingId || generateId();
        const html = `
            <div class="tree-node node-category" data-id="${id}" data-type="category" data-title="${escapeHtml(title)}">
                <div class="tree-node-header">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-grip-vertical text-muted me-2 drag-handle"></i>
                        <span><i class="bi bi-folder-fill text-warning me-2"></i> <strong>${escapeHtml(title)}</strong></span>
                    </div>
                    <div>
                        <button type="button" class="btn btn-sm btn-light text-primary btn-add-group" onclick="addGroup('${id}')"><i class="bi bi-plus"></i> Group</button>
                        <button type="button" class="btn btn-sm btn-light text-danger btn-delete-node" onclick="deleteNode('${id}')"><i class="bi bi-trash"></i></button>
                    </div>
                </div>
                <div class="tree-node-content list-group sortable-group" id="container_${id}">
                    <!-- Groups will go here -->
                </div>
            </div>
        `;
        treeContainer.insertAdjacentHTML('beforeend', html);
        
        // Init Sortable for Groups inside this Category
        new Sortable(document.getElementById(`container_${id}`), {
            group: 'groups',
            animation: 150,
            fallbackOnBody: true,
            swapThreshold: 0.65,
            handle: '.drag-handle'
        });
    }

    window.addGroup = function(parentId) {
        Swal.fire({
            title: 'Enter Group Name',
            input: 'text',
            inputPlaceholder: 'e.g. Instalasi Pipa',
            showCancelButton: true,
            confirmButtonText: 'Add Group',
            inputValidator: (value) => {
                if (!value) {
                    return 'Group name cannot be empty!'
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                window.addGroupDirectly(parentId, result.value);
            }
        });
    }

    window.addGroupDirectly = function(parentId, title, existingId = null) {
        const parentContainer = document.getElementById(`container_${parentId}`);
        if (!parentContainer) return;
        const id = existingId || generateId();
        
        const html = `
            <div class="tree-node node-group" data-id="${id}" data-type="group" data-title="${escapeHtml(title)}">
                <div class="tree-node-header">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-grip-vertical text-muted me-2 drag-handle"></i>
                        <span><i class="bi bi-layers-fill text-info me-2"></i> ${escapeHtml(title)}</span>
                    </div>
                    <div>
                        <button type="button" class="btn btn-sm btn-light text-success btn-add-item" onclick="openItemModal('${id}')"><i class="bi bi-plus"></i> Item</button>
                        <button type="button" class="btn btn-sm btn-light text-danger btn-delete-node" onclick="deleteNode('${id}')"><i class="bi bi-trash"></i></button>
                    </div>
                </div>
                <div class="tree-node-content list-group sortable-item" id="container_${id}">
                    <!-- Items will go here -->
                </div>
            </div>
        `;
        parentContainer.insertAdjacentHTML('beforeend', html);
        
        // Init Sortable for Items inside this Group
        new Sortable(document.getElementById(`container_${id}`), {
            group: 'items',
            animation: 150,
            fallbackOnBody: true,
            swapThreshold: 0.65,
            handle: '.drag-handle'
        });
    }

    window.openItemModal = function(parentId) {
        document.getElementById('itemParentId').value = parentId;
        document.getElementById('itemTitle').value = '';
        document.getElementById('itemQty').value = '';
        document.getElementById('itemRemark').value = '';
        document.getElementById('itemAttachments').value = '';
        document.getElementById('previewContainer').innerHTML = '';
        
        // Use bootstrap modal
        const modal = new bootstrap.Modal(document.getElementById('addItemModal'));
        modal.show();
    }
    
    // File upload preview logic
    document.getElementById('itemAttachments').addEventListener('change', function(e) {
        const container = document.getElementById('previewContainer');
        container.innerHTML = '';
        for (let i = 0; i < this.files.length; i++) {
            const file = this.files[i];
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.style.width = '60px';
                img.style.height = '60px';
                img.style.objectFit = 'cover';
                img.className = 'me-2 mb-2 rounded border';
                container.appendChild(img);
            }
            reader.readAsDataURL(file);
        }
    });

    // Store File objects in a global map linked to Node IDs
    window.itemFilesMap = new Map();

    window.addItemDirectly = function(parentId, title, qty, remark, existingId = null, existingAttachments = []) {
        const parentContainer = document.getElementById(`container_${parentId}`);
        if (!parentContainer) return;
        const id = existingId || generateId();
        
        let attachmentBadge = '';
        if (existingAttachments && existingAttachments.length > 0) {
            attachmentBadge = `<span class="badge bg-secondary ms-2"><i class="bi bi-paperclip"></i> ${existingAttachments.length}</span>`;
            // Also store existing attachments in the DOM so we can keep them when submitting
            const container = document.createElement('div');
            container.id = `existing_attachments_${id}`;
            container.style.display = 'none';
            existingAttachments.forEach(att => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `existing_attachments[${id}][]`;
                input.value = att.id;
                container.appendChild(input);
            });
            form.appendChild(container);
        }

        const qtyBadge = qty ? `<span class="badge bg-info ms-2">${escapeHtml(qty)}</span>` : '';
        
        const html = `
            <div class="tree-node node-item" data-id="${id}" data-type="item" data-title="${escapeHtml(title)}" data-qty="${escapeHtml(qty)}" data-remark="${escapeHtml(remark)}">
                <div class="tree-node-header">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-grip-vertical text-muted me-2 drag-handle"></i>
                        <span><i class="bi bi-record-circle text-primary me-2"></i> ${escapeHtml(title)} ${qtyBadge} ${attachmentBadge}</span>
                    </div>
                    <div>
                        <button type="button" class="btn btn-sm btn-light text-danger btn-delete-node" onclick="deleteNode('${id}')"><i class="bi bi-trash"></i></button>
                    </div>
                </div>
            </div>
        `;
        parentContainer.insertAdjacentHTML('beforeend', html);
    }

    window.saveItemNode = function() {
        const parentId = document.getElementById('itemParentId').value;
        const title = document.getElementById('itemTitle').value;
        const qty = document.getElementById('itemQty').value;
        const remark = document.getElementById('itemRemark').value;
        const fileInput = document.getElementById('itemAttachments');
        
        if (!title) {
            alert('Item title is required');
            return;
        }

        const id = generateId();
        
        // Store files in map
        if (fileInput.files.length > 0) {
            window.itemFilesMap.set(id, Array.from(fileInput.files));
        }

        window.addItemDirectly(parentId, title, qty, remark, id, fileInput.files.length > 0 ? Array(fileInput.files.length).fill({}) : []);
        
        const modal = bootstrap.Modal.getInstance(document.getElementById('addItemModal'));
        modal.hide();
    }

    window.deleteNode = function(id) {
        if (confirm('Delete this node and all its contents?')) {
            const node = document.querySelector(`[data-id="${id}"]`);
            if (node) {
                node.remove();
                window.itemFilesMap.delete(id); // Remove files if any
                updateEmptyState();
            }
        }
    }

    function updateEmptyState() {
        if (treeContainer.children.length > 0) {
            emptyState.style.display = 'none';
        } else {
            emptyState.style.display = 'block';
        }
    }

    function escapeHtml(unsafe) {
        return (unsafe || '').replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }

    // Overwrite form submit
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Build JSON tree
        const treeData = [];
        
        const categories = treeContainer.querySelectorAll(':scope > .node-category');
        categories.forEach(cat => {
            const catData = {
                id: cat.dataset.id,
                type: 'category',
                title: cat.dataset.title,
                children: []
            };
            
            const groups = cat.querySelector(':scope > .tree-node-content').querySelectorAll(':scope > .node-group');
            groups.forEach(grp => {
                const grpData = {
                    id: grp.dataset.id,
                    type: 'group',
                    title: grp.dataset.title,
                    children: []
                };
                
                const items = grp.querySelector(':scope > .tree-node-content').querySelectorAll(':scope > .node-item');
                items.forEach(itm => {
                    grpData.children.push({
                        id: itm.dataset.id,
                        type: 'item',
                        title: itm.dataset.title,
                        qty: itm.dataset.qty,
                        remark: itm.dataset.remark
                    });
                });
                
                catData.children.push(grpData);
            });
            
            treeData.push(catData);
        });

        // Use FormData
        const formData = new FormData(form);
        formData.set('tree_data', JSON.stringify(treeData));
        
        // Append files
        window.itemFilesMap.forEach((files, nodeId) => {
            files.forEach((file, index) => {
                formData.append(`attachments[${nodeId}][]`, file);
            });
        });

        const btnSave = document.getElementById('btnSave');
        const originalText = btnSave.innerHTML;
        btnSave.disabled = true;
        btnSave.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.href = data.redirect;
            } else {
                alert('Error saving data: ' + (data.message || 'Unknown error'));
                btnSave.disabled = false;
                btnSave.innerHTML = originalText;
            }
        })
        .catch(err => {
            console.error(err);
            alert('An error occurred while saving the report.');
            btnSave.disabled = false;
            btnSave.innerHTML = originalText;
        });
    });

    // Check if we have existing data (for edit page)
    if (window.existingTreeData && window.existingTreeData.length > 0) {
        emptyState.style.display = 'none';
        window.existingTreeData.forEach(cat => {
            addCategoryNode(cat.title, cat.id);
            if (cat.children) {
                cat.children.forEach(grp => {
                    window.addGroupDirectly(cat.id, grp.title, grp.id);
                    if (grp.children) {
                        grp.children.forEach(itm => {
                            window.addItemDirectly(grp.id, itm.title, itm.qty, itm.remark, itm.id, itm.existing_attachments);
                        });
                    }
                });
            }
        });
    }
});
