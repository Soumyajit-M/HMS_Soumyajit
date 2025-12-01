<?php
session_start();
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/Laboratory.php';
require_once 'classes/Patient.php';
require_once 'classes/Doctor.php';

$auth = new Auth();

if (!$auth->isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$laboratory = new Laboratory();
$orders = $laboratory->getAllOrders();
$testTypes = $laboratory->getAllTestTypes();
$patient = new Patient();
$patients = $patient->getAllPatients();
$doctor = new Doctor();
$doctors = $doctor->getAllDoctors();

$ordersToday = count(array_filter($orders, fn($o) => date('Y-m-d', strtotime($o['order_date'])) == date('Y-m-d')));
$pendingTests = count(array_filter($orders, fn($o) => $o['status'] == 'Pending'));
$completedTests = count(array_filter($orders, fn($o) => $o['status'] == 'Completed'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Laboratory Management</title>
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
                            <a class="nav-link active" href="laboratory.php">
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
                    <h1 class="h2"><i class="fas fa-flask"></i> Laboratory Management</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createOrderModal">
                            <i class="fas fa-plus"></i> Create Lab Order
                        </button>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3">
                        <div class="card text-white bg-primary">
                            <div class="card-body">
                                <h5 class="card-title">Orders Today</h5>
                                <h2 id="ordersToday"><?php echo $ordersToday; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-warning">
                            <div class="card-body">
                                <h5 class="card-title">Pending Tests</h5>
                                <h2 id="pendingTests"><?php echo $pendingTests; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-success">
                            <div class="card-body">
                                <h5 class="card-title">Completed Tests</h5>
                                <h2 id="completedTests"><?php echo $completedTests; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-info">
                            <div class="card-body">
                                <h5 class="card-title">Test Types</h5>
                                <h2 id="testTypesCount"><?php echo count($testTypes); ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#ordersTab" role="tab">
                                    <i class="fas fa-vial"></i> Lab Orders
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#testTypesTab" role="tab">
                                    <i class="fas fa-flask"></i> Test Types
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="ordersTab" role="tabpanel">
                                <div class="mb-3">
                                    <label class="form-label">Filter by Status:</label>
                                    <select class="form-select" id="statusFilter" style="width: 200px; display: inline-block;">
                                        <option value="">All</option>
                                        <option value="Pending">Pending</option>
                                        <option value="In Progress">In Progress</option>
                                        <option value="Completed">Completed</option>
                                    </select>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover" id="ordersTable">
                                        <thead>
                                            <tr>
                                                <th>Order ID</th>
                                                <th>Patient</th>
                                                <th>Doctor</th>
                                                <th>Order Date</th>
                                                <th>Status</th>
                                                <th>Priority</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($orders as $order): ?>
                                            <tr data-status="<?php echo htmlspecialchars($order['status']); ?>">
                                                <td><?php echo htmlspecialchars($order['id']); ?></td>
                                                <td><?php echo htmlspecialchars($order['patient_name']); ?></td>
                                                <td><?php echo htmlspecialchars($order['doctor_name']); ?></td>
                                                <td><?php echo htmlspecialchars($order['order_date']); ?></td>
                                                <td>
                                                    <?php
                                                    $statusClass = 'secondary';
                                                    if ($order['status'] == 'Completed') $statusClass = 'success';
                                                    elseif ($order['status'] == 'In Progress') $statusClass = 'warning';
                                                    elseif ($order['status'] == 'Pending') $statusClass = 'info';
                                                    ?>
                                                    <span class="badge bg-<?php echo $statusClass; ?>"><?php echo htmlspecialchars($order['status']); ?></span>
                                                </td>
                                                <td>
                                                    <?php
                                                    $priorityClass = 'secondary';
                                                    if ($order['priority'] == 'Urgent') $priorityClass = 'danger';
                                                    elseif ($order['priority'] == 'High') $priorityClass = 'warning';
                                                    ?>
                                                    <span class="badge bg-<?php echo $priorityClass; ?>"><?php echo htmlspecialchars($order['priority']); ?></span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-info view-tests" data-id="<?php echo $order['id']; ?>">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-success update-result" data-id="<?php echo $order['id']; ?>">
                                                        <i class="fas fa-edit"></i> Result
                                                    </button>
                                                    <button class="btn btn-sm btn-danger delete-order" data-id="<?php echo $order['id']; ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="testTypesTab" role="tabpanel">
                                <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addTestTypeModal">
                                    <i class="fas fa-plus"></i> Add Test Type
                                </button>
                                <div class="table-responsive">
                                    <table class="table table-striped" id="testTypesTable">
                                        <thead>
                                            <tr>
                                                <th>Test Name</th>
                                                <th>Test Code</th>
                                                <th>Category</th>
                                                <th>Price</th>
                                                <th>Normal Range</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($testTypes as $test): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($test['test_name']); ?></td>
                                                <td><?php echo htmlspecialchars($test['test_code']); ?></td>
                                                <td><?php echo htmlspecialchars($test['category']); ?></td>
                                                <td>$<?php echo htmlspecialchars($test['price']); ?></td>
                                                <td><?php echo htmlspecialchars($test['normal_range'] ?? 'N/A'); ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-info edit-test-type" data-id="<?php echo $test['id']; ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger delete-test-type" data-id="<?php echo $test['id']; ?>">
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

    <!-- Create Order Modal -->
    <div class="modal fade" id="createOrderModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Lab Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="createOrderForm">
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
                            <label class="form-label">Tests <span class="text-danger">*</span></label>
                            <select class="form-select" name="tests[]" id="testsSelect" multiple size="6" required>
                                <?php foreach ($testTypes as $test): ?>
                                <option value="<?php echo $test['id']; ?>"><?php echo htmlspecialchars($test['test_name'] . ' (' . $test['test_code'] . ')'); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Hold Ctrl/Cmd to select multiple tests</small>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Priority <span class="text-danger">*</span></label>
                                <select class="form-select" name="priority" required>
                                    <option value="Normal">Normal</option>
                                    <option value="High">High</option>
                                    <option value="Urgent">Urgent</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Order Date <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" name="order_date" value="<?php echo date('Y-m-d\TH:i'); ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveOrderBtn">Create Order</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Result Modal -->
    <div class="modal fade" id="updateResultModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Test Result</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="updateResultForm">
                        <input type="hidden" name="order_id" id="result_order_id">
                        <div class="mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" name="status" required>
                                <option value="Pending">Pending</option>
                                <option value="In Progress">In Progress</option>
                                <option value="Completed">Completed</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Result</label>
                            <textarea class="form-control" name="result" rows="4"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Remarks</label>
                            <textarea class="form-control" name="remarks" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveResultBtn">Save Result</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Test Type Modal -->
    <div class="modal fade" id="addTestTypeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Test Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addTestTypeForm">
                        <div class="mb-3">
                            <label class="form-label">Test Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="test_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Test Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="test_code" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select class="form-select" name="category" required>
                                <option value="">Select Category</option>
                                <option value="Hematology">Hematology</option>
                                <option value="Biochemistry">Biochemistry</option>
                                <option value="Microbiology">Microbiology</option>
                                <option value="Immunology">Immunology</option>
                                <option value="Radiology">Radiology</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Price <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="price" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Normal Range</label>
                            <input type="text" class="form-control" name="normal_range">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Unit</label>
                            <input type="text" class="form-control" name="unit">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveTestTypeBtn">Save Test Type</button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Tests Modal -->
    <div class="modal fade" id="viewTestsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Order Tests</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="orderTestsTable">
                            <thead>
                                <tr>
                                    <th>Test Name</th>
                                    <th>Test Code</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/laboratory.js"></script>
</body>
</html>
