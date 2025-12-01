// Dashboard JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize charts
    initializeCharts();
    
    // Initialize real-time updates
    initializeRealTimeUpdates();
    
    // Initialize notifications
    initializeNotifications();
});

// Initialize appointment chart
function initializeCharts() {
    const ctx = document.getElementById('appointmentsChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Appointments',
                    data: [12, 19, 3, 5, 2, 3, 8],
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
}

// Initialize real-time updates
function initializeRealTimeUpdates() {
    // Update dashboard every 30 seconds
    setInterval(function() {
        updateDashboardStats();
    }, 30000);
}

// Update dashboard statistics
function updateDashboardStats() {
    fetch('api/dashboard-stats.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update stat cards
                updateStatCard('total-patients', data.stats.total_patients);
                updateStatCard('today-appointments', data.stats.today_appointments);
                updateStatCard('pending-bills', data.stats.pending_bills);
                updateStatCard('emergency-cases', data.stats.emergency_cases);
            }
        })
        .catch(error => {
            console.error('Error updating dashboard stats:', error);
        });
}

// Update individual stat card
function updateStatCard(cardId, value) {
    const card = document.querySelector(`[data-stat="${cardId}"]`);
    if (card) {
        card.textContent = value;
    }
}

// Initialize notifications
function initializeNotifications() {
    // Check for new notifications every 60 seconds
    setInterval(function() {
        checkNewNotifications();
    }, 60000);
}

// Check for new notifications
function checkNewNotifications() {
    fetch('api/notifications.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.notifications.length > 0) {
                showNotificationToast(data.notifications[0]);
            }
        })
        .catch(error => {
            console.error('Error checking notifications:', error);
        });
}

// Show notification toast
function showNotificationToast(notification) {
    const toastContainer = document.getElementById('toast-container') || createToastContainer();
    
    const toast = document.createElement('div');
    toast.className = 'toast show';
    toast.innerHTML = `
        <div class="toast-header">
            <i class="fas fa-bell text-primary me-2"></i>
            <strong class="me-auto">${notification.title}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body">
            ${notification.message}
        </div>
    `;
    
    toastContainer.appendChild(toast);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        toast.remove();
    }, 5000);
}

// Create toast container if it doesn't exist
function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'toast-container position-fixed top-0 end-0 p-3';
    container.style.zIndex = '1055';
    document.body.appendChild(container);
    return container;
}

// Export data
function exportData(type) {
    fetch(`api/export.php?type=${type}`)
        .then(response => response.blob())
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `export_${type}_${new Date().toISOString().split('T')[0]}.csv`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
        })
        .catch(error => {
            console.error('Error exporting data:', error);
            showAlert('Error exporting data', 'danger');
        });
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
