<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';

class PDFReport {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function generatePatientReport($patientId, $format = 'html') {
        try {
            $patient = $this->getPatientData($patientId);
            $appointments = $this->getPatientAppointments($patientId);
            $medicalRecords = $this->getPatientMedicalRecords($patientId);
            $billing = $this->getPatientBilling($patientId);

            $data = [
                'patient' => $patient,
                'appointments' => $appointments,
                'medical_records' => $medicalRecords,
                'billing' => $billing,
                'generated_date' => date('Y-m-d H:i:s')
            ];

            if ($format === 'pdf') {
                return $this->generatePDF($data, 'patient_report');
            } else {
                return $this->generateHTML($data, 'patient_report');
            }
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function generateAppointmentReport($filters = [], $format = 'html') {
        try {
            $appointments = $this->getAppointmentsData($filters);
            $stats = $this->getAppointmentStats($filters);

            $data = [
                'appointments' => $appointments,
                'stats' => $stats,
                'filters' => $filters,
                'generated_date' => date('Y-m-d H:i:s')
            ];

            if ($format === 'pdf') {
                return $this->generatePDF($data, 'appointment_report');
            } else {
                return $this->generateHTML($data, 'appointment_report');
            }
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function generateBillingReport($filters = [], $format = 'html') {
        try {
            $bills = $this->getBillingData($filters);
            $stats = $this->getBillingStats($filters);

            $data = [
                'bills' => $bills,
                'stats' => $stats,
                'filters' => $filters,
                'generated_date' => date('Y-m-d H:i:s')
            ];

            if ($format === 'pdf') {
                return $this->generatePDF($data, 'billing_report');
            } else {
                return $this->generateHTML($data, 'billing_report');
            }
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function generateDashboardReport($format = 'html') {
        try {
            $dashboard = new Dashboard();
            $dashboardData = $dashboard->getDashboardData();

            $data = [
                'dashboard' => $dashboardData,
                'generated_date' => date('Y-m-d H:i:s')
            ];

            if ($format === 'pdf') {
                return $this->generatePDF($data, 'dashboard_report');
            } else {
                return $this->generateHTML($data, 'dashboard_report');
            }
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    private function getPatientData($patientId) {
        $query = "SELECT * FROM patients WHERE id = :patient_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':patient_id', $patientId);
        $stmt->execute();
        return $stmt->fetch();
    }

    private function getPatientAppointments($patientId) {
        $query = "SELECT a.*, d.doctor_id, u.first_name as doctor_first_name, 
                 u.last_name as doctor_last_name, dept.name as department_name
                 FROM appointments a 
                 JOIN doctors d ON a.doctor_id = d.id 
                 JOIN users u ON d.user_id = u.id 
                 LEFT JOIN departments dept ON a.department_id = dept.id 
                 WHERE a.patient_id = :patient_id 
                 ORDER BY a.appointment_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':patient_id', $patientId);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function getPatientMedicalRecords($patientId) {
        $query = "SELECT mr.*, d.doctor_id, u.first_name as doctor_first_name, 
                 u.last_name as doctor_last_name
                 FROM medical_records mr 
                 JOIN doctors d ON mr.doctor_id = d.id 
                 JOIN users u ON d.user_id = u.id 
                 WHERE mr.patient_id = :patient_id 
                 ORDER BY mr.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':patient_id', $patientId);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function getPatientBilling($patientId) {
        $query = "SELECT b.*, bi.item_name, bi.quantity, bi.unit_price, bi.total_price
                 FROM billing b 
                 LEFT JOIN billing_items bi ON b.id = bi.billing_id 
                 WHERE b.patient_id = :patient_id 
                 ORDER BY b.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':patient_id', $patientId);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function getAppointmentsData($filters) {
        $query = "SELECT a.*, p.first_name as patient_first_name, p.last_name as patient_last_name, 
                 p.patient_id, d.doctor_id, u.first_name as doctor_first_name, 
                 u.last_name as doctor_last_name, dept.name as department_name
                 FROM appointments a 
                 JOIN patients p ON a.patient_id = p.id 
                 JOIN doctors d ON a.doctor_id = d.id 
                 JOIN users u ON d.user_id = u.id 
                 LEFT JOIN departments dept ON a.department_id = dept.id 
                 WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['date_from'])) {
            $query .= " AND a.appointment_date >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $query .= " AND a.appointment_date <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }
        
        if (!empty($filters['status'])) {
            $query .= " AND a.status = :status";
            $params[':status'] = $filters['status'];
        }
        
        $query .= " ORDER BY a.appointment_date DESC";
        
        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function getBillingData($filters) {
        $query = "SELECT b.*, p.first_name as patient_first_name, p.last_name as patient_last_name, 
                 p.patient_id, a.appointment_id
                 FROM billing b 
                 JOIN patients p ON b.patient_id = p.id 
                 LEFT JOIN appointments a ON b.appointment_id = a.id 
                 WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['date_from'])) {
            $query .= " AND b.created_at >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $query .= " AND b.created_at <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }
        
        if (!empty($filters['payment_status'])) {
            $query .= " AND b.payment_status = :payment_status";
            $params[':payment_status'] = $filters['payment_status'];
        }
        
        $query .= " ORDER BY b.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function getAppointmentStats($filters) {
        $query = "SELECT 
                    COUNT(*) as total_appointments,
                    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_appointments,
                    COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_appointments,
                    COUNT(CASE WHEN status = 'scheduled' THEN 1 END) as scheduled_appointments
                 FROM appointments a 
                 WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['date_from'])) {
            $query .= " AND a.appointment_date >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $query .= " AND a.appointment_date <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }
        
        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return $stmt->fetch();
    }

    private function getBillingStats($filters) {
        $query = "SELECT 
                    COUNT(*) as total_bills,
                    SUM(total_amount) as total_revenue,
                    SUM(paid_amount) as total_paid,
                    SUM(balance_amount) as total_balance
                 FROM billing b 
                 WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['date_from'])) {
            $query .= " AND b.created_at >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $query .= " AND b.created_at <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }
        
        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return $stmt->fetch();
    }

    private function generateHTML($data, $template) {
        ob_start();
        include "templates/reports/{$template}.php";
        $html = ob_get_clean();
        return $html;
    }

    private function generatePDF($data, $template) {
        // This would integrate with a PDF library like TCPDF or mPDF
        // For now, we'll return HTML that can be converted to PDF
        $html = $this->generateHTML($data, $template);
        
        // In a real implementation, you would use a PDF library here
        // For example, with mPDF:
        // require_once 'vendor/autoload.php';
        // $mpdf = new \Mpdf\Mpdf();
        // $mpdf->WriteHTML($html);
        // return $mpdf->Output('report.pdf', 'D');
        
        return $html;
    }
}
?>
