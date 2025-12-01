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

// Get notifications
$notifications = $dashboard->getNotifications();

echo json_encode([
    'success' => true,
    'notifications' => $notifications
]);
?>
