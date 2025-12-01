<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Validation.php';

class Telemedicine {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Telemedicine Sessions
    public function createSession($data) {
        try {
            $validationErrors = [];

            if (empty($data['patient_id'])) {
                $validationErrors[] = 'Patient ID is required';
            }
            if (empty($data['doctor_id'])) {
                $validationErrors[] = 'Doctor ID is required';
            }
            if (empty($data['scheduled_time'])) {
                $validationErrors[] = 'Scheduled time is required';
            }

            if (!empty($validationErrors)) {
                return ['success' => false, 'message' => implode(', ', $validationErrors)];
            }

            $sql = "INSERT INTO telemedicine_sessions (patient_id, doctor_id, scheduled_time, duration_minutes, session_type, meeting_link, status) 
                    VALUES (:patient_id, :doctor_id, :scheduled_time, :duration_minutes, :session_type, :meeting_link, :status)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':patient_id', $data['patient_id']);
            $stmt->bindParam(':doctor_id', $data['doctor_id']);
            $stmt->bindParam(':scheduled_time', $data['scheduled_time']);
            $duration_minutes = $data['duration_minutes'] ?? 30;
            $stmt->bindParam(':duration_minutes', $duration_minutes);
            $session_type = $data['session_type'] ?? 'Video';
            $stmt->bindParam(':session_type', $session_type);
            $meeting_link = $data['meeting_link'] ?? null;
            $stmt->bindParam(':meeting_link', $meeting_link);
            $status = 'Scheduled';
            $stmt->bindParam(':status', $status);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Telemedicine session created successfully', 'id' => $this->conn->lastInsertId()];
            }
            return ['success' => false, 'message' => 'Failed to create telemedicine session'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function getAllSessions() {
        try {
            $sql = "SELECT ts.*, 
                    p.first_name as patient_first_name, p.last_name as patient_last_name,
                    d.first_name as doctor_first_name, d.last_name as doctor_last_name, d.specialization
                    FROM telemedicine_sessions ts
                    JOIN patients p ON ts.patient_id = p.id
                    JOIN doctors d ON ts.doctor_id = d.id
                    ORDER BY ts.scheduled_time DESC";
            $stmt = $this->conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getSessionById($id) {
        try {
            $sql = "SELECT ts.*, 
                    p.first_name as patient_first_name, p.last_name as patient_last_name, p.phone as patient_phone,
                    d.first_name as doctor_first_name, d.last_name as doctor_last_name, d.specialization
                    FROM telemedicine_sessions ts
                    JOIN patients p ON ts.patient_id = p.id
                    JOIN doctors d ON ts.doctor_id = d.id
                    WHERE ts.id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    public function updateSession($id, $data) {
        try {
            $sql = "UPDATE telemedicine_sessions SET 
                    scheduled_time = :scheduled_time,
                    duration_minutes = :duration_minutes,
                    session_type = :session_type,
                    meeting_link = :meeting_link,
                    status = :status
                    WHERE id = :id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':scheduled_time', $data['scheduled_time']);
            $stmt->bindParam(':duration_minutes', $data['duration_minutes']);
            $stmt->bindParam(':session_type', $data['session_type']);
            $stmt->bindParam(':meeting_link', $data['meeting_link']);
            $stmt->bindParam(':status', $data['status']);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Session updated successfully'];
            }
            return ['success' => false, 'message' => 'Failed to update session'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function completeSession($id, $notes) {
        try {
            $sql = "UPDATE telemedicine_sessions SET 
                    actual_start_time = COALESCE(actual_start_time, :actual_start_time),
                    actual_end_time = :actual_end_time,
                    session_notes = :session_notes,
                    status = 'Completed'
                    WHERE id = :id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $actual_time = date('Y-m-d H:i:s');
            $stmt->bindParam(':actual_start_time', $actual_time);
            $stmt->bindParam(':actual_end_time', $actual_time);
            $stmt->bindParam(':session_notes', $notes);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Session completed successfully'];
            }
            return ['success' => false, 'message' => 'Failed to complete session'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    // Remote Monitoring
    public function addMonitoring($data) {
        try {
            $validationErrors = [];

            if (empty($data['patient_id'])) {
                $validationErrors[] = 'Patient ID is required';
            }
            if (empty($data['vital_type'])) {
                $validationErrors[] = 'Vital type is required';
            }
            if (empty($data['value'])) {
                $validationErrors[] = 'Value is required';
            }

            if (!empty($validationErrors)) {
                return ['success' => false, 'message' => implode(', ', $validationErrors)];
            }

            $sql = "INSERT INTO remote_monitoring (patient_id, vital_type, value, unit, recorded_time, device_id, notes) 
                    VALUES (:patient_id, :vital_type, :value, :unit, :recorded_time, :device_id, :notes)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':patient_id', $data['patient_id']);
            $stmt->bindParam(':vital_type', $data['vital_type']);
            $stmt->bindParam(':value', $data['value']);
            $unit = $data['unit'] ?? null;
            $stmt->bindParam(':unit', $unit);
            $recorded_time = $data['recorded_time'] ?? date('Y-m-d H:i:s');
            $stmt->bindParam(':recorded_time', $recorded_time);
            $device_id = $data['device_id'] ?? null;
            $stmt->bindParam(':device_id', $device_id);
            $notes = $data['notes'] ?? null;
            $stmt->bindParam(':notes', $notes);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Monitoring data added successfully', 'id' => $this->conn->lastInsertId()];
            }
            return ['success' => false, 'message' => 'Failed to add monitoring data'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function getMonitoringByPatient($patientId, $vitalType = null, $days = 30) {
        try {
            $sql = "SELECT * FROM remote_monitoring 
                    WHERE patient_id = :patient_id";
            
            if ($vitalType) {
                $sql .= " AND vital_type = :vital_type";
            }
            
            $sql .= " AND recorded_time >= DATE('now', '-{$days} days')
                     ORDER BY recorded_time DESC";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':patient_id', $patientId);
            if ($vitalType) {
                $stmt->bindParam(':vital_type', $vitalType);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    // Telemedicine Prescriptions
    public function createPrescription($data) {
        try {
            $validationErrors = [];

            if (empty($data['session_id'])) {
                $validationErrors[] = 'Session ID is required';
            }
            if (empty($data['patient_id'])) {
                $validationErrors[] = 'Patient ID is required';
            }
            if (empty($data['doctor_id'])) {
                $validationErrors[] = 'Doctor ID is required';
            }
            if (empty($data['medication_name'])) {
                $validationErrors[] = 'Medication name is required';
            }

            if (!empty($validationErrors)) {
                return ['success' => false, 'message' => implode(', ', $validationErrors)];
            }

            $sql = "INSERT INTO telemedicine_prescriptions (session_id, patient_id, doctor_id, medication_name, dosage, frequency, duration_days, instructions, prescribed_date, is_sent_to_pharmacy) 
                    VALUES (:session_id, :patient_id, :doctor_id, :medication_name, :dosage, :frequency, :duration_days, :instructions, :prescribed_date, :is_sent_to_pharmacy)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':session_id', $data['session_id']);
            $stmt->bindParam(':patient_id', $data['patient_id']);
            $stmt->bindParam(':doctor_id', $data['doctor_id']);
            $stmt->bindParam(':medication_name', $data['medication_name']);
            $dosage = $data['dosage'] ?? null;
            $stmt->bindParam(':dosage', $dosage);
            $frequency = $data['frequency'] ?? null;
            $stmt->bindParam(':frequency', $frequency);
            $duration_days = $data['duration_days'] ?? null;
            $stmt->bindParam(':duration_days', $duration_days);
            $instructions = $data['instructions'] ?? null;
            $stmt->bindParam(':instructions', $instructions);
            $prescribed_date = $data['prescribed_date'] ?? date('Y-m-d');
            $stmt->bindParam(':prescribed_date', $prescribed_date);
            $is_sent_to_pharmacy = $data['is_sent_to_pharmacy'] ?? 0;
            $stmt->bindParam(':is_sent_to_pharmacy', $is_sent_to_pharmacy);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Prescription created successfully', 'id' => $this->conn->lastInsertId()];
            }
            return ['success' => false, 'message' => 'Failed to create prescription'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function getPrescriptionsBySession($sessionId) {
        try {
            $sql = "SELECT tp.*, 
                    p.first_name as patient_first_name, p.last_name as patient_last_name,
                    d.first_name as doctor_first_name, d.last_name as doctor_last_name
                    FROM telemedicine_prescriptions tp
                    JOIN patients p ON tp.patient_id = p.id
                    JOIN doctors d ON tp.doctor_id = d.id
                    WHERE tp.session_id = :session_id
                    ORDER BY tp.prescribed_date DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':session_id', $sessionId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getPrescriptionsByPatient($patientId) {
        try {
            $sql = "SELECT tp.*, 
                    d.first_name as doctor_first_name, d.last_name as doctor_last_name,
                    ts.scheduled_time as session_date
                    FROM telemedicine_prescriptions tp
                    JOIN doctors d ON tp.doctor_id = d.id
                    LEFT JOIN telemedicine_sessions ts ON tp.session_id = ts.id
                    WHERE tp.patient_id = :patient_id
                    ORDER BY tp.prescribed_date DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':patient_id', $patientId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function sendToPharmacy($id) {
        try {
            $sql = "UPDATE telemedicine_prescriptions SET is_sent_to_pharmacy = 1 WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Prescription sent to pharmacy successfully'];
            }
            return ['success' => false, 'message' => 'Failed to send prescription'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
}
