<?php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/auth_helper.php';
require_once __DIR__ . '/../classes/Inventory.php';

$auth = api_require_login();
$method = $_SERVER['REQUEST_METHOD'];

$inventory = new Inventory();

switch ($method) {
    case 'GET':
        if (isset($_GET['action'])) {
            switch ($_GET['action']) {
                case 'categories':
                    $categories = $inventory->getAllCategories();
                    echo json_encode(['success' => true, 'categories' => $categories]);
                    break;
                case 'batches':
                    if (isset($_GET['item_id'])) {
                        $batches = $inventory->getBatchesByItem($_GET['item_id']);
                        echo json_encode(['success' => true, 'batches' => $batches]);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Item ID required']);
                    }
                    break;
                case 'transactions':
                    if (isset($_GET['item_id'])) {
                        $transactions = $inventory->getTransactionsByItem($_GET['item_id']);
                        echo json_encode(['success' => true, 'transactions' => $transactions]);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Item ID required']);
                    }
                    break;
                case 'low_stock':
                    $items = $inventory->getLowStockItems();
                    echo json_encode(['success' => true, 'items' => $items]);
                    break;
                case 'expiring':
                    $days = $_GET['days'] ?? 30;
                    $items = $inventory->getExpiringItems($days);
                    echo json_encode(['success' => true, 'items' => $items]);
                    break;
                default:
                    echo json_encode(['success' => false, 'message' => 'Invalid action']);
            }
        } elseif (isset($_GET['id'])) {
            $item = $inventory->getItemById($_GET['id']);
            if ($item) {
                echo json_encode(['success' => true, 'item' => $item]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Item not found']);
            }
        } else {
            $items = $inventory->getAllItems();
            echo json_encode(['success' => true, 'items' => $items]);
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
                case 'batch':
                    if ($auth->hasRole('admin')) {
                        $result = $inventory->createBatch($input);
                    } else {
                        $result = ['success' => false, 'message' => 'Admin privileges required'];
                    }
                    break;
                case 'issue':
                    $result = $inventory->issueItem($input['item_id'], $input['quantity'], $input);
                    break;
                default:
                    if ($auth->hasRole('admin')) {
                        $result = $inventory->createItem($input);
                    } else {
                        $result = ['success' => false, 'message' => 'Admin privileges required'];
                    }
            }
        } else {
            if ($auth->hasRole('admin')) {
                $result = $inventory->createItem($input);
            } else {
                $result = ['success' => false, 'message' => 'Admin privileges required'];
            }
        }
        echo json_encode($result);
        break;

    case 'PUT':
        if (!$auth->hasRole('admin')) {
            echo json_encode(['success' => false, 'message' => 'Admin privileges required']);
            exit();
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || !isset($input['id'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit();
        }

        $result = $inventory->updateItem($input['id'], $input);
        echo json_encode($result);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}
