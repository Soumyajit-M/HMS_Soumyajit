<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Validation.php';

class Staff {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function createStaff($data) {
        try {
            $validationErrors = [];

            if (empty($data['first_name'])) {
                $validationErrors[] = 'First name is required';
            }
            if (empty($data['last_name'])) {
                $validationErrors[] = 'Last name is required';
            }
            if (empty($data['email'])) {
                $validationErrors[] = 'Email is required';
            } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $validationErrors[] = 'Invalid email format';
            }
            if (empty($data['phone'])) {
                $validationErrors[] = 'Phone is required';
            }
            if (empty($data['role'])) {
                $validationErrors[] = 'Role is required';
            }
            if (empty($data['department'])) {
                $validationErrors[] = 'Department is required';
            }

            if (!empty($validationErrors)) {
                return ['success' => false, 'message' => implode(', ', $validationErrors)];
            }

            // Generate staff ID
            $staff_id = 'STF' . uniqid();

            $sql = "INSERT INTO staff (staff_id, first_name, last_name, email, phone, role, department, hire_date, salary, emergency_contact, certification, is_active) 
                    VALUES (:staff_id, :first_name, :last_name, :email, :phone, :role, :department, :hire_date, :salary, :emergency_contact, :certification, :is_active)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':staff_id', $staff_id);
            $stmt->bindParam(':first_name', $data['first_name']);
            $stmt->bindParam(':last_name', $data['last_name']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':phone', $data['phone']);
            $stmt->bindParam(':role', $data['role']);
            $stmt->bindParam(':department', $data['department']);
            $hire_date = $data['hire_date'] ?? date('Y-m-d');
            $stmt->bindParam(':hire_date', $hire_date);
            $salary = $data['salary'] ?? null;
            $stmt->bindParam(':salary', $salary);
            $emergency_contact = $data['emergency_contact'] ?? null;
            $stmt->bindParam(':emergency_contact', $emergency_contact);
            $certification = $data['certification'] ?? null;
            $stmt->bindParam(':certification', $certification);
            $is_active = $data['is_active'] ?? 1;
            $stmt->bindParam(':is_active', $is_active);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Staff member created successfully', 'id' => $this->conn->lastInsertId()];
            }
            return ['success' => false, 'message' => 'Failed to create staff member'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function getAllStaff() {
        try {
            $sql = "SELECT * FROM staff ORDER BY last_name, first_name";
            $stmt = $this->conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getStaffById($id) {
        try {
            $sql = "SELECT * FROM staff WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    public function updateStaff($id, $data) {
        try {
            $sql = "UPDATE staff SET 
                    first_name = :first_name,
                    last_name = :last_name,
                    email = :email,
                    phone = :phone,
                    role = :role,
                    department = :department,
                    salary = :salary,
                    emergency_contact = :emergency_contact,
                    certification = :certification,
                    is_active = :is_active
                    WHERE id = :id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':first_name', $data['first_name']);
            $stmt->bindParam(':last_name', $data['last_name']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':phone', $data['phone']);
            $stmt->bindParam(':role', $data['role']);
            $stmt->bindParam(':department', $data['department']);
            $stmt->bindParam(':salary', $data['salary']);
            $stmt->bindParam(':emergency_contact', $data['emergency_contact']);
            $stmt->bindParam(':certification', $data['certification']);
            $stmt->bindParam(':is_active', $data['is_active']);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Staff member updated successfully'];
            }
            return ['success' => false, 'message' => 'Failed to update staff member'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function deleteStaff($id) {
        try {
            $sql = "DELETE FROM staff WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Staff member deleted successfully'];
            }
            return ['success' => false, 'message' => 'Failed to delete staff member'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    // Shift Management
    public function createShift($data) {
        try {
            $validationErrors = [];

            if (empty($data['staff_id'])) {
                $validationErrors[] = 'Staff ID is required';
            }
            if (empty($data['shift_date'])) {
                $validationErrors[] = 'Shift date is required';
            }
            if (empty($data['shift_type'])) {
                $validationErrors[] = 'Shift type is required';
            }
            if (empty($data['start_time'])) {
                $validationErrors[] = 'Start time is required';
            }
            if (empty($data['end_time'])) {
                $validationErrors[] = 'End time is required';
            }

            if (!empty($validationErrors)) {
                return ['success' => false, 'message' => implode(', ', $validationErrors)];
            }

            $sql = "INSERT INTO staff_shifts (staff_id, shift_date, shift_type, start_time, end_time, assigned_ward) 
                    VALUES (:staff_id, :shift_date, :shift_type, :start_time, :end_time, :assigned_ward)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':staff_id', $data['staff_id']);
            $stmt->bindParam(':shift_date', $data['shift_date']);
            $stmt->bindParam(':shift_type', $data['shift_type']);
            $stmt->bindParam(':start_time', $data['start_time']);
            $stmt->bindParam(':end_time', $data['end_time']);
            $assigned_ward = $data['assigned_ward'] ?? null;
            $stmt->bindParam(':assigned_ward', $assigned_ward);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Shift created successfully', 'id' => $this->conn->lastInsertId()];
            }
            return ['success' => false, 'message' => 'Failed to create shift'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function getShiftsByStaff($staffId) {
        try {
            $sql = "SELECT * FROM staff_shifts WHERE staff_id = :staff_id ORDER BY shift_date DESC, start_time";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':staff_id', $staffId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getAllShifts() {
        try {
            $sql = "SELECT ss.*, s.first_name, s.last_name, s.role, s.department 
                    FROM staff_shifts ss
                    JOIN staff s ON ss.staff_id = s.id
                    ORDER BY ss.shift_date DESC, ss.start_time";
            $stmt = $this->conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function deleteShift($id) {
        try {
            $sql = "DELETE FROM staff_shifts WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Shift deleted successfully'];
            }
            return ['success' => false, 'message' => 'Failed to delete shift'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
}
