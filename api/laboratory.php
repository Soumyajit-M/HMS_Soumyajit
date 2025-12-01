<?php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/auth_helper.php';
require_once __DIR__ . '/../classes/Laboratory.php';

$auth = api_require_login();
$method = $_SERVER['REQUEST_METHOD'];

$laboratory = new Laboratory();

switch ($method) {
    case 'GET':
        if (isset($_GET['action'])) {
            switch ($_GET['action']) {
                case 'test_types':
                    $testTypes = $laboratory->getAllTestTypes();
                    echo json_encode(['success' => true, 'test_types' => $testTypes]);
                    break;
                case 'order_tests':
                    if (isset($_GET['order_id'])) {
                        $tests = $laboratory->getOrderTests($_GET['order_id']);
                        echo json_encode(['success' => true, 'tests' => $tests]);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Order ID required']);
                    }
                    break;
                default:
                    echo json_encode(['success' => false, 'message' => 'Invalid action']);
            }
        } elseif (isset($_GET['id'])) {
            $order = $laboratory->getOrderById($_GET['id']);
            if ($order) {
                echo json_encode(['success' => true, 'order' => $order]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Order not found']);
            }
        } else {
            $orders = $laboratory->getAllOrders();
            echo json_encode(['success' => true, 'orders' => $orders]);
        }
        break;

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
            exit();
        }

        if (isset($input['action']) && $input['action'] === 'test_type' && $auth->hasRole('admin')) {
            $result = $laboratory->createTestType($input);
        } else {
            $result = $laboratory->createOrder($input);
        }
        echo json_encode($result);
        break;

    case 'PUT':
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit();
        }

        if (isset($input['action'])) {
            switch ($input['action']) {
                case 'update_result':
                    if (isset($input['test_id']) && isset($input['result'])) {
                        $result = $laboratory->updateTestResult($input['test_id'], $input['result'], $input['remarks'] ?? null);
                        echo json_encode($result);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Test ID and result required']);
                    }
                    break;
                case 'update_status':
                    if (isset($input['order_id']) && isset($input['status'])) {
                        $result = $laboratory->setOrderStatus($input['order_id'], $input['status']);
                        echo json_encode($result);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Order ID and status required']);
                    }
                    break;
                default:
                    echo json_encode(['success' => false, 'message' => 'Invalid action']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Action required']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}
