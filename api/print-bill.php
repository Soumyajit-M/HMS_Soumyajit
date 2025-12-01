<?php
// Prevent caching
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: 0');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Billing.php';
require_once __DIR__ . '/../classes/Currency.php';

// Allow print page access without strict auth (for convenience)
// In production, you may want to add auth back or use a token-based approach

$billing = new Billing();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    echo '<h3>Bill ID missing</h3>';
    exit();
}

$bill = $billing->getBill($id);
if (!$bill) {
    echo '<h3>Bill not found</h3>';
    exit();
}
$items = $billing->getBillItems($id);

// Load currency settings
try {
  $db = new Database();
  $conn = $db->getConnection();
  $stmt = $conn->prepare("SELECT setting_key, setting_value FROM system_settings WHERE setting_key IN ('currency_default','currency_live_conversion')");
  $stmt->execute();
  $rows = $stmt->fetchAll();
  $settings = [];
  foreach ($rows as $r) { $settings[$r['setting_key']] = $r['setting_value']; }
  $defaultCurrency = strtoupper($settings['currency_default'] ?? 'INR');
  $liveConversion = ($settings['currency_live_conversion'] ?? '0') === '1';
} catch (Throwable $e) {
  $defaultCurrency = 'INR';
  $liveConversion = false;
}

$currency = new Currency('INR');
// Helpers
$fmt = function($amount) use ($currency, $defaultCurrency, $liveConversion) {
  $amt = (float)$amount;
  if ($liveConversion && strtoupper($defaultCurrency) !== 'INR') {
    $amt = $currency->convert($amt, 'INR', $defaultCurrency);
    return $currency->format($amt, $defaultCurrency);
  }
  return $currency->format($amt, $defaultCurrency);
};

// Basic printable HTML
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Print Bill #<?= htmlspecialchars($bill['bill_number']) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { padding: 20px; }
.header { border-bottom: 2px solid #333; margin-bottom: 20px; }
.table th, .table td { vertical-align: middle; }
@media print { .no-print { display:none; } }
</style>
</head>
<body>
<div class="header d-flex justify-content-between align-items-center">
  <h2>Invoice: <?= htmlspecialchars($bill['bill_number']) ?></h2>
  <button class="btn btn-secondary no-print" onclick="window.print()">Print</button>
</div>
<div class="mb-3">
  <strong>Patient:</strong> <?= htmlspecialchars($bill['patient_name'] ?? ($bill['patient_first_name'].' '.$bill['patient_last_name'] ?? '')) ?><br>
  <strong>Phone:</strong> <?= htmlspecialchars($bill['patient_phone'] ?? '') ?><br>
  <strong>Appointment:</strong> <?= htmlspecialchars($bill['appointment_id'] ?? 'N/A') ?> on <?= htmlspecialchars($bill['appointment_date'] ?? 'N/A') ?><br>
  <strong>Status:</strong> <?= htmlspecialchars($bill['payment_status'] ?: 'pending') ?><br>
  <strong>Due Date:</strong> <?= htmlspecialchars($bill['due_date'] ?: 'N/A') ?><br>
</div>
<table class="table table-bordered">
  <thead><tr><th>#</th><th>Item</th><th>Description</th><th class="text-end">Qty</th><th class="text-end">Unit Price</th><th class="text-end">Total</th></tr></thead>
  <tbody>
  <?php $sum = 0; foreach ($items as $idx => $it): $sum += (float)$it['total_price']; ?>
    <tr>
      <td><?= $idx+1 ?></td>
      <td><?= htmlspecialchars($it['item_name']) ?></td>
      <td><?= htmlspecialchars($it['description']) ?></td>
      <td class="text-end"><?= (int)$it['quantity'] ?></td>
      <td class="text-end"><?= $fmt($it['unit_price']) ?></td>
      <td class="text-end"><?= $fmt($it['total_price']) ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
  <tfoot>
    <tr>
      <th colspan="5" class="text-end">Subtotal</th>
      <th class="text-end"><?= $fmt($sum) ?></th>
    </tr>
    <tr>
      <th colspan="5" class="text-end">Paid</th>
      <th class="text-end"><?= $fmt((float)($bill['paid_amount'] ?: 0)) ?></th>
    </tr>
    <tr>
      <th colspan="5" class="text-end">Balance</th>
      <th class="text-end"><?= $fmt((float)($bill['balance_amount'] ?: ($sum - (float)($bill['paid_amount'] ?: 0)))) ?></th>
    </tr>
  </tfoot>
</table>
<div class="mt-3">
  <strong>Notes:</strong><br>
  <p><?= nl2br(htmlspecialchars($bill['notes'] ?? '')) ?></p>
</div>
</body>
</html>
