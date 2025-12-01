<?php
// Start session FIRST before anything else
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
require_once __DIR__ . '/../classes/Patient.php';

$auth = new Auth();

// Check if user is logged in via session - NO HTTP status codes
if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Session expired. Please refresh and log in again.']);
    exit();
}

$patient = new Patient();

// Handle different HTTP methods
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            // Get single patient
            $patientData = $patient->getPatient($_GET['id']);
            if ($patientData) {
                // Combine first_name and last_name into name for frontend
                $patientData['name'] = trim(($patientData['first_name'] ?? '') . ' ' . ($patientData['last_name'] ?? ''));
                echo json_encode(['success' => true, 'patient' => $patientData]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Patient not found']);
            }
        } else {
            // Get all patients
            $patients = $patient->getAllPatients();
            echo json_encode(['success' => true, 'patients' => $patients]);
        }
        break;
        
    case 'POST':
        // Create new patient
        $data = [
            'first_name' => $_POST['first_name'] ?? '',
            'last_name' => $_POST['last_name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'date_of_birth' => $_POST['date_of_birth'] ?? '',
            'gender' => $_POST['gender'] ?? '',
            'address' => $_POST['address'] ?? '',
            'emergency_contact_name' => $_POST['emergency_contact_name'] ?? '',
            'emergency_contact_phone' => $_POST['emergency_contact_phone'] ?? '',
            'emergency_contact_email' => $_POST['emergency_contact_email'] ?? '',
            'blood_type' => $_POST['blood_type'] ?? '',
            'allergies' => $_POST['allergies'] ?? '',
            'medical_history' => $_POST['medical_history'] ?? '',
            'insurance_provider' => $_POST['insurance_provider'] ?? '',
            'insurance_number' => $_POST['insurance_number'] ?? ''
        ];

        $result = $patient->createPatient($data);
        if ($result['success']) {
            echo json_encode(['success' => true, 'message' => 'Patient created successfully', 'patient_id' => $result['patient_id']]);
        } else {
            echo json_encode(['success' => false, 'message' => $result['message']]);
        }
        break;
        
    case 'PUT':
        // Update patient
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
            break;
        }
        $id = $input['id'] ?? null;

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Patient ID required']);
            break;
        }
        
        // Parse name into first_name and last_name if provided as single field
        $firstName = $input['first_name'] ?? '';
        $lastName = $input['last_name'] ?? '';
        if (isset($input['name']) && !$firstName && !$lastName) {
            $nameParts = explode(' ', trim($input['name']), 2);
            $firstName = $nameParts[0] ?? '';
            $lastName = $nameParts[1] ?? '';
        }
        
        $data = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $input['email'] ?? '',
            'phone' => $input['phone'] ?? '',
            'date_of_birth' => $input['date_of_birth'] ?? '',
            'gender' => $input['gender'] ?? '',
            'address' => $input['address'] ?? '',
            'emergency_contact_name' => $input['emergency_contact_name'] ?? '',
            'emergency_contact_phone' => $input['emergency_contact_phone'] ?? '',
            'emergency_contact_email' => $input['emergency_contact_email'] ?? '',
            'blood_type' => $input['blood_type'] ?? '',
            'allergies' => $input['allergies'] ?? '',
            'medical_history' => $input['medical_history'] ?? '',
            'insurance_provider' => $input['insurance_provider'] ?? '',
            'insurance_number' => $input['insurance_number'] ?? ''
        ];
        
        $result = $patient->updatePatient($id, $data);
        if ($result['success']) {
            echo json_encode(['success' => true, 'message' => 'Patient updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => $result['message']]);
        }
        break;
        
    case 'DELETE':
        // Delete patient
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
            break;
        }
        $id = $input['id'] ?? null;

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Patient ID required']);
            break;
        }

        $result = $patient->deletePatient($id);
        if (is_array($result)) {
            echo json_encode($result);
        } else if ($result) {
            echo json_encode(['success' => true, 'message' => 'Patient deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error deleting patient']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}
?>
