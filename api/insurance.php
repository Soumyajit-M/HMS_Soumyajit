<?php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/auth_helper.php';
require_once __DIR__ . '/../classes/Insurance.php';

$auth = api_require_login();
$method = $_SERVER['REQUEST_METHOD'];

$insurance = new Insurance();

switch ($method) {
    case 'GET':
        if (isset($_GET['action'])) {
            switch ($_GET['action']) {
                case 'providers':
                    if (isset($_GET['id'])) {
                        $provider = $insurance->getProviderById($_GET['id']);
                        echo json_encode(['success' => true, 'provider' => $provider]);
                    } else {
                        $providers = $insurance->getAllProviders();
                        echo json_encode(['success' => true, 'providers' => $providers]);
                    }
                    break;
                case 'patient_insurance':
                    if (isset($_GET['patient_id'])) {
                        $policies = $insurance->getPatientInsurance($_GET['patient_id']);
                        echo json_encode(['success' => true, 'policies' => $policies]);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Patient ID required']);
                    }
                    break;
                case 'claims':
                    if (isset($_GET['id'])) {
                        $claim = $insurance->getClaimById($_GET['id']);
                        echo json_encode(['success' => true, 'claim' => $claim]);
                    } else {
                        $claims = $insurance->getAllClaims();
                        echo json_encode(['success' => true, 'claims' => $claims]);
                    }
                    break;
                default:
                    echo json_encode(['success' => false, 'message' => 'Invalid action']);
            }
        } else {
            $providers = $insurance->getAllProviders();
            echo json_encode(['success' => true, 'providers' => $providers]);
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
                case 'provider':
                    if ($auth->hasRole('admin')) {
                        $result = $insurance->createProvider($input);
                    } else {
                        $result = ['success' => false, 'message' => 'Admin privileges required'];
                    }
                    break;
                case 'patient_insurance':
                    $result = $insurance->addPatientInsurance($input);
                    break;
                case 'claim':
                    $result = $insurance->createClaim($input);
                    break;
                default:
                    $result = ['success' => false, 'message' => 'Invalid action'];
            }
        } else {
            $result = ['success' => false, 'message' => 'Action required'];
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
                case 'provider':
                    if ($auth->hasRole('admin')) {
                        $result = $insurance->updateProvider($input['id'], $input);
                    } else {
                        $result = ['success' => false, 'message' => 'Admin privileges required'];
                    }
                    break;
                case 'patient_insurance':
                    $result = $insurance->updatePatientInsurance($input['id'], $input);
                    break;
                case 'claim_status':
                    $result = $insurance->updateClaimStatus($input['id'], $input['status'], $input['approved_amount'] ?? null, $input['denial_reason'] ?? null);
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
