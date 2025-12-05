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

            if (!empty($validationErrors)) {
                return ['success' => false, 'message' => implode(', ', $validationErrors)];
            }

            // Generate staff ID
            $staff_id = 'STF' . uniqid();

            $sql = "INSERT INTO staff (staff_id, first_name, last_name, email, phone, role, department_id, date_of_joining, salary, emergency_contact_name, emergency_contact_phone, certifications, qualification, license_number, employment_type, is_active) 
                    VALUES (:staff_id, :first_name, :last_name, :email, :phone, :role, :department_id, :date_of_joining, :salary, :emergency_contact_name, :emergency_contact_phone, :certifications, :qualification, :license_number, :employment_type, :is_active)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':staff_id', $staff_id);
            $stmt->bindParam(':first_name', $data['first_name']);
            $stmt->bindParam(':last_name', $data['last_name']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':phone', $data['phone']);
            $stmt->bindParam(':role', $data['role']);
            $department_id = $data['department_id'] ?? null;
            $stmt->bindParam(':department_id', $department_id);
            $date_of_joining = $data['hire_date'] ?? date('Y-m-d');
            $stmt->bindParam(':date_of_joining', $date_of_joining);
            $salary = $data['salary'] ?? null;
            $stmt->bindParam(':salary', $salary);
            $emergency_contact_name = $data['emergency_contact_name'] ?? null;
            $stmt->bindParam(':emergency_contact_name', $emergency_contact_name);
            $emergency_contact_phone = $data['emergency_contact_phone'] ?? null;
            $stmt->bindParam(':emergency_contact_phone', $emergency_contact_phone);
            $certifications = $data['certifications'] ?? null;
            $stmt->bindParam(':certifications', $certifications);
            $qualification = $data['qualification'] ?? null;
            $stmt->bindParam(':qualification', $qualification);
            $license_number = $data['license_number'] ?? null;
            $stmt->bindParam(':license_number', $license_number);
            $employment_type = $data['employment_type'] ?? null;
            $stmt->bindParam(':employment_type', $employment_type);
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
            $sql = "SELECT s.*, d.name as department 
                    FROM staff s 
                    LEFT JOIN departments d ON s.department_id = d.id 
                    ORDER BY s.last_name, s.first_name";
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
            // Validate required fields
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

            if (!empty($validationErrors)) {
                return ['success' => false, 'message' => implode(', ', $validationErrors)];
            }

            $sql = "UPDATE staff SET 
                    first_name = :first_name,
                    last_name = :last_name,
                    email = :email,
                    phone = :phone,
                    role = :role,
                    department_id = :department_id,
                    salary = :salary,
                    emergency_contact_name = :emergency_contact_name,
                    emergency_contact_phone = :emergency_contact_phone,
                    certifications = :certifications,
                    qualification = :qualification,
                    license_number = :license_number,
                    employment_type = :employment_type,
                    is_active = :is_active
                    WHERE id = :id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':first_name', $data['first_name']);
            $stmt->bindParam(':last_name', $data['last_name']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':phone', $data['phone']);
            $stmt->bindParam(':role', $data['role']);
            $department_id = $data['department_id'] ?? null;
            $stmt->bindParam(':department_id', $department_id);
            $salary = $data['salary'] ?? null;
            $stmt->bindParam(':salary', $salary);
            
            // Use variables for nullable fields (bindParam requires variables by reference)
            $emergency_contact_name = $data['emergency_contact_name'] ?? null;
            $emergency_contact_phone = $data['emergency_contact_phone'] ?? null;
            $certifications = $data['certifications'] ?? null;
            $qualification = $data['qualification'] ?? null;
            $license_number = $data['license_number'] ?? null;
            $employment_type = $data['employment_type'] ?? null;
            $is_active = $data['is_active'] ?? 1;
            
            $stmt->bindParam(':emergency_contact_name', $emergency_contact_name);
            $stmt->bindParam(':emergency_contact_phone', $emergency_contact_phone);
            $stmt->bindParam(':certifications', $certifications);
            $stmt->bindParam(':qualification', $qualification);
            $stmt->bindParam(':license_number', $license_number);
            $stmt->bindParam(':employment_type', $employment_type);
            $stmt->bindParam(':is_active', $is_active);

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

            if (!empty($validationErrors)) {
                return ['success' => false, 'message' => implode(', ', $validationErrors)];
            }

            // Set start_time and end_time based on shift_type
            $shiftTimes = [
                'Morning' => ['start' => '06:00:00', 'end' => '14:00:00'],
                'Evening' => ['start' => '14:00:00', 'end' => '22:00:00'],
                'Night' => ['start' => '22:00:00', 'end' => '06:00:00']
            ];

            $start_time = $data['start_time'] ?? $shiftTimes[$data['shift_type']]['start'];
            $end_time = $data['end_time'] ?? $shiftTimes[$data['shift_type']]['end'];

            $sql = "INSERT INTO staff_shifts (staff_id, shift_date, shift_type, start_time, end_time, assigned_ward) 
                    VALUES (:staff_id, :shift_date, :shift_type, :start_time, :end_time, :assigned_ward)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':staff_id', $data['staff_id']);
            $stmt->bindParam(':shift_date', $data['shift_date']);
            $stmt->bindParam(':shift_type', $data['shift_type']);
            $stmt->bindParam(':start_time', $start_time);
            $stmt->bindParam(':end_time', $end_time);
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
            $sql = "SELECT ss.*, s.first_name, s.last_name, s.role 
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
