<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Validation.php';

class Insurance {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Insurance Providers
    public function createProvider($data) {
        try {
            $validationErrors = [];

            if (empty($data['provider_name'])) {
                $validationErrors[] = 'Provider name is required';
            }

            if (!empty($validationErrors)) {
                return ['success' => false, 'message' => implode(', ', $validationErrors)];
            }

            $sql = "INSERT INTO insurance_providers (provider_name, provider_code, contact_person, phone, email, address, is_active) 
                    VALUES (:provider_name, :provider_code, :contact_person, :phone, :email, :address, :is_active)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':provider_name', $data['provider_name']);
            $provider_code = $data['provider_code'] ?? null;
            $stmt->bindParam(':provider_code', $provider_code);
            $contact_person = $data['contact_person'] ?? null;
            $stmt->bindParam(':contact_person', $contact_person);
            $phone = $data['phone'] ?? null;
            $stmt->bindParam(':phone', $phone);
            $email = $data['email'] ?? null;
            $stmt->bindParam(':email', $email);
            $address = $data['address'] ?? null;
            $stmt->bindParam(':address', $address);
            $is_active = $data['is_active'] ?? 1;
            $stmt->bindParam(':is_active', $is_active);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Insurance provider created successfully', 'id' => $this->conn->lastInsertId()];
            }
            return ['success' => false, 'message' => 'Failed to create insurance provider'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function getAllProviders() {
        try {
            $sql = "SELECT * FROM insurance_providers WHERE is_active = 1 ORDER BY provider_name";
            $stmt = $this->conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getProviderById($id) {
        try {
            $sql = "SELECT * FROM insurance_providers WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    public function updateProvider($id, $data) {
        try {
            $sql = "UPDATE insurance_providers SET 
                    provider_name = :provider_name,
                    provider_code = :provider_code,
                    contact_person = :contact_person,
                    phone = :phone,
                    email = :email,
                    address = :address,
                    is_active = :is_active
                    WHERE id = :id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':provider_name', $data['provider_name']);
            $stmt->bindParam(':provider_code', $data['provider_code']);
            $stmt->bindParam(':contact_person', $data['contact_person']);
            $stmt->bindParam(':phone', $data['phone']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':address', $data['address']);
            $stmt->bindParam(':is_active', $data['is_active']);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Insurance provider updated successfully'];
            }
            return ['success' => false, 'message' => 'Failed to update insurance provider'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    // Patient Insurance
    public function addPatientInsurance($data) {
        try {
            $validationErrors = [];

            if (empty($data['patient_id'])) {
                $validationErrors[] = 'Patient ID is required';
            }
            if (empty($data['provider_id'])) {
                $validationErrors[] = 'Insurance provider is required';
            }
            if (empty($data['policy_number'])) {
                $validationErrors[] = 'Policy number is required';
            }

            if (!empty($validationErrors)) {
                return ['success' => false, 'message' => implode(', ', $validationErrors)];
            }

            $sql = "INSERT INTO patient_insurance (patient_id, provider_id, policy_number, policy_holder_name, relationship_to_patient, coverage_start_date, coverage_end_date, coverage_percentage, is_primary) 
                    VALUES (:patient_id, :provider_id, :policy_number, :policy_holder_name, :relationship_to_patient, :coverage_start_date, :coverage_end_date, :coverage_percentage, :is_primary)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':patient_id', $data['patient_id']);
            $stmt->bindParam(':provider_id', $data['provider_id']);
            $stmt->bindParam(':policy_number', $data['policy_number']);
            $policy_holder_name = $data['policy_holder_name'] ?? null;
            $stmt->bindParam(':policy_holder_name', $policy_holder_name);
            $relationship_to_patient = $data['relationship_to_patient'] ?? 'Self';
            $stmt->bindParam(':relationship_to_patient', $relationship_to_patient);
            $coverage_start_date = $data['coverage_start_date'] ?? null;
            $stmt->bindParam(':coverage_start_date', $coverage_start_date);
            $coverage_end_date = $data['coverage_end_date'] ?? null;
            $stmt->bindParam(':coverage_end_date', $coverage_end_date);
            $coverage_percentage = $data['coverage_percentage'] ?? 80;
            $stmt->bindParam(':coverage_percentage', $coverage_percentage);
            $is_primary = $data['is_primary'] ?? 1;
            $stmt->bindParam(':is_primary', $is_primary);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Patient insurance added successfully', 'id' => $this->conn->lastInsertId()];
            }
            return ['success' => false, 'message' => 'Failed to add patient insurance'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function getPatientInsurance($patientId) {
        try {
            $sql = "SELECT pi.*, ip.provider_name, ip.provider_code
                    FROM patient_insurance pi
                    JOIN insurance_providers ip ON pi.provider_id = ip.id
                    WHERE pi.patient_id = :patient_id
                    ORDER BY pi.is_primary DESC, pi.coverage_start_date DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':patient_id', $patientId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function updatePatientInsurance($id, $data) {
        try {
            $sql = "UPDATE patient_insurance SET 
                    policy_number = :policy_number,
                    policy_holder_name = :policy_holder_name,
                    relationship_to_patient = :relationship_to_patient,
                    coverage_start_date = :coverage_start_date,
                    coverage_end_date = :coverage_end_date,
                    coverage_percentage = :coverage_percentage,
                    is_primary = :is_primary
                    WHERE id = :id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':policy_number', $data['policy_number']);
            $stmt->bindParam(':policy_holder_name', $data['policy_holder_name']);
            $stmt->bindParam(':relationship_to_patient', $data['relationship_to_patient']);
            $stmt->bindParam(':coverage_start_date', $data['coverage_start_date']);
            $stmt->bindParam(':coverage_end_date', $data['coverage_end_date']);
            $stmt->bindParam(':coverage_percentage', $data['coverage_percentage']);
            $stmt->bindParam(':is_primary', $data['is_primary']);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Patient insurance updated successfully'];
            }
            return ['success' => false, 'message' => 'Failed to update patient insurance'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    // Insurance Claims
    public function createClaim($data) {
        try {
            $validationErrors = [];

            if (empty($data['patient_insurance_id'])) {
                $validationErrors[] = 'Patient insurance is required';
            }
            if (empty($data['billing_id'])) {
                $validationErrors[] = 'Billing ID is required';
            }
            if (empty($data['claim_amount'])) {
                $validationErrors[] = 'Claim amount is required';
            }

            if (!empty($validationErrors)) {
                return ['success' => false, 'message' => implode(', ', $validationErrors)];
            }

            $sql = "INSERT INTO insurance_claims (patient_insurance_id, billing_id, claim_number, claim_amount, filed_date, status, notes) 
                    VALUES (:patient_insurance_id, :billing_id, :claim_number, :claim_amount, :filed_date, :status, :notes)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':patient_insurance_id', $data['patient_insurance_id']);
            $stmt->bindParam(':billing_id', $data['billing_id']);
            $claim_number = $data['claim_number'] ?? 'CLM' . time();
            $stmt->bindParam(':claim_number', $claim_number);
            $stmt->bindParam(':claim_amount', $data['claim_amount']);
            $filed_date = $data['filed_date'] ?? date('Y-m-d');
            $stmt->bindParam(':filed_date', $filed_date);
            $status = 'Pending';
            $stmt->bindParam(':status', $status);
            $notes = $data['notes'] ?? null;
            $stmt->bindParam(':notes', $notes);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Insurance claim created successfully', 'id' => $this->conn->lastInsertId()];
            }
            return ['success' => false, 'message' => 'Failed to create insurance claim'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function getAllClaims() {
        try {
            $sql = "SELECT ic.*, 
                    pi.policy_number,
                    ip.provider_name,
                    p.first_name as patient_first_name, p.last_name as patient_last_name,
                    b.total_amount as billing_amount
                    FROM insurance_claims ic
                    JOIN patient_insurance pi ON ic.patient_insurance_id = pi.id
                    JOIN insurance_providers ip ON pi.provider_id = ip.id
                    JOIN patients p ON pi.patient_id = p.id
                    LEFT JOIN billing b ON ic.billing_id = b.id
                    ORDER BY ic.filed_date DESC";
            $stmt = $this->conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getClaimById($id) {
        try {
            $sql = "SELECT ic.*, 
                    pi.policy_number, pi.coverage_percentage,
                    ip.provider_name,
                    p.first_name as patient_first_name, p.last_name as patient_last_name,
                    b.total_amount as billing_amount
                    FROM insurance_claims ic
                    JOIN patient_insurance pi ON ic.patient_insurance_id = pi.id
                    JOIN insurance_providers ip ON pi.provider_id = ip.id
                    JOIN patients p ON pi.patient_id = p.id
                    LEFT JOIN billing b ON ic.billing_id = b.id
                    WHERE ic.id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    public function updateClaimStatus($id, $status, $approvedAmount = null, $denialReason = null) {
        try {
            $sql = "UPDATE insurance_claims SET 
                    status = :status,
                    approved_amount = :approved_amount,
                    processed_date = :processed_date,
                    denial_reason = :denial_reason
                    WHERE id = :id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':approved_amount', $approvedAmount);
            $processed_date = date('Y-m-d');
            $stmt->bindParam(':processed_date', $processed_date);
            $stmt->bindParam(':denial_reason', $denialReason);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Claim status updated successfully'];
            }
            return ['success' => false, 'message' => 'Failed to update claim status'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
}
