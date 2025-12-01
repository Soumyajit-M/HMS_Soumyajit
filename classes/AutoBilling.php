<?php
require_once __DIR__ . '/../config/database.php';

class AutoBilling {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Track patient admission and auto-create billing record
    public function trackAdmission($patientId, $roomId, $admissionDate) {
        try {
            // Get room charges
            $roomQuery = "SELECT r.room_number, r.room_type, r.charge_per_day, w.ward_name 
                         FROM rooms r 
                         JOIN wards w ON r.ward_id = w.id 
                         WHERE r.id = :room_id";
            $stmt = $this->conn->prepare($roomQuery);
            $stmt->bindParam(':room_id', $roomId);
            $stmt->execute();
            $room = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$room) {
                return ['success' => false, 'message' => 'Room not found'];
            }

            // Create or get active billing record
            $billId = $this->getOrCreateActiveBill($patientId);

            // Add bed charge item
            $this->addBillingItem($billId, 'Bed Charge', 'Room: ' . $room['room_number'] . ' (' . $room['room_type'] . ')', 
                                 $room['charge_per_day'], 1, 'daily');

            // Track admission in billing_item_tracking
            $this->trackItem($billId, 'admission', $roomId, $admissionDate);

            return ['success' => true, 'message' => 'Admission tracked successfully', 'bill_id' => $billId];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    // Track lab test and add to bill
    public function trackLabTest($patientId, $testId, $orderId = null) {
        try {
            // Get test details
            $testQuery = "SELECT test_name, test_code, standard_price FROM lab_test_catalog WHERE id = :test_id";
            $stmt = $this->conn->prepare($testQuery);
            $stmt->bindParam(':test_id', $testId);
            $stmt->execute();
            $test = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$test) {
                return ['success' => false, 'message' => 'Lab test not found'];
            }

            // Get or create active bill
            $billId = $this->getOrCreateActiveBill($patientId);

            // Add lab test item
            $itemId = $this->addBillingItem($billId, 'Lab Test', 
                                           $test['test_name'] . ' (' . $test['test_code'] . ')', 
                                           $test['standard_price'], 1, 'one-time');

            // Track in billing_item_tracking
            $this->trackItem($billId, 'lab_test', $testId, date('Y-m-d H:i:s'), $orderId);

            return ['success' => true, 'message' => 'Lab test added to bill', 'bill_id' => $billId, 'item_id' => $itemId];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    // Track doctor consultation and add to bill
    public function trackConsultation($patientId, $doctorId, $appointmentId = null) {
        try {
            // Get doctor details and consultation fee
            $doctorQuery = "SELECT u.first_name, u.last_name, d.specialization, d.consultation_fee 
                           FROM doctors d 
                           JOIN users u ON d.user_id = u.id 
                           WHERE d.id = :doctor_id";
            $stmt = $this->conn->prepare($doctorQuery);
            $stmt->bindParam(':doctor_id', $doctorId);
            $stmt->execute();
            $doctor = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$doctor) {
                return ['success' => false, 'message' => 'Doctor not found'];
            }

            // Get or create active bill
            $billId = $this->getOrCreateActiveBill($patientId);

            // Add consultation item
            $itemId = $this->addBillingItem($billId, 'Doctor Consultation', 
                                           'Dr. ' . $doctor['first_name'] . ' ' . $doctor['last_name'] . 
                                           ' (' . $doctor['specialization'] . ')', 
                                           $doctor['consultation_fee'], 1, 'one-time');

            // Track in billing_item_tracking
            $this->trackItem($billId, 'consultation', $doctorId, date('Y-m-d H:i:s'), $appointmentId);

            return ['success' => true, 'message' => 'Consultation added to bill', 'bill_id' => $billId, 'item_id' => $itemId];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    // Track medicine/inventory items
    public function trackMedicine($patientId, $itemId, $quantity) {
        try {
            // Get item details
            $itemQuery = "SELECT item_name, unit_price FROM inventory_items WHERE id = :item_id";
            $stmt = $this->conn->prepare($itemQuery);
            $stmt->bindParam(':item_id', $itemId);
            $stmt->execute();
            $item = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$item) {
                return ['success' => false, 'message' => 'Medicine not found'];
            }

            // Get or create active bill
            $billId = $this->getOrCreateActiveBill($patientId);

            // Add medicine item
            $itemIdBilling = $this->addBillingItem($billId, 'Medicine', $item['item_name'], 
                                                   $item['unit_price'], $quantity, 'one-time');

            // Track in billing_item_tracking
            $this->trackItem($billId, 'medicine', $itemId, date('Y-m-d H:i:s'), null, $quantity);

            return ['success' => true, 'message' => 'Medicine added to bill', 'bill_id' => $billId];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    // Calculate bed charges based on admission duration
    public function calculateBedCharges($billId) {
        try {
            // Get admission tracking
            $query = "SELECT bit.*, bi.unit_price, bi.created_at as item_created 
                     FROM billing_item_tracking bit
                     JOIN billing_items bi ON bit.billing_item_id = bi.id
                     WHERE bit.bill_id = :bill_id AND bit.item_type = 'admission'";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':bill_id', $billId);
            $stmt->execute();
            $admission = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($admission) {
                $admissionDate = new DateTime($admission['service_date']);
                $today = new DateTime();
                $days = $today->diff($admissionDate)->days + 1; // Include current day

                // Update billing item quantity (days)
                $updateQuery = "UPDATE billing_items SET quantity = :days WHERE id = :item_id";
                $stmt = $this->conn->prepare($updateQuery);
                $stmt->bindParam(':days', $days);
                $stmt->bindParam(':item_id', $admission['billing_item_id']);
                $stmt->execute();

                // Recalculate total
                $this->updateBillTotal($billId);

                return ['success' => true, 'days' => $days];
            }

            return ['success' => false, 'message' => 'No admission found'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    // Get or create active billing record for patient
    private function getOrCreateActiveBill($patientId) {
        try {
            // Check for existing active bill
            $query = "SELECT id FROM billing WHERE patient_id = :patient_id AND payment_status = 'pending' ORDER BY created_at DESC LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':patient_id', $patientId);
            $stmt->execute();
            $bill = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($bill) {
                return $bill['id'];
            }

            // Create new bill
            $billNumber = 'BILL-' . date('Ymd') . '-' . $patientId . '-' . time();
            $insertQuery = "INSERT INTO billing (patient_id, bill_number, total_amount, payment_status) 
                           VALUES (:patient_id, :bill_number, 0, 'pending')";
            $stmt = $this->conn->prepare($insertQuery);
            $stmt->bindParam(':patient_id', $patientId);
            $stmt->bindParam(':bill_number', $billNumber);
            $stmt->execute();

            return $this->conn->lastInsertId();
        } catch (PDOException $e) {
            throw $e;
        }
    }

    // Add item to billing
    private function addBillingItem($billId, $itemType, $description, $unitPrice, $quantity, $chargeType = 'one-time') {
        try {
            $totalPrice = $unitPrice * $quantity;
            $itemName = $itemType; // Use item type as item name
            
            $query = "INSERT INTO billing_items (billing_id, item_name, description, unit_price, quantity, total_price) 
                     VALUES (:billing_id, :item_name, :description, :unit_price, :quantity, :total_price)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':billing_id', $billId);
            $stmt->bindParam(':item_name', $itemName);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':unit_price', $unitPrice);
            $stmt->bindParam(':quantity', $quantity);
            $stmt->bindParam(':total_price', $totalPrice);
            $stmt->execute();

            $itemId = $this->conn->lastInsertId();

            // Update bill total
            $this->updateBillTotal($billId);

            return $itemId;
        } catch (PDOException $e) {
            throw $e;
        }
    }

    // Track billing item for reference
    private function trackItem($billId, $itemType, $referenceId, $serviceDate, $orderId = null, $quantity = 1) {
        try {
            // Get the last billing item added
            $query = "SELECT id FROM billing_items WHERE billing_id = :billing_id ORDER BY id DESC LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':billing_id', $billId);
            $stmt->execute();
            $item = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($item) {
                $insertQuery = "INSERT INTO billing_item_tracking (bill_id, billing_item_id, item_type, reference_id, service_date, order_id, quantity) 
                               VALUES (:bill_id, :billing_item_id, :item_type, :reference_id, :service_date, :order_id, :quantity)";
                $stmt = $this->conn->prepare($insertQuery);
                $stmt->bindParam(':bill_id', $billId);
                $stmt->bindParam(':billing_item_id', $item['id']);
                $stmt->bindParam(':item_type', $itemType);
                $stmt->bindParam(':reference_id', $referenceId);
                $stmt->bindParam(':service_date', $serviceDate);
                $stmt->bindParam(':order_id', $orderId);
                $stmt->bindParam(':quantity', $quantity);
                $stmt->execute();
            }
        } catch (PDOException $e) {
            // Silent fail for tracking
        }
    }

    // Update bill total
    private function updateBillTotal($billId) {
        try {
            $query = "SELECT SUM(total_price) as total FROM billing_items WHERE billing_id = :billing_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':billing_id', $billId);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $total = $result['total'] ?? 0;

            $updateQuery = "UPDATE billing SET total_amount = :total, updated_at = :updated_at WHERE id = :bill_id";
            $stmt = $this->conn->prepare($updateQuery);
            $stmt->bindParam(':total', $total);
            $updated = date('Y-m-d H:i:s');
            $stmt->bindParam(':updated_at', $updated);
            $stmt->bindParam(':bill_id', $billId);
            $stmt->execute();
        } catch (PDOException $e) {
            throw $e;
        }
    }

    // Get detailed bill with all items
    public function getDetailedBill($billId) {
        try {
            // Get bill header
            $billQuery = "SELECT b.*, p.first_name, p.last_name, p.patient_id 
                         FROM billing b 
                         JOIN patients p ON b.patient_id = p.id 
                         WHERE b.id = :bill_id";
            $stmt = $this->conn->prepare($billQuery);
            $stmt->bindParam(':bill_id', $billId);
            $stmt->execute();
            $bill = $stmt->fetch(PDO::FETCH_ASSOC);

            // Get all items
            $itemsQuery = "SELECT * FROM billing_items WHERE billing_id = :billing_id ORDER BY created_at";
            $stmt = $this->conn->prepare($itemsQuery);
            $stmt->bindParam(':billing_id', $billId);
            $stmt->execute();
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $bill['items'] = $items;

            return ['success' => true, 'bill' => $bill];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    // Finalize bill (discharge patient)
    public function finalizeBill($billId) {
        try {
            // Calculate final bed charges
            $this->calculateBedCharges($billId);

            // Update bill status
            $query = "UPDATE billing SET payment_status = 'pending', updated_at = :updated_at WHERE id = :bill_id";
            $stmt = $this->conn->prepare($query);
            $updated = date('Y-m-d H:i:s');
            $stmt->bindParam(':updated_at', $updated);
            $stmt->bindParam(':bill_id', $billId);
            $stmt->execute();

            return $this->getDetailedBill($billId);
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
}
