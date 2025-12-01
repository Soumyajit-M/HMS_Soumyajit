<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/auth_helper.php';
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../classes/Auth.php';
require_once '../classes/Dashboard.php';

$auth = api_require_login();

$dashboard = new Dashboard();

// Optional report params for reports page
$report = $_GET['report'] ?? null;
$start = $_GET['start'] ?? null;
$end = $_GET['end'] ?? null;

if ($report) {
    // Compose richer payload used by assets/js/reports.js
    $full = $dashboard->getDashboardData();
    // Keep naming expected by reports.js
    $data = [
        'patient_stats' => $full['patient_stats'] ?? null,
        'appointment_stats' => $full['appointment_stats'] ?? null,
        'billing_stats' => $full['billing_stats'] ?? null,
        'doctor_stats' => $full['doctor_stats'] ?? null,
        'revenue_chart' => $full['revenue_chart'] ?? [],
        'appointment_chart' => $full['appointment_chart'] ?? [],
        'monthly_stats' => $dashboard->getMonthlyStats(),
    ];

    echo json_encode(['success' => true] + $data);
    exit;
}

// Default lightweight dashboard counters
$stats = $dashboard->getDashboardStats();
echo json_encode(['success' => true, 'stats' => $stats]);
?>
