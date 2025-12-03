// Billing Management JavaScript

// Sanitize HTML to prevent XSS
function sanitizeHTML(str) {
    const temp = document.createElement('div');
    temp.textContent = str;
    return temp.innerHTML;
}

document.addEventListener('DOMContentLoaded', function() {
    // Initialize billing table
    initializeBillingTable();
    
    // Load billing data
    loadBills();
    
    // Load patients and appointments for form
    loadPatients();
    loadAppointments();
    
    // Initialize billing items
    initializeBillingItems();
    
    // Currency formatting helper for billing amounts
    window.formatCurrency = async function(amount, currency) {
        try {
            const params = new URLSearchParams({ action: 'format', amount: amount, currency: currency });
            const resp = await fetch('api/currency.php?' + params.toString());
            const data = await resp.json();
            if (data.success) return data.formatted;
        } catch(e) {}
        return amount.toFixed ? amount.toFixed(2) : amount;
    };

    // Convert (if enabled) and format amount for display
    window.convertAndFormat = async function(amount, toCurrency) {
        const live = typeof window.getLiveConversionEnabled === 'function' ? window.getLiveConversionEnabled() : false;
        const target = toCurrency || (typeof window.getDefaultCurrency === 'function' ? window.getDefaultCurrency() : 'INR');
        if (live && target && target.toUpperCase() !== 'INR') {
            try {
                const params = new URLSearchParams({ action: 'convert', amount: amount, from: 'INR', to: target });
                const resp = await fetch('api/currency.php?' + params.toString());
                const data = await resp.json();
                if (data.success) return data.formatted;
            } catch(e) {}
        }
        return window.formatCurrency(amount, target);
    };
});

// Initialize billing table with search and filter functionality
function initializeBillingTable() {
    const searchInput = document.getElementById('searchBills');
    const dateFilter = document.getElementById('filterDate');
    const statusFilter = document.getElementById('filterStatus');
    
    if (searchInput) {
        searchInput.addEventListener('input', filterBills);
    }
    
    if (dateFilter) {
        dateFilter.addEventListener('change', filterBills);
    }
    
    if (statusFilter) {
        statusFilter.addEventListener('change', filterBills);
    }
}

// Filter bills based on search and filter criteria
function filterBills() {
    const searchTerm = document.getElementById('searchBills').value.toLowerCase();
    const dateFilter = document.getElementById('filterDate').value;
    const statusFilter = document.getElementById('filterStatus').value;
    
    const table = document.getElementById('billsTable');
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    
    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        const patientName = row.cells[1].textContent.toLowerCase();
        const billNumber = row.cells[0].textContent.toLowerCase();
        const status = row.cells[6].textContent.toLowerCase();
        
        let showRow = true;
        
        // Search filter
        if (searchTerm && !patientName.includes(searchTerm) && !billNumber.includes(searchTerm)) {
            showRow = false;
        }
        
        // Status filter
        if (statusFilter && !status.includes(statusFilter)) {
            showRow = false;
        }
        
        row.style.display = showRow ? '' : 'none';
    }
}

// Load bills data
function loadBills() {
    fetch('api/billing.php')
        .then(async response => {
            const text = await response.text();
            try {
                return JSON.parse(text);
            } catch (parseError) {
                console.error('Failed to parse billing response:', text);
                throw parseError;
            }
        })
        .then(data => {
            if (data.success) {
                updateBillsTable(data.bills || []);
            } else {
                console.warn('Billing API responded with error:', data);
                showAlert(data.message || 'Unable to load bills', 'warning');
            }
        })
        .catch(error => {
            console.error('Error loading bills:', error);
            if (typeof showAlert === 'function') {
                showAlert('Error loading bills data', 'danger');
            }
        });
}

// Update bills table
function updateBillsTable(bills) {
    const tbody = document.querySelector('#billsTable tbody');
    tbody.innerHTML = '';
    
    bills.forEach(bill => {
        const row = createBillRow(bill);
        tbody.appendChild(row);
    });
}

// Create bill row
function createBillRow(bill) {
    const safeBill = {
        id: bill.id,
        bill_number: bill.bill_number || 'N/A',
        patient_image: bill.patient_image || 'assets/images/default-avatar.png',
        patient_name: (bill.patient_name !== undefined && bill.patient_name !== null && bill.patient_name !== '') ? String(bill.patient_name) : 'Unknown patient',
        patient_phone: bill.patient_phone ? String(bill.patient_phone) : 'N/A',
        appointment_id: bill.appointment_id ? String(bill.appointment_id) : 'N/A',
        appointment_date: bill.appointment_date || null,
        total_amount: parseFloat(bill.total_amount || 0),
        paid_amount: parseFloat(bill.paid_amount || 0),
        balance_amount: parseFloat(bill.balance_amount || 0),
        payment_status: (bill.payment_status || 'pending').toString().toLowerCase(),
        due_date: bill.due_date || null,
        currency: bill.currency || (typeof window.getDefaultCurrency === 'function' ? window.getDefaultCurrency() : 'INR')
    };

    const row = document.createElement('tr');
    const dueDate = safeBill.due_date ? new Date(safeBill.due_date) : null;
    const isOverdue = dueDate && dueDate < new Date();

    const statusLabel = safeBill.payment_status.charAt(0).toUpperCase() + safeBill.payment_status.slice(1);

    row.innerHTML = `
        <td>${sanitizeHTML(safeBill.bill_number)}</td>
        <td>
            <div class="d-flex align-items-center">
                <img src="${sanitizeHTML(safeBill.patient_image)}"
                     class="rounded-circle me-2" width="32" height="32" alt="Patient">
                <div>
                    <div class="fw-bold">${sanitizeHTML(safeBill.patient_name)}</div>
                    <small class="text-muted">${sanitizeHTML(safeBill.patient_phone)}</small>
                </div>
            </div>
        </td>
        <td>
            <div>
                <div class="fw-bold">${sanitizeHTML(safeBill.appointment_id)}</div>
                <small class="text-muted">${safeBill.appointment_date ? new Date(safeBill.appointment_date).toLocaleDateString() : 'N/A'}</small>
            </div>
        </td>
        <td class="fw-bold" data-amount="${safeBill.total_amount}" data-currency="${safeBill.currency}">${safeBill.total_amount.toFixed(2)}</td>
        <td class="text-success" data-amount="${safeBill.paid_amount}" data-currency="${safeBill.currency}">${safeBill.paid_amount.toFixed(2)}</td>
        <td class="fw-bold" data-amount="${safeBill.balance_amount}" data-currency="${safeBill.currency}">${safeBill.balance_amount.toFixed(2)}</td>
        <td>
            <span class="badge bg-${getStatusBadgeColor(safeBill.payment_status)}">
                ${sanitizeHTML(statusLabel)}
            </span>
        </td>
        <td>
            ${dueDate ? `
                <span class="${isOverdue ? 'text-danger' : 'text-muted'}">
                    ${dueDate.toLocaleDateString()}
                </span>
            ` : '<span class="text-muted">N/A</span>'}
        </td>
        <td>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-sm btn-outline-primary" 
                        onclick="viewBill(${safeBill.id})">
                    <i class="fas fa-eye"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-success" 
                        onclick="addPayment(${safeBill.id})">
                    <i class="fas fa-credit-card"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-info" 
                        onclick="printBill(${safeBill.id})">
                    <i class="fas fa-print"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-warning" 
                        onclick="editBill(${safeBill.id})">
                    <i class="fas fa-edit"></i>
                </button>
            </div>
        </td>
    `;

    return row;
}

// After table rows created, format currency cells via API
document.addEventListener('DOMContentLoaded', function() {
    const observer = new MutationObserver(async function() {
        const cells = document.querySelectorAll('#billsTable tbody td[data-amount]');
        for (const cell of cells) {
            const amt = parseFloat(cell.getAttribute('data-amount')) || 0;
            const cur = cell.getAttribute('data-currency') || 'INR';
            const fmt = await window.convertAndFormat(amt, cur);
            cell.textContent = fmt;
        }
    });
    const tbody = document.querySelector('#billsTable tbody');
    if (tbody) observer.observe(tbody, { childList: true, subtree: true });
});

// Get status badge color
function getStatusBadgeColor(status) {
    switch (status.toLowerCase()) {
        case 'paid': return 'success';
        case 'overdue': return 'danger';
        case 'partial': return 'warning';
        default: return 'info';
    }
}

// Load patients for form
function loadPatients() {
    fetch('api/patients.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updatePatientsSelect(data.patients);
            }
        })
        .catch(error => {
            console.error('Error loading patients:', error);
        });
}

// Update patients select options
function updatePatientsSelect(patients) {
    const select = document.getElementById('patientSelect');
    if (select) {
        select.innerHTML = '<option value="">Select Patient</option>';
        patients.forEach(patient => {
            const option = document.createElement('option');
            option.value = patient.id;
            option.textContent = `${patient.first_name} ${patient.last_name} (${patient.patient_id})`;
            select.appendChild(option);
        });
    }
}

// Load appointments for form
function loadAppointments() {
    fetch('api/appointments.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateAppointmentsSelect(data.appointments);
            }
        })
        .catch(error => {
            console.error('Error loading appointments:', error);
        });
}

// Update appointments select options
function updateAppointmentsSelect(appointments) {
    const select = document.getElementById('appointmentSelect');
    if (select) {
        select.innerHTML = '<option value="">Select Appointment (Optional)</option>';
        appointments.forEach(appointment => {
            const option = document.createElement('option');
            option.value = appointment.id;
            option.textContent = `${appointment.appointment_id} - ${appointment.patient_name}`;
            select.appendChild(option);
        });
    }
}

// Initialize billing items
function initializeBillingItems() {
    // Add event listeners to billing items
    document.addEventListener('input', function(e) {
        if (e.target.name === 'quantity[]' || e.target.name === 'unit_price[]') {
            calculateItemTotal(e.target);
        }
    });
}

// Calculate item total
function calculateItemTotal(input) {
    const row = input.closest('.billing-item');
    const quantity = parseFloat(row.querySelector('input[name="quantity[]"]').value) || 0;
    const unitPrice = parseFloat(row.querySelector('input[name="unit_price[]"]').value) || 0;
    const totalPrice = quantity * unitPrice;
    
    row.querySelector('input[name="total_price[]"]').value = totalPrice.toFixed(2);
    calculateBillTotal();
}

// Calculate bill total
function calculateBillTotal() {
    const items = document.querySelectorAll('.billing-item');
    let total = 0;
    
    items.forEach(item => {
        const totalPrice = parseFloat(item.querySelector('input[name="total_price[]"]').value) || 0;
        total += totalPrice;
    });
    
    document.getElementById('totalAmount').value = total.toFixed(2);
}

// Add billing item
function addBillingItem() {
    const container = document.getElementById('billingItems');
    const newItem = document.createElement('div');
    newItem.className = 'row mb-2 billing-item';
    newItem.innerHTML = `
        <div class="col-md-4">
            <input type="text" class="form-control" name="item_name[]" placeholder="Item name" required>
        </div>
        <div class="col-md-2">
            <input type="number" class="form-control" name="quantity[]" placeholder="Qty" value="1" min="1" required>
        </div>
        <div class="col-md-3">
            <input type="number" class="form-control" name="unit_price[]" placeholder="Unit price" step="0.01" required>
        </div>
        <div class="col-md-2">
            <input type="number" class="form-control" name="total_price[]" placeholder="Total" step="0.01" readonly>
        </div>
        <div class="col-md-1">
            <button type="button" class="btn btn-danger btn-sm" onclick="removeBillingItem(this)">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    container.appendChild(newItem);
}

// Remove billing item
function removeBillingItem(button) {
    button.closest('.billing-item').remove();
    calculateBillTotal();
}

// Add bill form submission
document.addEventListener('DOMContentLoaded', function() {
    const addBillForm = document.getElementById('addBillForm');
    if (!addBillForm) return;
    
    addBillForm.addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const data = {};
    for (let [key, value] of formData.entries()) {
        if (key.endsWith('[]')) {
            const arrayKey = key.slice(0, -2);
            if (!data[arrayKey]) data[arrayKey] = [];
            data[arrayKey].push(value);
        } else {
            data[key] = value;
        }
    }

    fetch('api/billing.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Bill created successfully', 'success');
            bootstrap.Modal.getInstance(document.getElementById('addBillModal')).hide();
            this.reset();
            loadBills();
        } else {
            showAlert(data.message || 'Error creating bill', 'danger');
        }
    })
    .catch(error => {
        console.error('Error creating bill:', error);
        showAlert('Error creating bill', 'danger');
    });
    });
});

// View bill details
function viewBill(billId) {
    fetch(`api/billing.php?id=${billId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showBillModal(data.bill, 'view');
            }
        })
        .catch(error => {
            console.error('Error loading bill:', error);
            showAlert('Error loading bill details', 'danger');
        });
}

// Add payment
function addPayment(billId) {
        let modalEl = document.getElementById('paymentModal');
        if (!modalEl) {
                // dynamically create payment modal if markup missing
                const div = document.createElement('div');
                div.innerHTML = `
                <div class="modal fade" id="paymentModal" tabindex="-1">
                    <div class="modal-dialog"><div class="modal-content">
                        <div class="modal-header"><h5 class="modal-title">Add Payment</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form id="paymentForm">
                            <div class="modal-body">
                                <input type="hidden" id="paymentBillingId" name="billing_id">
                                <div class="mb-3"><label class="form-label">Amount</label>
                                    <input type="number" step="0.01" min="0" class="form-control" name="amount" required></div>
                                <div class="mb-3"><label class="form-label">Payment Method</label>
                                    <select class="form-select" name="payment_method">
                                        <option value="cash">Cash</option>
                                        <option value="card">Card</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                        <option value="insurance">Insurance</option>
                                    </select></div>
                                <div class="mb-3"><label class="form-label">Transaction ID</label>
                                    <input type="text" class="form-control" name="transaction_id"></div>
                                <div class="mb-3"><label class="form-label">Notes</label>
                                    <textarea class="form-control" name="notes" rows="3"></textarea></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-success">Add Payment</button>
                            </div>
                        </form>
                    </div></div></div>`;
                document.body.appendChild(div.firstElementChild);
                modalEl = document.getElementById('paymentModal');
        }
        const billingInput = document.getElementById('paymentBillingId');
        if (billingInput) billingInput.value = billId;
        new bootstrap.Modal(modalEl).show();
}

// Print bill
function printBill(billId) {
    window.open(`api/print-bill.php?id=${billId}`, '_blank');
}

// Edit bill
function editBill(billId) {
    fetch(`api/billing.php?id=${billId}`)
        .then(r => r.json())
        .then(data => {
            if (!data.success) return showAlert('Failed to load bill', 'danger');
            data.bill.items = data.items || [];
            showBillModal(data.bill, 'edit');
        })
        .catch(() => showAlert('Network error loading bill', 'danger'));
}

// Show bill modal
function showBillModal(bill, mode) {
        const modalEl = document.getElementById('billDetailsModal');
        if (!modalEl) return showAlert('Bill modal missing', 'danger');
        const modal = new bootstrap.Modal(modalEl);
        const content = document.getElementById('billDetailsContent');
        const footer = document.getElementById('billDetailsFooter');
        const items = bill.items || [];
        const itemsHtml = items.map(i => `<tr>
            <td>${i.item_name}</td>
            <td>${i.description||''}</td>
            <td class='text-end'>${i.quantity}</td>
            <td class='text-end' data-amount='${parseFloat(i.unit_price)||0}' data-currency='${(window.getDefaultCurrency && window.getDefaultCurrency())||'INR'}'>${parseFloat(i.unit_price).toFixed(2)}</td>
            <td class='text-end' data-amount='${parseFloat(i.total_price)||0}' data-currency='${(window.getDefaultCurrency && window.getDefaultCurrency())||'INR'}'>${parseFloat(i.total_price).toFixed(2)}</td>
        </tr>`).join('');
        content.innerHTML = `
            <div class='mb-2'><strong>Bill #</strong> ${sanitizeHTML(bill.bill_number)}</div>
            <div class='mb-2'><strong>Patient:</strong> ${sanitizeHTML(bill.patient_name)} ${bill.patient_phone? '('+sanitizeHTML(bill.patient_phone)+')':''}</div>
            <div class='row g-2 mb-2'>
                <div class='col-6'><strong>Total:</strong> <span data-amount='${parseFloat(bill.total_amount)||0}' data-currency='${(window.getDefaultCurrency && window.getDefaultCurrency())||'INR'}'>${parseFloat(bill.total_amount).toFixed(2)}</span></div>
                <div class='col-6'><strong>Paid:</strong> <span data-amount='${parseFloat(bill.paid_amount)||0}' data-currency='${(window.getDefaultCurrency && window.getDefaultCurrency())||'INR'}'>${parseFloat(bill.paid_amount).toFixed(2)}</span></div>
                <div class='col-6'><strong>Balance:</strong> <span data-amount='${parseFloat(bill.balance_amount)||0}' data-currency='${(window.getDefaultCurrency && window.getDefaultCurrency())||'INR'}'>${parseFloat(bill.balance_amount).toFixed(2)}</span></div>
                <div class='col-6'><strong>Status:</strong> <span class='badge bg-${getStatusBadgeColor(bill.payment_status)}'>${sanitizeHTML(bill.payment_status)}</span></div>
            </div>
            <div class='mb-2'><strong>Appointment:</strong> ${sanitizeHTML(bill.appointment_id)||'N/A'} on ${sanitizeHTML(bill.appointment_date)||'N/A'}</div>
            <div class='mb-2'><strong>Due Date:</strong> ${bill.due_date? new Date(bill.due_date).toLocaleDateString(): 'N/A'}</div>
            <div class='mb-2'><strong>Notes:</strong><br>${(sanitizeHTML(bill.notes)||'').replace(/\n/g,'<br>')}</div>
            <div class='mt-3'>
                <strong>Items:</strong>
                <table class='table table-sm'>
                    <thead><tr><th>Name</th><th>Description</th><th class='text-end'>Qty</th><th class='text-end'>Unit</th><th class='text-end'>Total</th></tr></thead>
                    <tbody>${itemsHtml || '<tr><td colspan="5" class="text-muted">No Items</td></tr>'}</tbody>
                </table>
            </div>`;
        footer.innerHTML = mode === 'edit'
            ? `<button class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>
                 <button class='btn btn-primary' onclick='showEditForm(${bill.id})'>Edit</button>`
            : `<button class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>`;
        // Format currency inside modal after injecting HTML
        (async () => {
            const els = modalEl.querySelectorAll('[data-amount]');
            for (const el of els) {
                const amt = parseFloat(el.getAttribute('data-amount')) || 0;
                const cur = el.getAttribute('data-currency') || 'INR';
                try {
                    const fmt = await window.convertAndFormat(amt, cur);
                    el.textContent = fmt;
                } catch (e) {}
            }
        })();
        modal.show();
}

function showEditForm(billId) {
    fetch(`api/billing.php?id=${billId}`)
        .then(r=>r.json())
        .then(data => {
            if (!data.success) return showAlert('Failed to load bill for edit','danger');
            const bill = data.bill;
            const content = document.getElementById('billDetailsContent');
            const footer = document.getElementById('billDetailsFooter');
            content.innerHTML = `
                <form id='editBillForm'>
                    <input type='hidden' name='id' value='${bill.id}'>
                    <div class='mb-2'><label class='form-label'>Total Amount</label><input name='total_amount' type='number' step='0.01' class='form-control' value='${bill.total_amount}'></div>
                    <div class='mb-2'><label class='form-label'>Due Date</label><input name='due_date' type='date' class='form-control' value='${bill.due_date||''}'></div>
                    <div class='mb-2'><label class='form-label'>Notes</label><textarea name='notes' rows='3' class='form-control'>${bill.notes||''}</textarea></div>
                </form>`;
            footer.innerHTML = `<button class='btn btn-secondary' data-bs-dismiss='modal'>Cancel</button>
                                                    <button class='btn btn-primary' onclick='saveBillEdit()'>Save</button>`;
        });
}

function saveBillEdit() {
    const form = document.getElementById('editBillForm');
    if (!form) return showAlert('Edit form missing','danger');
    const data = Object.fromEntries(new FormData(form).entries());
    data.total_amount = parseFloat(data.total_amount)||0;
    fetch('api/billing.php', {method:'PUT', headers:{'Content-Type':'application/json'}, body: JSON.stringify(data)})
        .then(r=>r.json())
        .then(resp => {
            if (resp.success) {
                showAlert('Bill updated','success');
                loadBills();
                bootstrap.Modal.getInstance(document.getElementById('billDetailsModal')).hide();
            } else {
                showAlert(resp.message||'Update failed','danger');
            }
        })
        .catch(()=> showAlert('Network error updating bill','danger'));
}

// Payment form submission
document.addEventListener('submit', function(e){
    if (e.target && e.target.id === 'paymentForm') {
        e.preventDefault();
        const data = Object.fromEntries(new FormData(e.target).entries());
        data.amount = parseFloat(data.amount)||0;
        fetch('api/payments.php', {method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(data)})
            .then(r=>r.json())
            .then(resp => {
                if (resp.success) {
                    showAlert('Payment recorded','success');
                    loadBills();
                    bootstrap.Modal.getInstance(document.getElementById('paymentModal')).hide();
                } else {
                    showAlert(resp.message||'Failed to record payment','danger');
                }
            })
            .catch(()=> showAlert('Network error','danger'));
    }
});

// Clear filters
function clearFilters() {
    document.getElementById('searchBills').value = '';
    document.getElementById('filterDate').value = '';
    document.getElementById('filterStatus').value = '';
    filterBills();
}

// Show alert
function showAlert(message, type = 'info') {
    const alertContainer = document.getElementById('alert-container') || createAlertContainer();
    
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    alertContainer.appendChild(alert);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        alert.remove();
    }, 5000);
}

// Create alert container if it doesn't exist
function createAlertContainer() {
    const container = document.createElement('div');
    container.id = 'alert-container';
    container.className = 'position-fixed top-0 start-50 translate-middle-x p-3';
    container.style.zIndex = '1055';
    document.body.appendChild(container);
    return container;
}
