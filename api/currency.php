<?php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/auth_helper.php';
require_once __DIR__ . '/../classes/Currency.php';

$auth = api_require_login();

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

try {
    // Default base currency: from settings if available, else INR
    $base = isset($_GET['base']) ? strtoupper($_GET['base']) : null;
    if (!$base) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            $stmt = $conn->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'currency_default'");
            $stmt->execute();
            $row = $stmt->fetch();
            $base = strtoupper($row['setting_value'] ?? 'INR');
        } catch (Throwable $e) {
            $base = 'INR';
        }
    }
    $currency = new Currency($base);

    if ($method === 'GET') {
        $action = $_GET['action'] ?? 'format';
        if ($action === 'format') {
            $amount = (float)($_GET['amount'] ?? 0);
            $cur = strtoupper($_GET['currency'] ?? $base);
            echo json_encode(['success' => true, 'formatted' => $currency->format($amount, $cur)]);
            exit;
        } else if ($action === 'convert') {
            $amount = (float)($_GET['amount'] ?? 0);
            $from = strtoupper($_GET['from'] ?? $base);
            $to = strtoupper($_GET['to'] ?? $base);
            $converted = $currency->convert($amount, $from, $to);
            echo json_encode([
                'success' => true,
                'amount' => $amount,
                'from' => $from,
                'to' => $to,
                'converted' => $converted,
                'formatted' => $currency->format($converted, $to)
            ]);
            exit;
        }
        echo json_encode(['success' => false, 'message' => 'Unknown action']);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Unsupported method']);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
