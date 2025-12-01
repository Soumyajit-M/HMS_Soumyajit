<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Validation.php';

class Schedule {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Doctor Schedules
    public function createSchedule($data) {
        try {
            $validationErrors = [];

            if (empty($data['doctor_id'])) {
                $validationErrors[] = 'Doctor ID is required';
            }
            if (empty($data['day_of_week'])) {
                $validationErrors[] = 'Day of week is required';
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

            $sql = "INSERT INTO doctor_schedules (doctor_id, day_of_week, start_time, end_time, is_available, room_number) 
                    VALUES (:doctor_id, :day_of_week, :start_time, :end_time, :is_available, :room_number)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':doctor_id', $data['doctor_id']);
            $stmt->bindParam(':day_of_week', $data['day_of_week']);
            $stmt->bindParam(':start_time', $data['start_time']);
            $stmt->bindParam(':end_time', $data['end_time']);
            $is_available = $data['is_available'] ?? 1;
            $stmt->bindParam(':is_available', $is_available);
            $room_number = $data['room_number'] ?? null;
            $stmt->bindParam(':room_number', $room_number);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Schedule created successfully', 'id' => $this->conn->lastInsertId()];
            }
            return ['success' => false, 'message' => 'Failed to create schedule'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function getSchedulesByDoctor($doctorId) {
        try {
            $sql = "SELECT * FROM doctor_schedules WHERE doctor_id = :doctor_id ORDER BY 
                    CASE day_of_week
                        WHEN 'Monday' THEN 1
                        WHEN 'Tuesday' THEN 2
                        WHEN 'Wednesday' THEN 3
                        WHEN 'Thursday' THEN 4
                        WHEN 'Friday' THEN 5
                        WHEN 'Saturday' THEN 6
                        WHEN 'Sunday' THEN 7
                    END, start_time";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':doctor_id', $doctorId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getAllSchedules() {
        try {
            $sql = "SELECT ds.*, 
                    u.first_name, u.last_name, 
                    d.specialization 
                    FROM doctor_schedules ds
                    JOIN doctors d ON ds.doctor_id = d.id
                    JOIN users u ON d.user_id = u.id
                    ORDER BY u.last_name, u.first_name, 
                    CASE ds.day_of_week
                        WHEN 'Monday' THEN 1
                        WHEN 'Tuesday' THEN 2
                        WHEN 'Wednesday' THEN 3
                        WHEN 'Thursday' THEN 4
                        WHEN 'Friday' THEN 5
                        WHEN 'Saturday' THEN 6
                        WHEN 'Sunday' THEN 7
                    END";
            $stmt = $this->conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function updateSchedule($id, $data) {
        try {
            $sql = "UPDATE doctor_schedules SET 
                    day_of_week = :day_of_week,
                    start_time = :start_time,
                    end_time = :end_time,
                    is_available = :is_available,
                    room_number = :room_number
                    WHERE id = :id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':day_of_week', $data['day_of_week']);
            $stmt->bindParam(':start_time', $data['start_time']);
            $stmt->bindParam(':end_time', $data['end_time']);
            $stmt->bindParam(':is_available', $data['is_available']);
            $room_number = $data['room_number'] ?? null;
            $stmt->bindParam(':room_number', $room_number);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Schedule updated successfully'];
            }
            return ['success' => false, 'message' => 'Failed to update schedule'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function deleteSchedule($id) {
        try {
            $sql = "DELETE FROM doctor_schedules WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Schedule deleted successfully'];
            }
            return ['success' => false, 'message' => 'Failed to delete schedule'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    // Doctor Leaves
    public function createLeave($data) {
        try {
            $validationErrors = [];

            if (empty($data['doctor_id'])) {
                $validationErrors[] = 'Doctor ID is required';
            }
            if (empty($data['leave_date'])) {
                $validationErrors[] = 'Leave date is required';
            }
            if (empty($data['leave_type'])) {
                $validationErrors[] = 'Leave type is required';
            }

            if (!empty($validationErrors)) {
                return ['success' => false, 'message' => implode(', ', $validationErrors)];
            }

            $sql = "INSERT INTO doctor_leaves (doctor_id, leave_date, leave_type, reason, is_approved) 
                    VALUES (:doctor_id, :leave_date, :leave_type, :reason, :is_approved)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':doctor_id', $data['doctor_id']);
            $stmt->bindParam(':leave_date', $data['leave_date']);
            $stmt->bindParam(':leave_type', $data['leave_type']);
            $reason = $data['reason'] ?? null;
            $stmt->bindParam(':reason', $reason);
            $is_approved = $data['is_approved'] ?? 0;
            $stmt->bindParam(':is_approved', $is_approved);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Leave request created successfully', 'id' => $this->conn->lastInsertId()];
            }
            return ['success' => false, 'message' => 'Failed to create leave request'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function getLeavesByDoctor($doctorId) {
        try {
            $sql = "SELECT * FROM doctor_leaves WHERE doctor_id = :doctor_id ORDER BY leave_date DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':doctor_id', $doctorId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getAllLeaves() {
        try {
            $sql = "SELECT dl.*, 
                    u.first_name, u.last_name, 
                    d.specialization 
                    FROM doctor_leaves dl
                    JOIN doctors d ON dl.doctor_id = d.id
                    JOIN users u ON d.user_id = u.id
                    ORDER BY dl.leave_date DESC";
            $stmt = $this->conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function approveLeave($id) {
        try {
            $sql = "UPDATE doctor_leaves SET is_approved = 1 WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Leave approved successfully'];
            }
            return ['success' => false, 'message' => 'Failed to approve leave'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function deleteLeave($id) {
        try {
            $sql = "DELETE FROM doctor_leaves WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Leave deleted successfully'];
            }
            return ['success' => false, 'message' => 'Failed to delete leave'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
}
