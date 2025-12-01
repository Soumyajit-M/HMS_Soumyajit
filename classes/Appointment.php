<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/AutoBilling.php';

class Appointment {
    private $conn;
    private $autoBilling;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->autoBilling = new AutoBilling();
    }

    public function createAppointment($data) {
        try {
            // Generate unique appointment ID
            $appointmentId = 'APT' . date('Ymd') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
            
            $query = "INSERT INTO appointments (appointment_id, patient_id, doctor_id, department_id, 
                     appointment_date, appointment_time, status, reason, notes, created_by) 
                     VALUES (:appointment_id, :patient_id, :doctor_id, :department_id, 
                     :appointment_date, :appointment_time, :status, :reason, :notes, :created_by)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':appointment_id', $appointmentId);
            $stmt->bindParam(':patient_id', $data['patient_id']);
            $stmt->bindParam(':doctor_id', $data['doctor_id']);
            $stmt->bindParam(':department_id', $data['department_id']);
            $stmt->bindParam(':appointment_date', $data['appointment_date']);
            $stmt->bindParam(':appointment_time', $data['appointment_time']);
            $stmt->bindParam(':status', $data['status']);
            $stmt->bindParam(':reason', $data['reason']);
            $stmt->bindParam(':notes', $data['notes']);
            $stmt->bindParam(':created_by', $data['created_by']);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Appointment created successfully', 'appointment_id' => $appointmentId];
            } else {
                return ['success' => false, 'message' => 'Failed to create appointment'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function getAppointment($id) {
        try {
            $query = "SELECT a.*, p.first_name as patient_first_name, p.last_name as patient_last_name, 
                     p.phone as patient_phone, d.doctor_id, u.first_name as doctor_first_name, 
                     u.last_name as doctor_last_name, dept.name as department_name
                     FROM appointments a 
                     JOIN patients p ON a.patient_id = p.id 
                     JOIN doctors d ON a.doctor_id = d.id 
                     JOIN users u ON d.user_id = u.id 
                     LEFT JOIN departments dept ON a.department_id = dept.id 
                     WHERE a.id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getAllAppointments($limit = null, $offset = 0, $filters = []) {
        try {
            $query = "SELECT a.*, p.first_name as patient_first_name, p.last_name as patient_last_name, 
                     p.phone as patient_phone, d.doctor_id, u.first_name as doctor_first_name, 
                     u.last_name as doctor_last_name, dept.name as department_name
                     FROM appointments a 
                     JOIN patients p ON a.patient_id = p.id 
                     JOIN doctors d ON a.doctor_id = d.id 
                     JOIN users u ON d.user_id = u.id 
                     LEFT JOIN departments dept ON a.department_id = dept.id 
                     WHERE 1=1";
            
            $params = [];
            
            if (!empty($filters['status'])) {
                $query .= " AND a.status = :status";
                $params[':status'] = $filters['status'];
            }
            
            if (!empty($filters['doctor_id'])) {
                $query .= " AND a.doctor_id = :doctor_id";
                $params[':doctor_id'] = $filters['doctor_id'];
            }
            
            if (!empty($filters['date_from'])) {
                $query .= " AND a.appointment_date >= :date_from";
                $params[':date_from'] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $query .= " AND a.appointment_date <= :date_to";
                $params[':date_to'] = $filters['date_to'];
            }
            
            $query .= " ORDER BY a.appointment_date DESC, a.appointment_time DESC";
            
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
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getAppointmentsByDoctor($doctorId, $date = null) {
        try {
            $query = "SELECT a.*, p.first_name as patient_first_name, p.last_name as patient_last_name, 
                     p.phone as patient_phone, p.patient_id
                     FROM appointments a 
                     JOIN patients p ON a.patient_id = p.id 
                     WHERE a.doctor_id = :doctor_id";
            
            $params = [':doctor_id' => $doctorId];
            
            if ($date) {
                $query .= " AND a.appointment_date = :date";
                $params[':date'] = $date;
            }
            
            $query .= " ORDER BY a.appointment_time";
            
            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function updateAppointmentStatus($id, $status) {
        try {
            // Get appointment details first
            $getQuery = "SELECT patient_id, doctor_id FROM appointments WHERE id = :id";
            $getStmt = $this->conn->prepare($getQuery);
            $getStmt->bindParam(':id', $id);
            $getStmt->execute();
            $appointment = $getStmt->fetch(PDO::FETCH_ASSOC);
            
            $query = "UPDATE appointments SET status = :status WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                // Auto-bill consultation when appointment is completed
                if ($status === 'completed' && $appointment) {
                    $this->autoBilling->trackConsultation($appointment['patient_id'], $appointment['doctor_id'], $id);
                }
                return ['success' => true, 'message' => 'Appointment status updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to update appointment status'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function updateAppointment($id, $data) {
        try {
            $query = "UPDATE appointments SET patient_id = :patient_id, doctor_id = :doctor_id, appointment_date = :appointment_date, appointment_time = :appointment_time, status = :status, reason = :reason, notes = :notes WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':patient_id', $data['patient_id']);
            $stmt->bindParam(':doctor_id', $data['doctor_id']);
            $stmt->bindParam(':appointment_date', $data['appointment_date']);
            $stmt->bindParam(':appointment_time', $data['appointment_time']);
            $stmt->bindParam(':status', $data['status']);
            $stmt->bindParam(':reason', $data['reason']);
            $stmt->bindParam(':notes', $data['notes']);
            $stmt->bindParam(':id', $id);

            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getAppointmentStats() {
        try {
            // Use SQLite-compatible date functions (date('now') and date modifiers)
            $query = "SELECT 
                        COUNT(*) as total_appointments,
                        SUM(CASE WHEN status = 'scheduled' THEN 1 ELSE 0 END) as scheduled_appointments,
                        SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_appointments,
                        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_appointments,
                        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_appointments,
                        SUM(CASE WHEN appointment_date = date('now') THEN 1 ELSE 0 END) as today_appointments,
                        SUM(CASE WHEN appointment_date >= date('now') AND appointment_date <= date('now','+7 day') THEN 1 ELSE 0 END) as week_appointments
                     FROM appointments";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function checkAvailability($doctorId, $date, $time) {
        try {
            $query = "SELECT COUNT(*) as count FROM appointments
                     WHERE doctor_id = :doctor_id
                     AND appointment_date = :date
                     AND appointment_time = :time
                     AND status IN ('scheduled', 'confirmed')";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':doctor_id', $doctorId);
            $stmt->bindParam(':date', $date);
            $stmt->bindParam(':time', $time);
            $stmt->execute();
            $result = $stmt->fetch();
            return $result['count'] == 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getTodayAppointments() {
        try {
            $query = "SELECT COUNT(*) as count FROM appointments
                     WHERE appointment_date = date('now')
                     AND status IN ('scheduled', 'confirmed', 'in_progress')";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            return $result['count'];
        } catch (PDOException $e) {
            return 0;
        }
    }

    public function getEmergencyCases() {
        try {
            $query = "SELECT COUNT(*) as count FROM appointments
                     WHERE status = 'scheduled'
                     AND reason LIKE '%emergency%'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            return $result['count'];
        } catch (PDOException $e) {
            return 0;
        }
    }

    public function deleteAppointment($id) {
        if (empty($id)) {
            return false;
        }

        try {
            // Support deleting by numeric primary `id` or by `appointment_id` string
            $query = "DELETE FROM appointments WHERE id = :id OR appointment_id = :appointment_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':appointment_id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    // Backwards-compatible wrapper for API consistency
    public function getAppointmentById($id) {
        return $this->getAppointment($id);
    }
}
?>
