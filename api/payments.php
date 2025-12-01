<?php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/auth_helper.php';
require_once __DIR__ . '/../classes/Billing.php';

$auth = api_require_login();

$billing = new Billing();

// Handle different HTTP methods
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['billing_id'])) {
            // Get payments for a specific bill
            $payments = $billing->getPaymentsByBillId($_GET['billing_id']);
            echo json_encode(['success' => true, 'payments' => $payments]);
        } else {
            // Get all payments
            $payments = $billing->getAllPayments();
            echo json_encode(['success' => true, 'payments' => $payments]);
        }
        break;
        
    case 'POST':
        // Create new payment
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
            break;
        }

        $data = [
            'billing_id' => $input['billing_id'] ?? '',
            'amount' => $input['amount'] ?? 0,
            'payment_method' => $input['payment_method'] ?? 'cash',
            'transaction_id' => $input['transaction_id'] ?? '',
            'notes' => $input['notes'] ?? ''
        ];

        // Use Billing::recordPayment which returns an array on success
        $result = $billing->recordPayment($data);
        if (is_array($result) && isset($result['success']) && $result['success']) {
            echo json_encode(['success' => true, 'message' => $result['message'] ?? 'Payment added successfully', 'payment_id' => $result['payment_id'] ?? null]);
        } elseif ($result) {
            echo json_encode(['success' => true, 'message' => 'Payment added successfully', 'payment_id' => $result]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error adding payment']);
        }
        break;
        
    case 'PUT':
        // Update payment
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
            break;
        }
        $id = $input['id'] ?? null;

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Payment ID required']);
            break;
        }
        
        $data = [
            'amount' => $input['amount'] ?? 0,
            'payment_method' => $input['payment_method'] ?? 'cash',
            'transaction_id' => $input['transaction_id'] ?? '',
            'notes' => $input['notes'] ?? ''
        ];
        
        $result = $billing->updatePayment($id, $data);
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Payment updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating payment']);
        }
        break;
        
    case 'DELETE':
        // Delete payment
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? null;
        
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Payment ID required']);
            break;
        }
        
        $result = $billing->deletePayment($id);
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Payment deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error deleting payment']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}
?>
