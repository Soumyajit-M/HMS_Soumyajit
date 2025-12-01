<?php
require_once __DIR__ . '/../config/database.php';

class Billing {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function createBill($data) {
        try {
            // Generate unique bill number
            $billNumber = 'BILL' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            $query = "INSERT INTO billing (bill_number, patient_id, appointment_id, total_amount, 
                     paid_amount, balance_amount, payment_status, due_date) 
                     VALUES (:bill_number, :patient_id, :appointment_id, :total_amount, 
                     :paid_amount, :balance_amount, :payment_status, :due_date)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':bill_number', $billNumber);

            $resolvedPatientId = $this->resolvePatientIdentifier($data['patient_id'] ?? null);
            $stmt->bindParam(':patient_id', $resolvedPatientId);

            $resolvedAppointmentId = $this->resolveAppointmentIdentifier($data['appointment_id'] ?? null);
            $stmt->bindParam(':appointment_id', $resolvedAppointmentId);
            $stmt->bindParam(':total_amount', $data['total_amount']);
            $stmt->bindParam(':paid_amount', $data['paid_amount']);
            $stmt->bindParam(':balance_amount', $data['balance_amount']);
            $stmt->bindParam(':payment_status', $data['payment_status']);
            $stmt->bindParam(':due_date', $data['due_date']);
            
            if ($stmt->execute()) {
                $billingId = $this->conn->lastInsertId();
                
                // Add billing items if provided
                if (!empty($data['items'])) {
                    foreach ($data['items'] as $item) {
                        $this->addBillingItem($billingId, $item);
                    }
                }
                
                return ['success' => true, 'message' => 'Bill created successfully', 'bill_number' => $billNumber, 'billing_id' => $billingId];
            } else {
                return ['success' => false, 'message' => 'Failed to create bill'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function addBillingItem($billingId, $item) {
        try {
            $query = "INSERT INTO billing_items (billing_id, item_name, description, quantity, 
                     unit_price, total_price) 
                     VALUES (:billing_id, :item_name, :description, :quantity, :unit_price, :total_price)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':billing_id', $billingId);
            $stmt->bindParam(':item_name', $item['item_name']);
            $stmt->bindParam(':description', $item['description']);
            $stmt->bindParam(':quantity', $item['quantity']);
            $stmt->bindParam(':unit_price', $item['unit_price']);
            $stmt->bindParam(':total_price', $item['total_price']);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getBill($id) {
        try {
                $query = "SELECT b.*, 
                            p.first_name as patient_first_name, 
                            p.last_name as patient_last_name, 
                            (p.first_name || ' ' || p.last_name) AS patient_name, 
                            p.patient_id as patient_code, 
                            p.phone as patient_phone, 
                            a.appointment_id,
                            a.appointment_date
                            FROM billing b 
                            LEFT JOIN patients p ON (
                                CAST(b.patient_id AS TEXT) = CAST(p.id AS TEXT) 
                                OR b.patient_id = p.patient_id
                            )
                            LEFT JOIN appointments a ON (
                                CAST(b.appointment_id AS TEXT) = CAST(a.id AS TEXT)
                                OR b.appointment_id = a.appointment_id
                            )
                            WHERE b.id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $bill = $stmt->fetch();
            if ($bill) {
                // Normalize defaults
                if ($bill['paid_amount'] === '' || $bill['paid_amount'] === null) $bill['paid_amount'] = 0;
                if ($bill['balance_amount'] === '' || $bill['balance_amount'] === null) $bill['balance_amount'] = 0;
                if ($bill['payment_status'] === '' || $bill['payment_status'] === null) $bill['payment_status'] = 'pending';
                if (empty(trim($bill['patient_name'] ?? ''))) {
                    $bill['patient_name'] = $bill['patient_first_name'] ?? $bill['patient_id'];
                }
                if (empty($bill['patient_phone'])) {
                    $bill['patient_phone'] = '';
                }
            }
            return $bill;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getBillItems($billingId) {
        try {
            $query = "SELECT * FROM billing_items WHERE billing_id = :billing_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':billing_id', $billingId);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getAllBills($limit = null, $offset = 0, $filters = []) {
        try {
            $query = "SELECT b.*, 
                     COALESCE(NULLIF(p.first_name || ' ' || p.last_name, ' '), b.patient_id) AS patient_name, 
                     p.profile_image AS patient_image, 
                     p.phone AS patient_phone, 
                     a.appointment_id, 
                     a.appointment_date
                FROM billing b 
                LEFT JOIN patients p ON (
                    CAST(b.patient_id AS TEXT) = CAST(p.id AS TEXT) 
                    OR b.patient_id = p.patient_id
                )
                LEFT JOIN appointments a ON (
                    CAST(b.appointment_id AS TEXT) = CAST(a.id AS TEXT)
                    OR b.appointment_id = a.appointment_id
                )
                WHERE 1=1";
            
            $params = [];
            
            if (!empty($filters['payment_status'])) {
                $query .= " AND b.payment_status = :payment_status";
                $params[':payment_status'] = $filters['payment_status'];
            }
            
            if (!empty($filters['date_from'])) {
                $query .= " AND b.created_at >= :date_from";
                $params[':date_from'] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $query .= " AND b.created_at <= :date_to";
                $params[':date_to'] = $filters['date_to'];
            }
            
            $query .= " ORDER BY b.created_at DESC";
            
            if ($limit) {
                $query .= " LIMIT :limit OFFSET :offset";
                $params[':limit'] = $limit;
                $params[':offset'] = $offset;
            }
            
            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            $bills = $stmt->fetchAll();
            foreach ($bills as &$bill) {
                if ($bill['paid_amount'] === '' || $bill['paid_amount'] === null) $bill['paid_amount'] = 0;
                if ($bill['balance_amount'] === '' || $bill['balance_amount'] === null) $bill['balance_amount'] = 0;
                if ($bill['payment_status'] === '' || $bill['payment_status'] === null) $bill['payment_status'] = 'pending';
                if ($bill['due_date'] === '' || $bill['due_date'] === null) $bill['due_date'] = null;
            }
            return $bills;
        } catch (PDOException $e) {
            return [];
        }
    }

    public function recordPayment($data) {
        try {
            // Generate unique payment ID
            $paymentId = 'PAY' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            $query = "INSERT INTO payments (payment_id, billing_id, amount, payment_method, 
                     transaction_id, notes, created_by) 
                     VALUES (:payment_id, :billing_id, :amount, :payment_method, 
                     :transaction_id, :notes, :created_by)";
            
            $stmt = $this->conn->prepare($query);
            // Coerce input to scalars to avoid warnings
            $billingId = is_scalar($data['billing_id'] ?? null) ? $data['billing_id'] : (is_array($data['billing_id']) ? json_encode($data['billing_id']) : null);
            $amount = isset($data['amount']) ? floatval($data['amount']) : 0;
            $paymentMethod = isset($data['payment_method']) ? strval($data['payment_method']) : 'cash';
            $transactionId = isset($data['transaction_id']) ? strval($data['transaction_id']) : '';
            $notes = isset($data['notes']) ? strval($data['notes']) : '';
            $createdBy = isset($data['created_by']) ? $data['created_by'] : null;

            $stmt->bindParam(':payment_id', $paymentId);
            $stmt->bindParam(':billing_id', $billingId);
            $stmt->bindParam(':amount', $amount);
            $stmt->bindParam(':payment_method', $paymentMethod);
            $stmt->bindParam(':transaction_id', $transactionId);
            $stmt->bindParam(':notes', $notes);
            $stmt->bindParam(':created_by', $createdBy);
            
            if ($stmt->execute()) {
                // Update billing record
                $this->updateBillingPayment($data['billing_id']);
                
                return ['success' => true, 'message' => 'Payment recorded successfully', 'payment_id' => $paymentId];
            } else {
                return ['success' => false, 'message' => 'Failed to record payment'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    private function updateBillingPayment($billingId) {
        try {
            // Calculate total paid amount
            $query = "SELECT SUM(amount) as total_paid FROM payments WHERE billing_id = :billing_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':billing_id', $billingId);
            $stmt->execute();
            $result = $stmt->fetch();
            $totalPaid = 0;
            if ($result && is_array($result) && array_key_exists('total_paid', $result)) {
                $totalPaid = $result['total_paid'] ?? 0;
            }
            
            // Get billing details
            $query = "SELECT total_amount FROM billing WHERE id = :billing_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':billing_id', $billingId);
            $stmt->execute();
            $billing = $stmt->fetch();

            $totalAmount = isset($billing['total_amount']) ? floatval($billing['total_amount']) : 0;
            $balanceAmount = $totalAmount - $totalPaid;
            $paymentStatus = $balanceAmount <= 0 ? 'paid' : ($totalPaid > 0 ? 'partial' : 'pending');
            
            // Update billing record
            $query = "UPDATE billing SET paid_amount = :paid_amount, balance_amount = :balance_amount, 
                     payment_status = :payment_status WHERE id = :billing_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':paid_amount', $totalPaid);
            $stmt->bindValue(':balance_amount', $balanceAmount);
            $stmt->bindValue(':payment_status', $paymentStatus);
            $stmt->bindValue(':billing_id', $billingId);
            $stmt->execute();
            
        } catch (PDOException $e) {
            // Handle error silently for now
        }
    }

    public function getBillingStats() {
        try {
            $from30 = date('Y-m-d', strtotime('-30 days'));
            $query = "SELECT 
                        COUNT(*) as total_bills,
                        SUM(total_amount) as total_revenue,
                        SUM(paid_amount) as total_paid,
                        SUM(balance_amount) as total_balance,
                        SUM(CASE WHEN payment_status = 'paid' THEN 1 ELSE 0 END) as paid_bills,
                        SUM(CASE WHEN payment_status = 'pending' THEN 1 ELSE 0 END) as pending_bills,
                        SUM(CASE WHEN payment_status = 'partial' THEN 1 ELSE 0 END) as partial_bills,
                        SUM(CASE WHEN created_at >= :from30 THEN 1 ELSE 0 END) as bills_30_days
                     FROM billing";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':from30', $from30);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getRevenueByMonth($year = null) {
        try {
            if (!$year) {
                $year = date('Y');
            }
            $query = "SELECT
                        CAST(strftime('%m', created_at) AS INTEGER) as month,
                        COUNT(*) as bill_count,
                        SUM(total_amount) as total_revenue,
                        SUM(paid_amount) as paid_revenue
                     FROM billing
                     WHERE strftime('%Y', created_at) = :year
                     GROUP BY CAST(strftime('%m', created_at) AS INTEGER)
                     ORDER BY month";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':year', $year);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getPendingBillsCount() {
        try {
            $query = "SELECT COUNT(*) as pending_count FROM billing WHERE payment_status = 'pending'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            return $result['pending_count'] ?? 0;
        } catch (PDOException $e) {
            return 0;
        }
    }

    public function getTotalRevenue() {
        try {
            $query = "SELECT SUM(total_amount) as total_revenue FROM billing";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            return $result['total_revenue'] ?? 0;
        } catch (PDOException $e) {
            return 0;
        }
    }

    public function getOverdueBillsCount() {
        try {
            $today = date('Y-m-d');
            $query = "SELECT COUNT(*) as overdue_count FROM billing WHERE due_date < :today AND payment_status != 'paid'";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':today', $today);
            $stmt->execute();
            $result = $stmt->fetch();
            return $result['overdue_count'] ?? 0;
        } catch (PDOException $e) {
            return 0;
        }
    }

    public function getOutstandingAmount() {
        try {
            $query = "SELECT SUM(balance_amount) as outstanding_amount FROM billing WHERE balance_amount > 0";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            return $result['outstanding_amount'] ?? 0;
        } catch (PDOException $e) {
            return 0;
        }
    }

    // Update a bill with new data. Returns boolean.
    public function updateBill($id, $data) {
        if (empty($id)) {
            return false;
        }

        try {
            $query = "UPDATE billing SET patient_id = :patient_id, appointment_id = :appointment_id, 
                     due_date = :due_date, total_amount = :total_amount, notes = :notes WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $patient_id = $this->resolvePatientIdentifier($data['patient_id'] ?? null);
            $appointment_id = $this->resolveAppointmentIdentifier($data['appointment_id'] ?? null);
            $due_date = $data['due_date'] ?? null;
            $total_amount = $data['total_amount'] ?? 0;
            $notes = $data['notes'] ?? null;
            $stmt->bindParam(':patient_id', $patient_id);
            $stmt->bindParam(':appointment_id', $appointment_id);
            $stmt->bindParam(':due_date', $due_date);
            $stmt->bindParam(':total_amount', $total_amount);
            $stmt->bindParam(':notes', $notes);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    // Backwards-compatible wrapper expected by API
    public function getBillById($id) {
        return $this->getBill($id);
    }

    // Delete a bill by numeric id or bill_number (string). Returns boolean.
    public function deleteBill($id) {
        if (empty($id)) {
            return false;
        }

        try {
            $query = "DELETE FROM billing WHERE id = :id OR bill_number = :bill_number";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':bill_number', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    // Payment helper methods for api/payments.php
    public function getPaymentsByBillId($billingId) {
        try {
            $query = "SELECT * FROM payments WHERE billing_id = :billing_id ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':billing_id', $billingId);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getAllPayments($limit = null, $offset = 0) {
        try {
            $query = "SELECT p.*, b.bill_number, b.patient_id FROM payments p 
                     LEFT JOIN billing b ON p.billing_id = b.id 
                     ORDER BY p.created_at DESC";
            
            if ($limit) {
                $query .= " LIMIT :limit OFFSET :offset";
            }
            
            $stmt = $this->conn->prepare($query);
            if ($limit) {
                $stmt->bindValue(':limit', $limit);
                $stmt->bindValue(':offset', $offset);
            }
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    private function resolvePatientIdentifier($value) {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (int)$value;
        }

        try {
            $sql = "SELECT id FROM patients WHERE patient_id = :patient_id LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':patient_id', $value);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result && isset($result['id'])) {
                return (int)$result['id'];
            }
        } catch (PDOException $e) {
            // Ignore lookup errors and fall back to original value
        }

        return $value;
    }

    private function resolveAppointmentIdentifier($value) {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (int)$value;
        }

        try {
            $sql = "SELECT id FROM appointments WHERE appointment_id = :appointment_id LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':appointment_id', $value);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result && isset($result['id'])) {
                return (int)$result['id'];
            }
        } catch (PDOException $e) {
            // Ignore lookup errors and fall back to original value
        }

        return $value;
    }

    public function updatePayment($id, $data) {
        if (empty($id)) {
            return false;
        }

        try {
            $query = "UPDATE payments SET amount = :amount, payment_method = :payment_method, 
                     transaction_id = :transaction_id, notes = :notes WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $amount = $data['amount'] ?? 0;
            $payment_method = $data['payment_method'] ?? 'cash';
            $transaction_id = $data['transaction_id'] ?? null;
            $notes = $data['notes'] ?? null;
            $stmt->bindParam(':amount', $amount);
            $stmt->bindParam(':payment_method', $payment_method);
            $stmt->bindParam(':transaction_id', $transaction_id);
            $stmt->bindParam(':notes', $notes);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function deletePayment($id) {
        if (empty($id)) {
            return false;
        }

        try {
            $query = "DELETE FROM payments WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

}
?>
