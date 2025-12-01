// Staff Management JavaScript
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
            
            const staffIdInput = document.getElementById('shift_staff_id');
            const staffNameInput = document.getElementById('shift_staff_name');
            if (staffIdInput) staffIdInput.value = '';
            if (staffNameInput) staffNameInput.value = '';
            
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
                    document.getElementById('edit_id').value = staff.id;
                    document.getElementById('edit_first_name').value = staff.first_name;
                    document.getElementById('edit_last_name').value = staff.last_name;
                    document.getElementById('edit_email').value = staff.email;
                    document.getElementById('edit_phone').value = staff.phone;
                    document.getElementById('edit_role').value = staff.role;
                    document.getElementById('edit_department').value = staff.department;
                    document.getElementById('edit_salary').value = staff.salary || '';
                    document.getElementById('edit_is_active').value = staff.is_active;
                    document.getElementById('edit_emergency_contact').value = staff.emergency_contact || '';
                    document.getElementById('edit_certification').value = staff.certification || '';
                    
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
            
            const staffIdInput = document.getElementById('shift_staff_id');
            const staffNameInput = document.getElementById('shift_staff_name');
            
            if (staffIdInput && staffNameInput) {
                staffIdInput.value = staffId;
                staffNameInput.value = staffName;
                
                console.log('Opening assign shift modal...');
                const modal = document.getElementById('assignShiftModal');
                if (modal) {
                    const bsModal = new bootstrap.Modal(modal);
                    bsModal.show();
                    console.log('Shift modal shown');
                } else {
                    console.error('Assign shift modal not found!');
                }
            } else {
                console.error('Shift form inputs not found!');
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
        fetch('api/staff.php?action=shifts')
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                const tbody = document.querySelector('#shiftsTable tbody');
                tbody.innerHTML = '';
                
                result.shifts.forEach(shift => {
                    const row = `
                        <tr>
                            <td>${shift.shift_date}</td>
                            <td>${shift.first_name} ${shift.last_name}</td>
                            <td><span class="badge bg-info">${shift.shift_type}</span></td>
                            <td>${shift.start_time} - ${shift.end_time}</td>
                            <td>${shift.assigned_ward || 'Not assigned'}</td>
                            <td>
                                <button class="btn btn-sm btn-danger delete-shift" data-id="${shift.id}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                    tbody.innerHTML += row;
                });

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
            }
        })
        .catch(error => console.error('Error:', error));
    }

    // Add Shift button in Shifts tab is already handled above
});
