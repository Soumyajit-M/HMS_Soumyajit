// Schedules Management JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Load initial data
    loadSchedules();
    loadLeaves();
    
    // Add Schedule
    document.getElementById('saveScheduleBtn')?.addEventListener('click', function() {
        const form = document.getElementById('addScheduleForm');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);
        
        fetch('api/schedules.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert('Schedule added successfully!');
                form.reset();
                bootstrap.Modal.getInstance(document.getElementById('addScheduleModal')).hide();
                loadSchedules();
            } else {
                alert('Error: ' + result.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred');
        });
    });

    // Add Leave
    document.getElementById('saveLeaveBtn')?.addEventListener('click', function() {
        const form = document.getElementById('addLeaveForm');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);
        data.action = 'leave';
        
        fetch('api/schedules.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert('Leave request submitted successfully!');
                form.reset();
                bootstrap.Modal.getInstance(document.getElementById('addLeaveModal')).hide();
                loadLeaves();
            } else {
                alert('Error: ' + result.message);
            }
        });
    });
});

// Load Schedules
function loadSchedules() {
    fetch('api/schedules.php')
    .then(response => response.json())
    .then(result => {
        if (result.success && result.schedules) {
            updateSchedulesTable(result.schedules);
        }
    })
    .catch(error => console.error('Error loading schedules:', error));
}

// Update Schedules Table
function updateSchedulesTable(schedules) {
    const tbody = document.querySelector('#schedulesTable tbody');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    schedules.forEach(s => {
        const row = `
            <tr>
                <td>${s.first_name} ${s.last_name}</td>
                <td>${s.specialization}</td>
                <td>${s.day_of_week}</td>
                <td>${s.start_time} - ${s.end_time}</td>
                <td>${s.room_number || 'N/A'}</td>
                <td>${s.is_available ? '<span class="badge bg-success">Available</span>' : '<span class="badge bg-secondary">Not Available</span>'}</td>
                <td>
                    <button class="btn btn-sm btn-info edit-schedule" data-id="${s.id}"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-sm btn-danger delete-schedule" data-id="${s.id}"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
        `;
        tbody.innerHTML += row;
    });
    
    // Re-attach event listeners
    attachScheduleEventListeners();
}

// Load Leaves
function loadLeaves() {
    fetch('api/schedules.php?action=leaves')
    .then(response => response.json())
    .then(result => {
        if (result.success && result.leaves) {
            updateLeavesTable(result.leaves);
        }
    })
    .catch(error => console.error('Error loading leaves:', error));
}

// Update Leaves Table
function updateLeavesTable(leaves) {
    const tbody = document.querySelector('#leavesTable tbody');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    leaves.forEach(leave => {
        const row = `
            <tr>
                <td>${leave.first_name} ${leave.last_name}</td>
                <td>${leave.leave_date}</td>
                <td><span class="badge bg-info">${leave.leave_type}</span></td>
                <td>${leave.reason || 'N/A'}</td>
                <td>${leave.is_approved ? '<span class="badge bg-success">Approved</span>' : '<span class="badge bg-warning">Pending</span>'}</td>
                <td>
                    ${!leave.is_approved ? '<button class="btn btn-sm btn-success approve-leave" data-id="' + leave.id + '"><i class="fas fa-check"></i></button>' : ''}
                    <button class="btn btn-sm btn-danger delete-leave" data-id="${leave.id}"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
        `;
        tbody.innerHTML += row;
    });
    
    // Re-attach event listeners
    attachLeaveEventListeners();
}

// Attach Schedule Event Listeners
function attachScheduleEventListeners() {
    document.querySelectorAll('.delete-schedule').forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Delete this schedule?')) {
                fetch(`api/schedules.php?id=${this.getAttribute('data-id')}`, { method: 'DELETE' })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        alert('Schedule deleted!');
                        loadSchedules();
                    } else {
                        alert('Error: ' + result.message);
                    }
                });
            }
        });
    });
}

// Attach Leave Event Listeners
function attachLeaveEventListeners() {
    document.querySelectorAll('.delete-leave').forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Delete this leave request?')) {
                fetch(`api/schedules.php?id=${this.getAttribute('data-id')}&action=leave`, { method: 'DELETE' })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        alert('Leave deleted!');
                        loadLeaves();
                    } else {
                        alert('Error: ' + result.message);
                    }
                });
            }
        });
    });
    
    document.querySelectorAll('.approve-leave').forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Approve this leave request?')) {
                fetch('api/schedules.php', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: this.getAttribute('data-id'), action: 'approve_leave' })
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        alert('Leave approved!');
                        loadLeaves();
                    } else {
                        alert('Error: ' + result.message);
                    }
                });
            }
        });
    });
}
