<?php
session_start();
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/Doctor.php';

$auth = new Auth();

// Check if user is logged in
if (!$auth->isLoggedIn()) {
    header('Location: index.php');
    exit();
}

// Check if user is admin
if (!$auth->hasRole('admin')) {
    header('Location: dashboard.php');
    exit();
}

$doctor = new Doctor();
$doctors = $doctor->getAllDoctors();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Doctors</title>
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
                            <a class="nav-link active" href="doctors.php">
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
                            <a class="nav-link" href="settings.php">
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
                    <h1 class="h2">Doctors Management</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDoctorModal">
                            <i class="fas fa-plus"></i> Add New Doctor
                        </button>
                    </div>
                </div>

                <!-- Search and Filter -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" id="searchDoctors" placeholder="Search doctors...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="filterSpecialization">
                            <option value="">All Specializations</option>
                            <option value="Cardiology">Cardiology</option>
                            <option value="Neurology">Neurology</option>
                            <option value="Orthopedics">Orthopedics</option>
                            <option value="Pediatrics">Pediatrics</option>
                            <option value="Emergency">Emergency</option>
                            <option value="General Medicine">General Medicine</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="filterExperience">
                            <option value="">All Experience Levels</option>
                            <option value="0-5">0-5 years</option>
                            <option value="6-10">6-10 years</option>
                            <option value="11-15">11-15 years</option>
                            <option value="16+">16+ years</option>
                        </select>
                    </div>
                </div>

                <!-- Doctors Table -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">Doctors List</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="doctorsTable">
                                <thead>
                                    <tr>
                                        <th>Doctor ID</th>
                                        <th>Name</th>
                                        <th>Specialization</th>
                                        <th>Experience</th>
                                        <th>Consultation Fee</th>
                                        <th>Availability</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="doctorsTableBody">
                                    <!-- Doctors will be loaded dynamically -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Doctor Details Modal -->
    <div class="modal fade" id="doctorDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="doctorDetailsModalLabel">Doctor Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="doctorDetailsContent">
                    <!-- Content will be populated dynamically -->
                </div>
                <div class="modal-footer" id="doctorDetailsFooter">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Doctor Modal -->
    <div class="modal fade" id="addDoctorModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Doctor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addDoctorForm">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="firstName" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="firstName" name="first_name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="lastName" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="lastName" name="last_name" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" required pattern="[^\s@]+@[^\s@]+\.[^\s@]+" title="Please enter a valid email address with @ symbol">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone</label>
                                    <div class="input-group">
                                        <select class="form-select" id="countryCode" name="country_code" style="max-width: 120px;">
                                            <option value="+1">+1 (US)</option>
                                            <option value="+91" selected>+91 (IN)</option>
                                            <option value="+44">+44 (UK)</option>
                                            <option value="+61">+61 (AU)</option>
                                            <option value="+81">+81 (JP)</option>
                                            <option value="+49">+49 (DE)</option>
                                            <option value="+33">+33 (FR)</option>
                                            <option value="+86">+86 (CN)</option>
                                            <option value="+7">+7 (RU)</option>
                                            <option value="+55">+55 (BR)</option>
                                        </select>
                                        <input type="tel" class="form-control" id="phone" name="phone" placeholder="10-digit number" required maxlength="10" pattern="\d{10}" title="Please enter exactly 10 digits">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="specialization" class="form-label">Specialization</label>
                                    <select class="form-select" id="specialization" name="specialization" required>
                                        <option value="">Select Specialization</option>
                                        <option value="Cardiology">Cardiology</option>
                                        <option value="Neurology">Neurology</option>
                                        <option value="Orthopedics">Orthopedics</option>
                                        <option value="Pediatrics">Pediatrics</option>
                                        <option value="Emergency">Emergency</option>
                                        <option value="General Medicine">General Medicine</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="experienceYears" class="form-label">Experience (Years)</label>
                                    <input type="number" class="form-control" id="experienceYears" name="experience_years" min="0" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="consultationFee" class="form-label">Consultation Fee</label>
                                    <input type="number" class="form-control" id="consultationFee" name="consultation_fee" step="0.01" min="0" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="qualification" class="form-label">Qualification</label>
                                    <input type="text" class="form-control" id="qualification" name="qualification" placeholder="e.g., MBBS, MD, etc.">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="bio" class="form-label">Bio</label>
                            <textarea class="form-control" id="bio" name="bio" rows="3" placeholder="Brief description about the doctor"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="availableDays" class="form-label">Available Days</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="available_days[]" value="monday" id="monday">
                                        <label class="form-check-label" for="monday">Monday</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="available_days[]" value="tuesday" id="tuesday">
                                        <label class="form-check-label" for="tuesday">Tuesday</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="available_days[]" value="wednesday" id="wednesday">
                                        <label class="form-check-label" for="wednesday">Wednesday</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="available_days[]" value="thursday" id="thursday">
                                        <label class="form-check-label" for="thursday">Thursday</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="available_days[]" value="friday" id="friday">
                                        <label class="form-check-label" for="friday">Friday</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="available_days[]" value="saturday" id="saturday">
                                        <label class="form-check-label" for="saturday">Saturday</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="available_days[]" value="sunday" id="sunday">
                                        <label class="form-check-label" for="sunday">Sunday</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="availableTimeStart" class="form-label">Available Time Start</label>
                                    <input type="time" class="form-control" id="availableTimeStart" name="available_time_start">
                                </div>
                                <div class="mb-3">
                                    <label for="availableTimeEnd" class="form-label">Available Time End</label>
                                    <input type="time" class="form-control" id="availableTimeEnd" name="available_time_end">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Doctor</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/doctors.js"></script>
</body>
</html>
