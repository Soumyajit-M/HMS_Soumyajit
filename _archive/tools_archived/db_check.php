<?php
// Simple DB schema checker for this project
$sqliteFile = __DIR__ . '/../database/hms_database.sqlite';
if (!file_exists($sqliteFile)) {
    echo "ERROR: SQLite file not found: $sqliteFile\n";
    exit(2);
}
try {
    $pdo = new PDO('sqlite:' . $sqliteFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    echo "ERROR: Could not open SQLite DB: " . $e->getMessage() . "\n";
    exit(3);
}
$required = [
    'users' => ['id','username','password','email','role','is_active','created_at'],
    'patients' => ['id','patient_id','first_name','last_name','email','phone','date_of_birth','created_at'],
    'doctors' => ['id','doctor_id','user_id','specialization','created_at'],
    'appointments' => ['id','appointment_id','patient_id','doctor_id','department_id','appointment_date','appointment_time','status','created_at'],
    'billing' => ['id','bill_number','patient_id','appointment_id','total_amount','paid_amount','balance_amount','payment_status','created_at','due_date'],
    'billing_items' => ['id','billing_id','item_name','quantity','unit_price','total_price'],
    'payments' => ['id','payment_id','billing_id','amount','payment_method','transaction_id','created_at'],
    'departments' => ['id','name'],
    'ai_assistant_logs' => ['id','user_id','query','response','context','created_at']
];

$missingTables = [];
$missingColumns = [];
foreach ($required as $table => $cols) {
    $stmt = $pdo->prepare("SELECT name FROM sqlite_master WHERE type='table' AND name = :t");
    $stmt->execute([':t' => $table]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        $missingTables[] = $table;
        continue;
    }
    $stmt = $pdo->prepare("PRAGMA table_info('" . $table . "')");
    $stmt->execute();
    $existing = $stmt->fetchAll(PDO::FETCH_COLUMN, 1);
    foreach ($cols as $c) {
        if (!in_array($c, $existing)) {
            $missingColumns[$table][] = $c;
        }
    }
}

if (empty($missingTables) && empty($missingColumns)) {
    echo "DB CHECK: OK — all required tables and columns exist.\n";
    exit(0);
}

if (!empty($missingTables)) {
    echo "Missing tables:\n";
    foreach ($missingTables as $t) echo " - $t\n";
}
if (!empty($missingColumns)) {
    echo "Missing columns:\n";
    foreach ($missingColumns as $t => $cols) {
        echo " - $t: " . implode(', ', $cols) . "\n";
    }
}

exit(1);
