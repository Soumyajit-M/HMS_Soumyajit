<?php
session_start();
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/Schedule.php';
require_once 'classes/Doctor.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$schedule = new Schedule();
$doctor = new Doctor();
$doctors = $doctor->getAllDoctors();
$schedules = $schedule->getAllSchedules();
$leaves = $schedule->getAllLeaves();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Doctor Schedules</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <i class="fas fa-hospital fa-2x text-white mb-2"></i>
                        <h5 class="text-white"><?php echo SITE_NAME; ?></h5>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="patients.php"><i class="fas fa-user-injured"></i> Patients</a></li>
                        <li class="nav-item"><a class="nav-link" href="doctors.php"><i class="fas fa-user-md"></i> Doctors</a></li>
                        <li class="nav-item"><a class="nav-link" href="staff.php"><i class="fas fa-users"></i> Staff</a></li>
                        <li class="nav-item"><a class="nav-link active" href="schedules.php"><i class="fas fa-calendar-alt"></i> Schedules</a></li>
                        <li class="nav-item"><a class="nav-link" href="rooms.php"><i class="fas fa-bed"></i> Rooms & Beds</a></li>
                        <li class="nav-item"><a class="nav-link" href="laboratory.php"><i class="fas fa-flask"></i> Laboratory</a></li>
                        <li class="nav-item"><a class="nav-link" href="inventory.php"><i class="fas fa-boxes"></i> Inventory</a></li>
                        <li class="nav-item"><a class="nav-link" href="appointments.php"><i class="fas fa-calendar-check"></i> Appointments</a></li>
                        <li class="nav-item"><a class="nav-link" href="billing.php"><i class="fas fa-file-invoice-dollar"></i> Billing</a></li>
                        <li class="nav-item"><a class="nav-link" href="insurance.php"><i class="fas fa-shield-alt"></i> Insurance</a></li>
                        <li class="nav-item"><a class="nav-link" href="telemedicine.php"><i class="fas fa-video"></i> Telemedicine</a></li>
                        <li class="nav-item"><a class="nav-link" href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                        <li class="nav-item"><a class="nav-link" href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                    </ul>
                    <hr class="text-white">
                    <ul class="nav flex-column">
                        <li class="nav-item"><a class="nav-link text-white" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-calendar-alt"></i> Doctor Schedules</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addScheduleModal">
                            <i class="fas fa-plus"></i> Add Schedule
                        </button>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3"><div class="card text-white bg-primary"><div class="card-body"><h5 class="card-title">Total Schedules</h5><h2 id="totalSchedules"><?php echo count($schedules); ?></h2></div></div></div>
                    <div class="col-md-3"><div class="card text-white bg-warning"><div class="card-body"><h5 class="card-title">Pending Leaves</h5><h2 id="pendingLeaves"><?php echo count(array_filter($leaves, fn($l) => $l['is_approved'] == 0)); ?></h2></div></div></div>
                    <div class="col-md-3"><div class="card text-white bg-success"><div class="card-body"><h5 class="card-title">Approved Leaves</h5><h2 id="approvedLeaves"><?php echo count(array_filter($leaves, fn($l) => $l['is_approved'] == 1)); ?></h2></div></div></div>
                    <div class="col-md-3"><div class="card text-white bg-info"><div class="card-body"><h5 class="card-title">Active Doctors</h5><h2 id="activeDoctors"><?php echo count($doctors); ?></h2></div></div></div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs" role="tablist">
                            <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#schedulesTab"><i class="fas fa-calendar"></i> Schedules</a></li>
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#leavesTab"><i class="fas fa-calendar-times"></i> Leave Requests</a></li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="schedulesTab">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover" id="schedulesTable">
                                        <thead><tr><th>Doctor</th><th>Specialization</th><th>Day</th><th>Time</th><th>Room</th><th>Status</th><th>Actions</th></tr></thead>
                                        <tbody>
                                            <?php foreach ($schedules as $s): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($s['first_name'] . ' ' . $s['last_name']); ?></td>
                                                <td><?php echo htmlspecialchars($s['specialization']); ?></td>
                                                <td><?php echo htmlspecialchars($s['day_of_week']); ?></td>
                                                <td><?php echo htmlspecialchars($s['start_time'] . ' - ' . $s['end_time']); ?></td>
                                                <td><?php echo htmlspecialchars($s['room_number'] ?? 'N/A'); ?></td>
                                                <td><?php echo $s['is_available'] ? '<span class="badge bg-success">Available</span>' : '<span class="badge bg-secondary">Not Available</span>'; ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-info edit-schedule" data-id="<?php echo $s['id']; ?>"><i class="fas fa-edit"></i></button>
                                                    <button class="btn btn-sm btn-danger delete-schedule" data-id="<?php echo $s['id']; ?>"><i class="fas fa-trash"></i></button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="leavesTab">
                                <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addLeaveModal"><i class="fas fa-plus"></i> Request Leave</button>
                                <div class="table-responsive">
                                    <table class="table table-striped" id="leavesTable">
                                        <thead><tr><th>Doctor</th><th>Date</th><th>Type</th><th>Reason</th><th>Status</th><th>Actions</th></tr></thead>
                                        <tbody>
                                            <?php foreach ($leaves as $leave): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($leave['first_name'] . ' ' . $leave['last_name']); ?></td>
                                                <td><?php echo htmlspecialchars($leave['leave_date']); ?></td>
                                                <td><span class="badge bg-info"><?php echo htmlspecialchars($leave['leave_type']); ?></span></td>
                                                <td><?php echo htmlspecialchars($leave['reason'] ?? 'N/A'); ?></td>
                                                <td><?php echo $leave['is_approved'] ? '<span class="badge bg-success">Approved</span>' : '<span class="badge bg-warning">Pending</span>'; ?></td>
                                                <td>
                                                    <?php if (!$leave['is_approved'] && $auth->hasRole('admin')): ?>
                                                    <button class="btn btn-sm btn-success approve-leave" data-id="<?php echo $leave['id']; ?>"><i class="fas fa-check"></i></button>
                                                    <?php endif; ?>
                                                    <button class="btn btn-sm btn-danger delete-leave" data-id="<?php echo $leave['id']; ?>"><i class="fas fa-trash"></i></button>
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

    <!-- Add Schedule Modal -->
    <div class="modal fade" id="addScheduleModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Add Schedule</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <form id="addScheduleForm">
                <div class="mb-3"><label class="form-label">Doctor <span class="text-danger">*</span></label>
                    <select class="form-select" name="doctor_id" required>
                        <option value="">Select Doctor</option>
                        <?php foreach ($doctors as $d): ?><option value="<?php echo $d['id']; ?>"><?php echo htmlspecialchars($d['first_name'] . ' ' . $d['last_name'] . ' - ' . $d['specialization']); ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3"><label class="form-label">Day of Week <span class="text-danger">*</span></label>
                    <select class="form-select" name="day_of_week" required>
                        <option value="">Select Day</option>
                        <option value="Monday">Monday</option><option value="Tuesday">Tuesday</option><option value="Wednesday">Wednesday</option><option value="Thursday">Thursday</option><option value="Friday">Friday</option><option value="Saturday">Saturday</option><option value="Sunday">Sunday</option>
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label">Start Time <span class="text-danger">*</span></label><input type="time" class="form-control" name="start_time" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">End Time <span class="text-danger">*</span></label><input type="time" class="form-control" name="end_time" required></div>
                </div>
                <div class="mb-3"><label class="form-label">Room Number</label><input type="text" class="form-control" name="room_number"></div>
                <div class="mb-3"><label class="form-label">Available</label><select class="form-select" name="is_available"><option value="1">Yes</option><option value="0">No</option></select></div>
            </form>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="button" class="btn btn-primary" id="saveScheduleBtn">Save Schedule</button></div>
    </div></div></div>

    <!-- Add Leave Modal -->
    <div class="modal fade" id="addLeaveModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Request Leave</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <form id="addLeaveForm">
                <div class="mb-3"><label class="form-label">Doctor <span class="text-danger">*</span></label>
                    <select class="form-select" name="doctor_id" required>
                        <option value="">Select Doctor</option>
                        <?php foreach ($doctors as $d): ?><option value="<?php echo $d['id']; ?>"><?php echo htmlspecialchars($d['first_name'] . ' ' . $d['last_name']); ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3"><label class="form-label">Leave Date <span class="text-danger">*</span></label><input type="date" class="form-control" name="leave_date" required></div>
                <div class="mb-3"><label class="form-label">Leave Type <span class="text-danger">*</span></label>
                    <select class="form-select" name="leave_type" required>
                        <option value="">Select Type</option><option value="Sick Leave">Sick Leave</option><option value="Casual Leave">Casual Leave</option><option value="Emergency">Emergency</option><option value="Vacation">Vacation</option>
                    </select>
                </div>
                <div class="mb-3"><label class="form-label">Reason</label><textarea class="form-control" name="reason" rows="3"></textarea></div>
            </form>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="button" class="btn btn-primary" id="saveLeaveBtn">Submit Leave Request</button></div>
    </div></div></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/schedules.js"></script>
</body>
</html>
