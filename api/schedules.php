<?php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/auth_helper.php';
require_once __DIR__ . '/../classes/Schedule.php';

$auth = api_require_login();
$method = $_SERVER['REQUEST_METHOD'];

// Admin check for POST/PUT/DELETE
if (in_array($method, ['POST', 'PUT', 'DELETE']) && !$auth->hasRole('admin')) {
    echo json_encode(['success' => false, 'message' => 'Access denied. Admin privileges required.']);
    exit();
}

$schedule = new Schedule();

switch ($method) {
    case 'GET':
        if (isset($_GET['action']) && $_GET['action'] === 'leaves') {
            // Get all leaves or by doctor
            if (isset($_GET['doctor_id'])) {
                $leaves = $schedule->getLeavesByDoctor($_GET['doctor_id']);
                echo json_encode(['success' => true, 'leaves' => $leaves]);
            } else {
                $leaves = $schedule->getAllLeaves();
                echo json_encode(['success' => true, 'leaves' => $leaves]);
            }
        } else {
            // Get schedules
            if (isset($_GET['doctor_id'])) {
                $schedules = $schedule->getSchedulesByDoctor($_GET['doctor_id']);
                echo json_encode(['success' => true, 'schedules' => $schedules]);
            } else {
                $schedules = $schedule->getAllSchedules();
                echo json_encode(['success' => true, 'schedules' => $schedules]);
            }
        }
        break;

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
            exit();
        }

        if (isset($input['action']) && $input['action'] === 'leave') {
            $result = $schedule->createLeave($input);
        } else {
            $result = $schedule->createSchedule($input);
        }
        echo json_encode($result);
        break;

    case 'PUT':
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || !isset($input['id'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit();
        }

        if (isset($input['action']) && $input['action'] === 'approve_leave') {
            $result = $schedule->approveLeave($input['id']);
        } else {
            $result = $schedule->updateSchedule($input['id'], $input);
        }
        echo json_encode($result);
        break;

    case 'DELETE':
        if (!isset($_GET['id'])) {
            echo json_encode(['success' => false, 'message' => 'ID required']);
            exit();
        }

        if (isset($_GET['action']) && $_GET['action'] === 'leave') {
            $result = $schedule->deleteLeave($_GET['id']);
        } else {
            $result = $schedule->deleteSchedule($_GET['id']);
        }
        echo json_encode($result);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}
