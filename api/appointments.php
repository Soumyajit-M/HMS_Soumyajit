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
require_once __DIR__ . '/../classes/Appointment.php';

$auth = new Auth();

// Check if user is logged in via session
if (!$auth->isLoggedIn()) {
    http_response_code(200); // Don't send 401 to avoid browser auth popup
    echo json_encode(['success' => false, 'message' => 'Session expired. Please refresh and log in again.']);
    exit();
}

$appointment = new Appointment();

// Handle different HTTP methods
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            // Get single appointment using efficient direct method call
            $id = $_GET['id'];
            $appointmentData = $appointment->getAppointmentById($id);

            if ($appointmentData) {
                echo json_encode(['success' => true, 'appointment' => $appointmentData]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Appointment not found']);
            }
        } else {
            // Get all appointments
            $appointments = $appointment->getAllAppointments();
            echo json_encode(['success' => true, 'appointments' => $appointments]);
        }
        break;
        
    case 'POST':
        // Create new appointment
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
            break;
        }
        $data = [
            'patient_id' => $input['patient_id'] ?? '',
            'doctor_id' => $input['doctor_id'] ?? '',
            'appointment_date' => $input['appointment_date'] ?? '',
            'appointment_time' => $input['appointment_time'] ?? '',
            'reason' => $input['reason'] ?? '',
            'notes' => $input['notes'] ?? '',
            'department_id' => $input['department_id'] ?? null,
            'status' => $input['status'] ?? 'scheduled',
            'created_by' => $_SESSION['user_id'] ?? null
        ];

        $result = $appointment->createAppointment($data);

        // Support multiple possible return shapes from createAppointment()
        if (is_array($result) && isset($result['success']) && $result['success']) {
            $appointmentId = $result['appointment_id'] ?? null;
            echo json_encode(['success' => true, 'message' => $result['message'] ?? 'Appointment scheduled successfully', 'appointment_id' => $appointmentId]);
        } elseif ($result) {
            // If class returns a scalar ID or truthy value
            echo json_encode(['success' => true, 'message' => 'Appointment scheduled successfully', 'appointment_id' => $result]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error scheduling appointment']);
        }
        break;
        
    case 'PUT':
        // Update appointment
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? null;
        
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Appointment ID required']);
            break;
        }
        // If only status provided, use lightweight status update and return fresh record
        if (isset($input['status']) && count($input) === 2) { // id + status
            $statusResult = $appointment->updateAppointmentStatus($id, $input['status']);
            if ($statusResult['success']) {
                $updated = $appointment->getAppointmentById($id);
                $statusResult['appointment'] = $updated ?: null;
            }
            echo json_encode($statusResult);
            break;
        }

        // Fetch existing to preserve immutable / unchanged fields
        $existing = $appointment->getAppointmentById($id);
        if (!$existing) {
            echo json_encode(['success' => false, 'message' => 'Appointment not found']);
            break;
        }

        // Preserve patient_id / doctor_id when not explicitly sent
        $patientId = (isset($input['patient_id']) && $input['patient_id'] !== '') ? $input['patient_id'] : $existing['patient_id'];
        $doctorId = (isset($input['doctor_id']) && $input['doctor_id'] !== '') ? $input['doctor_id'] : $existing['doctor_id'];
        $apptDate = (isset($input['appointment_date']) && $input['appointment_date'] !== '') ? $input['appointment_date'] : $existing['appointment_date'];
        $apptTime = (isset($input['appointment_time']) && $input['appointment_time'] !== '') ? $input['appointment_time'] : $existing['appointment_time'];
        $status   = (isset($input['status']) && $input['status'] !== '') ? $input['status'] : $existing['status'];
        $reason   = (isset($input['reason']) && $input['reason'] !== '') ? $input['reason'] : $existing['reason'];
        $notes    = (isset($input['notes']) && $input['notes'] !== '') ? $input['notes'] : $existing['notes'];

        $data = [
            'patient_id' => $patientId,
            'doctor_id' => $doctorId,
            'appointment_date' => $apptDate,
            'appointment_time' => $apptTime,
            'status' => $status,
            'reason' => $reason,
            'notes' => $notes
        ];

        $result = $appointment->updateAppointment($id, $data);
        $response = [
            'success' => (bool)$result,
            'message' => $result ? 'Appointment updated successfully' : 'Error updating appointment'
        ];
        if ($result) {
            $response['appointment'] = $appointment->getAppointmentById($id) ?: null;
        }
        echo json_encode($response);
        break;
        
    case 'DELETE':
        // Delete appointment (hard delete)
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
            break;
        }
        $id = $input['id'] ?? null;

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Appointment ID required']);
            break;
        }
        
        // Use the dedicated deleteAppointment method for hard delete
        $result = $appointment->deleteAppointment($id);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Appointment deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error deleting appointment']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}

