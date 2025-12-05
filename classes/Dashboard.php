<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Patient.php';
require_once __DIR__ . '/../classes/Doctor.php';
require_once __DIR__ . '/../classes/Appointment.php';
require_once __DIR__ . '/../classes/Billing.php';

class Dashboard {
    private $conn;
    private $patient;
    private $doctor;
    private $appointment;
    private $billing;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->patient = new Patient();
        $this->doctor = new Doctor();
        $this->appointment = new Appointment();
        $this->billing = new Billing();
    }

    public function getDashboardData() {
        try {
            $data = [
                'patient_stats' => $this->patient->getPatientStats(),
                'doctor_stats' => $this->doctor->getDoctorStats(),
                'appointment_stats' => $this->appointment->getAppointmentStats(),
                'billing_stats' => $this->billing->getBillingStats(),
                'recent_appointments' => $this->getRecentAppointments(),
                'recent_patients' => $this->getRecentPatients(),
                'upcoming_appointments' => $this->getUpcomingAppointments(),
                'revenue_chart' => $this->getRevenueChartData(),
                'appointment_chart' => $this->getAppointmentChartData(),
                'department_stats' => $this->getDepartmentStats()
            ];
            
            return $data;
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function getRecentAppointments($limit = 5) {
        try {
            $query = "SELECT a.*, 
                     p.first_name as patient_first_name, 
                     p.last_name as patient_last_name, 
                     p.phone as patient_phone, 
                     d.doctor_id,
                     COALESCE(u.first_name, '') as doctor_first_name, 
                     COALESCE(u.last_name, '') as doctor_last_name,
                     COALESCE(dept.name, 'General') as department_name,
                     (p.first_name || ' ' || p.last_name) as patient_name
                     FROM appointments a 
                     LEFT JOIN patients p ON a.patient_id = p.id 
                     LEFT JOIN doctors d ON a.doctor_id = d.id 
                     LEFT JOIN users u ON d.user_id = u.id 
                     LEFT JOIN departments dept ON a.department_id = dept.id 
                     ORDER BY a.created_at DESC 
                     LIMIT :limit";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Dashboard getRecentAppointments error: " . $e->getMessage());
            return [];
        }
    }

    public function getRecentPatients($limit = 5) {
        try {
            $query = "SELECT * FROM patients ORDER BY created_at DESC LIMIT :limit";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getUpcomingAppointments($limit = 10) {
        try {
            $query = "SELECT a.*, p.first_name as patient_first_name, p.last_name as patient_last_name, 
                     p.phone as patient_phone, d.doctor_id, u.first_name as doctor_first_name, 
                     u.last_name as doctor_last_name, dept.name as department_name
                     FROM appointments a 
                     JOIN patients p ON a.patient_id = p.id 
                     JOIN doctors d ON a.doctor_id = d.id 
                     JOIN users u ON d.user_id = u.id 
                     LEFT JOIN departments dept ON a.department_id = dept.id 
                     WHERE a.appointment_date >= date('now') 
                     AND a.status IN ('scheduled', 'confirmed')
                     ORDER BY a.appointment_date ASC, a.appointment_time ASC 
                     LIMIT :limit";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getRevenueChartData($months = 12) {
        try {
            $from = date('Y-m-d', strtotime("-{$months} months"));
            $query = "SELECT 
                        strftime('%Y-%m', created_at) as month,
                        SUM(total_amount) as total_revenue,
                        SUM(paid_amount) as paid_revenue,
                        COUNT(*) as bill_count
                     FROM billing 
                     WHERE created_at >= :from
                     GROUP BY strftime('%Y-%m', created_at)
                     ORDER BY month";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':from', $from);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getAppointmentChartData($months = 12) {
        try {
            $from = date('Y-m-d', strtotime("-{$months} months"));
            $query = "SELECT 
                        strftime('%Y-%m', appointment_date) as month,
                        COUNT(*) as total_appointments,
                        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_appointments,
                        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_appointments
                     FROM appointments 
                     WHERE appointment_date >= :from
                     GROUP BY strftime('%Y-%m', appointment_date)
                     ORDER BY month";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':from', $from);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getDepartmentStats() {
        try {
            $from30 = date('Y-m-d', strtotime('-30 days'));
            $query = "SELECT 
                        d.name as department_name,
                        COUNT(a.id) as appointment_count,
                        COUNT(DISTINCT a.patient_id) as unique_patients,
                        COUNT(DISTINCT a.doctor_id) as doctors_count
                     FROM departments d 
                     LEFT JOIN appointments a ON d.id = a.department_id 
                     AND a.appointment_date >= :from30
                     GROUP BY d.id, d.name
                     ORDER BY appointment_count DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':from30', $from30);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getPatientAgeDistribution() {
        try {
            // Approximate age calculation using SQLite strftime
            $query = "SELECT 
                        CASE 
                            WHEN (CAST(strftime('%Y','now') AS INTEGER) - CAST(strftime('%Y', date_of_birth) AS INTEGER)) < 18 THEN '0-17'
                            WHEN (CAST(strftime('%Y','now') AS INTEGER) - CAST(strftime('%Y', date_of_birth) AS INTEGER)) BETWEEN 18 AND 30 THEN '18-30'
                            WHEN (CAST(strftime('%Y','now') AS INTEGER) - CAST(strftime('%Y', date_of_birth) AS INTEGER)) BETWEEN 31 AND 50 THEN '31-50'
                            WHEN (CAST(strftime('%Y','now') AS INTEGER) - CAST(strftime('%Y', date_of_birth) AS INTEGER)) BETWEEN 51 AND 70 THEN '51-70'
                            ELSE '70+'
                        END as age_group,
                        COUNT(*) as count
                     FROM patients 
                     GROUP BY age_group
                     ORDER BY age_group";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getDoctorPerformance() {
        try {
            $from30 = date('Y-m-d', strtotime('-30 days'));
            $query = "SELECT 
                        d.doctor_id,
                        u.first_name,
                        u.last_name,
                        d.specialization,
                        COUNT(a.id) as total_appointments,
                        SUM(CASE WHEN a.status = 'completed' THEN 1 ELSE 0 END) as completed_appointments,
                        ROUND(AVG(b.total_amount), 2) as avg_bill_amount
                     FROM doctors d 
                     JOIN users u ON d.user_id = u.id 
                     LEFT JOIN appointments a ON d.id = a.doctor_id 
                     AND a.appointment_date >= :from30
                     LEFT JOIN billing b ON a.id = b.appointment_id 
                     GROUP BY d.id, d.doctor_id, u.first_name, u.last_name, d.specialization
                     ORDER BY total_appointments DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':from30', $from30);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getMonthlyStats($year = null) {
        if (!$year) {
            $year = date('Y');
        }
        
        try {
            $query = "SELECT 
                        CAST(strftime('%m', created_at) AS INTEGER) as month,
                        COUNT(DISTINCT p.id) as new_patients,
                        COUNT(a.id) as appointments,
                        SUM(b.total_amount) as revenue
                     FROM patients p 
                     LEFT JOIN appointments a ON p.id = a.patient_id 
                     AND strftime('%Y', a.appointment_date) = :year
                     LEFT JOIN billing b ON a.id = b.appointment_id 
                     WHERE strftime('%Y', p.created_at) = :year
                     GROUP BY CAST(strftime('%m', p.created_at) AS INTEGER)
                     ORDER BY month";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':year', $year);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getDashboardStats() {
        try {
            $stats = [
                'total_patients' => $this->patient->getTotalPatients(),
                'today_appointments' => $this->appointment->getTodayAppointments(),
                'pending_bills' => $this->billing->getPendingBillsCount(),
                'emergency_cases' => $this->appointment->getEmergencyCases()
            ];
            return $stats;
        } catch (Exception $e) {
            return [
                'total_patients' => 0,
                'today_appointments' => 0,
                'pending_bills' => 0,
                'emergency_cases' => 0
            ];
        }
    }

    public function getStats() {
        try {
            // Get total patients
            $query = "SELECT COUNT(*) as count FROM patients";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['total_patients'] = $result['count'] ?? 0;
            
            // Get today's appointments
            $query = "SELECT COUNT(*) as count FROM appointments WHERE DATE(appointment_date) = DATE('now')";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['today_appointments'] = $result['count'] ?? 0;
            
            // Get pending bills
            $query = "SELECT COUNT(*) as count FROM billing WHERE payment_status = 'pending'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['pending_bills'] = $result['count'] ?? 0;
            
            // Get emergency cases
            $query = "SELECT COUNT(*) as count FROM appointments WHERE appointment_type = 'emergency' AND status = 'scheduled'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['emergency_cases'] = $result['count'] ?? 0;
            
            // Get total revenue
            $query = "SELECT SUM(paid_amount) as total FROM billing";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['total_revenue'] = $result['total'] ?? 0;
            
            // Get active doctors
            $query = "SELECT COUNT(*) as count FROM doctors WHERE status = 'active'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['active_doctors'] = $result['count'] ?? 0;
            
            return $stats;
        } catch (PDOException $e) {
            return [
                'total_patients' => 0,
                'today_appointments' => 0,
                'pending_bills' => 0,
                'emergency_cases' => 0,
                'total_revenue' => 0,
                'active_doctors' => 0
            ];
        }
    }

    public function getNotifications() {
        try {
            $query = "SELECT * FROM notifications WHERE is_read = 0 ORDER BY created_at DESC LIMIT 5";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>
