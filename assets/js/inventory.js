// Inventory Management JavaScript

// Sanitize HTML to prevent XSS
function sanitizeHTML(str) {
    const temp = document.createElement('div');
    temp.textContent = str;
    return temp.innerHTML;
}

document.addEventListener('DOMContentLoaded', function() {
    // Load tabs when clicked
    document.querySelector('a[href="#lowStockTab"]').addEventListener('click', loadLowStock);
    document.querySelector('a[href="#expiringTab"]').addEventListener('click', loadExpiring);

    // Add Item
    document.getElementById('saveItemBtn').addEventListener('click', function() {
        const form = document.getElementById('addItemForm');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);

        fetch('api/inventory.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert('Item added successfully!');
                form.reset();
                bootstrap.Modal.getInstance(document.getElementById('addItemModal')).hide();
                location.reload();
            } else {
                alert('Error: ' + result.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while adding item');
        });
    });

    // Edit Item
    document.querySelectorAll('.edit-item').forEach(button => {
        button.addEventListener('click', function() {
            const itemId = this.getAttribute('data-id');
            
            fetch(`api/inventory.php?id=${itemId}`)
            .then(response => response.json())
            .then(result => {
                if (result.success && result.item) {
                    const item = result.item;
                    document.getElementById('edit_item_id').value = item.id;
                    document.getElementById('edit_item_code').value = item.item_code;
                    document.getElementById('edit_item_name').value = item.item_name;
                    document.getElementById('edit_category').value = item.category;
                    document.getElementById('edit_unit').value = item.unit;
                    document.getElementById('edit_reorder_level').value = item.reorder_level;
                    document.getElementById('edit_description').value = item.description || '';
                    
                    new bootstrap.Modal(document.getElementById('editItemModal')).show();
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });

    // Update Item
    document.getElementById('updateItemBtn').addEventListener('click', function() {
        const form = document.getElementById('editItemForm');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);

        fetch('api/inventory.php', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert('Item updated successfully!');
                bootstrap.Modal.getInstance(document.getElementById('editItemModal')).hide();
                location.reload();
            } else {
                alert('Error: ' + result.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating item');
        });
    });

    // Delete Item
    document.querySelectorAll('.delete-item').forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Are you sure you want to delete this item?')) {
                const itemId = this.getAttribute('data-id');
                
                fetch(`api/inventory.php?id=${itemId}`, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        alert('Item deleted successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + result.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting item');
                });
            }
        });
    });

    // Add Batch
    document.querySelectorAll('.add-batch').forEach(button => {
        button.addEventListener('click', function() {
            const itemId = this.getAttribute('data-id');
            const itemName = this.getAttribute('data-name');
            
            document.getElementById('batch_item_id').value = itemId;
            document.getElementById('batch_item_name').value = itemName;
            
            new bootstrap.Modal(document.getElementById('addBatchModal')).show();
        });
    });

    // Save Batch
    document.getElementById('saveBatchBtn').addEventListener('click', function() {
        const form = document.getElementById('addBatchForm');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);
        data.action = 'batch';

        fetch('api/inventory.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert('Batch added successfully!');
                form.reset();
                bootstrap.Modal.getInstance(document.getElementById('addBatchModal')).hide();
                location.reload();
            } else {
                alert('Error: ' + result.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while adding batch');
        });
    });

    // Issue Item
    document.querySelectorAll('.issue-item').forEach(button => {
        button.addEventListener('click', function() {
            const itemId = this.getAttribute('data-id');
            const itemName = this.getAttribute('data-name');
            
            document.getElementById('issue_item_id').value = itemId;
            document.getElementById('issue_item_name').value = itemName;
            
            new bootstrap.Modal(document.getElementById('issueItemModal')).show();
        });
    });

    // Save Issue
    document.getElementById('issueBtn').addEventListener('click', function() {
        const form = document.getElementById('issueItemForm');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);
        data.action = 'issue';

        fetch('api/inventory.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert('Item issued successfully!');
                form.reset();
                bootstrap.Modal.getInstance(document.getElementById('issueItemModal')).hide();
                location.reload();
            } else {
                alert('Error: ' + result.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while issuing item');
        });
    });

    // Load Categories
    function loadCategories() {
        fetch('api/inventory.php?action=categories')
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                // Handle categories data if needed
                console.log('Categories loaded:', result.categories);
            }
        })
        .catch(error => console.error('Error:', error));
    }

    // Load Low Stock
    function loadLowStock() {
        fetch('api/inventory.php?action=low_stock')
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                const tbody = document.querySelector('#lowStockTable tbody');
                tbody.innerHTML = '';
                
                result.items.forEach(item => {
                    const row = `
                        <tr>
                            <td>${sanitizeHTML(item.item_code)}</td>
                            <td>${sanitizeHTML(item.item_name)}</td>
                            <td><span class="badge bg-warning">${sanitizeHTML(item.total_quantity)}</span></td>
                            <td>${sanitizeHTML(item.reorder_level)}</td>
                            <td>${sanitizeHTML(item.category)}</td>
                        </tr>
                    `;
                    tbody.innerHTML += row;
                });
            }
        })
        .catch(error => console.error('Error:', error));
    }

    // Load Expiring Items
    function loadExpiring() {
        fetch('api/inventory.php?action=expiring')
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                const tbody = document.querySelector('#expiringTable tbody');
                tbody.innerHTML = '';
                
                result.items.forEach(batch => {
                    const expiryDate = new Date(batch.expiry_date);
                    const today = new Date();
                    const daysUntilExpiry = Math.floor((expiryDate - today) / (1000 * 60 * 60 * 24));
                    const badgeClass = daysUntilExpiry < 7 ? 'bg-danger' : 'bg-warning';
                    
                    const row = `
                        <tr>
                            <td>${sanitizeHTML(batch.batch_number)}</td>
                            <td>${sanitizeHTML(batch.item_name)}</td>
                            <td>${sanitizeHTML(batch.quantity)}</td>
                            <td>${sanitizeHTML(batch.expiry_date)}</td>
                            <td><span class="badge ${badgeClass}">${daysUntilExpiry} days</span></td>
                        </tr>
                    `;
                    tbody.innerHTML += row;
                });
            }
        })
        .catch(error => console.error('Error:', error));
    }
});
