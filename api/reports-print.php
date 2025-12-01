<?php
// Printable HTML reports for PDF export via browser
session_start();
require_once __DIR__ . '/auth_helper.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Billing.php';
require_once __DIR__ . '/../classes/Dashboard.php';
require_once __DIR__ . '/../classes/Currency.php';

api_require_login();
$type = $_GET['type'] ?? 'dashboard';
$start = $_GET['start'] ?? null;
$end   = $_GET['end'] ?? null;
$doctorId = isset($_GET['doctor_id']) && $_GET['doctor_id'] !== '' ? $_GET['doctor_id'] : null;
$departmentId = isset($_GET['department_id']) && $_GET['department_id'] !== '' ? $_GET['department_id'] : null;

// Currency formatter from system settings
function get_currency_formatter() {
    try {
        $db = new Database();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("SELECT setting_key, setting_value FROM system_settings WHERE setting_key IN ('currency_default','currency_live_conversion')");
        $stmt->execute();
        $rows = $stmt->fetchAll();
        $settings = [];
        foreach ($rows as $r) { $settings[$r['setting_key']] = $r['setting_value']; }
        $default = strtoupper($settings['currency_default'] ?? 'INR');
        $live = ($settings['currency_live_conversion'] ?? '0') === '1';
        $currency = new Currency('INR');
        $fmt = function($amount) use ($currency, $default, $live) {
            $amt = (float)$amount;
            if ($live && $default !== 'INR') {
                $amt = $currency->convert($amt, 'INR', $default);
            }
            return $currency->format($amt, $default);
        };
        return [$fmt, $default, $live];
    } catch (Throwable $e) {
        $currency = new Currency('INR');
        $fmt = function($amount) use ($currency) { return $currency->format((float)$amount, 'INR'); };
        return [$fmt, 'INR', false];
    }
}

    [$fmt, $cur, $live] = get_currency_formatter();

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Report - <?= htmlspecialchars(ucfirst($type)) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  body { padding: 20px; }
  .report-header { border-bottom: 2px solid #333; margin-bottom: 20px; }
  .meta { color: #666; }
  @media print { .no-print { display:none; } }
</style>
</head>
<body>
<div class="report-header d-flex justify-content-between align-items-center">
  <div>
    <h3 class="mb-1"><?= htmlspecialchars(SITE_NAME ?? 'HMS') ?> - <?= htmlspecialchars(ucfirst($type)) ?> Report</h3>
    <div class="meta">
      Generated: <?= date('Y-m-d H:i') ?>
      <?php if (!empty($start) || !empty($end)): ?>
        | Range: <?= htmlspecialchars($start ?: '...') ?> to <?= htmlspecialchars($end ?: '...') ?>
      <?php endif; ?>
      | Currency: <?= htmlspecialchars($cur) ?><?= $live ? ' (Live)' : '' ?>
    </div>
  </div>
  <button class="btn btn-secondary no-print" onclick="window.print()">Print / Save PDF</button>
</div>
<?php
if ($type === 'billing') {
  // Build filtered bills list using date range if provided
  try {
    $db = new Database();
    $conn = $db->getConnection();
    $sql = "SELECT b.*, p.first_name || ' ' || p.last_name AS patient_name FROM billing b JOIN patients p ON b.patient_id = p.id WHERE 1=1";
    $params = [];
    if (!empty($start)) { $sql .= " AND date(b.created_at) >= :start"; $params[':start'] = $start; }
    if (!empty($end))   { $sql .= " AND date(b.created_at) <= :end";   $params[':end'] = $end; }
    // Optional filters via appointment relation
    if (!empty($doctorId)) { $sql .= " AND EXISTS (SELECT 1 FROM appointments a WHERE a.id = b.appointment_id AND a.doctor_id = :doctor_id)"; $params[':doctor_id'] = $doctorId; }
    if (!empty($departmentId)) { $sql .= " AND EXISTS (SELECT 1 FROM appointments a WHERE a.id = b.appointment_id AND a.department_id = :department_id)"; $params[':department_id'] = $departmentId; }
    $sql .= " ORDER BY b.created_at DESC";
    $stmt = $conn->prepare($sql);
    foreach ($params as $k=>$v) { $stmt->bindValue($k, $v); }
    $stmt->execute();
    $bills = $stmt->fetchAll();
  } catch (Throwable $e) {
    $billing = new Billing();
    $bills = $billing->getAllBills();
  }
    $totalRevenue = 0; $totalPaid = 0; $totalBalance = 0;
    foreach ($bills as $b) { $totalRevenue += (float)($b['total_amount']??0); $totalPaid += (float)($b['paid_amount']??0); $totalBalance += (float)($b['balance_amount']??0); }
    ?>
    <div class="mb-3">
      <strong>Summary:</strong>
      <div>
        Records: <?= count($bills) ?>
        | Total Revenue: <?= $fmt($totalRevenue) ?>
        | Paid: <?= $fmt($totalPaid) ?>
        | Balance: <?= $fmt($totalBalance) ?>
      </div>
    </div>
    <table class="table table-bordered table-sm">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Bill No</th>
          <th>Patient</th>
          <th>Date</th>
          <th class="text-end">Total</th>
          <th class="text-end">Paid</th>
          <th class="text-end">Balance</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($bills as $i=>$b): ?>
        <tr>
          <td><?= $i+1 ?></td>
          <td><?= htmlspecialchars($b['bill_number']) ?></td>
          <td><?= htmlspecialchars($b['patient_name'] ?? '') ?></td>
          <td><?= htmlspecialchars($b['created_at'] ?? '') ?></td>
          <td class="text-end"><?= $fmt((float)($b['total_amount']??0)) ?></td>
          <td class="text-end"><?= $fmt((float)($b['paid_amount']??0)) ?></td>
          <td class="text-end"><?= $fmt((float)($b['balance_amount']??0)) ?></td>
          <td><?= htmlspecialchars($b['payment_status'] ?? '') ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php
} elseif ($type === 'appointments') {
  // Appointments report with optional date filter
  try {
    $db = new Database();
    $conn = $db->getConnection();
        $sql = "SELECT a.id, a.patient_id, p.first_name || ' ' || p.last_name AS patient_name,
             a.doctor_id, uu.first_name || ' ' || uu.last_name AS doctor_name,
             a.appointment_date, a.status
           FROM appointments a
           LEFT JOIN patients p ON p.id = a.patient_id
           LEFT JOIN doctors d ON d.id = a.doctor_id
           LEFT JOIN users uu ON uu.id = d.user_id
           WHERE 1=1";
    $params = [];
    if (!empty($start)) { $sql .= " AND date(a.appointment_date) >= :start"; $params[':start'] = $start; }
    if (!empty($end))   { $sql .= " AND date(a.appointment_date) <= :end";   $params[':end'] = $end; }
    if (!empty($doctorId)) { $sql .= " AND a.doctor_id = :doctor_id"; $params[':doctor_id'] = $doctorId; }
    if (!empty($departmentId)) { $sql .= " AND a.department_id = :department_id"; $params[':department_id'] = $departmentId; }
    $sql .= " ORDER BY a.appointment_date DESC, a.id DESC";
    $stmt = $conn->prepare($sql);
    foreach ($params as $k=>$v) { $stmt->bindValue($k, $v); }
    $stmt->execute();
    $rows = $stmt->fetchAll();
  } catch (Throwable $e) {
    $rows = [];
  }
  ?>
  <div class="mb-3">
    <strong>Summary:</strong>
    <div>Records: <?= count($rows) ?></div>
  </div>
  <table class="table table-bordered table-sm">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Appointment ID</th>
        <th>Patient</th>
        <th>Doctor</th>
        <th>Date</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($rows as $i=>$r): ?>
      <tr>
        <td><?= $i+1 ?></td>
        <td><?= htmlspecialchars($r['id']) ?></td>
        <td><?= htmlspecialchars($r['patient_name'] ?: $r['patient_id']) ?></td>
        <td><?= htmlspecialchars($r['doctor_name'] ?: $r['doctor_id']) ?></td>
        <td><?= htmlspecialchars($r['appointment_date']) ?></td>
        <td><?= htmlspecialchars($r['status']) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php
} elseif ($type === 'patients') {
  // Patients report with optional created_at date filter
  try {
    $db = new Database();
    $conn = $db->getConnection();
    $sql = "SELECT p.id, p.first_name, p.last_name, p.gender, p.phone, p.email, p.created_at
            FROM patients p WHERE 1=1";
    $params = [];
    if (!empty($start)) { $sql .= " AND date(p.created_at) >= :start"; $params[':start'] = $start; }
    if (!empty($end))   { $sql .= " AND date(p.created_at) <= :end";   $params[':end'] = $end; }
    $sql .= " ORDER BY p.created_at DESC, p.id DESC";
    $stmt = $conn->prepare($sql);
    foreach ($params as $k=>$v) { $stmt->bindValue($k, $v); }
    $stmt->execute();
    $rows = $stmt->fetchAll();
  } catch (Throwable $e) {
    $rows = [];
  }
  ?>
  <div class="mb-3">
    <strong>Summary:</strong>
    <div>Records: <?= count($rows) ?></div>
  </div>
  <table class="table table-bordered table-sm">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Patient ID</th>
        <th>Name</th>
        <th>Gender</th>
        <th>Phone</th>
        <th>Email</th>
        <th>Created</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($rows as $i=>$r): ?>
      <tr>
        <td><?= $i+1 ?></td>
        <td><?= htmlspecialchars($r['id']) ?></td>
        <td><?= htmlspecialchars(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? '')) ?></td>
        <td><?= htmlspecialchars($r['gender'] ?? '') ?></td>
        <td><?= htmlspecialchars($r['phone'] ?? '') ?></td>
        <td><?= htmlspecialchars($r['email'] ?? '') ?></td>
        <td><?= htmlspecialchars($r['created_at'] ?? '') ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php
    $dashboard = new Dashboard();
    $data = $dashboard->getDashboardData();
    ?>
    <div class="row g-3">
      <div class="col-md-3"><div class="card"><div class="card-body"><div class="small text-muted">Total Patients</div><div class="h5 mb-0"><?= (int)($data['patient_stats']['total_patients'] ?? 0) ?></div></div></div></div>
      <div class="col-md-3"><div class="card"><div class="card-body"><div class="small text-muted">Total Revenue</div><div class="h5 mb-0"><?= $fmt((float)($data['billing_stats']['total_revenue'] ?? 0)) ?></div></div></div></div>
      <div class="col-md-3"><div class="card"><div class="card-body"><div class="small text-muted">Paid Amount</div><div class="h5 mb-0"><?= $fmt((float)($data['billing_stats']['total_paid'] ?? 0)) ?></div></div></div></div>
      <div class="col-md-3"><div class="card"><div class="card-body"><div class="small text-muted">Pending Bills</div><div class="h5 mb-0"><?= (int)($data['billing_stats']['pending_bills'] ?? 0) ?></div></div></div></div>
    </div>
    <p class="mt-3 text-muted">Billing report provides detailed transactions. Use type=billing to export full list.</p>
    <?php
}
?>
</body>
</html>
