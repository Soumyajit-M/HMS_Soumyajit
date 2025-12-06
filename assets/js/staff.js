// Staff Management JavaScript

// Sanitize HTML to prevent XSS
function sanitizeHTML(str) {
    const temp = document.createElement('div');
    temp.textContent = str;
    return temp.innerHTML;
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('Staff.js loaded successfully');
    let staffData = [];

    // Test if modals exist
    const addModal = document.getElementById('addStaffModal');
    const editModal = document.getElementById('editStaffModal');
    const shiftModal = document.getElementById('assignShiftModal');
    console.log('Add Modal exists:', !!addModal);
    console.log('Edit Modal exists:', !!editModal);
    console.log('Shift Modal exists:', !!shiftModal);

    // Load shifts when tab is clicked
    const shiftsTab = document.querySelector('a[href="#shiftsTab"]');
    if (shiftsTab) {
        shiftsTab.addEventListener('click', loadShifts);
        // Load shifts immediately if on shifts tab
        if (shiftsTab.classList.contains('active')) {
            loadShifts();
        }
    }

    // Add Shift button (from Shifts tab)
    const addShiftBtn = document.getElementById('addShiftBtn');
    if (addShiftBtn) {
        addShiftBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Add Shift button clicked');
            
            // Clear the form
            const form = document.getElementById('assignShiftForm');
            if (form) form.reset();
            
            // Load staff members into dropdown
            loadStaffDropdown();
            
            const modal = document.getElementById('assignShiftModal');
            if (modal) {
                try {
                    const bsModal = new bootstrap.Modal(modal);
                    bsModal.show();
                    console.log('Shift modal opened from Add Shift button');
                } catch(error) {
                    console.error('Error opening modal:', error);
                }
            } else {
                console.error('Assign shift modal not found!');
            }
        });
    }

    // Add Staff
    const saveStaffBtn = document.getElementById('saveStaffBtn');
    if (saveStaffBtn) {
        saveStaffBtn.addEventListener('click', function() {
            const form = document.getElementById('addStaffForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);

            // Validate phone number (exactly 10 digits)
            if (!/^\d{10}$/.test(data.phone)) {
                alert('Phone number must be exactly 10 digits');
                return;
            }

            // Validate email format
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data.email)) {
                alert('Please enter a valid email address');
                return;
            }

            // Remove country code from data
            delete data.country_code;

            fetch('api/staff.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert('Staff member added successfully!');
                    form.reset();
                    bootstrap.Modal.getInstance(document.getElementById('addStaffModal')).hide();
                    location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while adding staff member');
            });
        });
    }

    // Edit Staff - Using event delegation
    document.addEventListener('click', function(e) {
        if (e.target.closest('.edit-staff')) {
            const button = e.target.closest('.edit-staff');
            const staffId = button.getAttribute('data-id');
            console.log('Edit button clicked for staff ID:', staffId);
            
            fetch(`api/staff.php?id=${staffId}`)
            .then(response => response.json())
            .then(result => {
                console.log('Staff data received:', result);
                if (result.success && result.staff) {
                    const staff = result.staff;
                    
                    // Set values with null checks
                    const setFieldValue = (id, value) => {
                        const field = document.getElementById(id);
                        if (field) {
                            field.value = value || '';
                        } else {
                            console.error(`Field not found: ${id}`);
                        }
                    };
                    
                    setFieldValue('edit_id', staff.id);
                    setFieldValue('edit_first_name', staff.first_name);
                    setFieldValue('edit_last_name', staff.last_name);
                    setFieldValue('edit_email', staff.email);
                    setFieldValue('edit_phone', staff.phone);
                    setFieldValue('edit_role', staff.role);
                    setFieldValue('edit_department_id', staff.department_id);
                    setFieldValue('edit_salary', staff.salary);
                    setFieldValue('edit_is_active', staff.is_active);
                    setFieldValue('edit_emergency_contact', staff.emergency_contact_name);
                    setFieldValue('edit_certification', staff.certifications);
                    
                    console.log('Opening edit modal...');
                    const modal = document.getElementById('editStaffModal');
                    if (modal) {
                        const bsModal = new bootstrap.Modal(modal);
                        bsModal.show();
                        console.log('Modal shown');
                    } else {
                        console.error('Edit modal not found!');
                    }
                } else {
                    console.error('Failed to load staff data:', result.message);
                }
            })
            .catch(error => {
                console.error('Error loading staff:', error);
            });
        }
    });

    // Update Staff
    const updateStaffBtn = document.getElementById('updateStaffBtn');
    if (updateStaffBtn) {
        updateStaffBtn.addEventListener('click', function() {
            const form = document.getElementById('editStaffForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);

            // Validate phone number (exactly 10 digits)
            if (!/^\d{10}$/.test(data.phone)) {
                alert('Phone number must be exactly 10 digits');
                return;
            }

            // Validate email format
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data.email)) {
                alert('Please enter a valid email address');
                return;
            }

            // Remove country code from data
            delete data.edit_country_code;

            fetch('api/staff.php', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert('Staff member updated successfully!');
                    bootstrap.Modal.getInstance(document.getElementById('editStaffModal')).hide();
                    location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating staff member');
            });
        });
    }

    // Delete Staff - Using event delegation
    document.addEventListener('click', function(e) {
        if (e.target.closest('.delete-staff')) {
            if (confirm('Are you sure you want to delete this staff member?')) {
                const button = e.target.closest('.delete-staff');
                const staffId = button.getAttribute('data-id');
                
                fetch(`api/staff.php?id=${staffId}`, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        alert('Staff member deleted successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + result.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting staff member');
                });
            }
        }
    });

    // Assign Shift - Using event delegation
    document.addEventListener('click', function(e) {
        if (e.target.closest('.assign-shift')) {
            const button = e.target.closest('.assign-shift');
            const staffId = button.getAttribute('data-id');
            const staffName = button.getAttribute('data-name');
            console.log('Assign shift clicked for:', staffName, 'ID:', staffId);
            
            // Load staff members into dropdown
            loadStaffDropdown(staffId);
            
            console.log('Opening assign shift modal...');
            const modal = document.getElementById('assignShiftModal');
            if (modal) {
                const bsModal = new bootstrap.Modal(modal);
                bsModal.show();
                console.log('Shift modal shown');
            } else {
                console.error('Assign shift modal not found!');
            }
        }
    });

    // Save Shift
    const saveShiftBtn = document.getElementById('saveShiftBtn');
    if (saveShiftBtn) {
        saveShiftBtn.addEventListener('click', function() {
            const form = document.getElementById('assignShiftForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);
            data.action = 'shift';

            fetch('api/staff.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert('Shift assigned successfully!');
                    form.reset();
                    bootstrap.Modal.getInstance(document.getElementById('assignShiftModal')).hide();
                    loadShifts();
                } else {
                    alert('Error: ' + result.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while assigning shift');
            });
        });
    }

    // Load Shifts
    function loadShifts() {
        console.log('Loading shifts...');
        fetch('api/staff.php?action=shifts')
        .then(response => {
            console.log('Shifts response status:', response.status);
            return response.json();
        })
        .then(result => {
            console.log('Shifts result:', result);
            if (result.success) {
                const tbody = document.querySelector('#shiftsTable tbody');
                tbody.innerHTML = '';
                
                if (result.shifts && result.shifts.length > 0) {
                    result.shifts.forEach(shift => {
                        const row = `
                            <tr>
                                <td>${sanitizeHTML(shift.shift_date)}</td>
                                <td>${sanitizeHTML(shift.first_name)} ${sanitizeHTML(shift.last_name)}</td>
                                <td><span class="badge bg-info">${sanitizeHTML(shift.shift_type)}</span></td>
                                <td>${sanitizeHTML(shift.start_time)} - ${sanitizeHTML(shift.end_time)}</td>
                                <td>${sanitizeHTML(shift.assigned_ward) || 'Not assigned'}</td>
                                <td>
                                    <button class="btn btn-sm btn-danger delete-shift" data-id="${shift.id}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                        tbody.innerHTML += row;
                    });
                } else {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center">No shifts found. Click "Assign Shift" to create one.</td></tr>';
                }

                // Add delete event listeners
                document.querySelectorAll('.delete-shift').forEach(button => {
                    button.addEventListener('click', function() {
                        if (confirm('Are you sure you want to delete this shift?')) {
                            const shiftId = this.getAttribute('data-id');
                            
                            fetch(`api/staff.php?id=${shiftId}&action=shift`, {
                                method: 'DELETE'
                            })
                            .then(response => response.json())
                            .then(result => {
                                if (result.success) {
                                    alert('Shift deleted successfully!');
                                    loadShifts();
                                } else {
                                    alert('Error: ' + result.message);
                                }
                            })
                            .catch(error => console.error('Error:', error));
                        }
                    });
                });
            } else {
                console.error('Failed to load shifts:', result.message);
            }
        })
        .catch(error => {
            console.error('Error loading shifts:', error);
        });
    }

    // Function to load staff members into dropdown
    function loadStaffDropdown(selectedId = null) {
        fetch('api/staff.php')
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                const dropdown = document.getElementById('shift_staff_id');
                if (dropdown) {
                    // Clear existing options except the first one
                    dropdown.innerHTML = '<option value="">Select Staff Member</option>';
                    
                    // Add staff members as options
                    result.staff.forEach(staff => {
                        const option = document.createElement('option');
                        option.value = staff.id;
                        option.textContent = `${staff.first_name} ${staff.last_name} - ${staff.role}`;
                        
                        // Select the staff member if specified
                        if (selectedId && staff.id == selectedId) {
                            option.selected = true;
                        }
                        
                        dropdown.appendChild(option);
                    });
                }
            }
        })
        .catch(error => console.error('Error loading staff:', error));
    }

    // Add Shift button in Shifts tab is already handled above
});
