document.addEventListener('DOMContentLoaded', function() {
    let treeData = [];
    let draggedNode = null;
    let nextNodeId = 1;

    const treeContainer = document.getElementById('tree-container');
    const emptyState = document.getElementById('empty-state');
    const treeDataInput = document.getElementById('tree_data');
    const grandTotalDisplay = document.getElementById('grandTotalDisplay');

    // Item Modal Elements
    const itemModal = new bootstrap.Modal(document.getElementById('addItemModal'));
    const itemParentIdInput = document.getElementById('itemParentId');
    const itemExistingIdInput = document.getElementById('itemExistingId');
    const itemTitleInput = document.getElementById('itemTitle');
    const itemSpecInput = document.getElementById('itemSpec');
    const itemQtyInput = document.getElementById('itemQty');
    const itemUnitInput = document.getElementById('itemUnit');
    const itemPriceInput = document.getElementById('itemPrice');
    const itemTotalDisplay = document.getElementById('itemTotalDisplay');

    // Initialize Sortable for main container (Sections)
    new Sortable(treeContainer, {
        group: 'sections',
        animation: 150,
        handle: '.drag-handle',
        onEnd: function() {
            updateDataFromDOM();
        }
    });

    // Auto calculate item total
    function calculateItemTotal() {
        const qty = parseFloat(itemQtyInput.value) || 0;
        const price = parseFloat(itemPriceInput.value) || 0;
        const total = qty * price;
        itemTotalDisplay.innerText = formatRupiah(total);
    }
    itemQtyInput.addEventListener('input', calculateItemTotal);
    itemPriceInput.addEventListener('input', calculateItemTotal);

    // Format Rupiah
    function formatRupiah(amount) {
        return amount.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Safe ID Comparator (Number vs String)
    function sameNodeId(idA, idB) {
        return String(idA ?? '') === String(idB ?? '');
    }

    // Convert number to A, B... Z, AA, AB...
    function getColumnName(num) {
        for (var ret = '', a = 1, b = 26; (num -= a) >= 0; a = b, b *= 26) {
            ret = String.fromCharCode(parseInt((num % b) / a) + 65) + ret;
        }
        return ret;
    }

    // Add Section
    document.getElementById('btnAddSection').addEventListener('click', async function() {
        const id = 'node_sec_' + nextNodeId++;
        const { value: title } = await Swal.fire({
            title: 'Enter Section Name',
            input: 'text',
            inputPlaceholder: 'e.g. PRELIMINARY, CIVIL, dsb',
            showCancelButton: true,
            confirmButtonText: 'OK',
            cancelButtonText: 'Cancel',
            inputValidator: (value) => {
                if (!value) return 'Please enter a name!'
            }
        });
        
        if (title && title.trim()) {
            const nodeData = {
                id: id,
                type: 'Section',
                title: title.trim(),
                children: []
            };
            treeData.push(nodeData);
            renderTree();
        }
    });

    // Render entire tree
    function renderTree() {
        treeContainer.innerHTML = '';
        
        if (treeData.length === 0) {
            emptyState.style.display = 'block';
            treeDataInput.value = '';
            grandTotalDisplay.innerText = '0,00';
            return;
        }

        emptyState.style.display = 'none';
        let grandTotal = 0;

        treeData.forEach(section => {
            let sectionTotal = 0;
            const sectionEl = createNodeElement(section);
            
            const groupsContainer = document.createElement('div');
            groupsContainer.className = 'sortable-groups ps-4 pb-2';
            groupsContainer.dataset.parentId = section.id;
            
            section.children.forEach(group => {
                let groupTotal = 0;
                const groupEl = createNodeElement(group);
                
                const itemsContainer = document.createElement('div');
                itemsContainer.className = 'sortable-items ps-5 pb-2';
                itemsContainer.dataset.parentId = group.id;

                group.children.forEach(item => {
                    const itemEl = createNodeElement(item);
                    itemsContainer.appendChild(itemEl);
                    groupTotal += (parseFloat(item.qty || 0) * parseFloat(item.unit_price || 0));
                });
                
                // Add group's own nominal if any
                const groupOwnTotal = (parseFloat(group.qty || 0) * parseFloat(group.unit_price || 0));
                groupTotal += groupOwnTotal;

                // Set group total
                groupEl.querySelector('.node-total').innerText = 'Rp ' + formatRupiah(groupTotal);
                sectionTotal += groupTotal;

                groupEl.querySelector('.tree-node-content').appendChild(itemsContainer);
                groupsContainer.appendChild(groupEl);
                
                // Init sortable for items within this group
                new Sortable(itemsContainer, {
                    group: 'items-' + group.id,
                    animation: 150,
                    handle: '.drag-handle',
                    onEnd: function() { updateDataFromDOM(); }
                });
            });

            // Set section total
            sectionEl.querySelector('.node-total').innerText = 'Rp ' + formatRupiah(sectionTotal);
            grandTotal += sectionTotal;

            sectionEl.querySelector('.tree-node-content').appendChild(groupsContainer);
            treeContainer.appendChild(sectionEl);

            // Init sortable for groups within this section
            new Sortable(groupsContainer, {
                group: 'groups-' + section.id,
                animation: 150,
                handle: '.drag-handle',
                onEnd: function() { updateDataFromDOM(); }
            });
        });

        grandTotalDisplay.innerText = formatRupiah(grandTotal);
        updateHiddenInput();
        updateNumbering();
    }

    function updateNumbering() {
        let secIndex = 1;
        Array.from(treeContainer.children).forEach(sectionEl => {
            if (sectionEl.id === 'empty-state') return;
            
            const numSpan = sectionEl.querySelector('.tree-node-header > div > .node-number');
            if (numSpan) numSpan.innerText = '[' + secIndex + '.] ';
            
            let grpIndex = 1;
            const groupsContainer = sectionEl.querySelector('.sortable-groups');
            if (groupsContainer) {
                Array.from(groupsContainer.children).forEach(groupEl => {
                    const numSpanGrp = groupEl.querySelector('.tree-node-header > div > .node-number');
                    if (numSpanGrp) numSpanGrp.innerText = '[' + grpIndex + '] ';
                    
                    let itmIndex = 1;
                    const itemsContainer = groupEl.querySelector('.sortable-items');
                    if (itemsContainer) {
                        Array.from(itemsContainer.children).forEach(itemEl => {
                            const numSpanItm = itemEl.querySelector('.tree-node-header > div > .node-number');
                            if (numSpanItm) numSpanItm.innerText = '[' + itmIndex + '] ';
                            itmIndex++;
                        });
                    }
                    grpIndex++;
                });
            }
            secIndex++;
        });
    }

    // Create DOM element for a node
    function createNodeElement(node) {
        const div = document.createElement('div');
        div.className = `tree-node node-${node.type.toLowerCase()}`;
        div.dataset.id = node.id;
        div.dataset.type = node.type;

        let actionsHtml = '';
        let contentHtml = '';
        let nodeTitleHtml = node.title;
        
        if (node.type === 'Section') {
            actionsHtml = `
                <span class="node-total me-3 fw-bold text-success">Rp 0,00</span>
                <button type="button" class="btn btn-sm btn-light btn-add-group" title="Add Group"><i class="bi bi-plus"></i> Group</button>
                <button type="button" class="btn btn-sm btn-light text-warning btn-edit-node" title="Edit Section"><i class="bi bi-pencil"></i></button>
                <button type="button" class="btn btn-sm btn-light text-danger btn-delete-node" title="Delete"><i class="bi bi-trash"></i></button>
            `;
            contentHtml = `<div class="tree-node-content"></div>`;
        } else if (node.type === 'Group') {
            let specHtml = node.specification ? `<br><small class="text-muted fw-normal">${node.specification}</small>` : '';
            let qtyUnitHtml = (node.qty && node.unit) ? `<span class="badge bg-secondary me-2">${node.qty} ${node.unit}</span>` : '';
            
            actionsHtml = `
                ${qtyUnitHtml}
                <span class="node-total me-3 fw-bold text-success">Rp 0,00</span>
                <button type="button" class="btn btn-sm btn-light text-primary btn-edit-item" title="Edit Nominal"><i class="bi bi-currency-dollar"></i> Nominal</button>
                <button type="button" class="btn btn-sm btn-light btn-add-item" title="Add Item"><i class="bi bi-plus"></i> Item</button>
                <button type="button" class="btn btn-sm btn-light text-warning btn-edit-node" title="Edit Group Name"><i class="bi bi-pencil"></i></button>
                <button type="button" class="btn btn-sm btn-light text-danger btn-delete-node" title="Delete"><i class="bi bi-trash"></i></button>
            `;
            contentHtml = `<div class="tree-node-content"></div>`;
            nodeTitleHtml = `<strong>${node.title}</strong>${specHtml}`;
        } else if (node.type === 'Item') {
            let specHtml = node.specification ? `<br><small class="text-muted fw-normal">${node.specification}</small>` : '';
            let qtyUnitHtml = (node.qty && node.unit) ? `<span class="badge bg-secondary me-2">${node.qty} ${node.unit}</span>` : '';
            let priceHtml = `<span class="me-3 fw-bold text-success">Rp ${formatRupiah((parseFloat(node.qty||0) * parseFloat(node.unit_price||0)))}</span>`;
            
            div.innerHTML = `
                <div class="tree-node-header">
                    <div>
                        <i class="bi bi-grip-vertical drag-handle me-2 text-muted"></i>
                        <span class="node-number fw-bold text-primary"></span>
                        <strong>${node.title}</strong>${specHtml}
                    </div>
                    <div>
                        ${qtyUnitHtml}
                        ${priceHtml}
                        <button type="button" class="btn btn-sm btn-light text-warning btn-edit-item" title="Edit Item"><i class="bi bi-pencil"></i></button>
                        <button type="button" class="btn btn-sm btn-light text-danger btn-delete-node" title="Delete"><i class="bi bi-trash"></i></button>
                    </div>
                </div>
            `;
            
            // Event listeners for item buttons
            div.querySelector('.btn-edit-item').addEventListener('click', () => editItemNode(node));
            div.querySelector('.btn-delete-node').addEventListener('click', () => deleteNode(node.id));
            
            return div;
        }

        div.innerHTML = `
            <div class="tree-node-header">
                <div>
                    <i class="bi bi-grip-vertical drag-handle me-2 text-muted"></i>
                    <span class="node-number fw-bold text-primary"></span>
                    ${nodeTitleHtml}
                </div>
                <div>${actionsHtml}</div>
            </div>
            ${contentHtml}
        `;

        if (node.type === 'Section') {
            div.querySelector('.btn-add-group').addEventListener('click', () => addGroup(node.id));
        } else if (node.type === 'Group') {
            div.querySelector('.btn-add-item').addEventListener('click', () => openItemModal(node.id));
            div.querySelector('.btn-edit-item').addEventListener('click', () => editItemNode(node));
        }

        div.querySelector('.btn-edit-node').addEventListener('click', () => editSimpleNode(node));
        div.querySelector('.btn-delete-node').addEventListener('click', () => deleteNode(node.id));

        return div;
    }

    // Add Group
    async function addGroup(sectionId) {
        const { value: title } = await Swal.fire({
            title: 'Enter Group Name',
            input: 'text',
            inputPlaceholder: 'e.g. Pekerjaan Tanah, Pekerjaan Beton',
            showCancelButton: true,
            confirmButtonText: 'OK',
            cancelButtonText: 'Cancel',
            inputValidator: (value) => {
                if (!value) return 'Please enter a name!'
            }
        });

        if (title && title.trim()) {
            const section = treeData.find(s => sameNodeId(s.id, sectionId));
            if (section) {
                section.children.push({
                    id: 'node_grp_' + nextNodeId++,
                    type: 'Group',
                    title: title.trim(),
                    children: []
                });
                renderTree();
            }
        }
    }

    // Edit Simple Node (Section or Group)
    async function editSimpleNode(node) {
        const { value: newTitle } = await Swal.fire({
            title: `Edit ${node.type} Name`,
            input: 'text',
            inputValue: node.title,
            showCancelButton: true,
            confirmButtonText: 'Save',
            cancelButtonText: 'Cancel',
            inputValidator: (value) => {
                if (!value) return 'Please enter a name!'
            }
        });

        if (newTitle && newTitle.trim()) {
            // Find and update in treeData
            updateNodeTitle(treeData, node.id, newTitle.trim());
            renderTree();
        }
    }

    function updateNodeTitle(nodes, id, newTitle) {
        for (let i = 0; i < nodes.length; i++) {
            if (sameNodeId(nodes[i].id, id)) {
                nodes[i].title = newTitle;
                return true;
            }
            if (nodes[i].children && nodes[i].children.length > 0) {
                if (updateNodeTitle(nodes[i].children, id, newTitle)) return true;
            }
        }
        return false;
    }

    // Delete Node
    async function deleteNode(id) {
        const result = await Swal.fire({
            title: 'Are you sure?',
            text: "You want to delete this item and all its contents?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        });

        if (result.isConfirmed) {
            treeData = removeNodeById(treeData, id);
            renderTree();
        }
    }

    function removeNodeById(nodes, id) {
        return nodes.filter(node => {
            if (sameNodeId(node.id, id)) return false;
            if (node.children) {
                node.children = removeNodeById(node.children, id);
            }
            return true;
        });
    }

    // Item Modal Functions
    window.openItemModal = function(groupId) {
        itemParentIdInput.value = groupId;
        itemExistingIdInput.value = '';
        itemTitleInput.value = '';
        itemSpecInput.value = '';
        itemQtyInput.value = '';
        itemUnitInput.value = '';
        itemPriceInput.value = '';
        itemTotalDisplay.innerText = '0,00';
        itemModal.show();
    };

    window.editItemNode = function(node) {
        itemParentIdInput.value = '';
        itemExistingIdInput.value = node.id;
        itemTitleInput.value = node.title;
        itemSpecInput.value = node.specification || '';
        itemQtyInput.value = node.qty || '';
        itemUnitInput.value = node.unit || '';
        itemPriceInput.value = node.unit_price || '';
        calculateItemTotal();
        itemModal.show();
    };

    window.saveItemNode = function() {
        if (!itemTitleInput.value.trim()) {
            alert('Work description is required');
            return;
        }

        const itemData = {
            id: itemExistingIdInput.value || ('node_itm_' + nextNodeId++),
            type: (itemExistingIdInput.value && itemExistingIdInput.value.includes('grp')) ? 'Group' : 'Item',
            title: itemTitleInput.value.trim(),
            specification: itemSpecInput.value.trim(),
            qty: parseFloat(itemQtyInput.value) || 0,
            unit: itemUnitInput.value.trim(),
            unit_price: parseFloat(itemPriceInput.value) || 0
        };

        if (itemExistingIdInput.value) {
            // Update existing
            updateExistingItemNode(treeData, itemData);
        } else {
            // Add new
            const groupId = itemParentIdInput.value;
            for (let s = 0; s < treeData.length; s++) {
                for (let g = 0; g < treeData[s].children.length; g++) {
                    if (sameNodeId(treeData[s].children[g].id, groupId)) {
                        treeData[s].children[g].children.push(itemData);
                        break;
                    }
                }
            }
        }

        itemModal.hide();
        renderTree();
    };

    function updateExistingItemNode(nodes, itemData) {
        for (let i = 0; i < nodes.length; i++) {
            if (sameNodeId(nodes[i].id, itemData.id)) {
                // Do not override node.type if it's Group!
                // itemData.type might be 'Item' by default if we didn't handle it perfectly,
                // but we explicitly preserve the node's type.
                const updatedData = { ...itemData, type: nodes[i].type };
                Object.assign(nodes[i], updatedData);
                return true;
            }
            if (nodes[i].children && nodes[i].children.length > 0) {
                if (updateExistingItemNode(nodes[i].children, itemData)) return true;
            }
        }
        return false;
    }

    // Sync DOM state back to treeData after sorting
    function updateDataFromDOM() {
        const newTreeData = [];
        
        // Loop sections
        const sections = treeContainer.children;
        for (let s = 0; s < sections.length; s++) {
            const secEl = sections[s];
            if (secEl.id === 'empty-state') continue;
            
            const secId = secEl.dataset.id;
            const originalSec = findNodeById(treeData, secId);
            if (!originalSec) continue;

            const newSec = { ...originalSec, children: [] };
            
            // Loop groups
            const groupsContainer = secEl.querySelector('.sortable-groups');
            if (groupsContainer) {
                const groups = groupsContainer.children;
                for (let g = 0; g < groups.length; g++) {
                    const grpEl = groups[g];
                    const grpId = grpEl.dataset.id;
                    const originalGrp = findNodeById(originalSec.children, grpId);
                    if (!originalGrp) continue;

                    const newGrp = { ...originalGrp, children: [] };
                    
                    // Loop items
                    const itemsContainer = grpEl.querySelector('.sortable-items');
                    if (itemsContainer) {
                        const items = itemsContainer.children;
                        for (let i = 0; i < items.length; i++) {
                            const itmEl = items[i];
                            const itmId = itmEl.dataset.id;
                            const originalItm = findNodeById(originalGrp.children, itmId);
                            if (originalItm) {
                                newGrp.children.push(originalItm);
                            }
                        }
                    }
                    newSec.children.push(newGrp);
                }
            }
            newTreeData.push(newSec);
        }
        
        treeData = newTreeData;
        updateHiddenInput();
        updateNumbering();
    }

    function findNodeById(nodes, id) {
        for (let i = 0; i < nodes.length; i++) {
            if (sameNodeId(nodes[i].id, id)) return nodes[i];
            if (nodes[i].children) {
                const found = findNodeById(nodes[i].children, id);
                if (found) return found;
            }
        }
        return null;
    }

    function updateHiddenInput() {
        treeDataInput.value = JSON.stringify(treeData);
    }

    // Form submission validation
    document.getElementById('rabForm').addEventListener('submit', function(e) {
        if (treeData.length === 0) {
            e.preventDefault();
            alert('Please add at least one section and some items to the RAB before saving.');
            return false;
        }
        
        const btn = document.getElementById('btnSave');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
    });

    // Initialize if editing
    if (window.existingTreeData && window.existingTreeData.length > 0) {
        treeData = window.existingTreeData;
        
        // Find max node ID to avoid collisions
        let maxId = 0;
        function checkIds(nodes) {
            nodes.forEach(n => {
                if (typeof n.id === 'string' && n.id.startsWith('node_')) {
                    const parts = n.id.split('_');
                    if (parts.length === 3) {
                        const num = parseInt(parts[2]);
                        if (!isNaN(num) && num > maxId) maxId = num;
                    }
                }
                if (typeof n.id === 'number' && n.id > maxId) maxId = n.id;
                
                if (n.children) checkIds(n.children);
            });
        }
        checkIds(treeData);
        nextNodeId = maxId + 1;
        
        renderTree();
    }
});
