<?php
// Simple departments API: list departments
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header_remove('WWW-Authenticate');

require_once __DIR__ . '/auth_helper.php';
require_once __DIR__ . '/../config/database.php';

api_require_login();

try {
    $db = new Database();
    $conn = $db->getConnection();
    $stmt = $conn->query("SELECT id, name FROM departments ORDER BY name ASC");
    $rows = $stmt->fetchAll();
    echo json_encode(['success' => true, 'departments' => $rows]);
} catch (Throwable $e) {
    http_response_code(200);
    echo json_encode(['success' => false, 'message' => 'Failed to load departments']);
}
?>
