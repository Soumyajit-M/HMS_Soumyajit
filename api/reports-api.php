<?php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/auth_helper.php';
require_once __DIR__ . '/../classes/Report.php';

$auth = api_require_login();
$method = $_SERVER['REQUEST_METHOD'];

$report = new Report();

switch ($method) {
    case 'GET':
        if (isset($_GET['action'])) {
            switch ($_GET['action']) {
                case 'templates':
                    if (isset($_GET['id'])) {
                        $template = $report->getTemplateById($_GET['id']);
                        echo json_encode(['success' => true, 'template' => $template]);
                    } else {
                        $templates = $report->getAllTemplates();
                        echo json_encode(['success' => true, 'templates' => $templates]);
                    }
                    break;
                case 'executions':
                    if (isset($_GET['id'])) {
                        $execution = $report->getExecutionById($_GET['id']);
                        echo json_encode(['success' => true, 'execution' => $execution]);
                    } else {
                        $executions = $report->getAllExecutions();
                        echo json_encode(['success' => true, 'executions' => $executions]);
                    }
                    break;
                case 'patient_demographics':
                    $data = $report->getPatientDemographicsReport();
                    echo json_encode(['success' => true, 'data' => $data]);
                    break;
                case 'appointment_statistics':
                    $startDate = $_GET['start_date'] ?? null;
                    $endDate = $_GET['end_date'] ?? null;
                    $data = $report->getAppointmentStatistics($startDate, $endDate);
                    echo json_encode(['success' => true, 'data' => $data]);
                    break;
                case 'revenue':
                    $startDate = $_GET['start_date'] ?? null;
                    $endDate = $_GET['end_date'] ?? null;
                    $data = $report->getRevenueReport($startDate, $endDate);
                    echo json_encode(['success' => true, 'data' => $data]);
                    break;
                case 'doctor_performance':
                    $data = $report->getDoctorPerformanceReport();
                    echo json_encode(['success' => true, 'data' => $data]);
                    break;
                case 'inventory_status':
                    $data = $report->getInventoryStatusReport();
                    echo json_encode(['success' => true, 'data' => $data]);
                    break;
                default:
                    echo json_encode(['success' => false, 'message' => 'Invalid action']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Action required']);
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
                case 'template':
                    if ($auth->hasRole('admin')) {
                        $result = $report->createTemplate($input);
                    } else {
                        $result = ['success' => false, 'message' => 'Admin privileges required'];
                    }
                    break;
                case 'execute':
                    if (isset($input['template_id'])) {
                        $result = $report->executeReport(
                            $input['template_id'],
                            $input['parameters'] ?? null,
                            $_SESSION['user_id'] ?? null
                        );
                    } else {
                        $result = ['success' => false, 'message' => 'Template ID required'];
                    }
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
