// Telemedicine Management JavaScript

// Sanitize HTML to prevent XSS
function sanitizeHTML(str) {
    const temp = document.createElement('div');
    temp.textContent = str;
    return temp.innerHTML;
}

document.addEventListener('DOMContentLoaded', function() {
    // Schedule Session
    document.getElementById('saveSessionBtn').addEventListener('click', function() {
        const form = document.getElementById('scheduleSessionForm');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);

        fetch('api/telemedicine.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert('Session scheduled successfully!');
                form.reset();
                bootstrap.Modal.getInstance(document.getElementById('scheduleSessionModal')).hide();
                location.reload();
            } else {
                alert('Error: ' + result.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while scheduling session');
        });
    });

    // Edit Session
    document.querySelectorAll('.edit-session').forEach(button => {
        button.addEventListener('click', function() {
            const sessionId = this.getAttribute('data-id');
            
            fetch(`api/telemedicine.php?id=${sessionId}`)
            .then(response => response.json())
            .then(result => {
                if (result.success && result.session) {
                    // You would need to create an edit modal similar to schedule modal
                    alert('Edit functionality - implement edit session modal');
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });

    // Delete Session
    document.querySelectorAll('.delete-session').forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Are you sure you want to delete this session?')) {
                const sessionId = this.getAttribute('data-id');
                
                fetch(`api/telemedicine.php?id=${sessionId}`, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        alert('Session deleted successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + result.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting session');
                });
            }
        });
    });

    // Complete Session
    document.querySelectorAll('.complete-session').forEach(button => {
        button.addEventListener('click', function() {
            const sessionId = this.getAttribute('data-id');
            document.getElementById('complete_session_id').value = sessionId;
            new bootstrap.Modal(document.getElementById('completeSessionModal')).show();
        });
    });

    // Save Complete Session
    document.getElementById('saveCompleteBtn').addEventListener('click', function() {
        const form = document.getElementById('completeSessionForm');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);
        data.action = 'complete';

        fetch('api/telemedicine.php', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert('Session completed successfully!');
                form.reset();
                bootstrap.Modal.getInstance(document.getElementById('completeSessionModal')).hide();
                location.reload();
            } else {
                alert('Error: ' + result.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while completing session');
        });
    });

    // Add Monitoring Data
    document.getElementById('saveMonitoringBtn').addEventListener('click', function() {
        const form = document.getElementById('addMonitoringForm');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);
        data.action = 'monitoring';
        
        // Handle checkbox
        if (!data.is_abnormal) {
            data.is_abnormal = 0;
        }

        fetch('api/telemedicine.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert('Monitoring data added successfully!');
                form.reset();
                bootstrap.Modal.getInstance(document.getElementById('addMonitoringModal')).hide();
                location.reload();
            } else {
                alert('Error: ' + result.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while adding monitoring data');
        });
    });

    // Create Prescription
    document.getElementById('savePrescriptionBtn').addEventListener('click', function() {
        const form = document.getElementById('createPrescriptionForm');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);
        data.action = 'prescription';

        fetch('api/telemedicine.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert('Prescription created successfully!');
                form.reset();
                bootstrap.Modal.getInstance(document.getElementById('createPrescriptionModal')).hide();
                location.reload();
            } else {
                alert('Error: ' + result.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while creating prescription');
        });
    });

    // Send to Pharmacy
    document.querySelectorAll('.send-to-pharmacy').forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Send this prescription to pharmacy?')) {
                const prescriptionId = this.getAttribute('data-id');
                
                fetch('api/telemedicine.php?action=send_to_pharmacy', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: prescriptionId })
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        alert('Prescription sent to pharmacy successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + result.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while sending prescription');
                });
            }
        });
    });

    // Delete Prescription
    document.querySelectorAll('.delete-prescription').forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Are you sure you want to delete this prescription?')) {
                const prescriptionId = this.getAttribute('data-id');
                
                fetch(`api/telemedicine.php?action=prescription&id=${prescriptionId}`, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        alert('Prescription deleted successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + result.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting prescription');
                });
            }
        });
    });
});
