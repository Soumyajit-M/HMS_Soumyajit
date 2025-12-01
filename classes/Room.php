<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Validation.php';

class Room {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Wards
    public function createWard($data) {
        try {
            $validationErrors = [];

            if (empty($data['ward_name'])) {
                $validationErrors[] = 'Ward name is required';
            }
            if (empty($data['ward_type'])) {
                $validationErrors[] = 'Ward type is required';
            }

            if (!empty($validationErrors)) {
                return ['success' => false, 'message' => implode(', ', $validationErrors)];
            }

            $sql = "INSERT INTO wards (ward_name, ward_type, floor_number, total_beds, available_beds) 
                    VALUES (:ward_name, :ward_type, :floor_number, :total_beds, :available_beds)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':ward_name', $data['ward_name']);
            $stmt->bindParam(':ward_type', $data['ward_type']);
            // Accept both 'floor' and 'floor_number' from form
            $floor_number = $data['floor_number'] ?? $data['floor'] ?? null;
            $stmt->bindParam(':floor_number', $floor_number);
            // Accept both 'capacity' and 'total_beds' from form
            $total_beds = $data['total_beds'] ?? $data['capacity'] ?? 0;
            $stmt->bindParam(':total_beds', $total_beds);
            $available_beds = $data['available_beds'] ?? $total_beds;
            $stmt->bindParam(':available_beds', $available_beds);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Ward created successfully', 'id' => $this->conn->lastInsertId()];
            }
            return ['success' => false, 'message' => 'Failed to create ward'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function getAllWards() {
        try {
            $sql = "SELECT * FROM wards ORDER BY floor_number, ward_name";
            $stmt = $this->conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getWardById($id) {
        try {
            $sql = "SELECT * FROM wards WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    public function updateWard($id, $data) {
        try {
            $sql = "UPDATE wards SET 
                    ward_name = :ward_name,
                    ward_type = :ward_type,
                    floor_number = :floor_number,
                    total_beds = :total_beds,
                    available_beds = :available_beds
                    WHERE id = :id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':ward_name', $data['ward_name']);
            $stmt->bindParam(':ward_type', $data['ward_type']);
            $floor_number = $data['floor_number'] ?? $data['floor'] ?? null;
            $stmt->bindParam(':floor_number', $floor_number);
            $total_beds = $data['total_beds'] ?? $data['capacity'] ?? 0;
            $stmt->bindParam(':total_beds', $total_beds);
            $available_beds = $data['available_beds'] ?? $total_beds;
            $stmt->bindParam(':available_beds', $available_beds);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Ward updated successfully'];
            }
            return ['success' => false, 'message' => 'Failed to update ward'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    // Rooms
    public function createRoom($data) {
        try {
            $validationErrors = [];

            if (empty($data['ward_id'])) {
                $validationErrors[] = 'Ward ID is required';
            }
            if (empty($data['room_number'])) {
                $validationErrors[] = 'Room number is required';
            }
            if (empty($data['room_type'])) {
                $validationErrors[] = 'Room type is required';
            }

            if (!empty($validationErrors)) {
                return ['success' => false, 'message' => implode(', ', $validationErrors)];
            }

            $sql = "INSERT INTO rooms (ward_id, room_number, room_type, total_beds, available_beds, daily_rate, status) 
                    VALUES (:ward_id, :room_number, :room_type, :total_beds, :available_beds, :daily_rate, :status)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':ward_id', $data['ward_id']);
            $stmt->bindParam(':room_number', $data['room_number']);
            $stmt->bindParam(':room_type', $data['room_type']);
            // Accept both 'bed_count' and 'total_beds' from form
            $total_beds = $data['total_beds'] ?? $data['bed_count'] ?? 1;
            $stmt->bindParam(':total_beds', $total_beds);
            $available_beds = $data['available_beds'] ?? $total_beds;
            $stmt->bindParam(':available_beds', $available_beds);
            $daily_rate = $data['daily_rate'] ?? null;
            $stmt->bindParam(':daily_rate', $daily_rate);
            $status = $data['status'] ?? 'available';
            $stmt->bindParam(':status', $status);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Room created successfully', 'id' => $this->conn->lastInsertId()];
            }
            return ['success' => false, 'message' => 'Failed to create room'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function getAllRooms() {
        try {
            $sql = "SELECT r.*, w.ward_name, w.ward_type 
                    FROM rooms r
                    LEFT JOIN wards w ON r.ward_id = w.id
                    ORDER BY w.ward_name, r.room_number";
            $stmt = $this->conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getRoomsByWard($wardId) {
        try {
            $sql = "SELECT * FROM rooms WHERE ward_id = :ward_id ORDER BY room_number";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':ward_id', $wardId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function updateRoom($id, $data) {
        try {
            $sql = "UPDATE rooms SET 
                    room_number = :room_number,
                    room_type = :room_type,
                    total_beds = :total_beds,
                    available_beds = :available_beds,
                    daily_rate = :daily_rate,
                    status = :status
                    WHERE id = :id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':room_number', $data['room_number']);
            $stmt->bindParam(':room_type', $data['room_type']);
            $total_beds = $data['total_beds'] ?? $data['bed_count'] ?? 1;
            $stmt->bindParam(':total_beds', $total_beds);
            $available_beds = $data['available_beds'] ?? $total_beds;
            $stmt->bindParam(':available_beds', $available_beds);
            $daily_rate = $data['daily_rate'] ?? null;
            $stmt->bindParam(':daily_rate', $daily_rate);
            $status = $data['status'] ?? 'available';
            $stmt->bindParam(':status', $status);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Room updated successfully'];
            }
            return ['success' => false, 'message' => 'Failed to update room'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    // Bed Assignments
    public function assignBed($data) {
        try {
            $validationErrors = [];

            if (empty($data['room_id'])) {
                $validationErrors[] = 'Room ID is required';
            }
            if (empty($data['patient_id'])) {
                $validationErrors[] = 'Patient ID is required';
            }
            if (empty($data['bed_number'])) {
                $validationErrors[] = 'Bed number is required';
            }
            if (empty($data['admission_date'])) {
                $validationErrors[] = 'Admission date is required';
            }

            if (!empty($validationErrors)) {
                return ['success' => false, 'message' => implode(', ', $validationErrors)];
            }

            $sql = "INSERT INTO bed_assignments (room_id, patient_id, bed_number, admission_date, expected_discharge_date, attending_doctor_id, status) 
                    VALUES (:room_id, :patient_id, :bed_number, :admission_date, :expected_discharge_date, :attending_doctor_id, :status)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':room_id', $data['room_id']);
            $stmt->bindParam(':patient_id', $data['patient_id']);
            $stmt->bindParam(':bed_number', $data['bed_number']);
            $stmt->bindParam(':admission_date', $data['admission_date']);
            $expected_discharge_date = $data['expected_discharge_date'] ?? null;
            $stmt->bindParam(':expected_discharge_date', $expected_discharge_date);
            $attending_doctor_id = $data['attending_doctor_id'] ?? null;
            $stmt->bindParam(':attending_doctor_id', $attending_doctor_id);
            $status = $data['status'] ?? 'occupied';
            $stmt->bindParam(':status', $status);

            if ($stmt->execute()) {
                // Update room occupation status
                $this->updateRoomOccupancy($data['room_id']);
                return ['success' => true, 'message' => 'Bed assigned successfully', 'id' => $this->conn->lastInsertId()];
            }
            return ['success' => false, 'message' => 'Failed to assign bed'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function dischargeBed($id) {
        try {
            $sql = "UPDATE bed_assignments SET 
                    discharge_date = :discharge_date,
                    status = 'available'
                    WHERE id = :id";
            
            $stmt = $this->conn->prepare($sql);
            $discharge_date = date('Y-m-d H:i:s');
            $stmt->bindParam(':discharge_date', $discharge_date);
            $stmt->bindParam(':id', $id);

            if ($stmt->execute()) {
                // Get room_id and update occupancy
                $assignment = $this->getBedAssignmentById($id);
                if ($assignment) {
                    $this->updateRoomOccupancy($assignment['room_id']);
                }
                return ['success' => true, 'message' => 'Patient discharged successfully'];
            }
            return ['success' => false, 'message' => 'Failed to discharge patient'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function getAllBedAssignments() {
        try {
            $sql = "SELECT ba.*, 
                    p.first_name || ' ' || p.last_name AS patient_full_name,
                    p.first_name AS patient_first_name,
                    p.last_name AS patient_last_name,
                    r.room_number, r.room_type,
                    w.ward_name
                    FROM bed_assignments ba
                    LEFT JOIN patients p ON ba.patient_id = p.id
                    LEFT JOIN rooms r ON ba.room_id = r.id
                    LEFT JOIN wards w ON r.ward_id = w.id
                    ORDER BY ba.admission_date DESC";
            $stmt = $this->conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getBedAssignmentById($id) {
        try {
            $sql = "SELECT * FROM bed_assignments WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    private function updateRoomOccupancy($roomId) {
        try {
            // Count beds that are currently occupied or reserved
            $sql = "SELECT COUNT(*) as active_count FROM bed_assignments 
                    WHERE room_id = :room_id AND status IN ('occupied', 'reserved')";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':room_id', $roomId);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $activeCount = (int)($result['active_count'] ?? 0);

            // Fetch room capacity information
            $roomSql = "SELECT total_beds, available_beds FROM rooms WHERE id = :room_id";
            $roomStmt = $this->conn->prepare($roomSql);
            $roomStmt->bindParam(':room_id', $roomId);
            $roomStmt->execute();
            $room = $roomStmt->fetch(PDO::FETCH_ASSOC);

            if (!$room) {
                return;
            }

            $totalBeds = (int)($room['total_beds'] ?? 0);
            if ($totalBeds <= 0) {
                $totalBeds = 1; // Prevent division by zero / negative values
            }

            $availableBeds = max($totalBeds - $activeCount, 0);

            // Determine status based on availability
            if ($activeCount >= $totalBeds) {
                $status = 'occupied';
            } elseif ($activeCount > 0) {
                $status = 'reserved';
            } else {
                $status = 'available';
            }

            $updateSql = "UPDATE rooms 
                          SET available_beds = :available_beds,
                              status = :status
                          WHERE id = :room_id";
            $updateStmt = $this->conn->prepare($updateSql);
            $updateStmt->bindParam(':available_beds', $availableBeds, PDO::PARAM_INT);
            $updateStmt->bindParam(':status', $status);
            $updateStmt->bindParam(':room_id', $roomId);
            $updateStmt->execute();
        } catch (PDOException $e) {
            // Silently fail
        }
    }

    public function deleteWard($id) {
        try {
            // Check if ward has any rooms
            $checkSql = "SELECT COUNT(*) as room_count FROM rooms WHERE ward_id = :id";
            $checkStmt = $this->conn->prepare($checkSql);
            $checkStmt->bindParam(':id', $id);
            $checkStmt->execute();
            $result = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['room_count'] > 0) {
                return ['success' => false, 'message' => 'Cannot delete ward with rooms. Delete rooms first.'];
            }

            $sql = "DELETE FROM wards WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return ['success' => true, 'message' => 'Ward deleted successfully'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    public function deleteRoom($id) {
        try {
            // Check if room has any active bed assignments
            $checkSql = "SELECT COUNT(*) as assignment_count FROM bed_assignments 
                         WHERE room_id = :id AND status = 'occupied'";
            $checkStmt = $this->conn->prepare($checkSql);
            $checkStmt->bindParam(':id', $id);
            $checkStmt->execute();
            $result = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['assignment_count'] > 0) {
                return ['success' => false, 'message' => 'Cannot delete room with occupied beds. Discharge patients first.'];
            }

            // Delete any bed assignments for this room first
            $deleteBedsSql = "DELETE FROM bed_assignments WHERE room_id = :id";
            $deleteBedsStmt = $this->conn->prepare($deleteBedsSql);
            $deleteBedsStmt->bindParam(':id', $id);
            $deleteBedsStmt->execute();

            $sql = "DELETE FROM rooms WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return ['success' => true, 'message' => 'Room deleted successfully'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    public function getRoomById($id) {
        try {
            $sql = "SELECT * FROM rooms WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }
}
