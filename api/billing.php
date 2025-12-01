<?php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../classes/Billing.php';
require_once __DIR__ . '/auth_helper.php';

 $auth = api_require_login();

// Quiet session check (no 403 to avoid auth dialog)
 // No need for explicit session check, api_require_login() handles it.

$billing = new Billing();

// Handle different HTTP methods
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            $billData = $billing->getBillById($id);
            if ($billData) {
                // Normalize defaults
                if ($billData['paid_amount'] === '' || $billData['paid_amount'] === null) $billData['paid_amount'] = 0;
                if ($billData['balance_amount'] === '' || $billData['balance_amount'] === null) $billData['balance_amount'] = 0;
                if ($billData['payment_status'] === '' || $billData['payment_status'] === null) $billData['payment_status'] = 'pending';
                $items = $billing->getBillItems($id);
                echo json_encode(['success' => true, 'bill' => $billData, 'items' => $items]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Bill not found']);
            }
        } else {
            $bills = $billing->getAllBills();
            echo json_encode(['success' => true, 'bills' => $bills]);
        }
        break;
        
    case 'POST':
        // Create new bill
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
            break;
        }
        
        $totalAmount = floatval($input['total_amount'] ?? 0);
        $paidAmount = floatval($input['paid_amount'] ?? 0);
        
        $data = [
            'patient_id' => $input['patient_id'] ?? '',
            'appointment_id' => $input['appointment_id'] ?? '',
            'due_date' => $input['due_date'] ?? '',
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'balance_amount' => $totalAmount - $paidAmount,
            'payment_status' => $paidAmount >= $totalAmount ? 'paid' : ($paidAmount > 0 ? 'partial' : 'pending'),
            'notes' => $input['notes'] ?? '',
            'items' => $input['items'] ?? []
        ];

        $result = $billing->createBill($data);
        echo json_encode($result);
        break;
        
    case 'PUT':
        // Update bill
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
            break;
        }
        $id = $input['id'] ?? null;

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Bill ID required']);
            break;
        }
        
        $data = [
            'patient_id' => $input['patient_id'] ?? '',
            'appointment_id' => $input['appointment_id'] ?? '',
            'due_date' => $input['due_date'] ?? '',
            'total_amount' => $input['total_amount'] ?? 0,
            'notes' => $input['notes'] ?? ''
        ];
        
        $result = $billing->updateBill($id, $data);
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Bill updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating bill']);
        }
        break;
        
    case 'DELETE':
        // Delete bill
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? null;
        
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Bill ID required']);
            break;
        }
        
        $result = $billing->deleteBill($id);
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Bill deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error deleting bill']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}
?>
