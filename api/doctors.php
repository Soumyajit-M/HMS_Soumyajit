<?php
// Start session FIRST
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Prevent caching and authentication popups
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header_remove('WWW-Authenticate');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/Doctor.php';

$auth = new Auth();

// Check if user is logged in
if (!$auth->isLoggedIn()) {
    http_response_code(200); // Don't send 401 to avoid browser auth popup
    echo json_encode(['success' => false, 'message' => 'Session expired. Please refresh and log in again.']);
    exit();
}

// Handle different HTTP methods
$method = $_SERVER['REQUEST_METHOD'];

// Check if user is admin for POST operations
if ($method === 'POST' && !$auth->hasRole('admin')) {
    echo json_encode(['success' => false, 'message' => 'Access denied. Admin privileges required.']);
    exit();
}

$doctor = new Doctor();

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            // Get single doctor; unwrap data from class response if present
            $result = $doctor->getDoctorById($_GET['id']);
            if (is_array($result)) {
                if (isset($result['success']) && $result['success'] && isset($result['data'])) {
                    echo json_encode(['success' => true, 'doctor' => $result['data']]);
                } elseif (!empty($result)) {
                    // Fallback: already an associative row
                    echo json_encode(['success' => true, 'doctor' => $result]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Doctor not found']);
                }
            } else if ($result) {
                echo json_encode(['success' => true, 'doctor' => $result]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Doctor not found']);
            }
        } else {
            // Get all doctors
            $doctors = $doctor->getAllDoctors();
            echo json_encode(['success' => true, 'doctors' => $doctors]);
        }
        break;
        
    case 'POST':
        // Create new doctor
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
            break;
        }
        $data = [
            'first_name' => $input['first_name'] ?? '',
            'last_name' => $input['last_name'] ?? '',
            'email' => $input['email'] ?? '',
            'phone' => $input['phone'] ?? '',
            'specialization' => $input['specialization'] ?? '',
            'qualification' => $input['qualification'] ?? '',
            'experience_years' => $input['experience_years'] ?? 0,
            'consultation_fee' => $input['consultation_fee'] ?? 0,
            'bio' => $input['bio'] ?? ''
        ];

        $result = $doctor->createDoctor($data);
        if ($result['success']) {
            echo json_encode(['success' => true, 'message' => $result['message'], 'doctor_id' => $result['doctor_id']]);
        } else {
            echo json_encode(['success' => false, 'message' => $result['message']]);
        }
        break;
        
    case 'PUT':
        // Update doctor
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
            break;
        }
        $id = $input['id'] ?? null;

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Doctor ID required']);
            break;
        }
        
        $data = [
            'first_name' => $input['first_name'] ?? '',
            'last_name' => $input['last_name'] ?? '',
            'email' => $input['email'] ?? '',
            'phone' => $input['phone'] ?? '',
            'specialization' => $input['specialization'] ?? '',
            'qualification' => $input['qualification'] ?? '',
            'experience_years' => $input['experience_years'] ?? 0,
            'consultation_fee' => $input['consultation_fee'] ?? 0,
            'bio' => $input['bio'] ?? ''
        ];
        
        $result = $doctor->updateDoctor($id, $data);
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Doctor updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating doctor']);
        }
        break;
        
    case 'DELETE':
        // Delete doctor
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
            break;
        }
        $id = $input['id'] ?? null;

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Doctor ID required']);
            break;
        }
        
        $result = $doctor->deleteDoctor($id);
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Doctor deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error deleting doctor']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}
?>
