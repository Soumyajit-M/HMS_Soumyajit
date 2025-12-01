<?php
session_start();
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/Billing.php';
require_once 'classes/Currency.php';

$auth = new Auth();

// Check if user is logged in
if (!$auth->isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$billing = new Billing();
$currency = new Currency();
$bills = $billing->getAllBills();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Billing</title>
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
                            <a class="nav-link active" href="billing.php">
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
                    <h1 class="h2">Billing Management</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBillModal">
                            <i class="fas fa-plus"></i> Create New Bill
                        </button>
                    </div>
                </div>

                <!-- Billing Summary Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card success">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Total Revenue
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo $currency->format($billing->getTotalRevenue()); ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-dollar-sign fa-2x text-success"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card warning">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Pending Bills
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo $billing->getPendingBillsCount(); ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-clock fa-2x text-warning"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card info">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Overdue Bills
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo $billing->getOverdueBillsCount(); ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-exclamation-triangle fa-2x text-info"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card danger">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                            Outstanding Amount
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo $currency->format($billing->getOutstandingAmount()); ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-exclamation-circle fa-2x text-danger"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter Tabs -->
                <ul class="nav nav-tabs mb-4" id="billingTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="all-bills-tab" data-bs-toggle="tab" data-bs-target="#all-bills" type="button" role="tab">
                            All Bills
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pending-bills-tab" data-bs-toggle="tab" data-bs-target="#pending-bills" type="button" role="tab">
                            Pending
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="paid-bills-tab" data-bs-toggle="tab" data-bs-target="#paid-bills" type="button" role="tab">
                            Paid
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="overdue-bills-tab" data-bs-toggle="tab" data-bs-target="#overdue-bills" type="button" role="tab">
                            Overdue
                        </button>
                    </li>
                </ul>

                <!-- Search and Filter -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" id="searchBills" placeholder="Search bills...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <input type="date" class="form-control" id="filterDate" placeholder="Filter by date">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="filterStatus">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="partial">Partial</option>
                            <option value="paid">Paid</option>
                            <option value="overdue">Overdue</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-outline-secondary w-100" onclick="clearFilters()">
                            <i class="fas fa-times"></i> Clear
                        </button>
                    </div>
                </div>

                <!-- Bills Table -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">Bills List</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="billsTable">
                                <thead>
                                    <tr>
                                        <th>Bill Number</th>
                                        <th>Patient</th>
                                        <th>Appointment</th>
                                        <th>Total Amount</th>
                                        <th>Paid Amount</th>
                                        <th>Balance</th>
                                        <th>Status</th>
                                        <th>Due Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($bills as $bill): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($bill['bill_number']); ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="<?php echo $bill['patient_image'] ?: 'assets/images/default-avatar.png'; ?>" 
                                                     class="rounded-circle me-2" width="32" height="32" alt="Patient">
                                                <div>
                                                    <div class="fw-bold"><?php echo htmlspecialchars($bill['patient_name']); ?></div>
                                                    <small class="text-muted"><?php echo htmlspecialchars($bill['patient_phone']); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <div class="fw-bold"><?php echo htmlspecialchars($bill['appointment_id'] ?? 'N/A'); ?></div>
                                                <small class="text-muted"><?php echo !empty($bill['appointment_date']) ? date('M j, Y', strtotime($bill['appointment_date'])) : 'N/A'; ?></small>
                                            </div>
                                        </td>
                                        <td class="fw-bold"><?php echo $currency->format((float)($bill['total_amount'] ?? 0)); ?></td>
                                        <td class="text-success"><?php echo $currency->format((float)($bill['paid_amount'] ?? 0)); ?></td>
                                        <td class="fw-bold"><?php echo $currency->format((float)($bill['balance_amount'] ?? 0)); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $bill['payment_status'] == 'paid' ? 'success' : 
                                                    ($bill['payment_status'] == 'overdue' ? 'danger' : 
                                                    ($bill['payment_status'] == 'partial' ? 'warning' : 'info')); 
                                            ?>">
                                                <?php echo ucfirst($bill['payment_status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($bill['due_date']): ?>
                                                <span class="<?php echo strtotime($bill['due_date']) < time() ? 'text-danger' : 'text-muted'; ?>">
                                                    <?php echo date('M j, Y', strtotime($bill['due_date'])); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                                        onclick="viewBill(<?php echo $bill['id']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-success" 
                                                        onclick="addPayment(<?php echo $bill['id']; ?>)">
                                                    <i class="fas fa-credit-card"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-info" 
                                                        onclick="printBill(<?php echo $bill['id']; ?>)">
                                                    <i class="fas fa-print"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-warning" 
                                                        onclick="editBill(<?php echo $bill['id']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add Bill Modal -->
    <div class="modal fade" id="addBillModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Bill</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addBillForm">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="patientSelect" class="form-label">Patient</label>
                                    <select class="form-select" id="patientSelect" name="patient_id" required>
                                        <option value="">Select Patient</option>
                                        <!-- Patients will be loaded via AJAX -->
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="appointmentSelect" class="form-label">Appointment</label>
                                    <select class="form-select" id="appointmentSelect" name="appointment_id">
                                        <option value="">Select Appointment (Optional)</option>
                                        <!-- Appointments will be loaded via AJAX -->
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="dueDate" class="form-label">Due Date</label>
                                    <input type="date" class="form-control" id="dueDate" name="due_date">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="totalAmount" class="form-label">Total Amount</label>
                                    <input type="number" class="form-control" id="totalAmount" name="total_amount" step="0.01" required>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Billing Items -->
                        <div class="mb-3">
                            <label class="form-label">Billing Items</label>
                            <div id="billingItems">
                                <div class="row mb-2 billing-item">
                                    <div class="col-md-4">
                                        <input type="text" class="form-control" name="item_name[]" placeholder="Item name" required>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="number" class="form-control" name="quantity[]" placeholder="Qty" value="1" min="1" required>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="number" class="form-control" name="unit_price[]" placeholder="Unit price" step="0.01" required>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="number" class="form-control" name="total_price[]" placeholder="Total" step="0.01" readonly>
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" class="btn btn-danger btn-sm" onclick="removeBillingItem(this)">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="addBillingItem()">
                                <i class="fas fa-plus"></i> Add Item
                            </button>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Additional notes for the bill"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Bill</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- Bill Details/Edit Modal -->
    <div class="modal fade" id="billDetailsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="billDetailsModalLabel">Bill Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="billDetailsContent">
                    <!-- Bill details will be injected here by JS -->
                </div>
                <div class="modal-footer" id="billDetailsFooter">
                    <!-- Footer actions injected by JS -->
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="paymentForm">
                    <div class="modal-body">
                        <input type="hidden" id="paymentBillingId" name="billing_id">
                        <div class="mb-3">
                            <label class="form-label">Amount</label>
                            <input type="number" step="0.01" min="0" class="form-control" name="amount" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Payment Method</label>
                            <select class="form-select" name="payment_method">
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="insurance">Insurance</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Transaction ID</label>
                            <input type="text" class="form-control" name="transaction_id">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Add Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/billing.js"></script>
</body>
</html>
