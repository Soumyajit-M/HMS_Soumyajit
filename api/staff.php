<?php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/auth_helper.php';
require_once __DIR__ . '/../classes/Staff.php';

$auth = api_require_login();
$method = $_SERVER['REQUEST_METHOD'];

// Admin check for POST/PUT/DELETE
if (in_array($method, ['POST', 'PUT', 'DELETE']) && !$auth->hasRole('admin')) {
    echo json_encode(['success' => false, 'message' => 'Access denied. Admin privileges required.']);
    exit();
}

$staff = new Staff();

switch ($method) {
    case 'GET':
        if (isset($_GET['action']) && $_GET['action'] === 'shifts') {
            // Get shifts
            if (isset($_GET['staff_id'])) {
                $shifts = $staff->getShiftsByStaff($_GET['staff_id']);
                echo json_encode(['success' => true, 'shifts' => $shifts]);
            } else {
                $shifts = $staff->getAllShifts();
                echo json_encode(['success' => true, 'shifts' => $shifts]);
            }
        } elseif (isset($_GET['id'])) {
            // Get single staff member
            $member = $staff->getStaffById($_GET['id']);
            if ($member) {
                echo json_encode(['success' => true, 'staff' => $member]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Staff member not found']);
            }
        } else {
            // Get all staff
            $members = $staff->getAllStaff();
            echo json_encode(['success' => true, 'staff' => $members]);
        }
        break;

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
            exit();
        }

        if (isset($input['action']) && $input['action'] === 'shift') {
            $result = $staff->createShift($input);
        } else {
            $result = $staff->createStaff($input);
        }
        echo json_encode($result);
        break;

    case 'PUT':
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || !isset($input['id'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit();
        }

        // Log the received data for debugging
        error_log("PUT request data: " . json_encode($input));
        error_log("Role value: " . ($input['role'] ?? 'NOT SET'));

        $result = $staff->updateStaff($input['id'], $input);
        echo json_encode($result);
        break;

    case 'DELETE':
        if (!isset($_GET['id'])) {
            echo json_encode(['success' => false, 'message' => 'ID required']);
            exit();
        }

        if (isset($_GET['action']) && $_GET['action'] === 'shift') {
            $result = $staff->deleteShift($_GET['id']);
        } else {
            $result = $staff->deleteStaff($_GET['id']);
        }
        echo json_encode($result);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}
