// Rooms & Bed Management JavaScript

// Sanitize HTML to prevent XSS
function sanitizeHTML(str) {
    const temp = document.createElement('div');
    temp.textContent = str;
    return temp.innerHTML;
}

document.addEventListener('DOMContentLoaded', function() {
    // Data is loaded server-side via PHP, no need to call loadWards/loadRooms
    
    // Load assignments when tab is clicked
    const assignmentsTab = document.querySelector('a[href="#assignmentsTab"]');
    if (assignmentsTab) {
        assignmentsTab.addEventListener('click', loadAssignments);
    }

    // Add Ward
    const saveWardBtn = document.getElementById('saveWardBtn');
    if (saveWardBtn) {
        saveWardBtn.addEventListener('click', function() {
            const form = document.getElementById('addWardForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);
            data.action = 'ward';

            fetch('api/rooms.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert('Ward added successfully!');
                    form.reset();
                    bootstrap.Modal.getInstance(document.getElementById('addWardModal')).hide();
                    location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while adding ward');
            });
        });
    }

    // Edit Ward - Use event delegation
    document.addEventListener('click', function(e) {
        if (e.target.closest('.edit-ward')) {
            const wardId = e.target.closest('.edit-ward').getAttribute('data-id');
            
            fetch(`api/rooms.php?action=wards&id=${wardId}`)
            .then(response => response.json())
            .then(result => {
                if (result.success && result.ward) {
                    const ward = result.ward;
                    document.getElementById('edit_ward_id').value = ward.id;
                    document.getElementById('edit_ward_name').value = ward.ward_name;
                    document.getElementById('edit_floor').value = ward.floor_number || ward.floor || '';
                    document.getElementById('edit_ward_type').value = ward.ward_type;
                    document.getElementById('edit_capacity').value = ward.total_beds || ward.capacity || '';
                    
                    new bootstrap.Modal(document.getElementById('editWardModal')).show();
                }
            })
            .catch(error => console.error('Error:', error));
        }
    });

    // Update Ward
    const updateWardBtn = document.getElementById('updateWardBtn');
    if (updateWardBtn) {
        updateWardBtn.addEventListener('click', function() {
            const form = document.getElementById('editWardForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);
            data.action = 'ward';

            fetch('api/rooms.php', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert('Ward updated successfully!');
                    bootstrap.Modal.getInstance(document.getElementById('editWardModal')).hide();
                    location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating ward');
            });
        });
    }

    // Delete Ward - Use event delegation for dynamically loaded content
    document.addEventListener('click', function(e) {
        if (e.target.closest('.delete-ward')) {
            if (confirm('Are you sure you want to delete this ward?')) {
                const wardId = e.target.closest('.delete-ward').getAttribute('data-id');
                
                fetch(`api/rooms.php?action=wards&id=${wardId}`, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        alert('Ward deleted successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + result.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting ward');
                });
            }
        }
    });

    // Add Room
    const saveRoomBtn = document.getElementById('saveRoomBtn');
    if (saveRoomBtn) {
        saveRoomBtn.addEventListener('click', function() {
            const form = document.getElementById('addRoomForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);

            fetch('api/rooms.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert('Room added successfully!');
                    form.reset();
                    bootstrap.Modal.getInstance(document.getElementById('addRoomModal')).hide();
                    location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while adding room');
            });
        });
    }

    // Edit Room - Use event delegation
    document.addEventListener('click', function(e) {
        if (e.target.closest('.edit-room')) {
            const roomId = e.target.closest('.edit-room').getAttribute('data-id');
            
            fetch(`api/rooms.php?id=${roomId}`)
            .then(response => response.json())
            .then(result => {
                if (result.success && result.room) {
                    const room = result.room;
                    document.getElementById('edit_room_id').value = room.id;
                    document.getElementById('edit_room_ward_id').value = room.ward_id;
                    document.getElementById('edit_room_number').value = room.room_number;
                    document.getElementById('edit_room_type').value = room.room_type;
                    document.getElementById('edit_bed_count').value = room.total_beds || room.bed_count;
                    document.getElementById('edit_room_status').value = room.status;
                    
                    new bootstrap.Modal(document.getElementById('editRoomModal')).show();
                }
            })
            .catch(error => console.error('Error:', error));
        }
    });

    // Update Room
    const updateRoomBtn = document.getElementById('updateRoomBtn');
    if (updateRoomBtn) {
        updateRoomBtn.addEventListener('click', function() {
            const form = document.getElementById('editRoomForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);

            fetch('api/rooms.php', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert('Room updated successfully!');
                    bootstrap.Modal.getInstance(document.getElementById('editRoomModal')).hide();
                    location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating room');
            });
        });
    }

    // Delete Room - Use event delegation
    document.addEventListener('click', function(e) {
        if (e.target.closest('.delete-room')) {
            if (confirm('Are you sure you want to delete this room?')) {
                const roomId = e.target.closest('.delete-room').getAttribute('data-id');
                
                fetch(`api/rooms.php?id=${roomId}`, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        alert('Room deleted successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + result.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting room');
                });
            }
        }
    });

    // Assign Bed
    const assignBedBtn = document.getElementById('assignBedBtn');
    if (assignBedBtn) {
        assignBedBtn.addEventListener('click', function() {
            const form = document.getElementById('assignBedForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);
            data.action = 'assign_bed';

            fetch('api/rooms.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert('Bed assigned successfully!');
                    form.reset();
                    bootstrap.Modal.getInstance(document.getElementById('assignBedModal')).hide();
                    location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while assigning bed');
            });
        });
    }

    // Discharge Bed - Use event delegation
    document.addEventListener('click', function(e) {
        if (e.target.closest('.discharge-bed')) {
            if (confirm('Are you sure you want to discharge this patient from the bed?')) {
                const assignmentId = e.target.closest('.discharge-bed').getAttribute('data-id');
                
                fetch('api/rooms.php', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: assignmentId, action: 'discharge' })
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        alert('Bed discharged successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + result.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while discharging bed');
                });
            }
        }
    });

    // Load Assignments
    function loadAssignments() {
        fetch('api/rooms.php?action=assignments')
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                const tbody = document.querySelector('#assignmentsTable tbody');
                tbody.innerHTML = '';
                
                result.assignments.forEach(assignment => {
                    const patientName = (assignment.patient_full_name && assignment.patient_full_name.trim().length > 0)
                        ? assignment.patient_full_name
                        : ((assignment.patient_first_name || '') + ' ' + (assignment.patient_last_name || '')).trim();
                    const row = `
                        <tr>
                            <td>${sanitizeHTML(assignment.room_number) || ''}</td>
                            <td>${sanitizeHTML(assignment.bed_number) || ''}</td>
                            <td>${sanitizeHTML(patientName) || 'N/A'}</td>
                            <td>${sanitizeHTML(assignment.admission_date) || 'N/A'}</td>
                            <td>
                                ${(() => {
                                    const status = (assignment.status || '').toLowerCase();
                                    if (status === 'occupied') {
                                        return '<span class="badge bg-danger">Occupied</span>';
                                    }
                                    if (status === 'reserved') {
                                        return '<span class="badge bg-warning">Reserved</span>';
                                    }
                                    return '<span class="badge bg-success">Available</span>';
                                })()}
                            </td>
                            <td>
                                ${((assignment.status || '').toLowerCase() === 'occupied') ? 
                                    `<button class="btn btn-sm btn-warning discharge-bed" data-id="${assignment.id}">
                                        <i class="fas fa-sign-out-alt"></i> Discharge
                                    </button>` : ''}
                            </td>
                        </tr>
                    `;
                    tbody.innerHTML += row;
                });

                // Re-attach discharge event listeners
                document.querySelectorAll('.discharge-bed').forEach(button => {
                    button.addEventListener('click', function() {
                        if (confirm('Are you sure you want to discharge this patient from the bed?')) {
                            const assignmentId = this.getAttribute('data-id');
                            
                            fetch('api/rooms.php?action=discharge', {
                                method: 'PUT',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ id: assignmentId })
                            })
                            .then(response => response.json())
                            .then(result => {
                                if (result.success) {
                                    alert('Bed discharged successfully!');
                                    loadAssignments();
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
});
