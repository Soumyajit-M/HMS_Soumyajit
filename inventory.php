<?php
session_start();
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/Inventory.php';

$auth = new Auth();

if (!$auth->isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$inventory = new Inventory();
$items = $inventory->getAllItems();
$batches = [];
$categories = $inventory->getAllCategories();
$lowStock = $inventory->getLowStockItems();
$expiring = $inventory->getExpiringItems();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Inventory Management</title>
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
                            <a class="nav-link active" href="inventory.php">
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
                    <h1 class="h2"><i class="fas fa-boxes"></i> Inventory Management</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addItemModal">
                            <i class="fas fa-plus"></i> Add Item
                        </button>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3">
                        <div class="card text-white bg-primary">
                            <div class="card-body">
                                <h5 class="card-title">Total Items</h5>
                                <h2 id="totalItems"><?php echo count($items); ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-warning">
                            <div class="card-body">
                                <h5 class="card-title">Low Stock Items</h5>
                                <h2 id="lowStockItems"><?php echo count($lowStock); ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-danger">
                            <div class="card-body">
                                <h5 class="card-title">Expiring Soon</h5>
                                <h2 id="expiringItems"><?php echo count($expiring); ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-info">
                            <div class="card-body">
                                <h5 class="card-title">Categories</h5>
                                <h2 id="categoriesCount"><?php echo count($categories); ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#itemsTab" role="tab">
                                    <i class="fas fa-boxes"></i> Items
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#batchesTab" role="tab">
                                    <i class="fas fa-box"></i> Batches
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#lowStockTab" role="tab">
                                    <i class="fas fa-exclamation-triangle"></i> Low Stock
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#expiringTab" role="tab">
                                    <i class="fas fa-calendar-times"></i> Expiring Items
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="itemsTab" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover" id="itemsTable">
                                        <thead>
                                            <tr>
                                                <th>Item Code</th>
                                                <th>Item Name</th>
                                                <th>Category</th>
                                                <th>Total Quantity</th>
                                                <th>Unit</th>
                                                <th>Reorder Level</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($items as $item): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($item['item_code']); ?></td>
                                                <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                                                <td><?php echo htmlspecialchars($item['category']); ?></td>
                                                <td><?php echo htmlspecialchars($item['total_quantity']); ?></td>
                                                <td><?php echo htmlspecialchars($item['unit']); ?></td>
                                                <td><?php echo htmlspecialchars($item['reorder_level']); ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-success add-batch" data-id="<?php echo $item['id']; ?>" data-name="<?php echo htmlspecialchars($item['item_name']); ?>">
                                                        <i class="fas fa-plus"></i> Batch
                                                    </button>
                                                    <button class="btn btn-sm btn-warning issue-item" data-id="<?php echo $item['id']; ?>" data-name="<?php echo htmlspecialchars($item['item_name']); ?>">
                                                        <i class="fas fa-minus"></i> Issue
                                                    </button>
                                                    <button class="btn btn-sm btn-info edit-item" data-id="<?php echo $item['id']; ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger delete-item" data-id="<?php echo $item['id']; ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="batchesTab" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-striped" id="batchesTable">
                                        <thead>
                                            <tr>
                                                <th>Batch Number</th>
                                                <th>Item Name</th>
                                                <th>Quantity</th>
                                                <th>Manufacturing Date</th>
                                                <th>Expiry Date</th>
                                                <th>Supplier</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($batches as $batch): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($batch['batch_number']); ?></td>
                                                <td><?php echo htmlspecialchars($batch['item_name']); ?></td>
                                                <td><?php echo htmlspecialchars($batch['quantity']); ?></td>
                                                <td><?php echo htmlspecialchars($batch['manufacturing_date']); ?></td>
                                                <td><?php echo htmlspecialchars($batch['expiry_date']); ?></td>
                                                <td><?php echo htmlspecialchars($batch['supplier'] ?? 'N/A'); ?></td>
                                                <td>
                                                    <?php
                                                    $expiryDate = strtotime($batch['expiry_date']);
                                                    $daysUntilExpiry = floor(($expiryDate - time()) / (60 * 60 * 24));
                                                    if ($daysUntilExpiry < 0) {
                                                        echo '<span class="badge bg-danger">Expired</span>';
                                                    } elseif ($daysUntilExpiry < 30) {
                                                        echo '<span class="badge bg-warning">Expiring Soon</span>';
                                                    } else {
                                                        echo '<span class="badge bg-success">Active</span>';
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="lowStockTab" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-striped" id="lowStockTable">
                                        <thead>
                                            <tr>
                                                <th>Item Code</th>
                                                <th>Item Name</th>
                                                <th>Current Quantity</th>
                                                <th>Reorder Level</th>
                                                <th>Category</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($lowStock as $item): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($item['item_code']); ?></td>
                                                <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                                                <td><span class="badge bg-warning"><?php echo htmlspecialchars($item['total_quantity']); ?></span></td>
                                                <td><?php echo htmlspecialchars($item['reorder_level']); ?></td>
                                                <td><?php echo htmlspecialchars($item['category']); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="expiringTab" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-striped" id="expiringTable">
                                        <thead>
                                            <tr>
                                                <th>Batch Number</th>
                                                <th>Item Name</th>
                                                <th>Quantity</th>
                                                <th>Expiry Date</th>
                                                <th>Days Until Expiry</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($expiring as $batch): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($batch['batch_number']); ?></td>
                                                <td><?php echo htmlspecialchars($batch['item_name']); ?></td>
                                                <td><?php echo htmlspecialchars($batch['quantity']); ?></td>
                                                <td><?php echo htmlspecialchars($batch['expiry_date']); ?></td>
                                                <td>
                                                    <?php
                                                    $expiryDate = strtotime($batch['expiry_date']);
                                                    $daysUntilExpiry = floor(($expiryDate - time()) / (60 * 60 * 24));
                                                    $badgeClass = $daysUntilExpiry < 7 ? 'bg-danger' : 'bg-warning';
                                                    echo '<span class="badge ' . $badgeClass . '">' . $daysUntilExpiry . ' days</span>';
                                                    ?>
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

    <!-- Add Item Modal -->
    <div class="modal fade" id="addItemModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addItemForm">
                        <div class="mb-3">
                            <label class="form-label">Item Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="item_code" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Item Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="item_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select class="form-select" name="category_id" required>
                                <option value="">Select Category</option>
                                <option value="1">Medicines</option>
                                <option value="2">Surgical Supplies</option>
                                <option value="3">Laboratory Reagents</option>
                                <option value="4">Medical Equipment</option>
                                <option value="5">Disposables</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Unit <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="unit" placeholder="e.g., Tablets, Bottles" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Reorder Level <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="reorder_level" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveItemBtn">Save Item</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Batch Modal -->
    <div class="modal fade" id="addBatchModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Batch</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addBatchForm">
                        <input type="hidden" name="item_id" id="batch_item_id">
                        <div class="mb-3">
                            <label class="form-label">Item Name</label>
                            <input type="text" class="form-control" id="batch_item_name" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Batch Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="batch_number" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="quantity" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Manufacturing Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="manufacturing_date" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Expiry Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="expiry_date" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Supplier</label>
                            <input type="text" class="form-control" name="supplier">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Unit Cost</label>
                            <input type="number" class="form-control" name="unit_cost" step="0.01">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveBatchBtn">Save Batch</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Issue Item Modal -->
    <div class="modal fade" id="issueItemModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Issue Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="issueItemForm">
                        <input type="hidden" name="item_id" id="issue_item_id">
                        <div class="mb-3">
                            <label class="form-label">Item Name</label>
                            <input type="text" class="form-control" id="issue_item_name" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="quantity" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Issued To <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="issued_to" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Purpose <span class="text-danger">*</span></label>
                            <select class="form-select" name="purpose" required>
                                <option value="">Select Purpose</option>
                                <option value="Patient Treatment">Patient Treatment</option>
                                <option value="Surgery">Surgery</option>
                                <option value="Laboratory">Laboratory</option>
                                <option value="Department Use">Department Use</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" rows="2"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="issueBtn">Issue Item</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Item Modal -->
    <div class="modal fade" id="editItemModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editItemForm">
                        <input type="hidden" name="id" id="edit_item_id">
                        <div class="mb-3">
                            <label class="form-label">Item Code</label>
                            <input type="text" class="form-control" name="item_code" id="edit_item_code" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Item Name</label>
                            <input type="text" class="form-control" name="item_name" id="edit_item_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select class="form-select" name="category_id" id="edit_category" required>
                                <option value="Medicines">Medicines</option>
                                <option value="Surgical Supplies">Surgical Supplies</option>
                                <option value="Laboratory Reagents">Laboratory Reagents</option>
                                <option value="Medical Equipment">Medical Equipment</option>
                                <option value="Disposables">Disposables</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Unit</label>
                                <input type="text" class="form-control" name="unit" id="edit_unit" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Reorder Level</label>
                                <input type="number" class="form-control" name="reorder_level" id="edit_reorder_level" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="edit_description" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="updateItemBtn">Update Item</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/inventory.js"></script>
</body>
</html>
