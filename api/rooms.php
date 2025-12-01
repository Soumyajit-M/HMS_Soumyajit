<?php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/auth_helper.php';
require_once __DIR__ . '/../classes/Room.php';

$auth = api_require_login();
$method = $_SERVER['REQUEST_METHOD'];

// Admin check for POST/PUT/DELETE
if (in_array($method, ['POST', 'PUT', 'DELETE']) && !$auth->hasRole('admin')) {
    echo json_encode(['success' => false, 'message' => 'Access denied. Admin privileges required.']);
    exit();
}

$room = new Room();

switch ($method) {
    case 'GET':
        if (isset($_GET['action'])) {
            switch ($_GET['action']) {
                case 'wards':
                    if (isset($_GET['id'])) {
                        $ward = $room->getWardById($_GET['id']);
                        echo json_encode(['success' => true, 'ward' => $ward]);
                    } else {
                        $wards = $room->getAllWards();
                        echo json_encode(['success' => true, 'wards' => $wards]);
                    }
                    break;
                case 'assignments':
                    $assignments = $room->getAllBedAssignments();
                    echo json_encode(['success' => true, 'assignments' => $assignments]);
                    break;
                default:
                    echo json_encode(['success' => false, 'message' => 'Invalid action']);
            }
        } elseif (isset($_GET['id'])) {
            // Get single room by id
            $roomData = $room->getRoomById($_GET['id']);
            echo json_encode(['success' => true, 'room' => $roomData]);
        } elseif (isset($_GET['ward_id'])) {
            $rooms = $room->getRoomsByWard($_GET['ward_id']);
            echo json_encode(['success' => true, 'rooms' => $rooms]);
        } else {
            $rooms = $room->getAllRooms();
            echo json_encode(['success' => true, 'rooms' => $rooms]);
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
                case 'ward':
                    $result = $room->createWard($input);
                    break;
                case 'assign_bed':
                    $result = $room->assignBed($input);
                    break;
                default:
                    $result = $room->createRoom($input);
            }
        } else {
            $result = $room->createRoom($input);
        }
        echo json_encode($result);
        break;

    case 'PUT':
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || !isset($input['id'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit();
        }

        if (isset($input['action'])) {
            switch ($input['action']) {
                case 'ward':
                    $result = $room->updateWard($input['id'], $input);
                    break;
                case 'discharge':
                    $result = $room->dischargeBed($input['id']);
                    break;
                default:
                    $result = $room->updateRoom($input['id'], $input);
            }
        } else {
            $result = $room->updateRoom($input['id'], $input);
        }
        echo json_encode($result);
        break;

    case 'DELETE':
        if (!isset($_GET['id'])) {
            echo json_encode(['success' => false, 'message' => 'ID required']);
            exit();
        }
        
        $id = $_GET['id'];
        
        if (isset($_GET['action']) && $_GET['action'] === 'wards') {
            $result = $room->deleteWard($id);
        } else {
            $result = $room->deleteRoom($id);
        }
        echo json_encode($result);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}
