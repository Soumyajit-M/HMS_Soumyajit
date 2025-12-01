<?php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/auth_helper.php';
require_once __DIR__ . '/../classes/AutoBilling.php';

$auth = api_require_login();

$method = $_SERVER['REQUEST_METHOD'];
$autoBilling = new AutoBilling();

switch ($method) {
    case 'GET':
        if (isset($_GET['action'])) {
            switch ($_GET['action']) {
                case 'bill':
                    if (isset($_GET['bill_id'])) {
                        $result = $autoBilling->getDetailedBill($_GET['bill_id']);
                        echo json_encode($result);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Bill ID required']);
                    }
                    break;
                    
                case 'calculate_bed':
                    if (isset($_GET['bill_id'])) {
                        $result = $autoBilling->calculateBedCharges($_GET['bill_id']);
                        echo json_encode($result);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Bill ID required']);
                    }
                    break;
                    
                default:
                    echo json_encode(['success' => false, 'message' => 'Invalid action']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Action required']);
        }
        break;

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
            exit();
        }

        if (isset($input['action'])) {
            switch ($input['action']) {
                case 'admission':
                    $result = $autoBilling->trackAdmission(
                        $input['patient_id'],
                        $input['room_id'],
                        $input['admission_date'] ?? date('Y-m-d H:i:s')
                    );
                    break;
                    
                case 'lab_test':
                    $result = $autoBilling->trackLabTest(
                        $input['patient_id'],
                        $input['test_id'],
                        $input['order_id'] ?? null
                    );
                    break;
                    
                case 'consultation':
                    $result = $autoBilling->trackConsultation(
                        $input['patient_id'],
                        $input['doctor_id'],
                        $input['appointment_id'] ?? null
                    );
                    break;
                    
                case 'medicine':
                    $result = $autoBilling->trackMedicine(
                        $input['patient_id'],
                        $input['item_id'],
                        $input['quantity']
                    );
                    break;
                    
                case 'finalize':
                    if (isset($input['bill_id'])) {
                        $result = $autoBilling->finalizeBill($input['bill_id']);
                    } else {
                        $result = ['success' => false, 'message' => 'Bill ID required'];
                    }
                    break;
                    
                default:
                    $result = ['success' => false, 'message' => 'Invalid action'];
            }
        } else {
            $result = ['success' => false, 'message' => 'Action required'];
        }
        
        echo json_encode($result);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}
