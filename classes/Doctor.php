<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Validation.php';
require_once __DIR__ . '/Auth.php';

class Doctor {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function createDoctor($data) {
        try {
            // Validate required fields
            $validationErrors = [];

            // Validate first name
            $firstNameValidation = Validation::validateRequired($data['first_name'], 'first name');
            if (!$firstNameValidation['valid']) {
                $validationErrors[] = $firstNameValidation['message'];
            }

            // Validate last name
            $lastNameValidation = Validation::validateRequired($data['last_name'], 'last name');
            if (!$lastNameValidation['valid']) {
                $validationErrors[] = $lastNameValidation['message'];
            }

            // Validate email
            $emailValidation = Validation::validateEmail($data['email']);
            if (!$emailValidation['valid']) {
                $validationErrors[] = $emailValidation['message'];
            }

            // Validate phone
            $phoneValidation = Validation::validatePhone($data['phone']);
            if (!$phoneValidation['valid']) {
                $validationErrors[] = $phoneValidation['message'];
            }

            // Validate specialization
            $specializationValidation = Validation::validateRequired($data['specialization'], 'specialization');
            if (!$specializationValidation['valid']) {
                $validationErrors[] = $specializationValidation['message'];
            }

            // Validate experience years
            if (!isset($data['experience_years']) || $data['experience_years'] < 0) {
                $validationErrors[] = 'Experience years must be a non-negative number';
            }

            // Validate consultation fee
            if (!isset($data['consultation_fee']) || $data['consultation_fee'] < 0) {
                $validationErrors[] = 'Consultation fee must be a non-negative number';
            }

            if (!empty($validationErrors)) {
                return ['success' => false, 'message' => implode(', ', $validationErrors)];
            }

            // Generate unique default credentials for doctor
            $baseUsername = strtolower($data['first_name'] . '.' . $data['last_name']);
            $defaultUsername = $baseUsername;
            $counter = 1;

            // Check if username already exists and generate unique one
            while ($this->usernameExists($defaultUsername)) {
                $defaultUsername = $baseUsername . $counter;
                $counter++;
            }

            $defaultPassword = bin2hex(random_bytes(6)); // Secure random password
            $defaultAddress = 'To be updated'; // Default address

            // First create user account with default credentials
            $auth = new Auth();
            $userData = [
                'username' => $defaultUsername,
                'email' => $data['email'],
                'password' => $defaultPassword,
                'role' => 'doctor',
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'phone' => $data['phone'],
                'address' => $defaultAddress,
                'require_password_change' => true // Flag for first login password change
            ];

            $userResult = $auth->register($userData);
            if (!$userResult['success']) {
                return $userResult;
            }

            // Get the created user ID
            $userId = $userResult['user_id'];

            // Generate unique doctor ID
            $doctorId = 'DOC' . uniqid();

            $query = "INSERT INTO doctors (user_id, doctor_id, specialization, qualification,
                     experience_years, consultation_fee, available_days, available_time_start,
                     available_time_end, bio)
                     VALUES (:user_id, :doctor_id, :specialization, :qualification,
                     :experience_years, :consultation_fee, :available_days, :available_time_start,
                     :available_time_end, :bio)";

            $stmt = $this->conn->prepare($query);
            $availableDays = json_encode($data['available_days'] ?? []);
            $availableTimeStart = $data['available_time_start'] ?? null;
            $availableTimeEnd = $data['available_time_end'] ?? null;

            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':doctor_id', $doctorId);
            $stmt->bindParam(':specialization', $data['specialization']);
            $stmt->bindParam(':qualification', $data['qualification']);
            $stmt->bindParam(':experience_years', $data['experience_years']);
            $stmt->bindParam(':consultation_fee', $data['consultation_fee']);
            $stmt->bindParam(':available_days', $availableDays);
            $stmt->bindParam(':available_time_start', $availableTimeStart);
            $stmt->bindParam(':available_time_end', $availableTimeEnd);
            $stmt->bindParam(':bio', $data['bio']);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Doctor created successfully', 'doctor_id' => $doctorId];
            } else {
                return ['success' => false, 'message' => 'Failed to create doctor'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function getDoctor($id) {
        try {
            $query = "SELECT d.*, u.username, u.email, u.first_name, u.last_name, u.phone, u.address 
                     FROM doctors d 
                     JOIN users u ON d.user_id = u.id 
                     WHERE d.id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $doctor = $stmt->fetch();
            if ($doctor) {
                return ['success' => true, 'data' => $doctor];
            } else {
                return ['success' => false, 'message' => 'Doctor not found'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function getAllDoctors($limit = null, $offset = 0) {
        try {
            $query = "SELECT d.*, u.username, u.email, u.first_name, u.last_name, u.phone, u.address, u.is_active
                     FROM doctors d
                     JOIN users u ON d.user_id = u.id
                     ORDER BY d.created_at DESC";
            if ($limit) {
                $query .= " LIMIT :limit OFFSET :offset";
            }
            $stmt = $this->conn->prepare($query);
            if ($limit) {
                $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            }
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result ?: [];
        } catch (PDOException $e) {
            error_log('Database error in getAllDoctors: ' . $e->getMessage());
            return [];
        }
    }

    public function getDoctorsBySpecialization($specialization) {
        try {
            $query = "SELECT d.*, u.first_name, u.last_name, u.phone 
                     FROM doctors d 
                     JOIN users u ON d.user_id = u.id 
                     WHERE d.specialization = :specialization AND u.is_active = 1
                     ORDER BY u.first_name";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':specialization', $specialization);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function updateDoctor($id, $data) {
        try {
            $query = "UPDATE doctors SET specialization = :specialization, qualification = :qualification, 
                     experience_years = :experience_years, consultation_fee = :consultation_fee, 
                     available_days = :available_days, available_time_start = :available_time_start, 
                     available_time_end = :available_time_end, bio = :bio 
                     WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $availableDays = json_encode($data['available_days']);
            $stmt->bindParam(':specialization', $data['specialization']);
            $stmt->bindParam(':qualification', $data['qualification']);
            $stmt->bindParam(':experience_years', $data['experience_years']);
            $stmt->bindParam(':consultation_fee', $data['consultation_fee']);
            $stmt->bindParam(':available_days', $availableDays);
            $stmt->bindParam(':available_time_start', $data['available_time_start']);
            $stmt->bindParam(':available_time_end', $data['available_time_end']);
            $stmt->bindParam(':bio', $data['bio']);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Doctor updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to update doctor'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function getDoctorStats() {
        try {
            $query = "SELECT 
                        COUNT(*) as total_doctors,
                        COUNT(CASE WHEN specialization = 'Cardiology' THEN 1 END) as cardiology_doctors,
                        COUNT(CASE WHEN specialization = 'Neurology' THEN 1 END) as neurology_doctors,
                        COUNT(CASE WHEN specialization = 'Orthopedics' THEN 1 END) as orthopedics_doctors,
                        COUNT(CASE WHEN specialization = 'Pediatrics' THEN 1 END) as pediatrics_doctors
                     FROM doctors d 
                     JOIN users u ON d.user_id = u.id 
                     WHERE u.is_active = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getAvailableDoctors($date, $time) {
        try {
            $dayOfWeek = date('l', strtotime($date));
            
            $query = "SELECT d.*, u.first_name, u.last_name, u.phone 
                     FROM doctors d 
                     JOIN users u ON d.user_id = u.id 
                     WHERE u.is_active = 1 
                     AND :time BETWEEN d.available_time_start AND d.available_time_end
                     AND d.id NOT IN (
                         SELECT doctor_id FROM appointments 
                         WHERE appointment_date = :date AND appointment_time = :time 
                         AND status IN ('scheduled', 'confirmed')
                     )
                     ORDER BY u.first_name";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':time', $time);
            $stmt->bindParam(':date', $date);
            $stmt->execute();
            $doctors = $stmt->fetchAll();
            // Filter available_days in PHP for SQLite compatibility
            $availableDoctors = [];
            foreach ($doctors as $doctor) {
                $days = json_decode($doctor['available_days'], true);
                if (is_array($days) && in_array($dayOfWeek, $days)) {
                    $availableDoctors[] = $doctor;
                }
            }
            return $availableDoctors;
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getDoctorById($id) {
        return $this->getDoctor($id);
    }

    public function createDoctorSimple($data) {
        // Simplified version for API compatibility
        try {
            $query = "INSERT INTO doctors (doctor_id, specialization, qualification,
                     experience_years, consultation_fee, bio)
                     VALUES (:doctor_id, :specialization, :qualification,
                     :experience_years, :consultation_fee, :bio)";

            $doctorId = 'DOC' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':doctor_id', $doctorId);
            $stmt->bindParam(':specialization', $data['specialization']);
            $stmt->bindParam(':qualification', $data['qualification']);
            $stmt->bindParam(':experience_years', $data['experience_years']);
            $stmt->bindParam(':consultation_fee', $data['consultation_fee']);
            $stmt->bindParam(':bio', $data['bio']);

            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function deleteDoctor($id) {
        try {
            $query = "DELETE FROM doctors WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Doctor deleted successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to delete doctor'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    private function usernameExists($username) {
        try {
            $query = "SELECT COUNT(*) as count FROM users WHERE username = :username";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            $result = $stmt->fetch();
            return $result['count'] > 0;
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>
