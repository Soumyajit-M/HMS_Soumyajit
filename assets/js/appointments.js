// Appointments Management JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize appointments table
    initializeAppointmentsTable();
    
    // Load appointments data
    loadAppointments();
    
    // Load patients and doctors for form
    loadPatients();
    loadDoctors();
    
    // Initialize date picker
    initializeDatePicker();
    
    // Check if redirected with action=new parameter
    checkForNewAppointmentAction();
});

// Check URL for action=new and auto-open modal
function checkForNewAppointmentAction() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('action') === 'new') {
        const modal = new bootstrap.Modal(document.getElementById('addAppointmentModal'));
        modal.show();
        // Clean up URL without reloading page
        window.history.replaceState({}, document.title, window.location.pathname);
    }
}

// Initialize appointments table with search and filter functionality
function initializeAppointmentsTable() {
    const searchInput = document.getElementById('searchAppointments');
    const dateFilter = document.getElementById('filterDate');
    const doctorFilter = document.getElementById('filterDoctor');
    
    if (searchInput) {
        searchInput.addEventListener('input', filterAppointments);
    }
    
    if (dateFilter) {
        dateFilter.addEventListener('change', filterAppointments);
    }
    
    if (doctorFilter) {
        doctorFilter.addEventListener('change', filterAppointments);
    }
}

// Filter appointments based on search and filter criteria
function filterAppointments() {
    const searchTerm = document.getElementById('searchAppointments').value.toLowerCase();
    const dateFilter = document.getElementById('filterDate').value;
    const doctorFilter = document.getElementById('filterDoctor').value;
    
    const table = document.getElementById('appointmentsTable');
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    
    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        const patientName = row.cells[1].textContent.toLowerCase();
        const doctorName = row.cells[2].textContent.toLowerCase();
        const appointmentDate = row.cells[3].textContent;
        
        let showRow = true;
        
        // Search filter
        if (searchTerm && !patientName.includes(searchTerm) && !doctorName.includes(searchTerm)) {
            showRow = false;
        }
        
        // Date filter
        if (dateFilter) {
            const rowDate = new Date(appointmentDate).toISOString().split('T')[0];
            if (rowDate !== dateFilter) {
                showRow = false;
            }
        }
        
        // Doctor filter
        if (doctorFilter && !doctorName.includes(doctorFilter)) {
            showRow = false;
        }
        
        row.style.display = showRow ? '' : 'none';
    }
}

// Load appointments data
function loadAppointments() {
    fetch('api/appointments.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateAppointmentsTable(data.appointments);
            }
        })
        .catch(error => {
            console.error('Error loading appointments:', error);
            showAlert('Error loading appointments data', 'danger');
        });
}

// Update appointments table
function updateAppointmentsTable(appointments) {
    const tbody = document.querySelector('#appointmentsTable tbody');
    tbody.innerHTML = '';
    
    appointments.forEach(appointment => {
        const row = createAppointmentRow(appointment);
        tbody.appendChild(row);
    });
}

// Create appointment row
function createAppointmentRow(appointment) {
    const row = document.createElement('tr');
    const appointmentDate = new Date(appointment.appointment_date);
    const appointmentTime = new Date(`2000-01-01T${appointment.appointment_time}`);
    // Normalize field names in case API returned *_first_name pattern
    const patientName = appointment.patient_name || [appointment.patient_first_name, appointment.patient_last_name].filter(Boolean).join(' ').trim();
    const doctorName = appointment.doctor_name || [appointment.doctor_first_name, appointment.doctor_last_name].filter(Boolean).join(' ').trim();
    const patientPhone = appointment.patient_phone || appointment.phone || '';
    const specialization = appointment.specialization || appointment.department_name || '';
    
    row.innerHTML = `
        <td>${appointment.appointment_id}</td>
        <td>
            <div class="d-flex align-items-center">
                <img src="${appointment.patient_image || 'assets/images/default-avatar.png'}" 
                     class="rounded-circle me-2" width="32" height="32" alt="Patient">
                <div>
                    <div class="fw-bold">${patientName}</div>
                    <small class="text-muted">${patientPhone}</small>
                </div>
            </div>
        </td>
        <td>
            <div class="d-flex align-items-center">
                <img src="${appointment.doctor_image || 'assets/images/default-avatar.png'}" 
                     class="rounded-circle me-2" width="32" height="32" alt="Doctor">
                <div>
                    <div class="fw-bold">Dr. ${doctorName}</div>
                    <small class="text-muted">${specialization}</small>
                </div>
            </div>
        </td>
        <td>
            <div>
                <div class="fw-bold">${appointmentDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</div>
                <small class="text-muted">${appointmentTime.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' })}</small>
            </div>
        </td>
        <td>
            <span class="badge bg-${getStatusBadgeColor(appointment.status)}">
                ${appointment.status.charAt(0).toUpperCase() + appointment.status.slice(1)}
            </span>
        </td>
        <td>${appointment.reason || 'N/A'}</td>
        <td>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-sm btn-outline-primary" 
                        onclick="viewAppointment(${appointment.id})">
                    <i class="fas fa-eye"></i>
                </button>
                ${appointment.status !== 'completed' && appointment.status !== 'cancelled' ? `
                <button type="button" class="btn btn-sm btn-outline-warning" 
                        onclick="editAppointment(${appointment.id})">
                    <i class="fas fa-edit"></i>
                </button>
                ` : ''}
            </div>
        </td>
    `;
    
    return row;
}

// Get status badge color
function getStatusBadgeColor(status) {
    switch (status.toLowerCase()) {
        case 'completed': return 'success';
        case 'cancelled': return 'danger';
        case 'in_progress': return 'warning';
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

// Load doctors for form
function loadDoctors() {
    fetch('api/doctors.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateDoctorsSelect(data.doctors);
            }
        })
        .catch(error => {
            console.error('Error loading doctors:', error);
        });
}

// Update doctors select options
function updateDoctorsSelect(doctors) {
    const select = document.getElementById('doctorSelect');
    if (select) {
        select.innerHTML = '<option value="">Select Doctor</option>';
        doctors.forEach(doctor => {
            const option = document.createElement('option');
            option.value = doctor.id;
            option.textContent = `Dr. ${doctor.first_name} ${doctor.last_name} - ${doctor.specialization}`;
            select.appendChild(option);
        });
    }
}

// Initialize date picker
function initializeDatePicker() {
    const dateInput = document.getElementById('appointmentDate');
    if (dateInput) {
        // Set minimum date to today
        const today = new Date().toISOString().split('T')[0];
        dateInput.min = today;
        dateInput.value = today;
    }
}

// Add appointment form submission
document.addEventListener('DOMContentLoaded', function() {
    const addAppointmentForm = document.getElementById('addAppointmentForm');
    if (!addAppointmentForm) return;
    
    addAppointmentForm.addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const data = {};
    for (let [key, value] of formData.entries()) {
        data[key] = value;
    }

    fetch('api/appointments.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Appointment scheduled successfully', 'success');
            bootstrap.Modal.getInstance(document.getElementById('addAppointmentModal')).hide();
            this.reset();
            loadAppointments();
        } else {
            showAlert(data.message || 'Error scheduling appointment', 'danger');
        }
    })
    .catch(error => {
        console.error('Error scheduling appointment:', error);
        showAlert('Error scheduling appointment', 'danger');
    });
    });
});

// View appointment details
function viewAppointment(appointmentId) {
    fetch(`api/appointments.php?id=${appointmentId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAppointmentModal(data.appointment, 'view');
            }
        })
        .catch(error => {
            console.error('Error loading appointment:', error);
            showAlert('Error loading appointment details', 'danger');
        });
}

// Edit appointment
function editAppointment(appointmentId) {
    fetch(`api/appointments.php?id=${appointmentId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAppointmentModal(data.appointment, 'edit');
            }
        })
        .catch(error => {
            console.error('Error loading appointment:', error);
            showAlert('Error loading appointment details', 'danger');
        });
}

// Start appointment
function startAppointment(appointmentId) {
    fetch(`api/appointments.php?v=${Date.now()}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ 
            id: appointmentId, 
            status: 'in_progress' 
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Appointment started', 'success');
            loadAppointments();
        } else {
            showAlert(data.message || 'Error starting appointment', 'danger');
        }
    })
    .catch(error => {
        console.error('Error starting appointment:', error);
        showAlert('Error starting appointment', 'danger');
    });
}

// Cancel appointment
function cancelAppointment(appointmentId) {
    if (confirm('Are you sure you want to cancel this appointment?')) {
        fetch(`api/appointments.php`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ 
                id: appointmentId, 
                status: 'cancelled' 
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Appointment cancelled', 'success');
                loadAppointments();
            } else {
                showAlert(data.message || 'Error cancelling appointment', 'danger');
            }
        })
        .catch(error => {
            console.error('Error cancelling appointment:', error);
            showAlert('Error cancelling appointment', 'danger');
        });
    }
}

// Show appointment modal
function showAppointmentModal(appointment, mode) {
    const modal = document.getElementById('appointmentDetailsModal');
    const modalLabel = document.getElementById('appointmentDetailsModalLabel');
    const modalContent = document.getElementById('appointmentDetailsContent');
    const modalFooter = document.getElementById('appointmentDetailsFooter');
    
    if (!modal) {
        showAlert('Appointment details modal not found', 'danger');
        return;
    }
    
    const appointmentDate = new Date(appointment.appointment_date);
    const formattedDate = appointmentDate.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
    
    if (mode === 'view') {
        modalLabel.textContent = 'Appointment Details';
        modalContent.innerHTML = `
            <div class="row">
                <div class="col-md-12 mb-3">
                    <h6 class="text-primary">Appointment Information</h6>
                    <hr>
                </div>
                <div class="col-md-6">
                    <p><strong>Appointment ID:</strong> ${appointment.appointment_id || 'N/A'}</p>
                    <p><strong>Date:</strong> ${formattedDate}</p>
                    <p><strong>Time:</strong> ${appointment.appointment_time}</p>
                    <p><strong>Type:</strong> ${appointment.appointment_type || 'Regular'}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Status:</strong> <span class="badge bg-${getStatusBadgeColor(appointment.status)}">${appointment.status}</span></p>
                    <p><strong>Duration:</strong> ${appointment.duration || '30'} minutes</p>
                    <p><strong>Department:</strong> ${appointment.department_name || 'N/A'}</p>
                </div>
                
                <div class="col-md-12 mt-3 mb-3">
                    <h6 class="text-primary">Patient Information</h6>
                    <hr>
                </div>
                <div class="col-md-6">
                    <p><strong>Patient Name:</strong> ${appointment.patient_name}</p>
                    <p><strong>Phone:</strong> ${appointment.patient_phone || 'N/A'}</p>
                </div>
                
                <div class="col-md-12 mt-3 mb-3">
                    <h6 class="text-primary">Doctor Information</h6>
                    <hr>
                </div>
                <div class="col-md-6">
                    <p><strong>Doctor:</strong> Dr. ${appointment.doctor_name}</p>
                    <p><strong>Specialization:</strong> ${appointment.specialization || 'N/A'}</p>
                </div>
                
                <div class="col-md-12 mt-3 mb-3">
                    <h6 class="text-primary">Additional Details</h6>
                    <hr>
                </div>
                <div class="col-md-12">
                    <p><strong>Reason:</strong><br>${appointment.reason || 'Not specified'}</p>
                    <p><strong>Symptoms:</strong><br>${appointment.symptoms || 'Not specified'}</p>
                    <p><strong>Notes:</strong><br>${appointment.notes || 'None'}</p>
                </div>
            </div>
        `;
        modalFooter.innerHTML = `
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            ${appointment.status !== 'completed' && appointment.status !== 'cancelled' ? `<button type="button" class="btn btn-warning" onclick="editAppointment(${appointment.id})">Edit Appointment</button>` : ''}
        `;
    } else if (mode === 'edit') {
        modalLabel.textContent = 'Edit Appointment';
        modalContent.innerHTML = `
            <form id="editAppointmentForm">
                <input type="hidden" id="edit_appointment_id" value="${appointment.id}">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Appointment Date</label>
                            <input type="date" class="form-control" id="edit_appointment_date" value="${appointment.appointment_date}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Appointment Time</label>
                            <input type="time" class="form-control" id="edit_appointment_time" value="${appointment.appointment_time}" required>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="edit_status">
                                <option value="scheduled" ${appointment.status === 'scheduled' ? 'selected' : ''}>Scheduled</option>
                                <option value="confirmed" ${appointment.status === 'confirmed' ? 'selected' : ''}>Confirmed</option>
                                <option value="in_progress" ${appointment.status === 'in_progress' ? 'selected' : ''}>In Progress</option>
                                <option value="completed" ${appointment.status === 'completed' ? 'selected' : ''}>Completed</option>
                                <option value="cancelled" ${appointment.status === 'cancelled' ? 'selected' : ''}>Cancelled</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Type</label>
                            <select class="form-select" id="edit_appointment_type">
                                <option value="consultation" ${appointment.appointment_type === 'consultation' ? 'selected' : ''}>Consultation</option>
                                <option value="follow-up" ${appointment.appointment_type === 'follow-up' ? 'selected' : ''}>Follow-up</option>
                                <option value="emergency" ${appointment.appointment_type === 'emergency' ? 'selected' : ''}>Emergency</option>
                                <option value="routine" ${appointment.appointment_type === 'routine' ? 'selected' : ''}>Routine</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Reason</label>
                    <textarea class="form-control" id="edit_reason" rows="2">${appointment.reason || ''}</textarea>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Symptoms</label>
                    <textarea class="form-control" id="edit_symptoms" rows="2">${appointment.symptoms || ''}</textarea>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea class="form-control" id="edit_notes" rows="3">${appointment.notes || ''}</textarea>
                </div>
            </form>
        `;
        modalFooter.innerHTML = `
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="saveAppointmentEdit()">Save Changes</button>
        `;
    }
    
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
}

// Clear filters
function clearFilters() {
    document.getElementById('searchAppointments').value = '';
    document.getElementById('filterDate').value = '';
    document.getElementById('filterDoctor').value = '';
    filterAppointments();
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

// Save appointment edit
function saveAppointmentEdit() {
    const appointmentId = document.getElementById('edit_appointment_id').value;
    
    const appointmentData = {
        id: appointmentId,
        appointment_date: document.getElementById('edit_appointment_date').value,
        appointment_time: document.getElementById('edit_appointment_time').value,
        status: document.getElementById('edit_status').value,
        appointment_type: document.getElementById('edit_appointment_type').value,
        reason: document.getElementById('edit_reason').value,
        symptoms: document.getElementById('edit_symptoms').value,
        notes: document.getElementById('edit_notes').value
    };
    
    fetch('api/appointments.php', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(appointmentData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Appointment updated successfully', 'success');
            const modal = bootstrap.Modal.getInstance(document.getElementById('appointmentDetailsModal'));
            if (modal) {
                modal.hide();
            }
            // If API returned updated record, patch row in-place; else fallback to full reload
            if (data.appointment) {
                updateSingleAppointmentRow(data.appointment);
            } else {
                loadAppointments();
            }
        } else {
            showAlert(data.message || 'Error updating appointment', 'danger');
        }
    })
    .catch(error => {
        console.error('Error updating appointment:', error);
        showAlert('Error updating appointment', 'danger');
    });
}

// Update a single appointment row in-place without reloading entire table
function updateSingleAppointmentRow(updated) {
    const tableBody = document.querySelector('#appointmentsTable tbody');
    if (!tableBody) { loadAppointments(); return; }

    // Find existing row by matching appointment_id (unique) or internal id
    let targetRow = null;
    const rows = tableBody.querySelectorAll('tr');
    rows.forEach(r => {
        const firstCell = r.querySelector('td');
        if (firstCell && firstCell.textContent.trim() === updated.appointment_id) {
            targetRow = r;
        }
    });

    if (!targetRow) { loadAppointments(); return; }

    // Rebuild a fresh row and replace (keeps event handlers simple)
    const normalized = Object.assign({}, updated, {
        patient_name: updated.patient_name || [updated.patient_first_name, updated.patient_last_name].filter(Boolean).join(' ').trim(),
        doctor_name: updated.doctor_name || [updated.doctor_first_name, updated.doctor_last_name].filter(Boolean).join(' ').trim(),
        specialization: updated.specialization || updated.department_name || ''
    });
    const newRow = createAppointmentRow(normalized);
    tableBody.replaceChild(newRow, targetRow);

    // Brief highlight effect
    newRow.classList.add('table-success');
    setTimeout(() => newRow.classList.remove('table-success'), 1500);
}
