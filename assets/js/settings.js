// Settings JavaScript

// Sanitize HTML to prevent XSS
function sanitizeHTML(str) {
    const temp = document.createElement('div');
    temp.textContent = str;
    return temp.innerHTML;
}

document.addEventListener('DOMContentLoaded', function() {
    // Initialize settings forms
    initializeSettingsForms();
    
    // Load current settings
    loadSettings();
    
    // Save Currency Settings
    const currencyForm = document.getElementById('currencySettingsForm');
    if (currencyForm) {
        currencyForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(currencyForm);
            const data = Object.fromEntries(formData);
            // Persist to server-side system_settings
            const updates = [
                { key: 'currency_default', value: data.default_currency || 'INR' },
                { key: 'currency_live_conversion', value: data.enable_live_conversion ? '1' : '0' }
            ];
            Promise.all(
                updates.map(u => fetch('api/set_setting.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(u)
                }).then(r => r.json()))
            ).then(results => {
                const ok = results.every(r => r && r.success);
                if (ok) {
                    // Mirror to localStorage for quick reads client-side
                    localStorage.setItem('hms_default_currency', updates[0].value);
                    localStorage.setItem('hms_enable_live_conversion', updates[1].value);
                    showAlert('Currency settings saved', 'success');
                } else {
                    showAlert('Error saving currency settings', 'danger');
                }
            }).catch(err => {
                console.error('Currency save error', err);
                showAlert('Error saving currency settings', 'danger');
            });
        });
    }
    
    // Expose selected currency to other modules
    window.getDefaultCurrency = function() {
        return localStorage.getItem('hms_default_currency') || 'INR';
    };
    window.getLiveConversionEnabled = function() {
        return (localStorage.getItem('hms_enable_live_conversion') || '0') === '1';
    };
});

// Initialize settings forms
function initializeSettingsForms() {
    // General settings form
    document.getElementById('generalSettingsForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        saveSettings('general', new FormData(this));
    });
    
    // Hospital settings form
    document.getElementById('hospitalSettingsForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        saveSettings('hospital', new FormData(this));
    });
    
    // Email settings form
    document.getElementById('emailSettingsForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        saveSettings('email', new FormData(this));
    });
    
    // Security settings form
    document.getElementById('securitySettingsForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        saveSettings('security', new FormData(this));
    });
}

// Load current settings
function loadSettings() {
    fetch('api/settings.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateSettingsForms(data.settings);
            }
        })
        .catch(error => {
            console.error('Error loading settings:', error);
            showAlert('Error loading settings', 'danger');
        });
}

// Populate settings forms
function populateSettingsForms(settings) {
    // General settings
    if (settings.general) {
        Object.keys(settings.general).forEach(key => {
            const element = document.getElementById(key);
            if (element) {
                element.value = settings.general[key];
            }
        });
    }
    
    // Hospital settings
    if (settings.hospital) {
        Object.keys(settings.hospital).forEach(key => {
            const element = document.getElementById(key);
            if (element) {
                element.value = settings.hospital[key];
            }
        });
    }
    
    // Email settings
    if (settings.email) {
        Object.keys(settings.email).forEach(key => {
            const element = document.getElementById(key);
            if (element) {
                element.value = settings.email[key];
            }
        });
    }
    
    // AI settings
    if (settings.ai) {
        Object.keys(settings.ai).forEach(key => {
            const element = document.getElementById(key);
            if (element) {
                if (element.type === 'checkbox') {
                    element.checked = settings.ai[key];
                } else {
                    element.value = settings.ai[key];
                }
            }
        });
    }
    
    // Security settings
    if (settings.security) {
        Object.keys(settings.security).forEach(key => {
            const element = document.getElementById(key);
            if (element) {
                if (element.type === 'checkbox') {
                    element.checked = settings.security[key];
                } else {
                    element.value = settings.security[key];
                }
            }
        });
    }

    // Currency settings (stored under general keys)
    if (settings.general) {
        const defaultCurrency = settings.general['currency_default'];
        const liveConv = settings.general['currency_live_conversion'];
        const dcEl = document.getElementById('default_currency');
        const lcEl = document.getElementById('enable_live_conversion');
        if (dcEl && defaultCurrency) {
            dcEl.value = defaultCurrency;
            localStorage.setItem('hms_default_currency', defaultCurrency);
        }
        if (lcEl && typeof liveConv !== 'undefined') {
            lcEl.value = liveConv;
            localStorage.setItem('hms_enable_live_conversion', liveConv);
        }
    }
}

// Save settings
function saveSettings(category, formData) {
    const data = Object.fromEntries(formData.entries());
    
    fetch('api/settings.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            category: category,
            settings: data
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(`${category.charAt(0).toUpperCase() + category.slice(1)} settings saved successfully`, 'success');
        } else {
            showAlert(data.message || `Error saving ${category} settings`, 'danger');
        }
    })
    .catch(error => {
        console.error(`Error saving ${category} settings:`, error);
        showAlert(`Error saving ${category} settings`, 'danger');
    });
}

// Test email settings
function testEmailSettings() {
    const formData = new FormData(document.getElementById('emailSettingsForm'));
    const data = Object.fromEntries(formData.entries());
    
    fetch('api/test-email.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Test email sent successfully', 'success');
        } else {
            showAlert(data.message || 'Error sending test email', 'danger');
        }
    })
    .catch(error => {
        console.error('Error testing email settings:', error);
        showAlert('Error testing email settings', 'danger');
    });
}

// Test AI settings
function testAISettings() {
    const formData = new FormData(document.getElementById('aiSettingsForm'));
    const data = Object.fromEntries(formData.entries());
    
    fetch('api/test-ai.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('AI settings test successful', 'success');
        } else {
            showAlert(data.message || 'Error testing AI settings', 'danger');
        }
    })
    .catch(error => {
        console.error('Error testing AI settings:', error);
        showAlert('Error testing AI settings', 'danger');
    });
}

// Reset settings to default
function resetSettings(category) {
    if (confirm(`Are you sure you want to reset ${category} settings to default?`)) {
        fetch('api/settings.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ category: category })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert(`${category.charAt(0).toUpperCase() + category.slice(1)} settings reset to default`, 'success');
                loadSettings();
            } else {
                showAlert(data.message || `Error resetting ${category} settings`, 'danger');
            }
        })
        .catch(error => {
            console.error(`Error resetting ${category} settings:`, error);
            showAlert(`Error resetting ${category} settings`, 'danger');
        });
    }
}

// Export settings
function exportSettings() {
    fetch('api/settings.php?action=export')
        .then(response => response.blob())
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `settings_${new Date().toISOString().split('T')[0]}.json`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
        })
        .catch(error => {
            console.error('Error exporting settings:', error);
            showAlert('Error exporting settings', 'danger');
        });
}

// Import settings
function importSettings() {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = '.json';
    input.onchange = function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                try {
                    const settings = JSON.parse(e.target.result);
                    importSettingsData(settings);
                } catch (error) {
                    showAlert('Invalid settings file', 'danger');
                }
            };
            reader.readAsText(file);
        }
    };
    input.click();
}

// Import settings data
function importSettingsData(settings) {
    fetch('api/settings.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            action: 'import',
            settings: settings
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Settings imported successfully', 'success');
            loadSettings();
        } else {
            showAlert(data.message || 'Error importing settings', 'danger');
        }
    })
    .catch(error => {
        console.error('Error importing settings:', error);
        showAlert('Error importing settings', 'danger');
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
