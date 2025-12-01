<?php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/auth_helper.php';
require_once __DIR__ . '/../classes/Telemedicine.php';

$auth = api_require_login();
$method = $_SERVER['REQUEST_METHOD'];

$telemedicine = new Telemedicine();

switch ($method) {
    case 'GET':
        if (isset($_GET['action'])) {
            switch ($_GET['action']) {
                case 'monitoring':
                    if (isset($_GET['patient_id'])) {
                        $vitalType = $_GET['vital_type'] ?? null;
                        $days = $_GET['days'] ?? 30;
                        $data = $telemedicine->getMonitoringByPatient($_GET['patient_id'], $vitalType, $days);
                        echo json_encode(['success' => true, 'monitoring' => $data]);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Patient ID required']);
                    }
                    break;
                case 'prescriptions':
                    if (isset($_GET['session_id'])) {
                        $prescriptions = $telemedicine->getPrescriptionsBySession($_GET['session_id']);
                        echo json_encode(['success' => true, 'prescriptions' => $prescriptions]);
                    } elseif (isset($_GET['patient_id'])) {
                        $prescriptions = $telemedicine->getPrescriptionsByPatient($_GET['patient_id']);
                        echo json_encode(['success' => true, 'prescriptions' => $prescriptions]);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Session ID or Patient ID required']);
                    }
                    break;
                default:
                    echo json_encode(['success' => false, 'message' => 'Invalid action']);
            }
        } elseif (isset($_GET['id'])) {
            $session = $telemedicine->getSessionById($_GET['id']);
            if ($session) {
                echo json_encode(['success' => true, 'session' => $session]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Session not found']);
            }
        } else {
            $sessions = $telemedicine->getAllSessions();
            echo json_encode(['success' => true, 'sessions' => $sessions]);
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
                case 'monitoring':
                    $result = $telemedicine->addMonitoring($input);
                    break;
                case 'prescription':
                    $result = $telemedicine->createPrescription($input);
                    break;
                default:
                    $result = $telemedicine->createSession($input);
            }
        } else {
            $result = $telemedicine->createSession($input);
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
                case 'complete':
                    if (isset($input['id'])) {
                        $result = $telemedicine->completeSession($input['id'], $input['notes'] ?? null);
                    } else {
                        $result = ['success' => false, 'message' => 'Session ID required'];
                    }
                    break;
                case 'send_to_pharmacy':
                    if (isset($input['id'])) {
                        $result = $telemedicine->sendToPharmacy($input['id']);
                    } else {
                        $result = ['success' => false, 'message' => 'Prescription ID required'];
                    }
                    break;
                default:
                    if (isset($input['id'])) {
                        $result = $telemedicine->updateSession($input['id'], $input);
                    } else {
                        $result = ['success' => false, 'message' => 'Session ID required'];
                    }
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
