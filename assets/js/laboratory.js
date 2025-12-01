// Laboratory Management JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Load initial data
    loadOrders();
    
    // Status Filter
    document.getElementById('statusFilter').addEventListener('change', function() {
        const status = this.value;
        const rows = document.querySelectorAll('#ordersTable tbody tr');
        
        rows.forEach(row => {
            if (status === '' || row.getAttribute('data-status') === status) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    // Create Order
    document.getElementById('saveOrderBtn').addEventListener('click', function() {
        const form = document.getElementById('createOrderForm');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);
        
        // Get selected tests as array
        const testsSelect = document.getElementById('testsSelect');
        const selectedTests = Array.from(testsSelect.selectedOptions).map(option => option.value);
        data.tests = selectedTests;

        fetch('api/laboratory.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert('Lab order created successfully!');
                form.reset();
                bootstrap.Modal.getInstance(document.getElementById('createOrderModal')).hide();
                loadOrders();
            } else {
                alert('Error: ' + result.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while creating order');
        });
    });

    // Save Result
    document.getElementById('saveResultBtn').addEventListener('click', function() {
        const form = document.getElementById('updateResultForm');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);
        data.action = 'update_result';

        fetch('api/laboratory.php', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert('Test result updated successfully!');
                form.reset();
                bootstrap.Modal.getInstance(document.getElementById('updateResultModal')).hide();
                loadOrders();
            } else {
                alert('Error: ' + result.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating result');
        });
    });

    // Add Test Type
    document.getElementById('saveTestTypeBtn').addEventListener('click', function() {
        const form = document.getElementById('addTestTypeForm');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);
        data.action = 'test_types';

        fetch('api/laboratory.php?action=test_types', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert('Test type added successfully!');
                form.reset();
                bootstrap.Modal.getInstance(document.getElementById('addTestTypeModal')).hide();
                location.reload();
            } else {
                alert('Error: ' + result.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while adding test type');
        });
    });

    // Edit Test Type
    document.querySelectorAll('.edit-test-type').forEach(button => {
        button.addEventListener('click', function() {
            const testId = this.getAttribute('data-id');
            
            fetch(`api/laboratory.php?action=test_types&id=${testId}`)
            .then(response => response.json())
            .then(result => {
                if (result.success && result.test) {
                    // You would need to create an edit modal similar to add modal
                    alert('Edit functionality - implement edit test type modal');
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });

    // Delete Test Type
    document.querySelectorAll('.delete-test-type').forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Are you sure you want to delete this test type?')) {
                const testId = this.getAttribute('data-id');
                
                fetch(`api/laboratory.php?action=test_types&id=${testId}`, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        alert('Test type deleted successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + result.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting test type');
                });
            }
        });
    });
});

// Load Orders
function loadOrders() {
    fetch('api/laboratory.php')
    .then(response => response.json())
    .then(result => {
        if (result.success && result.orders) {
            updateOrdersTable(result.orders);
        }
    })
    .catch(error => console.error('Error loading orders:', error));
}

// Update Orders Table
function updateOrdersTable(orders) {
    const tbody = document.querySelector('#ordersTable tbody');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    orders.forEach(order => {
        let statusClass = 'secondary';
        if (order.status == 'Completed') statusClass = 'success';
        else if (order.status == 'In Progress') statusClass = 'warning';
        else if (order.status == 'Pending') statusClass = 'info';
        
        let priorityClass = 'secondary';
        if (order.priority == 'Urgent') priorityClass = 'danger';
        else if (order.priority == 'High') priorityClass = 'warning';
        
        const row = `
            <tr data-status="${order.status}">
                <td>${order.id}</td>
                <td>${order.patient_name}</td>
                <td>${order.doctor_name}</td>
                <td>${order.order_date}</td>
                <td><span class="badge bg-${statusClass}">${order.status}</span></td>
                <td><span class="badge bg-${priorityClass}">${order.priority}</span></td>
                <td>
                    <button class="btn btn-sm btn-info view-tests" data-id="${order.id}">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-success update-result" data-id="${order.id}">
                        <i class="fas fa-edit"></i> Result
                    </button>
                    <button class="btn btn-sm btn-danger delete-order" data-id="${order.id}">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        tbody.innerHTML += row;
    });
    
    // Re-attach event listeners
    attachOrderEventListeners();
}

// Attach Order Event Listeners
function attachOrderEventListeners() {
    // Update Result
    document.querySelectorAll('.update-result').forEach(button => {
        button.addEventListener('click', function() {
            const orderId = this.getAttribute('data-id');
            document.getElementById('result_order_id').value = orderId;
            new bootstrap.Modal(document.getElementById('updateResultModal')).show();
        });
    });

    // Delete Order
    document.querySelectorAll('.delete-order').forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Are you sure you want to delete this order?')) {
                const orderId = this.getAttribute('data-id');
                
                fetch(`api/laboratory.php?id=${orderId}`, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        alert('Order deleted successfully!');
                        loadOrders();
                    } else {
                        alert('Error: ' + result.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting order');
                });
            }
        });
    });

    // View Tests
    document.querySelectorAll('.view-tests').forEach(button => {
        button.addEventListener('click', function() {
            const orderId = this.getAttribute('data-id');
            loadOrderTests(orderId);
        });
    });
}

// Load Order Tests (already exists, keep as is)
function loadOrderTests(orderId) {
    fetch(`api/laboratory.php?action=order_tests&order_id=${orderId}`)
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            const tbody = document.querySelector('#orderTestsTable tbody');
            tbody.innerHTML = '';
            
            result.tests.forEach(test => {
                const row = `
                    <tr>
                        <td>${test.test_name}</td>
                        <td>${test.test_code}</td>
                        <td>${test.category}</td>
                        <td>$${test.price}</td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });

            new bootstrap.Modal(document.getElementById('viewTestsModal')).show();
        }
    })
    .catch(error => console.error('Error:', error));
}
