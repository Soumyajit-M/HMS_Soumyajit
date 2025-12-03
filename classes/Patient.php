<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Validation.php';

class Patient {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function createPatient($data) {
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

            // Validate emergency contact phone (if provided)
            if (!empty($data['emergency_contact_phone'])) {
                $emergencyPhoneValidation = Validation::validatePhone($data['emergency_contact_phone']);
                if (!$emergencyPhoneValidation['valid']) {
                    $validationErrors[] = 'Emergency contact phone: ' . $emergencyPhoneValidation['message'];
                }
            }

            // Validate emergency contact email (if provided)
            if (!empty($data['emergency_contact_email'])) {
                $emergencyEmailValidation = Validation::validateEmail($data['emergency_contact_email']);
                if (!$emergencyEmailValidation['valid']) {
                    $validationErrors[] = 'Emergency contact email: ' . $emergencyEmailValidation['message'];
                }
            }

            // Validate date of birth
            $dobValidation = Validation::validateRequired($data['date_of_birth'], 'date of birth');
            if (!$dobValidation['valid']) {
                $validationErrors[] = $dobValidation['message'];
            }

            // Validate gender
            $genderValidation = Validation::validateRequired($data['gender'], 'gender');
            if (!$genderValidation['valid']) {
                $validationErrors[] = $genderValidation['message'];
            }

            if (!empty($validationErrors)) {
                return ['success' => false, 'message' => implode(', ', $validationErrors)];
            }

            // Generate unique patient ID
            $patientId = 'PAT' . uniqid();

            $query = "INSERT INTO patients (patient_id, first_name, last_name, email, phone,
                     date_of_birth, gender, address, emergency_contact_name, emergency_contact_phone,
                     emergency_contact_email, blood_type, allergies, medical_history, insurance_provider, insurance_number)
                     VALUES (:patient_id, :first_name, :last_name, :email, :phone, :date_of_birth,
                     :gender, :address, :emergency_contact_name, :emergency_contact_phone, :emergency_contact_email, :blood_type,
                     :allergies, :medical_history, :insurance_provider, :insurance_number)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':patient_id', $patientId);
            $stmt->bindParam(':first_name', $data['first_name']);
            $stmt->bindParam(':last_name', $data['last_name']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':phone', $data['phone']);
            $stmt->bindParam(':date_of_birth', $data['date_of_birth']);
            $stmt->bindParam(':gender', $data['gender']);
            $stmt->bindParam(':address', $data['address']);
            $stmt->bindParam(':emergency_contact_name', $data['emergency_contact_name']);
            $stmt->bindParam(':emergency_contact_phone', $data['emergency_contact_phone']);
            $stmt->bindParam(':emergency_contact_email', $data['emergency_contact_email']);
            $stmt->bindParam(':blood_type', $data['blood_type']);
            $stmt->bindParam(':allergies', $data['allergies']);
            $stmt->bindParam(':medical_history', $data['medical_history']);
            $stmt->bindParam(':insurance_provider', $data['insurance_provider']);
            $stmt->bindParam(':insurance_number', $data['insurance_number']);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Patient created successfully', 'patient_id' => $patientId];
            } else {
                return ['success' => false, 'message' => 'Failed to create patient: ' . implode(', ', $stmt->errorInfo())];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function getPatient($id) {
        try {
            $query = "SELECT * FROM patients WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getPatientByPatientId($patientId) {
        try {
            $query = "SELECT * FROM patients WHERE patient_id = :patient_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':patient_id', $patientId);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getAllPatients($limit = null, $offset = 0) {
        try {
            $query = "SELECT * FROM patients ORDER BY created_at DESC";
            if ($limit) {
                $query .= " LIMIT :limit OFFSET :offset";
            }
            $stmt = $this->conn->prepare($query);
            if ($limit) {
                $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            }
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function searchPatients($searchTerm) {
        try {
            $query = "SELECT * FROM patients WHERE 
                     first_name LIKE :search OR 
                     last_name LIKE :search OR 
                     patient_id LIKE :search OR 
                     phone LIKE :search OR 
                     email LIKE :search
                     ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($query);
            $searchTerm = '%' . $searchTerm . '%';
            $stmt->bindParam(':search', $searchTerm);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function updatePatient($id, $data) {
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

            // Validate date of birth
            $dobValidation = Validation::validateRequired($data['date_of_birth'], 'date of birth');
            if (!$dobValidation['valid']) {
                $validationErrors[] = $dobValidation['message'];
            }

            // Validate gender
            $genderValidation = Validation::validateRequired($data['gender'], 'gender');
            if (!$genderValidation['valid']) {
                $validationErrors[] = $genderValidation['message'];
            }

            if (!empty($validationErrors)) {
                return ['success' => false, 'message' => implode(', ', $validationErrors)];
            }

            $query = "UPDATE patients SET first_name = :first_name, last_name = :last_name,
                     email = :email, phone = :phone, date_of_birth = :date_of_birth,
                     gender = :gender, address = :address, emergency_contact_name = :emergency_contact_name,
                     emergency_contact_phone = :emergency_contact_phone, emergency_contact_email = :emergency_contact_email,
                     blood_type = :blood_type, allergies = :allergies, medical_history = :medical_history,
                     insurance_provider = :insurance_provider, insurance_number = :insurance_number
                     WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':first_name', $data['first_name']);
            $stmt->bindParam(':last_name', $data['last_name']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':phone', $data['phone']);
            $stmt->bindParam(':date_of_birth', $data['date_of_birth']);
            $stmt->bindParam(':gender', $data['gender']);
            $stmt->bindParam(':address', $data['address']);
            $stmt->bindParam(':emergency_contact_name', $data['emergency_contact_name']);
            $stmt->bindParam(':emergency_contact_phone', $data['emergency_contact_phone']);
            $stmt->bindParam(':emergency_contact_email', $data['emergency_contact_email']);
            $stmt->bindParam(':blood_type', $data['blood_type']);
            $stmt->bindParam(':allergies', $data['allergies']);
            $stmt->bindParam(':medical_history', $data['medical_history']);
            $stmt->bindParam(':insurance_provider', $data['insurance_provider']);
            $stmt->bindParam(':insurance_number', $data['insurance_number']);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Patient updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to update patient'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function deletePatient($id) {
        try {
            $query = "DELETE FROM patients WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Patient deleted successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to delete patient'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function getPatientStats() {
        try {
            $from30 = date('Y-m-d', strtotime('-30 days'));
            $query = "SELECT
                        COUNT(*) as total_patients,
                        SUM(CASE WHEN gender = 'male' THEN 1 ELSE 0 END) as male_patients,
                        SUM(CASE WHEN gender = 'female' THEN 1 ELSE 0 END) as female_patients,
                        SUM(CASE WHEN created_at >= :from30 THEN 1 ELSE 0 END) as new_patients_30_days
                     FROM patients";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':from30', $from30);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getTotalPatients() {
        try {
            $query = "SELECT COUNT(*) as count FROM patients";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            return $result['count'];
        } catch (PDOException $e) {
            return 0;
        }
    }
}
?>
