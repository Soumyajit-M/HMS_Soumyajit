// Patients Management JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize patients table
    initializePatientsTable();
    
    // Load patients data
    loadPatients();
    
    // Load doctors for appointment creation
    loadDoctors();
});

// Initialize patients table with search and filter functionality
function initializePatientsTable() {
    const searchInput = document.getElementById('searchPatients');
    const genderFilter = document.getElementById('filterGender');
    const ageFilter = document.getElementById('filterAge');
    
    if (searchInput) {
        searchInput.addEventListener('input', filterPatients);
    }
    
    if (genderFilter) {
        genderFilter.addEventListener('change', filterPatients);
    }
    
    if (ageFilter) {
        ageFilter.addEventListener('change', filterPatients);
    }

    // Initialize phone number validation
    initializePhoneValidation();
}

// Initialize phone number validation
function initializePhoneValidation() {
    // Add input event listeners to phone number fields
    const phoneInputs = document.querySelectorAll('input[type="tel"]');
    phoneInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            // Only allow digits
            this.value = this.value.replace(/\D/g, '');
            // Limit to 10 digits
            if (this.value.length > 10) {
                this.value = this.value.slice(0, 10);
            }
        });
    });
}

// Filter patients based on search and filter criteria
function filterPatients() {
    const searchTerm = document.getElementById('searchPatients').value.toLowerCase();
    const genderFilter = document.getElementById('filterGender').value;
    const ageFilter = document.getElementById('filterAge').value;
    
    const table = document.getElementById('patientsTable');
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    
    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        const name = row.cells[1].textContent.toLowerCase();
        const gender = row.cells[4].textContent.toLowerCase();
        const age = parseInt(row.cells[3].textContent);
        
        let showRow = true;
        
        // Search filter
        if (searchTerm && !name.includes(searchTerm)) {
            showRow = false;
        }
        
        // Gender filter
        if (genderFilter && !gender.includes(genderFilter)) {
            showRow = false;
        }
        
        // Age filter
        if (ageFilter) {
            const [minAge, maxAge] = ageFilter.split('-').map(Number);
            if (maxAge && (age < minAge || age > maxAge)) {
                showRow = false;
            } else if (!maxAge && age < minAge) {
                showRow = false;
            }
        }
        
        row.style.display = showRow ? '' : 'none';
    }
}

// Load patients data
function loadPatients() {
    fetch('api/patients.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updatePatientsTable(data.patients);
            }
        })
        .catch(error => {
            console.error('Error loading patients:', error);
            showAlert('Error loading patients data', 'danger');
        });
}

// Update patients table
function updatePatientsTable(patients) {
    const tbody = document.querySelector('#patientsTable tbody');
    tbody.innerHTML = '';
    
    patients.forEach(patient => {
        const row = createPatientRow(patient);
        tbody.appendChild(row);
    });
}

// Create patient row
function createPatientRow(patient) {
    const row = document.createElement('tr');
    const age = calculateAge(patient.date_of_birth);
    
    row.innerHTML = `
        <td>${patient.patient_id}</td>
        <td>
            <div class="d-flex align-items-center">
                <img src="${patient.profile_image || 'assets/images/default-avatar.png'}" 
                     class="rounded-circle me-2" width="32" height="32" alt="Avatar">
                <div>
                    <div class="fw-bold">${patient.first_name} ${patient.last_name}</div>
                    <small class="text-muted">${patient.email}</small>
                </div>
            </div>
        </td>
        <td>${patient.phone}</td>
        <td>${age}</td>
        <td>
            <span class="badge bg-${getGenderBadgeColor(patient.gender)}">
                ${patient.gender.charAt(0).toUpperCase() + patient.gender.slice(1)}
            </span>
        </td>
        <td>${patient.blood_type || 'N/A'}</td>
        <td>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-sm btn-outline-primary" 
                        onclick="viewPatient(${patient.id})">
                    <i class="fas fa-eye"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-warning" 
                        onclick="editPatient(${patient.id})">
                    <i class="fas fa-edit"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-success" 
                        onclick="addAppointment(${patient.id})">
                    <i class="fas fa-calendar-plus"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger" 
                        onclick="deletePatient(${patient.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </td>
    `;
    
    return row;
}

// Calculate age from date of birth
function calculateAge(dateOfBirth) {
    const today = new Date();
    const birthDate = new Date(dateOfBirth);
    let age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    
    return age;
}

// Get gender badge color
function getGenderBadgeColor(gender) {
    switch (gender.toLowerCase()) {
        case 'male': return 'primary';
        case 'female': return 'danger';
        default: return 'secondary';
    }
}

// Load doctors for appointment creation
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

// Add patient form submission
document.addEventListener('DOMContentLoaded', function() {
    const addPatientForm = document.getElementById('addPatientForm');
    if (!addPatientForm) return;
    
    addPatientForm.addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    // Validate email format
    const email = formData.get('email');
    if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        showAlert('Please enter a valid email address with @ symbol', 'danger');
        return;
    }

    // Validate phone number (exactly 10 digits)
    const phoneNumber = formData.get('phone');
    if (!/^\d{10}$/.test(phoneNumber)) {
        showAlert('Phone number must be exactly 10 digits', 'danger');
        return;
    }

    // Validate emergency contact phone if provided
    const emergencyPhone = formData.get('emergency_contact_phone');
    if (emergencyPhone && !/^\d{10}$/.test(emergencyPhone)) {
        showAlert('Emergency contact phone number must be exactly 10 digits', 'danger');
        return;
    }
    
    // Validate emergency email if provided
    const emergencyEmail = formData.get('emergency_contact_email');
    if (emergencyEmail && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emergencyEmail)) {
        showAlert('Please enter a valid emergency contact email address with @ symbol', 'danger');
        return;
    }

    // Remove country code from form data as we only want 10 digits
    formData.delete('country_code');
    formData.delete('emergency_country_code');

    fetch('api/patients.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Patient added successfully', 'success');
            bootstrap.Modal.getInstance(document.getElementById('addPatientModal')).hide();
            this.reset();
            loadPatients();
        } else {
            showAlert(data.message || 'Error adding patient', 'danger');
        }
    })
    .catch(error => {
        console.error('Error adding patient:', error);
        showAlert('Error adding patient', 'danger');
    });
    });
});

// View patient details
function viewPatient(patientId) {
    fetch(`api/patients.php?id=${patientId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showPatientModal(data.patient, 'view');
            }
        })
        .catch(error => {
            console.error('Error loading patient:', error);
            showAlert('Error loading patient details', 'danger');
        });
}

// Edit patient
function editPatient(patientId) {
    fetch(`api/patients.php?id=${patientId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showPatientModal(data.patient, 'edit');
            }
        })
        .catch(error => {
            console.error('Error loading patient:', error);
            showAlert('Error loading patient details', 'danger');
        });
}

// Add appointment for patient
function addAppointment(patientId) {
    // Redirect to appointments page with patient pre-selected
    window.location.href = `appointments.php?patient_id=${patientId}`;
}

// Delete patient
function deletePatient(patientId) {
    if (confirm('Are you sure you want to delete this patient? This action cannot be undone.')) {
        fetch(`api/patients.php?v=${Date.now()}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: patientId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Patient deleted successfully', 'success');
                loadPatients();
            } else {
                showAlert(data.message || 'Error deleting patient', 'danger');
            }
        })
        .catch(error => {
            console.error('Error deleting patient:', error);
            showAlert('Error deleting patient', 'danger');
        });
    }
}

// Show patient modal
function showPatientModal(patient, mode) {
    const modal = document.getElementById('patientDetailsModal');
    const modalLabel = document.getElementById('patientDetailsModalLabel');
    const modalContent = document.getElementById('patientDetailsContent');
    const modalFooter = document.getElementById('patientDetailsFooter');
    
    if (!modal) {
        showAlert('Patient details modal not found', 'danger');
        return;
    }
    
    // Calculate age from date of birth
    let age = 'N/A';
    if (patient.date_of_birth) {
        const birthDate = new Date(patient.date_of_birth);
        const today = new Date();
        age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
    }
    
    if (mode === 'view') {
        modalLabel.textContent = 'Patient Details';
        modalContent.innerHTML = `
            <div class="row">
                <div class="col-md-12 mb-3">
                    <h6 class="text-primary">Personal Information</h6>
                    <hr>
                </div>
                <div class="col-md-6">
                    <p><strong>Name:</strong> ${patient.name || 'N/A'}</p>
                    <p><strong>Email:</strong> ${patient.email || 'N/A'}</p>
                    <p><strong>Phone:</strong> ${patient.phone || 'N/A'}</p>
                    <p><strong>Date of Birth:</strong> ${patient.date_of_birth || 'N/A'}</p>
                    <p><strong>Age:</strong> ${age} years</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Gender:</strong> ${patient.gender ? patient.gender.charAt(0).toUpperCase() + patient.gender.slice(1) : 'N/A'}</p>
                    <p><strong>Blood Type:</strong> ${patient.blood_type || 'N/A'}</p>
                    <p><strong>Address:</strong> ${patient.address || 'N/A'}</p>
                </div>
                
                <div class="col-md-12 mt-3 mb-3">
                    <h6 class="text-primary">Emergency Contact</h6>
                    <hr>
                </div>
                <div class="col-md-6">
                    <p><strong>Contact Name:</strong> ${patient.emergency_contact_name || 'N/A'}</p>
                    <p><strong>Contact Phone:</strong> ${patient.emergency_contact_phone || 'N/A'}</p>
                    <p><strong>Contact Email:</strong> ${patient.emergency_contact_email || 'N/A'}</p>
                </div>
                
                <div class="col-md-12 mt-3 mb-3">
                    <h6 class="text-primary">Medical Information</h6>
                    <hr>
                </div>
                <div class="col-md-12">
                    <p><strong>Allergies:</strong><br>${patient.allergies || 'None reported'}</p>
                    <p><strong>Medical History:</strong><br>${patient.medical_history || 'No history available'}</p>
                </div>
                
                <div class="col-md-12 mt-3 mb-3">
                    <h6 class="text-primary">Insurance Information</h6>
                    <hr>
                </div>
                <div class="col-md-6">
                    <p><strong>Provider:</strong> ${patient.insurance_provider || 'N/A'}</p>
                    <p><strong>Insurance Number:</strong> ${patient.insurance_number || 'N/A'}</p>
                </div>
            </div>
        `;
        modalFooter.innerHTML = `
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn btn-warning" onclick="editPatient(${patient.id})">Edit Patient</button>
        `;
    } else if (mode === 'edit') {
        modalLabel.textContent = 'Edit Patient';
        modalContent.innerHTML = `
            <form id="editPatientForm">
                <input type="hidden" id="edit_patient_id" value="${patient.id}">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" id="edit_name" value="${patient.name || ''}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit_email" value="${patient.email || ''}">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="edit_phone" value="${patient.phone || ''}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" id="edit_date_of_birth" value="${patient.date_of_birth || ''}">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Gender</label>
                            <select class="form-select" id="edit_gender">
                                <option value="">Select Gender</option>
                                <option value="male" ${patient.gender === 'male' ? 'selected' : ''}>Male</option>
                                <option value="female" ${patient.gender === 'female' ? 'selected' : ''}>Female</option>
                                <option value="other" ${patient.gender === 'other' ? 'selected' : ''}>Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Blood Type</label>
                            <select class="form-select" id="edit_blood_type">
                                <option value="">Select Blood Type</option>
                                <option value="A+" ${patient.blood_type === 'A+' ? 'selected' : ''}>A+</option>
                                <option value="A-" ${patient.blood_type === 'A-' ? 'selected' : ''}>A-</option>
                                <option value="B+" ${patient.blood_type === 'B+' ? 'selected' : ''}>B+</option>
                                <option value="B-" ${patient.blood_type === 'B-' ? 'selected' : ''}>B-</option>
                                <option value="AB+" ${patient.blood_type === 'AB+' ? 'selected' : ''}>AB+</option>
                                <option value="AB-" ${patient.blood_type === 'AB-' ? 'selected' : ''}>AB-</option>
                                <option value="O+" ${patient.blood_type === 'O+' ? 'selected' : ''}>O+</option>
                                <option value="O-" ${patient.blood_type === 'O-' ? 'selected' : ''}>O-</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <input type="text" class="form-control" id="edit_address" value="${patient.address || ''}">
                        </div>
                    </div>
                </div>
                
                <h6 class="text-primary mt-3">Emergency Contact</h6>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Contact Name</label>
                            <input type="text" class="form-control" id="edit_emergency_contact_name" value="${patient.emergency_contact_name || ''}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Contact Phone</label>
                            <input type="tel" class="form-control" id="edit_emergency_contact_phone" value="${patient.emergency_contact_phone || ''}">
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Contact Email</label>
                    <input type="email" class="form-control" id="edit_emergency_contact_email" value="${patient.emergency_contact_email || ''}">
                </div>
                
                <h6 class="text-primary mt-3">Medical Information</h6>
                <hr>
                <div class="mb-3">
                    <label class="form-label">Allergies</label>
                    <textarea class="form-control" id="edit_allergies" rows="2">${patient.allergies || ''}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Medical History</label>
                    <textarea class="form-control" id="edit_medical_history" rows="3">${patient.medical_history || ''}</textarea>
                </div>
                
                <h6 class="text-primary mt-3">Insurance Information</h6>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Insurance Provider</label>
                            <input type="text" class="form-control" id="edit_insurance_provider" value="${patient.insurance_provider || ''}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Insurance Number</label>
                            <input type="text" class="form-control" id="edit_insurance_number" value="${patient.insurance_number || ''}">
                        </div>
                    </div>
                </div>
            </form>
        `;
        modalFooter.innerHTML = `
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="savePatientEdit()">Save Changes</button>
        `;
    }
    
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
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

// Save patient edit
function savePatientEdit() {
    const patientId = document.getElementById('edit_patient_id').value;
    
    const patientData = {
        id: patientId,
        name: document.getElementById('edit_name').value,
        email: document.getElementById('edit_email').value,
        phone: document.getElementById('edit_phone').value,
        date_of_birth: document.getElementById('edit_date_of_birth').value,
        gender: document.getElementById('edit_gender').value,
        blood_type: document.getElementById('edit_blood_type').value,
        address: document.getElementById('edit_address').value,
        emergency_contact_name: document.getElementById('edit_emergency_contact_name').value,
        emergency_contact_phone: document.getElementById('edit_emergency_contact_phone').value,
        emergency_contact_email: document.getElementById('edit_emergency_contact_email').value,
        allergies: document.getElementById('edit_allergies').value,
        medical_history: document.getElementById('edit_medical_history').value,
        insurance_provider: document.getElementById('edit_insurance_provider').value,
        insurance_number: document.getElementById('edit_insurance_number').value
    };
    
    fetch('api/patients.php', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(patientData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Patient updated successfully', 'success');
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('patientDetailsModal'));
            if (modal) {
                modal.hide();
            }
            // Reload patients table
            loadPatients();
        } else {
            showAlert(data.message || 'Error updating patient', 'danger');
        }
    })
    .catch(error => {
        console.error('Error updating patient:', error);
        showAlert('Error updating patient', 'danger');
    });
}
