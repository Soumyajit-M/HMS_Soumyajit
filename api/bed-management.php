<?php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/auth_helper.php';

$auth = api_require_login();

try {
    $database = new Database();
    $db = $database->getConnection();

    // Handle GET requests - Fetch all beds
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmt = $db->query("
            SELECT 
                b.id,
                b.bed_number,
                b.ward_name,
                b.bed_type,
                b.status,
                ba.admitted_at as allocated_date,
                ba.diagnosis as notes,
                p.first_name || ' ' || p.last_name as patient_name,
                p.id as patient_id
            FROM beds b
            LEFT JOIN bed_assignments ba ON b.id = ba.bed_id AND ba.status = 'active'
            LEFT JOIN patients p ON ba.patient_id = p.id
            ORDER BY b.ward_name, b.bed_number
        ");
        
        $beds = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'beds' => $beds
        ]);
        exit();
    }

    // Handle POST requests - Allocate or discharge bed
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Also support form data
        if (!$input) {
            $input = $_POST;
        }
        
        if (!$input || !isset($input['action'])) {
            throw new Exception('Invalid input data');
        }

        if ($input['action'] === 'assign' || $input['action'] === 'allocate') {
            // Assign bed to patient
            if (!isset($input['bed_id']) || !isset($input['patient_id'])) {
                throw new Exception('Bed ID and Patient ID required');
            }

            // Check if bed exists and is available
            $stmt = $db->prepare("SELECT id, status FROM beds WHERE id = ?");
            $stmt->execute([$input['bed_id']]);
            $bed = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$bed) {
                throw new Exception('Bed not found');
            }
            
            if ($bed['status'] !== 'available') {
                throw new Exception('Bed is not available');
            }

            // Check if patient exists
            $stmt = $db->prepare("SELECT id FROM patients WHERE id = ?");
            $stmt->execute([$input['patient_id']]);
            $patient = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$patient) {
                throw new Exception('Patient not found');
            }

            // Create bed assignment
            $stmt = $db->prepare("
                INSERT INTO bed_assignments (bed_id, patient_id, diagnosis, admitted_by, admitted_at, status) 
                VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP, 'active')
            ");
            
            $stmt->execute([
                $input['bed_id'],
                $input['patient_id'],
                $input['diagnosis'] ?? '',
                $_SESSION['user_id'] ?? 'System'
            ]);

            // Update bed status
            $stmt = $db->prepare("UPDATE beds SET status = 'occupied' WHERE id = ?");
            $stmt->execute([$input['bed_id']]);
            
            echo json_encode(['success' => true, 'message' => 'Bed assigned successfully']);
            
        } elseif ($input['action'] === 'discharge') {
            // Discharge patient from bed
            if (!isset($input['bed_id'])) {
                throw new Exception('Bed ID required');
            }

            // Find active assignment for this bed
            $stmt = $db->prepare("
                SELECT id FROM bed_assignments 
                WHERE bed_id = ? AND status = 'active' AND discharged_at IS NULL
                LIMIT 1
            ");
            $stmt->execute([$input['bed_id']]);
            $assignment = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$assignment) {
                throw new Exception('No active assignment found for this bed');
            }

            // Update assignment
            $stmt = $db->prepare("
                UPDATE bed_assignments 
                SET discharged_at = CURRENT_TIMESTAMP, 
                    discharge_notes = ?, 
                    status = 'discharged' 
                WHERE id = ?
            ");
            $stmt->execute([
                $input['discharge_notes'] ?? '',
                $assignment['id']
            ]);

            // Update bed status
            $stmt = $db->prepare("UPDATE beds SET status = 'available' WHERE id = ?");
            $stmt->execute([$input['bed_id']]);
            
            echo json_encode(['success' => true, 'message' => 'Patient discharged successfully']);
            
        } else {
            throw new Exception('Invalid action');
        }
        exit();
    }

    echo json_encode(['success' => false, 'message' => 'Invalid request method']);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
