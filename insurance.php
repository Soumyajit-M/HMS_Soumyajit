<?php
session_start();
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/Insurance.php';
require_once 'classes/Patient.php';

$auth = new Auth();

if (!$auth->isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$insurance = new Insurance();
$providers = $insurance->getAllProviders();
$policies = [];
$claims = $insurance->getAllClaims();
$patient = new Patient();
$patients = $patient->getAllPatients();

$activePolicies = count(array_filter($policies, fn($p) => $p['status'] == 'Active'));
$pendingClaims = count(array_filter($claims, fn($c) => $c['status'] == 'Pending'));
$approvedClaims = count(array_filter($claims, fn($c) => $c['status'] == 'Approved'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Insurance Management</title>
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
                            <a class="nav-link active" href="insurance.php">
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
                    <h1 class="h2"><i class="fas fa-shield-alt"></i> Insurance Management</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProviderModal">
                            <i class="fas fa-plus"></i> Add Provider
                        </button>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3">
                        <div class="card text-white bg-primary">
                            <div class="card-body">
                                <h5 class="card-title">Total Providers</h5>
                                <h2 id="totalProviders"><?php echo count($providers); ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-success">
                            <div class="card-body">
                                <h5 class="card-title">Active Policies</h5>
                                <h2 id="activePolicies"><?php echo $activePolicies; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-warning">
                            <div class="card-body">
                                <h5 class="card-title">Pending Claims</h5>
                                <h2 id="pendingClaims"><?php echo $pendingClaims; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-info">
                            <div class="card-body">
                                <h5 class="card-title">Approved Claims</h5>
                                <h2 id="approvedClaims"><?php echo $approvedClaims; ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#providersTab" role="tab">
                                    <i class="fas fa-building"></i> Providers
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#policiesTab" role="tab">
                                    <i class="fas fa-file-contract"></i> Patient Insurance
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#claimsTab" role="tab">
                                    <i class="fas fa-clipboard-list"></i> Claims
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="providersTab" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover" id="providersTable">
                                        <thead>
                                            <tr>
                                                <th>Provider Name</th>
                                                <th>Contact Person</th>
                                                <th>Email</th>
                                                <th>Phone</th>
                                                <th>Address</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($providers as $provider): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($provider['provider_name']); ?></td>
                                                <td><?php echo htmlspecialchars($provider['contact_person'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($provider['email'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($provider['phone'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($provider['address'] ?? 'N/A'); ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-info edit-provider" data-id="<?php echo $provider['id']; ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger delete-provider" data-id="<?php echo $provider['id']; ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="policiesTab" role="tabpanel">
                                <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addPolicyModal">
                                    <i class="fas fa-plus"></i> Add Patient Insurance
                                </button>
                                <div class="table-responsive">
                                    <table class="table table-striped" id="policiesTable">
                                        <thead>
                                            <tr>
                                                <th>Patient</th>
                                                <th>Provider</th>
                                                <th>Policy Number</th>
                                                <th>Coverage Amount</th>
                                                <th>Start Date</th>
                                                <th>End Date</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($policies as $policy): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($policy['patient_name']); ?></td>
                                                <td><?php echo htmlspecialchars($policy['provider_name']); ?></td>
                                                <td><?php echo htmlspecialchars($policy['policy_number']); ?></td>
                                                <td>$<?php echo htmlspecialchars(number_format($policy['coverage_amount'], 2)); ?></td>
                                                <td><?php echo htmlspecialchars($policy['start_date']); ?></td>
                                                <td><?php echo htmlspecialchars($policy['end_date']); ?></td>
                                                <td>
                                                    <?php if ($policy['status'] == 'Active'): ?>
                                                        <span class="badge bg-success">Active</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Inactive</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-success create-claim" data-id="<?php echo $policy['id']; ?>" data-patient="<?php echo htmlspecialchars($policy['patient_name']); ?>">
                                                        <i class="fas fa-plus"></i> Claim
                                                    </button>
                                                    <button class="btn btn-sm btn-danger delete-policy" data-id="<?php echo $policy['id']; ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="claimsTab" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-striped" id="claimsTable">
                                        <thead>
                                            <tr>
                                                <th>Claim Number</th>
                                                <th>Patient</th>
                                                <th>Provider</th>
                                                <th>Claim Amount</th>
                                                <th>Approved Amount</th>
                                                <th>Claim Date</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($claims as $claim): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($claim['claim_number']); ?></td>
                                                <td><?php echo htmlspecialchars($claim['patient_name']); ?></td>
                                                <td><?php echo htmlspecialchars($claim['provider_name']); ?></td>
                                                <td>$<?php echo htmlspecialchars(number_format($claim['claim_amount'], 2)); ?></td>
                                                <td>$<?php echo htmlspecialchars(number_format($claim['approved_amount'] ?? 0, 2)); ?></td>
                                                <td><?php echo htmlspecialchars($claim['claim_date']); ?></td>
                                                <td>
                                                    <?php
                                                    $statusClass = 'secondary';
                                                    if ($claim['status'] == 'Approved') $statusClass = 'success';
                                                    elseif ($claim['status'] == 'Pending') $statusClass = 'warning';
                                                    elseif ($claim['status'] == 'Rejected') $statusClass = 'danger';
                                                    ?>
                                                    <span class="badge bg-<?php echo $statusClass; ?>"><?php echo htmlspecialchars($claim['status']); ?></span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-info update-claim-status" data-id="<?php echo $claim['id']; ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger delete-claim" data-id="<?php echo $claim['id']; ?>">
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

    <!-- Add Provider Modal -->
    <div class="modal fade" id="addProviderModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Insurance Provider</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addProviderForm">
                        <div class="mb-3">
                            <label class="form-label">Provider Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="provider_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contact Person</label>
                            <input type="text" class="form-control" name="contact_person">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="tel" class="form-control" name="phone">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveProviderBtn">Save Provider</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Policy Modal -->
    <div class="modal fade" id="addPolicyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Patient Insurance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addPolicyForm">
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
                            <label class="form-label">Insurance Provider <span class="text-danger">*</span></label>
                            <select class="form-select" name="provider_id" required>
                                <option value="">Select Provider</option>
                                <?php foreach ($providers as $provider): ?>
                                <option value="<?php echo $provider['id']; ?>"><?php echo htmlspecialchars($provider['provider_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Policy Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="policy_number" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Coverage Amount <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="coverage_amount" step="0.01" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Start Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="start_date" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">End Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="end_date" required>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="savePolicyBtn">Save Policy</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Claim Modal -->
    <div class="modal fade" id="createClaimModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Insurance Claim</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="createClaimForm">
                        <input type="hidden" name="insurance_id" id="claim_insurance_id">
                        <div class="mb-3">
                            <label class="form-label">Patient</label>
                            <input type="text" class="form-control" id="claim_patient_name" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Claim Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="claim_number" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Claim Amount <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="claim_amount" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Claim Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="claim_date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Diagnosis</label>
                            <textarea class="form-control" name="diagnosis" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Treatment Details</label>
                            <textarea class="form-control" name="treatment_details" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveClaimBtn">Submit Claim</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Claim Status Modal -->
    <div class="modal fade" id="updateClaimStatusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Claim Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="updateClaimStatusForm">
                        <input type="hidden" name="id" id="update_claim_id">
                        <div class="mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" name="status" required>
                                <option value="Pending">Pending</option>
                                <option value="Approved">Approved</option>
                                <option value="Rejected">Rejected</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Approved Amount</label>
                            <input type="number" class="form-control" name="approved_amount" step="0.01">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Remarks</label>
                            <textarea class="form-control" name="remarks" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="updateClaimStatusBtn">Update Status</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/insurance.js"></script>
</body>
</html>
