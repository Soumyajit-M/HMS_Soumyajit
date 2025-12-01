<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/auth_helper.php';

api_require_login();

$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['key'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing key']);
    exit;
}
$key = trim($input['key']);
$value = isset($input['value']) ? $input['value'] : '';

try {
    $db = new Database();
    $conn = $db->getConnection();
    $stmt = $conn->prepare(
        "INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) \n" .
        "ON CONFLICT(setting_key) DO UPDATE SET setting_value = excluded.setting_value, updated_at = CURRENT_TIMESTAMP"
    );
    $stmt->execute([$key, is_array($value) ? json_encode($value) : $value]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
