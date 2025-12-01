<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Validation.php';
require_once __DIR__ . '/AutoBilling.php';

class Laboratory {
    private $conn;
    private $autoBilling;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->autoBilling = new AutoBilling();
    }

    // Lab Test Types
    public function getAllTestTypes() {
        try {
            $sql = "SELECT * FROM lab_test_types WHERE is_active = 1 ORDER BY test_name";
            $stmt = $this->conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function createTestType($data) {
        try {
            $validationErrors = [];

            if (empty($data['test_code'])) {
                $validationErrors[] = 'Test code is required';
            }
            if (empty($data['test_name'])) {
                $validationErrors[] = 'Test name is required';
            }

            if (!empty($validationErrors)) {
                return ['success' => false, 'message' => implode(', ', $validationErrors)];
            }

            $sql = "INSERT INTO lab_test_types (test_code, test_name, category, normal_range, unit, price, turnaround_time, is_active) 
                    VALUES (:test_code, :test_name, :category, :normal_range, :unit, :price, :turnaround_time, :is_active)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':test_code', $data['test_code']);
            $stmt->bindParam(':test_name', $data['test_name']);
            $category = $data['category'] ?? null;
            $stmt->bindParam(':category', $category);
            $normal_range = $data['normal_range'] ?? null;
            $stmt->bindParam(':normal_range', $normal_range);
            $unit = $data['unit'] ?? null;
            $stmt->bindParam(':unit', $unit);
            $price = $data['price'] ?? null;
            $stmt->bindParam(':price', $price);
            $turnaround_time = $data['turnaround_time'] ?? null;
            $stmt->bindParam(':turnaround_time', $turnaround_time);
            $is_active = $data['is_active'] ?? 1;
            $stmt->bindParam(':is_active', $is_active);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Test type created successfully', 'id' => $this->conn->lastInsertId()];
            }
            return ['success' => false, 'message' => 'Failed to create test type'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    // Lab Orders
    public function createOrder($data) {
        try {
            $validationErrors = [];

            if (empty($data['patient_id'])) {
                $validationErrors[] = 'Patient ID is required';
            }
            if (empty($data['doctor_id'])) {
                $validationErrors[] = 'Doctor ID is required';
            }
            if (empty($data['tests']) || !is_array($data['tests'])) {
                $validationErrors[] = 'At least one test is required';
            }

            if (!empty($validationErrors)) {
                return ['success' => false, 'message' => implode(', ', $validationErrors)];
            }

            // Start transaction
            $this->conn->beginTransaction();

            try {
                // Create lab order
                $sql = "INSERT INTO lab_orders (patient_id, doctor_id, order_date, priority, clinical_notes, status) 
                        VALUES (:patient_id, :doctor_id, :order_date, :priority, :clinical_notes, :status)";
                
                $stmt = $this->conn->prepare($sql);
                $stmt->bindParam(':patient_id', $data['patient_id']);
                $stmt->bindParam(':doctor_id', $data['doctor_id']);
                $order_date = $data['order_date'] ?? date('Y-m-d H:i:s');
                $stmt->bindParam(':order_date', $order_date);
                $priority = $data['priority'] ?? 'Routine';
                $stmt->bindParam(':priority', $priority);
                $clinical_notes = $data['clinical_notes'] ?? null;
                $stmt->bindParam(':clinical_notes', $clinical_notes);
                $status = 'Pending';
                $stmt->bindParam(':status', $status);

                $stmt->execute();
                $orderId = $this->conn->lastInsertId();

                // Add tests to order
                $testSql = "INSERT INTO lab_order_tests (lab_order_id, test_type_id, status) 
                            VALUES (:lab_order_id, :test_type_id, :status)";
                $testStmt = $this->conn->prepare($testSql);

                foreach ($data['tests'] as $testId) {
                    $testStmt->bindParam(':lab_order_id', $orderId);
                    $testStmt->bindParam(':test_type_id', $testId);
                    $testStmt->bindParam(':status', $status);
                    $testStmt->execute();
                    
                    // Auto-add to billing
                    $this->autoBilling->trackLabTest($data['patient_id'], $testId, $orderId);
                }

                $this->conn->commit();
                return ['success' => true, 'message' => 'Lab order created successfully', 'id' => $orderId];
            } catch (Exception $e) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Failed to create lab order: ' . $e->getMessage()];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function getAllOrders() {
        try {
            $sql = "SELECT lo.*, 
                    p.first_name as patient_first_name, p.last_name as patient_last_name,
                    d.first_name as doctor_first_name, d.last_name as doctor_last_name
                    FROM lab_orders lo
                    LEFT JOIN patients p ON lo.patient_id = p.id
                    LEFT JOIN doctors d ON lo.doctor_id = d.id
                    ORDER BY lo.order_date DESC";
            $stmt = $this->conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getOrderById($id) {
        try {
            $sql = "SELECT lo.*, 
                    p.first_name as patient_first_name, p.last_name as patient_last_name,
                    d.first_name as doctor_first_name, d.last_name as doctor_last_name
                    FROM lab_orders lo
                    LEFT JOIN patients p ON lo.patient_id = p.id
                    LEFT JOIN doctors d ON lo.doctor_id = d.id
                    WHERE lo.id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    public function getOrderTests($orderId) {
        try {
            $sql = "SELECT lot.*, ltt.test_code, ltt.test_name, ltt.normal_range, ltt.unit
                    FROM lab_order_tests lot
                    JOIN lab_test_types ltt ON lot.test_type_id = ltt.id
                    WHERE lot.lab_order_id = :order_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':order_id', $orderId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function updateTestResult($testId, $result, $remarks = null) {
        try {
            $sql = "UPDATE lab_order_tests SET 
                    result_value = :result_value,
                    result_date = :result_date,
                    technician_remarks = :technician_remarks,
                    status = 'Completed'
                    WHERE id = :id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $testId);
            $stmt->bindParam(':result_value', $result);
            $result_date = date('Y-m-d H:i:s');
            $stmt->bindParam(':result_date', $result_date);
            $stmt->bindParam(':technician_remarks', $remarks);

            if ($stmt->execute()) {
                // Check if all tests in order are completed
                $this->updateOrderStatus($testId);
                return ['success' => true, 'message' => 'Test result updated successfully'];
            }
            return ['success' => false, 'message' => 'Failed to update test result'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    private function updateOrderStatus($testId) {
        try {
            // Get order_id from test
            $sql = "SELECT lab_order_id FROM lab_order_tests WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $testId);
            $stmt->execute();
            $test = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$test) return;
            
            // Check if all tests are completed
            $checkSql = "SELECT COUNT(*) as total, 
                        SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed
                        FROM lab_order_tests WHERE lab_order_id = :order_id";
            $checkStmt = $this->conn->prepare($checkSql);
            $checkStmt->bindParam(':order_id', $test['lab_order_id']);
            $checkStmt->execute();
            $counts = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($counts['total'] == $counts['completed']) {
                $updateSql = "UPDATE lab_orders SET status = 'Completed', completed_date = :completed_date 
                              WHERE id = :order_id";
                $updateStmt = $this->conn->prepare($updateSql);
                $completed_date = date('Y-m-d H:i:s');
                $updateStmt->bindParam(':completed_date', $completed_date);
                $updateStmt->bindParam(':order_id', $test['lab_order_id']);
                $updateStmt->execute();
            }
        } catch (PDOException $e) {
            // Silently fail
        }
    }

    public function setOrderStatus($orderId, $status) {
        try {
            $sql = "UPDATE lab_orders SET status = :status WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $orderId);
            $stmt->bindParam(':status', $status);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Order status updated successfully'];
            }
            return ['success' => false, 'message' => 'Failed to update order status'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
}
