<?php
session_start();
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/Telemedicine.php';
require_once 'classes/Patient.php';
require_once 'classes/Doctor.php';

$auth = new Auth();

if (!$auth->isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$telemedicine = new Telemedicine();
$sessions = $telemedicine->getAllSessions();
$monitoring = [];
$prescriptions = [];
$patient = new Patient();
$patients = $patient->getAllPatients();
$doctor = new Doctor();
$doctors = $doctor->getAllDoctors();

$sessionsToday = count(array_filter($sessions, fn($s) => date('Y-m-d', strtotime($s['session_date'])) == date('Y-m-d')));
$completedSessions = count(array_filter($sessions, fn($s) => $s['status'] == 'Completed'));
$pendingSessions = count(array_filter($sessions, fn($s) => $s['status'] == 'Scheduled'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Telemedicine Management</title>
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
                            <a class="nav-link active" href="telemedicine.php">
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
                            <a class="nav-link text-white" href="logout.php">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-video"></i> Telemedicine Management</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#scheduleSessionModal">
                            <i class="fas fa-plus"></i> Schedule Session
                        </button>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3">
                        <div class="card text-white bg-primary">
                            <div class="card-body">
                                <h5 class="card-title">Sessions Today</h5>
                                <h2 id="sessionsToday"><?php echo $sessionsToday; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-success">
                            <div class="card-body">
                                <h5 class="card-title">Completed</h5>
                                <h2 id="completedSessions"><?php echo $completedSessions; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-warning">
                            <div class="card-body">
                                <h5 class="card-title">Pending</h5>
                                <h2 id="pendingSessions"><?php echo $pendingSessions; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-info">
                            <div class="card-body">
                                <h5 class="card-title">Prescriptions</h5>
                                <h2 id="prescriptionsCount"><?php echo count($prescriptions); ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#sessionsTab" role="tab">
                                    <i class="fas fa-video"></i> Sessions
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#monitoringTab" role="tab">
                                    <i class="fas fa-heartbeat"></i> Remote Monitoring
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#prescriptionsTab" role="tab">
                                    <i class="fas fa-prescription"></i> Prescriptions
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="sessionsTab" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover" id="sessionsTable">
                                        <thead>
                                            <tr>
                                                <th>Session ID</th>
                                                <th>Patient</th>
                                                <th>Doctor</th>
                                                <th>Session Date</th>
                                                <th>Duration</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($sessions as $session): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($session['id']); ?></td>
                                                <td><?php echo htmlspecialchars($session['patient_name']); ?></td>
                                                <td>Dr. <?php echo htmlspecialchars($session['doctor_name']); ?></td>
                                                <td><?php echo htmlspecialchars($session['session_date']); ?></td>
                                                <td><?php echo htmlspecialchars($session['duration'] ?? 'N/A'); ?> min</td>
                                                <td>
                                                    <?php
                                                    $statusClass = 'secondary';
                                                    if ($session['status'] == 'Completed') $statusClass = 'success';
                                                    elseif ($session['status'] == 'In Progress') $statusClass = 'warning';
                                                    elseif ($session['status'] == 'Scheduled') $statusClass = 'info';
                                                    ?>
                                                    <span class="badge bg-<?php echo $statusClass; ?>"><?php echo htmlspecialchars($session['status']); ?></span>
                                                </td>
                                                <td>
                                                    <?php if ($session['status'] == 'Scheduled' || $session['status'] == 'In Progress'): ?>
                                                        <button class="btn btn-sm btn-success complete-session" data-id="<?php echo $session['id']; ?>">
                                                            <i class="fas fa-check"></i> Complete
                                                        </button>
                                                    <?php endif; ?>
                                                    <button class="btn btn-sm btn-info edit-session" data-id="<?php echo $session['id']; ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger delete-session" data-id="<?php echo $session['id']; ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="monitoringTab" role="tabpanel">
                                <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addMonitoringModal">
                                    <i class="fas fa-plus"></i> Add Monitoring Data
                                </button>
                                <div class="table-responsive">
                                    <table class="table table-striped" id="monitoringTable">
                                        <thead>
                                            <tr>
                                                <th>Patient</th>
                                                <th>Measurement Type</th>
                                                <th>Value</th>
                                                <th>Unit</th>
                                                <th>Recorded At</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($monitoring as $data): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($data['patient_name']); ?></td>
                                                <td><?php echo htmlspecialchars($data['measurement_type']); ?></td>
                                                <td><?php echo htmlspecialchars($data['value']); ?></td>
                                                <td><?php echo htmlspecialchars($data['unit']); ?></td>
                                                <td><?php echo htmlspecialchars($data['recorded_at']); ?></td>
                                                <td>
                                                    <?php
                                                    $statusClass = 'success';
                                                    if ($data['is_abnormal'] == 1) $statusClass = 'danger';
                                                    ?>
                                                    <span class="badge bg-<?php echo $statusClass; ?>">
                                                        <?php echo $data['is_abnormal'] == 1 ? 'Abnormal' : 'Normal'; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="prescriptionsTab" role="tabpanel">
                                <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createPrescriptionModal">
                                    <i class="fas fa-plus"></i> Create Prescription
                                </button>
                                <div class="table-responsive">
                                    <table class="table table-striped" id="prescriptionsTable">
                                        <thead>
                                            <tr>
                                                <th>Session ID</th>
                                                <th>Patient</th>
                                                <th>Doctor</th>
                                                <th>Medication</th>
                                                <th>Dosage</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($prescriptions as $prescription): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($prescription['session_id']); ?></td>
                                                <td><?php echo htmlspecialchars($prescription['patient_name']); ?></td>
                                                <td>Dr. <?php echo htmlspecialchars($prescription['doctor_name']); ?></td>
                                                <td><?php echo htmlspecialchars($prescription['medication']); ?></td>
                                                <td><?php echo htmlspecialchars($prescription['dosage']); ?></td>
                                                <td>
                                                    <?php
                                                    $statusClass = 'warning';
                                                    if ($prescription['sent_to_pharmacy'] == 1) $statusClass = 'success';
                                                    ?>
                                                    <span class="badge bg-<?php echo $statusClass; ?>">
                                                        <?php echo $prescription['sent_to_pharmacy'] == 1 ? 'Sent to Pharmacy' : 'Pending'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($prescription['sent_to_pharmacy'] == 0): ?>
                                                        <button class="btn btn-sm btn-success send-to-pharmacy" data-id="<?php echo $prescription['id']; ?>">
                                                            <i class="fas fa-paper-plane"></i> Send
                                                        </button>
                                                    <?php endif; ?>
                                                    <button class="btn btn-sm btn-danger delete-prescription" data-id="<?php echo $prescription['id']; ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Schedule Session Modal -->
    <div class="modal fade" id="scheduleSessionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Schedule Telemedicine Session</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="scheduleSessionForm">
                        <div class="mb-3">
                            <label class="form-label">Patient <span class="text-danger">*</span></label>
                            <select class="form-select" name="patient_id" required>
                                <option value="">Select Patient</option>
                                <?php foreach ($patients as $p): ?>
                                <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['first_name'] . ' ' . $p['last_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Doctor <span class="text-danger">*</span></label>
                            <select class="form-select" name="doctor_id" required>
                                <option value="">Select Doctor</option>
                                <?php foreach ($doctors as $d): ?>
                                <option value="<?php echo $d['id']; ?>">Dr. <?php echo htmlspecialchars($d['first_name'] . ' ' . $d['last_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Session Date & Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" name="session_date" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Meeting Link</label>
                            <input type="url" class="form-control" name="meeting_link" placeholder="https://meet.example.com/...">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveSessionBtn">Schedule Session</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Complete Session Modal -->
    <div class="modal fade" id="completeSessionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Complete Session</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="completeSessionForm">
                        <input type="hidden" name="id" id="complete_session_id">
                        <div class="mb-3">
                            <label class="form-label">Duration (minutes) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="duration" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Consultation Notes <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="consultation_notes" rows="4" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Diagnosis</label>
                            <textarea class="form-control" name="diagnosis" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveCompleteBtn">Complete Session</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Monitoring Modal -->
    <div class="modal fade" id="addMonitoringModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Monitoring Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addMonitoringForm">
                        <div class="mb-3">
                            <label class="form-label">Patient <span class="text-danger">*</span></label>
                            <select class="form-select" name="patient_id" required>
                                <option value="">Select Patient</option>
                                <?php foreach ($patients as $p): ?>
                                <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['first_name'] . ' ' . $p['last_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Measurement Type <span class="text-danger">*</span></label>
                            <select class="form-select" name="measurement_type" required>
                                <option value="">Select Type</option>
                                <option value="Blood Pressure">Blood Pressure</option>
                                <option value="Heart Rate">Heart Rate</option>
                                <option value="Temperature">Temperature</option>
                                <option value="Oxygen Saturation">Oxygen Saturation</option>
                                <option value="Blood Glucose">Blood Glucose</option>
                                <option value="Weight">Weight</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Value <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="value" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Unit <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="unit" placeholder="e.g., mmHg, bpm" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" rows="2"></textarea>
                        </div>
                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" name="is_abnormal" id="is_abnormal" value="1">
                            <label class="form-check-label" for="is_abnormal">
                                Mark as Abnormal
                            </label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveMonitoringBtn">Save Data</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Prescription Modal -->
    <div class="modal fade" id="createPrescriptionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Prescription</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="createPrescriptionForm">
                        <div class="mb-3">
                            <label class="form-label">Session ID <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="session_id" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Medication <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="medication" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Dosage <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="dosage" placeholder="e.g., 500mg twice daily" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Duration <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="duration" placeholder="e.g., 7 days" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Instructions</label>
                            <textarea class="form-control" name="instructions" rows="3" placeholder="Take with food..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="savePrescriptionBtn">Create Prescription</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/telemedicine.js"></script>
</body>
</html>
