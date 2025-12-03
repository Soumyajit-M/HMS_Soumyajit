// Doctors Management JavaScript

// Sanitize HTML to prevent XSS
function sanitizeHTML(str) {
    const temp = document.createElement('div');
    temp.textContent = str;
    return temp.innerHTML;
}

document.addEventListener('DOMContentLoaded', function() {
    // Initialize doctors table
    initializeDoctorsTable();

    // Load doctors data
    loadDoctors();
});

// Initialize doctors table with search and filter functionality
function initializeDoctorsTable() {
    const searchInput = document.getElementById('searchDoctors');
    const specializationFilter = document.getElementById('filterSpecialization');
    const experienceFilter = document.getElementById('filterExperience');
    
    if (searchInput) {
        searchInput.addEventListener('input', filterDoctors);
    }
    
    if (specializationFilter) {
        specializationFilter.addEventListener('change', filterDoctors);
    }
    
    if (experienceFilter) {
        experienceFilter.addEventListener('change', filterDoctors);
    }
}

// Filter doctors based on search and filter criteria
function filterDoctors() {
    const searchTerm = document.getElementById('searchDoctors').value.toLowerCase();
    const specializationFilter = document.getElementById('filterSpecialization').value;
    const experienceFilter = document.getElementById('filterExperience').value;
    
    const table = document.getElementById('doctorsTable');
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    
    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        const name = row.cells[1].textContent.toLowerCase();
        const specialization = row.cells[2].textContent.toLowerCase();
        const experience = parseInt(row.cells[3].textContent);
        
        let showRow = true;
        
        // Search filter
        if (searchTerm && !name.includes(searchTerm)) {
            showRow = false;
        }
        
        // Specialization filter
        if (specializationFilter && !specialization.includes(specializationFilter.toLowerCase())) {
            showRow = false;
        }
        
        // Experience filter
        if (experienceFilter) {
            const [minExp, maxExp] = experienceFilter.split('-').map(Number);
            if (maxExp && (experience < minExp || experience > maxExp)) {
                showRow = false;
            } else if (!maxExp && experience < minExp) {
                showRow = false;
            }
        }
        
        row.style.display = showRow ? '' : 'none';
    }
}

// Load doctors data
function loadDoctors() {
    fetch('api/doctors.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateDoctorsTable(data.doctors);
            }
        })
        .catch(error => {
            console.error('Error loading doctors:', error);
            showAlert('Error loading doctors data', 'danger');
        });
}

// Add doctor form submission
document.addEventListener('DOMContentLoaded', function() {
    const addDoctorForm = document.getElementById('addDoctorForm');
    if (addDoctorForm) {
        addDoctorForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            // Validate phone number (exactly 10 digits)
            const phoneNumber = formData.get('phone');
            if (!/^\d{10}$/.test(phoneNumber)) {
                showAlert('Phone number must be exactly 10 digits', 'danger');
                return;
            }
            
            // Validate email format
            const email = formData.get('email');
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                showAlert('Please enter a valid email address', 'danger');
                return;
            }
            
            // Remove country code from form data
            formData.delete('country_code');
            
            fetch('api/doctors.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Doctor added successfully', 'success');
                    bootstrap.Modal.getInstance(document.getElementById('addDoctorModal')).hide();
                    this.reset();
                    loadDoctors();
                } else {
                    showAlert(data.message || 'Error adding doctor', 'danger');
                }
            })
            .catch(error => {
                console.error('Error adding doctor:', error);
                showAlert('Error adding doctor', 'danger');
            });
        });
    }
});

// Update doctors table
function updateDoctorsTable(doctors) {
    const tbody = document.querySelector('#doctorsTable tbody');
    tbody.innerHTML = '';
    
    doctors.forEach(doctor => {
        const row = createDoctorRow(doctor);
        tbody.appendChild(row);
    });
}

// Create doctor row
function createDoctorRow(doctor) {
    const row = document.createElement('tr');
    
    row.innerHTML = `
        <td>${sanitizeHTML(doctor.doctor_id)}</td>
        <td>
            <div class="d-flex align-items-center">
                <img src="${sanitizeHTML(doctor.profile_image) || 'assets/images/default-avatar.png'}"
                     class="rounded-circle me-2" width="32" height="32" alt="Doctor">
                <div>
                    <div class="fw-bold">Dr. ${sanitizeHTML(doctor.first_name)} ${sanitizeHTML(doctor.last_name)}</div>
                    <small class="text-muted">${sanitizeHTML(doctor.email)}</small>
                </div>
            </div>
        </td>
        <td>
            <span class="badge bg-primary">${sanitizeHTML(doctor.specialization)}</span>
        </td>
        <td>${sanitizeHTML(doctor.experience_years)} years</td>
        <td>$${parseFloat(doctor.consultation_fee).toFixed(2)}</td>
        <td>
            <span class="badge bg-${doctor.is_active ? 'success' : 'danger'}">
                ${doctor.is_active ? 'Available' : 'Unavailable'}
            </span>
        </td>
        <td>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-sm btn-outline-primary" 
                        onclick="viewDoctor(${doctor.id})">
                    <i class="fas fa-eye"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-warning" 
                        onclick="editDoctor(${doctor.id})">
                    <i class="fas fa-edit"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-success" 
                        onclick="viewSchedule(${doctor.id})">
                    <i class="fas fa-calendar"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger"
                        onclick="deleteDoctor(${doctor.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </td>
    `;
    
    return row;
}

// Validation functions
function validateRequired(value, fieldName) {
    if (!value || value.trim() === '') {
        return `${fieldName} is required`;
    }
    return null;
}

function validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function validatePhone(phone) {
    const phoneRegex = /^\d{10}$/;
    return phoneRegex.test(phone);
}

// Add doctor form submission
document.addEventListener('DOMContentLoaded', function() {
    const addDoctorForm = document.getElementById('addDoctorForm');
    if (!addDoctorForm) return;
    
    addDoctorForm.addEventListener('submit', function(e) {
    e.preventDefault();

    // Client-side validation
    const formData = new FormData(this);
    const data = {};
    for (let [key, value] of formData.entries()) {
        data[key] = value;
    }

    // Remove country code from data as we only want 10 digits
    delete data.country_code;

    // Validate required fields
    let validationErrors = [];

    // Validate first name
    const firstNameError = validateRequired(data.first_name, 'First name');
    if (firstNameError) validationErrors.push(firstNameError);

    // Validate last name
    const lastNameError = validateRequired(data.last_name, 'Last name');
    if (lastNameError) validationErrors.push(lastNameError);

    // Validate email
    const emailError = validateRequired(data.email, 'Email');
    if (emailError) {
        validationErrors.push(emailError);
    } else if (!validateEmail(data.email)) {
        validationErrors.push('Invalid email format');
    }

    // Validate phone
    const phoneError = validateRequired(data.phone, 'Phone');
    if (phoneError) {
        validationErrors.push(phoneError);
    } else if (!validatePhone(data.phone)) {
        validationErrors.push('Phone number must be exactly 10 digits');
    }

    // Validate specialization
    const specializationError = validateRequired(data.specialization, 'Specialization');
    if (specializationError) validationErrors.push(specializationError);

    // Validate experience years
    if (data.experience_years === '' || isNaN(data.experience_years) || data.experience_years < 0) {
        validationErrors.push('Experience years must be a non-negative number');
    }

    // Validate consultation fee
    if (data.consultation_fee === '' || isNaN(data.consultation_fee) || data.consultation_fee < 0) {
        validationErrors.push('Consultation fee must be a non-negative number');
    }

    if (validationErrors.length > 0) {
        showAlert(validationErrors.join(', '), 'danger');
        return;
    }

    fetch('api/doctors.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Doctor added successfully', 'success');
            bootstrap.Modal.getInstance(document.getElementById('addDoctorModal')).hide();
            this.reset();
            loadDoctors();
        } else {
            showAlert(data.message || 'Error adding doctor', 'danger');
        }
    })
    .catch(error => {
        console.error('Error adding doctor:', error);
        showAlert('Error adding doctor', 'danger');
    });
    });
});

// View doctor details
function viewDoctor(doctorId) {
    fetch(`api/doctors.php?id=${doctorId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showDoctorModal(data.doctor, 'view');
            }
        })
        .catch(error => {
            console.error('Error loading doctor:', error);
            showAlert('Error loading doctor details', 'danger');
        });
}

// Edit doctor
function editDoctor(doctorId) {
    fetch(`api/doctors.php?id=${doctorId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showDoctorModal(data.doctor, 'edit');
            }
        })
        .catch(error => {
            console.error('Error loading doctor:', error);
            showAlert('Error loading doctor details', 'danger');
        });
}

// View doctor schedule
function viewSchedule(doctorId) {
    // Redirect to appointments page with doctor filter
    window.location.href = `appointments.php?doctor_id=${doctorId}`;
}

// Delete doctor
function deleteDoctor(doctorId) {
    if (confirm('Are you sure you want to delete this doctor? This action cannot be undone.')) {
        fetch(`api/doctors.php`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: doctorId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Doctor deleted successfully', 'success');
                loadDoctors();
            } else {
                showAlert(data.message || 'Error deleting doctor', 'danger');
            }
        })
        .catch(error => {
            console.error('Error deleting doctor:', error);
            showAlert('Error deleting doctor', 'danger');
        });
    }
}

// Show doctor modal
function showDoctorModal(doctor, mode) {
    const modal = document.getElementById('doctorDetailsModal');
    const modalLabel = document.getElementById('doctorDetailsModalLabel');
    const modalContent = document.getElementById('doctorDetailsContent');
    const modalFooter = document.getElementById('doctorDetailsFooter');
    
    if (!modal) {
        showAlert('Doctor details modal not found', 'danger');
        return;
    }
    
    const availableDays = doctor.available_days ? JSON.parse(doctor.available_days) : [];
    const daysText = availableDays.length > 0 ? availableDays.join(', ') : 'Not set';
    
    if (mode === 'view') {
        modalLabel.textContent = 'Doctor Details';
        modalContent.innerHTML = `
            <div class="row">
                <div class="col-md-12 mb-3">
                    <h6 class="text-primary">Personal Information</h6>
                    <hr>
                </div>
                <div class="col-md-6">
                    <p><strong>Name:</strong> Dr. ${sanitizeHTML(doctor.first_name)} ${sanitizeHTML(doctor.last_name)}</p>
                    <p><strong>Doctor ID:</strong> ${sanitizeHTML(doctor.doctor_id)}</p>
                    <p><strong>Email:</strong> ${sanitizeHTML(doctor.email)}</p>
                    <p><strong>Phone:</strong> ${sanitizeHTML(doctor.phone) || 'N/A'}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Specialization:</strong> ${sanitizeHTML(doctor.specialization)}</p>
                    <p><strong>Qualification:</strong> ${sanitizeHTML(doctor.qualification) || 'N/A'}</p>
                    <p><strong>Experience:</strong> ${sanitizeHTML(doctor.experience_years)} years</p>
                    <p><strong>License Number:</strong> ${sanitizeHTML(doctor.license_number) || 'N/A'}</p>
                </div>
                
                <div class="col-md-12 mt-3 mb-3">
                    <h6 class="text-primary">Consultation Information</h6>
                    <hr>
                </div>
                <div class="col-md-6">
                    <p><strong>Consultation Fee:</strong> $${parseFloat(doctor.consultation_fee).toFixed(2)}</p>
                    <p><strong>Available Days:</strong> ${daysText}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Status:</strong> <span class="badge bg-${doctor.is_active ? 'success' : 'danger'}">${doctor.is_active ? 'Active' : 'Inactive'}</span></p>
                    <p><strong>Department:</strong> ${sanitizeHTML(doctor.department_name) || 'N/A'}</p>
                </div>
            </div>
        `;
        modalFooter.innerHTML = `
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn btn-warning" onclick="editDoctor(${doctor.id})">Edit Doctor</button>
        `;
    } else if (mode === 'edit') {
        modalLabel.textContent = 'Edit Doctor';
        modalContent.innerHTML = `
            <form id="editDoctorForm">
                <input type="hidden" id="edit_doctor_id" value="${doctor.id}">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">First Name</label>
                            <input type="text" class="form-control" id="edit_first_name" value="${doctor.first_name}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="edit_last_name" value="${doctor.last_name}" required>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit_email" value="${doctor.email}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="edit_phone" value="${doctor.phone || ''}">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Specialization</label>
                            <input type="text" class="form-control" id="edit_specialization" value="${doctor.specialization}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Qualification</label>
                            <input type="text" class="form-control" id="edit_qualification" value="${doctor.qualification || ''}">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Experience (years)</label>
                            <input type="number" class="form-control" id="edit_experience_years" value="${doctor.experience_years}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Consultation Fee</label>
                            <input type="number" step="0.01" class="form-control" id="edit_consultation_fee" value="${doctor.consultation_fee}" required>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">License Number</label>
                    <input type="text" class="form-control" id="edit_license_number" value="${doctor.license_number || ''}">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select class="form-select" id="edit_is_active">
                        <option value="1" ${doctor.is_active ? 'selected' : ''}>Active</option>
                        <option value="0" ${!doctor.is_active ? 'selected' : ''}>Inactive</option>
                    </select>
                </div>
            </form>
        `;
        modalFooter.innerHTML = `
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="saveDoctorEdit()">Save Changes</button>
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

// Save doctor edit
function saveDoctorEdit() {
    const doctorId = document.getElementById('edit_doctor_id').value;
    
    const doctorData = {
        id: doctorId,
        first_name: document.getElementById('edit_first_name').value,
        last_name: document.getElementById('edit_last_name').value,
        email: document.getElementById('edit_email').value,
        phone: document.getElementById('edit_phone').value,
        specialization: document.getElementById('edit_specialization').value,
        qualification: document.getElementById('edit_qualification').value,
        experience_years: document.getElementById('edit_experience_years').value,
        consultation_fee: document.getElementById('edit_consultation_fee').value,
        license_number: document.getElementById('edit_license_number').value,
        is_active: document.getElementById('edit_is_active').value
    };
    
    fetch('api/doctors.php', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(doctorData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Doctor updated successfully', 'success');
            const modal = bootstrap.Modal.getInstance(document.getElementById('doctorDetailsModal'));
            if (modal) {
                modal.hide();
            }
            loadDoctors();
        } else {
            showAlert(data.message || 'Error updating doctor', 'danger');
        }
    })
    .catch(error => {
        console.error('Error updating doctor:', error);
        showAlert('Error updating doctor', 'danger');
    });
}
