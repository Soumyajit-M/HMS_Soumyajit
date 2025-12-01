<?php
session_start();
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/Room.php';
require_once 'classes/Patient.php';

$auth = new Auth();

if (!$auth->isLoggedIn()) {
    header('Location: index.php');
    exit();
}

if (!$auth->hasRole('admin')) {
    header('Location: dashboard.php');
    exit();
}

$room = new Room();
$wards = $room->getAllWards();
$rooms = $room->getAllRooms();
$assignments = $room->getAllBedAssignments();

$patient = new Patient();
$patients = $patient->getAllPatients();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Rooms & Bed Management</title>
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
                            <a class="nav-link active" href="rooms.php">
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
                    <h1 class="h2"><i class="fas fa-bed"></i> Rooms & Bed Management</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addWardModal">
                            <i class="fas fa-plus"></i> Add Ward
                        </button>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3">
                        <div class="card text-white bg-primary">
                            <div class="card-body">
                                <h5 class="card-title">Total Wards</h5>
                                <h2 id="totalWards"><?php echo count($wards); ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-success">
                            <div class="card-body">
                                <h5 class="card-title">Total Rooms</h5>
                                <h2 id="totalRooms"><?php echo count($rooms); ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-info">
                            <div class="card-body">
                                <h5 class="card-title">Occupied Beds</h5>
                                <h2 id="occupiedBeds"><?php echo count(array_filter($assignments, fn($a) => $a['status'] == 'Occupied')); ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-warning">
                            <div class="card-body">
                                <h5 class="card-title">Available Beds</h5>
                                <h2 id="availableBeds"><?php echo count(array_filter($assignments, fn($a) => $a['status'] == 'Available')); ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#wardsTab" role="tab">
                                    <i class="fas fa-building"></i> Wards
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#roomsTab" role="tab">
                                    <i class="fas fa-door-open"></i> Rooms
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#assignmentsTab" role="tab">
                                    <i class="fas fa-bed"></i> Bed Assignments
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="wardsTab" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover" id="wardsTable">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Ward Name</th>
                                                <th>Floor</th>
                                                <th>Type</th>
                                                <th>Capacity</th>
                                                <th>Head Nurse</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($wards as $ward): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($ward['id']); ?></td>
                                                <td><?php echo htmlspecialchars($ward['ward_name']); ?></td>
                                                <td><?php echo htmlspecialchars($ward['floor']); ?></td>
                                                <td><?php echo htmlspecialchars($ward['ward_type']); ?></td>
                                                <td><?php echo htmlspecialchars($ward['capacity']); ?></td>
                                                <td><?php echo htmlspecialchars($ward['head_nurse'] ?? 'N/A'); ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-info edit-ward" data-id="<?php echo $ward['id']; ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger delete-ward" data-id="<?php echo $ward['id']; ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="roomsTab" role="tabpanel">
                                <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addRoomModal">
                                    <i class="fas fa-plus"></i> Add Room
                                </button>
                                <div class="table-responsive">
                                    <table class="table table-striped" id="roomsTable">
                                        <thead>
                                            <tr>
                                                <th>Room Number</th>
                                                <th>Ward</th>
                                                <th>Type</th>
                                                <th>Bed Count</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($rooms as $roomData): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($roomData['room_number']); ?></td>
                                                <td><?php echo htmlspecialchars($roomData['ward_name']); ?></td>
                                                <td><?php echo htmlspecialchars($roomData['room_type']); ?></td>
                                                <td>
                                                    <?php
                                                        $totalBeds = (int)($roomData['total_beds'] ?? $roomData['bed_count'] ?? 0);
                                                        $availableBeds = (int)($roomData['available_beds'] ?? $totalBeds);
                                                        echo htmlspecialchars($availableBeds . ' / ' . $totalBeds);
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php $roomStatus = strtolower($roomData['status'] ?? 'available'); ?>
                                                    <?php if ($roomStatus === 'available'): ?>
                                                        <span class="badge bg-success">Available</span>
                                                    <?php elseif ($roomStatus === 'reserved'): ?>
                                                        <span class="badge bg-warning text-dark">Reserved</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Occupied</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-info edit-room" data-id="<?php echo $roomData['id']; ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger delete-room" data-id="<?php echo $roomData['id']; ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="assignmentsTab" role="tabpanel">
                                <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#assignBedModal">
                                    <i class="fas fa-plus"></i> Assign Bed
                                </button>
                                <div class="table-responsive">
                                    <table class="table table-striped" id="assignmentsTable">
                                        <thead>
                                            <tr>
                                                <th>Room Number</th>
                                                <th>Bed Number</th>
                                                <th>Patient</th>
                                                <th>Admission Date</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($assignments as $assignment): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($assignment['room_number']); ?></td>
                                                <td><?php echo htmlspecialchars($assignment['bed_number']); ?></td>
                                                <td><?php 
                                                    $patientName = trim(($assignment['patient_full_name'] ?? '') ?: (($assignment['patient_first_name'] ?? '') . ' ' . ($assignment['patient_last_name'] ?? '')));
                                                    echo htmlspecialchars($patientName !== '' ? $patientName : 'N/A');
                                                ?></td>
                                                <td><?php echo htmlspecialchars($assignment['admission_date'] ?? 'N/A'); ?></td>
                                                <td>
                                                    <?php $status = strtolower($assignment['status'] ?? ''); ?>
                                                    <?php if ($status === 'occupied'): ?>
                                                        <span class="badge bg-danger">Occupied</span>
                                                    <?php elseif ($status === 'reserved'): ?>
                                                        <span class="badge bg-warning">Reserved</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-success">Available</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($status === 'occupied'): ?>
                                                        <button class="btn btn-sm btn-warning discharge-bed" data-id="<?php echo $assignment['id']; ?>">
                                                            <i class="fas fa-sign-out-alt"></i> Discharge
                                                        </button>
                                                    <?php endif; ?>
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

    <!-- Add Ward Modal -->
    <div class="modal fade" id="addWardModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Ward</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addWardForm">
                        <div class="mb-3">
                            <label class="form-label">Ward Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="ward_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Floor <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="floor" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ward Type <span class="text-danger">*</span></label>
                            <select class="form-select" name="ward_type" required>
                                <option value="">Select Type</option>
                                <option value="general">General</option>
                                <option value="icu">ICU</option>
                                <option value="emergency">Emergency</option>
                                <option value="pediatric">Pediatric</option>
                                <option value="maternity">Maternity</option>
                                <option value="surgery">Surgery</option>
                                <option value="private">Private</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Capacity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="capacity" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveWardBtn">Save Ward</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Room Modal -->
    <div class="modal fade" id="addRoomModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Room</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addRoomForm">
                        <div class="mb-3">
                            <label class="form-label">Ward <span class="text-danger">*</span></label>
                            <select class="form-select" name="ward_id" id="ward_id" required>
                                <option value="">Select Ward</option>
                                <?php foreach ($wards as $ward): ?>
                                <option value="<?php echo $ward['id']; ?>"><?php echo htmlspecialchars($ward['ward_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Room Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="room_number" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Room Type <span class="text-danger">*</span></label>
                            <select class="form-select" name="room_type" required>
                                <option value="">Select Type</option>
                                <option value="single">Single</option>
                                <option value="double">Double</option>
                                <option value="shared">Shared</option>
                                <option value="icu">ICU</option>
                                <option value="operation_theater">Operation Theater</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Bed Count <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="bed_count" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveRoomBtn">Save Room</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Assign Bed Modal -->
    <div class="modal fade" id="assignBedModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Assign Bed</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="assignBedForm">
                        <div class="mb-3">
                            <label class="form-label">Room <span class="text-danger">*</span></label>
                            <select class="form-select" name="room_id" id="assign_room_id" required>
                                <option value="">Select Room</option>
                                <?php foreach ($rooms as $roomData): ?>
                                <option value="<?php echo $roomData['id']; ?>"><?php echo htmlspecialchars($roomData['room_number']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Bed Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="bed_number" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Patient <span class="text-danger">*</span></label>
                            <select class="form-select" name="patient_id" id="assign_patient_id" required>
                                <option value="">Select Patient</option>
                                <?php foreach ($patients as $pat): ?>
                                <option value="<?php echo $pat['id']; ?>"><?php echo htmlspecialchars($pat['patient_id'] . ' - ' . $pat['first_name'] . ' ' . $pat['last_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Admission Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="admission_date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="assignBedBtn">Assign Bed</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Ward Modal -->
    <div class="modal fade" id="editWardModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Ward</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editWardForm">
                        <input type="hidden" name="id" id="edit_ward_id">
                        <div class="mb-3">
                            <label class="form-label">Ward Name</label>
                            <input type="text" class="form-control" name="ward_name" id="edit_ward_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Floor</label>
                            <input type="number" class="form-control" name="floor" id="edit_floor" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ward Type</label>
                            <select class="form-select" name="ward_type" id="edit_ward_type" required>
                                <option value="general">General</option>
                                <option value="icu">ICU</option>
                                <option value="emergency">Emergency</option>
                                <option value="pediatric">Pediatric</option>
                                <option value="maternity">Maternity</option>
                                <option value="surgery">Surgery</option>
                                <option value="private">Private</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Capacity</label>
                            <input type="number" class="form-control" name="capacity" id="edit_capacity" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="updateWardBtn">Update Ward</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Room Modal -->
    <div class="modal fade" id="editRoomModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Room</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editRoomForm">
                        <input type="hidden" name="id" id="edit_room_id">
                        <div class="mb-3">
                            <label class="form-label">Ward</label>
                            <select class="form-select" name="ward_id" id="edit_room_ward_id" required>
                                <?php foreach ($wards as $ward): ?>
                                <option value="<?php echo $ward['id']; ?>"><?php echo htmlspecialchars($ward['ward_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Room Number</label>
                            <input type="text" class="form-control" name="room_number" id="edit_room_number" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Room Type</label>
                            <select class="form-select" name="room_type" id="edit_room_type" required>
                                <option value="single">Single</option>
                                <option value="double">Double</option>
                                <option value="shared">Shared</option>
                                <option value="icu">ICU</option>
                                <option value="operation_theater">Operation Theater</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Bed Count</label>
                            <input type="number" class="form-control" name="bed_count" id="edit_bed_count" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" id="edit_room_status">
                                <option value="available">Available</option>
                                <option value="occupied">Occupied</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="cleaning">Cleaning</option>
                                <option value="reserved">Reserved</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="updateRoomBtn">Update Room</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/rooms.js"></script>
</body>
</html>
