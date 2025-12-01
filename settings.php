<?php
session_start();
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'classes/Auth.php';

$auth = new Auth();

// Check if user is logged in
if (!$auth->isLoggedIn()) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <i class="fas fa-hospital fa-2x text-white mb-2"></i>
                        <h5 class="text-white"><?php echo SITE_NAME; ?></h5>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="patients.php">
                                <i class="fas fa-user-injured"></i> Patients
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="doctors.php">
                                <i class="fas fa-user-md"></i> Doctors
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="staff.php">
                                <i class="fas fa-users"></i> Staff
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="schedules.php">
                                <i class="fas fa-calendar-alt"></i> Schedules
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="rooms.php">
                                <i class="fas fa-bed"></i> Rooms & Beds
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="laboratory.php">
                                <i class="fas fa-flask"></i> Laboratory
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="inventory.php">
                                <i class="fas fa-boxes"></i> Inventory
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="appointments.php">
                                <i class="fas fa-calendar-check"></i> Appointments
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="billing.php">
                                <i class="fas fa-file-invoice-dollar"></i> Billing
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="insurance.php">
                                <i class="fas fa-shield-alt"></i> Insurance
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="telemedicine.php">
                                <i class="fas fa-video"></i> Telemedicine
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reports.php">
                                <i class="fas fa-chart-bar"></i> Reports
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="settings.php">
                                <i class="fas fa-cog"></i> Settings
                            </a>
                        </li>
                    </ul>
                    
                    <hr class="text-white">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">System Settings</h1>
                </div>

                <!-- Settings Tabs -->
                <ul class="nav nav-tabs mb-4" id="settingsTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">
                            <i class="fas fa-cog me-2"></i>General
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="hospital-tab" data-bs-toggle="tab" data-bs-target="#hospital" type="button" role="tab">
                            <i class="fas fa-hospital me-2"></i>Hospital Info
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="email-tab" data-bs-toggle="tab" data-bs-target="#email" type="button" role="tab">
                            <i class="fas fa-envelope me-2"></i>Email Settings
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab">
                            <i class="fas fa-shield-alt me-2"></i>Security
                        </button>
                    </li>
                </ul>

                <!-- Settings Content -->
                <div class="tab-content" id="settingsTabContent">
                    <!-- General Settings -->
                    <div class="tab-pane fade show active" id="general" role="tabpanel">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-primary">General Settings</h6>
                            </div>
                            <div class="card-body">
                                <form id="generalSettingsForm">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="siteName" class="form-label">Site Name</label>
                                                <input type="text" class="form-control" id="siteName" name="site_name" value="<?php echo SITE_NAME; ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="siteUrl" class="form-label">Site URL</label>
                                                <input type="url" class="form-control" id="siteUrl" name="site_url" value="<?php echo SITE_URL; ?>">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="timezone" class="form-label">Timezone</label>
                                                <select class="form-select" id="timezone" name="timezone">
                                                    <option value="UTC">UTC</option>
                                                    <option value="America/New_York">Eastern Time</option>
                                                    <option value="America/Chicago">Central Time</option>
                                                    <option value="America/Denver">Mountain Time</option>
                                                    <option value="America/Los_Angeles">Pacific Time</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="currency" class="form-label">Currency</label>
                                                <select class="form-select" id="currency" name="currency">
                                                    <option value="USD">USD ($)</option>
                                                    <option value="EUR">EUR (€)</option>
                                                    <option value="GBP">GBP (£)</option>
                                                    <option value="INR">INR (₹)</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="recordsPerPage" class="form-label">Records Per Page</label>
                                                <input type="number" class="form-control" id="recordsPerPage" name="records_per_page" value="<?php echo RECORDS_PER_PAGE; ?>" min="5" max="100">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="maxFileSize" class="form-label">Max File Size (MB)</label>
                                                <input type="number" class="form-control" id="maxFileSize" name="max_file_size" value="<?php echo MAX_FILE_SIZE / 1024 / 1024; ?>" min="1" max="50">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Save General Settings
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Hospital Information -->
                    <div class="tab-pane fade" id="hospital" role="tabpanel">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-primary">Hospital Information</h6>
                            </div>
                            <div class="card-body">
                                <form id="hospitalSettingsForm">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="hospitalName" class="form-label">Hospital Name</label>
                                                <input type="text" class="form-control" id="hospitalName" name="hospital_name" value="City General Hospital">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="hospitalPhone" class="form-label">Hospital Phone</label>
                                                <input type="tel" class="form-control" id="hospitalPhone" name="hospital_phone" value="+1-234-567-8900">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="hospitalAddress" class="form-label">Hospital Address</label>
                                        <textarea class="form-control" id="hospitalAddress" name="hospital_address" rows="3">123 Medical Street, City, State 12345</textarea>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="hospitalEmail" class="form-label">Hospital Email</label>
                                                <input type="email" class="form-control" id="hospitalEmail" name="hospital_email" value="info@hospital.com">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="hospitalWebsite" class="form-label">Hospital Website</label>
                                                <input type="url" class="form-control" id="hospitalWebsite" name="hospital_website" value="https://hospital.com">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Save Hospital Settings
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Email Settings -->
                    <div class="tab-pane fade" id="email" role="tabpanel">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-primary">Email Settings</h6>
                            </div>
                            <div class="card-body">
                                <form id="emailSettingsForm">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="smtpHost" class="form-label">SMTP Host</label>
                                                <input type="text" class="form-control" id="smtpHost" name="smtp_host" value="<?php echo SMTP_HOST; ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="smtpPort" class="form-label">SMTP Port</label>
                                                <input type="number" class="form-control" id="smtpPort" name="smtp_port" value="<?php echo SMTP_PORT; ?>">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="smtpUsername" class="form-label">SMTP Username</label>
                                                <input type="text" class="form-control" id="smtpUsername" name="smtp_username" value="<?php echo SMTP_USERNAME; ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="smtpPassword" class="form-label">SMTP Password</label>
                                                <input type="password" class="form-control" id="smtpPassword" name="smtp_password" value="<?php echo SMTP_PASSWORD; ?>">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="smtpSecure" name="smtp_secure">
                                            <label class="form-check-label" for="smtpSecure">
                                                Use SSL/TLS
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Save Email Settings
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <!-- Currency Settings -->
                    <div class="tab-pane fade" id="currency" role="tabpanel">
                        <div class="card patient-card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="m-0 font-weight-bold text-primary">Currency Settings</h6>
                            </div>
                            <div class="card-body">
                                <form id="currencySettingsForm">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Default Currency</label>
                                            <select class="form-select" id="defaultCurrency" name="default_currency">
                                                <option value="INR">INR (₹)</option>
                                                <option value="USD">USD ($)</option>
                                                <option value="EUR">EUR (€)</option>
                                                <option value="GBP">GBP (£)</option>
                                                <option value="JPY">JPY (¥)</option>
                                                <option value="AUD">AUD (A$)</option>
                                                <option value="CAD">CAD (C$)</option>
                                                <option value="CHF">CHF</option>
                                                <option value="CNY">CNY (¥)</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Enable Live Conversion</label>
                                            <select class="form-select" id="enableLiveConversion" name="enable_live_conversion">
                                                <option value="1">Enabled</option>
                                                <option value="0">Disabled</option>
                                            </select>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Save Currency Settings</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Security Settings -->
                    <div class="tab-pane fade" id="security" role="tabpanel">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-primary">Security Settings</h6>
                            </div>
                            <div class="card-body">
                                <form id="securitySettingsForm">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="sessionTimeout" class="form-label">Session Timeout (minutes)</label>
                                                <input type="number" class="form-control" id="sessionTimeout" name="session_timeout" value="30" min="5" max="480">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="maxLoginAttempts" class="form-label">Max Login Attempts</label>
                                                <input type="number" class="form-control" id="maxLoginAttempts" name="max_login_attempts" value="5" min="3" max="10">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="twoFactorAuth" name="two_factor_auth">
                                            <label class="form-check-label" for="twoFactorAuth">
                                                Enable Two-Factor Authentication
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="passwordPolicy" name="password_policy" checked>
                                            <label class="form-check-label" for="passwordPolicy">
                                                Enforce Strong Password Policy
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Save Security Settings
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/settings.js"></script>
</body>
</html>
