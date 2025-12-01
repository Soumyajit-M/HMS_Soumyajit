<?php
session_start();
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../classes/Auth.php';
require_once '../classes/Dashboard.php';
require_once '../classes/Patient.php';
require_once '../classes/Doctor.php';
require_once '../classes/Appointment.php';
require_once '../classes/Billing.php';
require_once '../classes/Currency.php';
require_once __DIR__ . '/auth_helper.php';

$auth = api_require_login();

// api_require_login already enforced session; remove redundant block

$type = $_GET['type'] ?? 'dashboard';
$start = $_GET['start'] ?? null;
$end = $_GET['end'] ?? null;
$doctorId = isset($_GET['doctor_id']) && $_GET['doctor_id'] !== '' ? $_GET['doctor_id'] : null;
$departmentId = isset($_GET['department_id']) && $_GET['department_id'] !== '' ? $_GET['department_id'] : null;

// Set CSV headers
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="export_' . $type . '_' . date('Y-m-d') . '.csv"');

// Create output stream
$output = fopen('php://output', 'w');

try {
    switch ($type) {
        case 'dashboard':
            exportDashboardStats($output);
            break;
        case 'patients':
            exportPatients($output, $start, $end);
            break;
        case 'appointments':
            exportAppointments($output, $start, $end, $doctorId, $departmentId);
            break;
        case 'billing':
            exportBilling($output, $start, $end, $doctorId, $departmentId);
            break;
        default:
            exportDashboardStats($output);
    }
} catch (Exception $e) {
    fputcsv($output, ['Error: ' . $e->getMessage()]);
}

fclose($output);

function exportDashboardStats($output) {
    $dashboard = new Dashboard();
    $stats = $dashboard->getStats();
    [$fmt, $symbol] = currency_formatter();
    
    // Add header
    fputcsv($output, ['Dashboard Statistics - ' . date('Y-m-d H:i:s')]);
    fputcsv($output, []);
    
    // Add stats
    fputcsv($output, ['Metric', 'Value']);
    fputcsv($output, ['Total Patients', $stats['total_patients'] ?? 0]);
    fputcsv($output, ['Today\'s Appointments', $stats['today_appointments'] ?? 0]);
    fputcsv($output, ['Pending Bills', $stats['pending_bills'] ?? 0]);
    fputcsv($output, ['Emergency Cases', $stats['emergency_cases'] ?? 0]);
    $totalRevenue = (float)($stats['total_revenue'] ?? 0);
    fputcsv($output, ['Total Revenue', $fmt($totalRevenue)]);
    fputcsv($output, ['Active Doctors', $stats['active_doctors'] ?? 0]);
}

function exportPatients($output, $start = null, $end = null) {
    // Apply optional created_at date range
    try {
        $db = new Database();
        $conn = $db->getConnection();
        $sql = "SELECT p.id, p.first_name, p.last_name, p.email, p.phone, p.date_of_birth, p.gender, p.blood_group, p.address, p.created_at FROM patients p WHERE 1=1";
        $params = [];
        if (!empty($start)) { $sql .= " AND date(p.created_at) >= :start"; $params[':start'] = $start; }
        if (!empty($end))   { $sql .= " AND date(p.created_at) <= :end";   $params[':end'] = $end; }
        $sql .= " ORDER BY p.created_at DESC, p.id DESC";
        $stmt = $conn->prepare($sql);
        foreach ($params as $k=>$v) { $stmt->bindValue($k, $v); }
        $stmt->execute();
        $patients = $stmt->fetchAll();
    } catch (Throwable $e) {
        $patient = new Patient();
        $patients = $patient->getAllPatients();
    }
    
    // Add header
    fputcsv($output, ['ID', 'Name', 'Email', 'Phone', 'Date of Birth', 'Gender', 'Blood Group', 'Address', 'Created']);
    
    // Add patient data
    if (!empty($patients)) {
        foreach ($patients as $p) {
            $fullName = ($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? '');
            fputcsv($output, [
                $p['id'],
                $fullName,
                $p['email'] ?? '',
                $p['phone'] ?? '',
                $p['date_of_birth'] ?? '',
                $p['gender'] ?? '',
                $p['blood_group'] ?? '',
                $p['address'] ?? '',
                $p['created_at'] ?? ''
            ]);
        }
    }
}

function exportAppointments($output, $start = null, $end = null, $doctorId = null, $departmentId = null) {
    // Apply optional appointment_date range
    try {
        $db = new Database();
        $conn = $db->getConnection();
        $sql = "SELECT a.id, a.appointment_date, a.appointment_time, a.appointment_type, a.status, a.reason,
                   p.first_name || ' ' || p.last_name AS patient_name,
                   uu.first_name || ' ' || uu.last_name AS doctor_name
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
        $appointments = $stmt->fetchAll();
    } catch (Throwable $e) {
        $appointment = new Appointment();
        $appointments = $appointment->getAllAppointments();
    }
    
    // Add header
    fputcsv($output, ['ID', 'Patient', 'Doctor', 'Date', 'Time', 'Type', 'Status', 'Reason']);
    
    // Add appointment data
    if (!empty($appointments)) {
        foreach ($appointments as $a) {
            fputcsv($output, [
                $a['id'],
                $a['patient_name'] ?? '',
                $a['doctor_name'] ?? '',
                $a['appointment_date'] ?? '',
                $a['appointment_time'] ?? '',
                $a['appointment_type'] ?? '',
                $a['status'] ?? '',
                $a['reason'] ?? ''
            ]);
        }
    }
}

function exportBilling($output, $start = null, $end = null, $doctorId = null, $departmentId = null) {
    $billing = new Billing();
    // Apply optional date range
    $bills = [];
    if ($start || $end) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            $sql = "SELECT b.*, p.first_name || ' ' || p.last_name AS patient_name FROM billing b JOIN patients p ON b.patient_id = p.id WHERE 1=1";
            $params = [];
            if (!empty($start)) { $sql .= " AND date(b.created_at) >= :start"; $params[':start'] = $start; }
            if (!empty($end))   { $sql .= " AND date(b.created_at) <= :end";   $params[':end'] = $end; }
            if (!empty($doctorId)) { $sql .= " AND EXISTS (SELECT 1 FROM appointments a WHERE a.id = b.appointment_id AND a.doctor_id = :doctor_id)"; $params[':doctor_id'] = $doctorId; }
            if (!empty($departmentId)) { $sql .= " AND EXISTS (SELECT 1 FROM appointments a WHERE a.id = b.appointment_id AND a.department_id = :department_id)"; $params[':department_id'] = $departmentId; }
            $sql .= " ORDER BY b.created_at DESC";
            $stmt = $conn->prepare($sql);
            foreach ($params as $k=>$v) { $stmt->bindValue($k, $v); }
            $stmt->execute();
            $bills = $stmt->fetchAll();
        } catch (Throwable $e) {
            $bills = $billing->getAllBills();
        }
    } else {
        $bills = $billing->getAllBills();
    }
    [$fmt, $symbol] = currency_formatter();
    
    // Add header
    fputcsv($output, ['ID', 'Patient', 'Date', 'Total Amount', 'Paid Amount', 'Balance', 'Payment Status']);
    
    // Add billing data
    if (!empty($bills)) {
        foreach ($bills as $b) {
            $total = (float)($b['total_amount'] ?? 0);
            $paid = (float)($b['paid_amount'] ?? 0);
            $bal  = (float)($b['balance_amount'] ?? 0);
            fputcsv($output, [
                $b['id'],
                $b['patient_name'] ?? '',
                $b['created_at'] ?? '',
                $fmt($total),
                $fmt($paid),
                $fmt($bal),
                $b['payment_status'] ?? ''
            ]);
        }
    }
}

// Helper: build currency formatter based on system settings
function currency_formatter() {
    try {
        $db = new Database();
        $conn = $db->getConnection();
        $rows = $conn->query("SELECT setting_key, setting_value FROM system_settings WHERE setting_key IN ('currency_default','currency_live_conversion')")->fetchAll();
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
        return [$fmt, $default];
    } catch (Throwable $e) {
        $currency = new Currency('INR');
        $fmt = function($amount) use ($currency) { return $currency->format((float)$amount, 'INR'); };
        return [$fmt, 'INR'];
    }
}
