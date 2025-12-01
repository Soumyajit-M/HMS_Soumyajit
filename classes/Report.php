<?php
require_once __DIR__ . '/../config/database.php';

class Report {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Report Templates
    public function createTemplate($data) {
        try {
            $validationErrors = [];

            if (empty($data['template_name'])) {
                $validationErrors[] = 'Template name is required';
            }
            if (empty($data['report_type'])) {
                $validationErrors[] = 'Report type is required';
            }

            if (!empty($validationErrors)) {
                return ['success' => false, 'message' => implode(', ', $validationErrors)];
            }

            $sql = "INSERT INTO report_templates (template_name, report_type, description, sql_query, parameters, created_by, is_active) 
                    VALUES (:template_name, :report_type, :description, :sql_query, :parameters, :created_by, :is_active)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':template_name', $data['template_name']);
            $stmt->bindParam(':report_type', $data['report_type']);
            $description = $data['description'] ?? null;
            $stmt->bindParam(':description', $description);
            $sql_query = $data['sql_query'] ?? null;
            $stmt->bindParam(':sql_query', $sql_query);
            $parameters = $data['parameters'] ?? null;
            $stmt->bindParam(':parameters', $parameters);
            $created_by = $data['created_by'] ?? null;
            $stmt->bindParam(':created_by', $created_by);
            $is_active = $data['is_active'] ?? 1;
            $stmt->bindParam(':is_active', $is_active);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Report template created successfully', 'id' => $this->conn->lastInsertId()];
            }
            return ['success' => false, 'message' => 'Failed to create report template'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function getAllTemplates() {
        try {
            $sql = "SELECT * FROM report_templates WHERE is_active = 1 ORDER BY report_type, template_name";
            $stmt = $this->conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getTemplateById($id) {
        try {
            $sql = "SELECT * FROM report_templates WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    // Report Executions
    public function executeReport($templateId, $parameters = null, $executedBy = null) {
        try {
            $template = $this->getTemplateById($templateId);
            if (!$template) {
                return ['success' => false, 'message' => 'Report template not found'];
            }

            $sql = "INSERT INTO report_executions (template_id, executed_by, execution_parameters, status) 
                    VALUES (:template_id, :executed_by, :execution_parameters, 'Running')";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':template_id', $templateId);
            $stmt->bindParam(':executed_by', $executedBy);
            $params_json = $parameters ? json_encode($parameters) : null;
            $stmt->bindParam(':execution_parameters', $params_json);
            $stmt->execute();
            
            $executionId = $this->conn->lastInsertId();

            try {
                // Execute the query (simplified - in production, implement proper parameter binding)
                $query = $template['sql_query'];
                $stmt = $this->conn->query($query);
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $result_data = json_encode($results);
                
                // Update execution record
                $updateSql = "UPDATE report_executions SET 
                              status = 'Completed',
                              completion_time = :completion_time,
                              result_data = :result_data,
                              row_count = :row_count
                              WHERE id = :id";
                
                $updateStmt = $this->conn->prepare($updateSql);
                $completion_time = date('Y-m-d H:i:s');
                $updateStmt->bindParam(':completion_time', $completion_time);
                $updateStmt->bindParam(':result_data', $result_data);
                $row_count = count($results);
                $updateStmt->bindParam(':row_count', $row_count);
                $updateStmt->bindParam(':id', $executionId);
                $updateStmt->execute();

                return [
                    'success' => true, 
                    'message' => 'Report executed successfully', 
                    'id' => $executionId,
                    'data' => $results
                ];
            } catch (Exception $e) {
                // Update execution as failed
                $updateSql = "UPDATE report_executions SET 
                              status = 'Failed',
                              completion_time = :completion_time,
                              error_message = :error_message
                              WHERE id = :id";
                
                $updateStmt = $this->conn->prepare($updateSql);
                $completion_time = date('Y-m-d H:i:s');
                $updateStmt->bindParam(':completion_time', $completion_time);
                $error_message = $e->getMessage();
                $updateStmt->bindParam(':error_message', $error_message);
                $updateStmt->bindParam(':id', $executionId);
                $updateStmt->execute();

                return ['success' => false, 'message' => 'Report execution failed: ' . $e->getMessage()];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function getAllExecutions() {
        try {
            $sql = "SELECT re.*, rt.template_name, rt.report_type
                    FROM report_executions re
                    JOIN report_templates rt ON re.template_id = rt.id
                    ORDER BY re.execution_time DESC
                    LIMIT 100";
            $stmt = $this->conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getExecutionById($id) {
        try {
            $sql = "SELECT re.*, rt.template_name, rt.report_type, rt.description
                    FROM report_executions re
                    JOIN report_templates rt ON re.template_id = rt.id
                    WHERE re.id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    // Predefined Report Queries
    public function getPatientDemographicsReport() {
        $sql = "SELECT 
                COUNT(*) as total_patients,
                SUM(CASE WHEN gender = 'Male' THEN 1 ELSE 0 END) as male_count,
                SUM(CASE WHEN gender = 'Female' THEN 1 ELSE 0 END) as female_count,
                SUM(CASE WHEN gender NOT IN ('Male', 'Female') THEN 1 ELSE 0 END) as other_count,
                AVG(CAST((julianday('now') - julianday(date_of_birth)) / 365.25 AS INTEGER)) as avg_age
                FROM patients";
        
        $stmt = $this->conn->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAppointmentStatistics($startDate = null, $endDate = null) {
        $sql = "SELECT 
                status,
                COUNT(*) as count
                FROM appointments";
        
        if ($startDate && $endDate) {
            $sql .= " WHERE appointment_date BETWEEN :start_date AND :end_date";
        }
        
        $sql .= " GROUP BY status";
        
        $stmt = $this->conn->prepare($sql);
        if ($startDate && $endDate) {
            $stmt->bindParam(':start_date', $startDate);
            $stmt->bindParam(':end_date', $endDate);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRevenueReport($startDate = null, $endDate = null) {
        $sql = "SELECT 
                DATE(billing_date) as date,
                COUNT(*) as bill_count,
                SUM(total_amount) as total_revenue,
                SUM(amount_paid) as amount_collected,
                SUM(total_amount - amount_paid) as outstanding
                FROM billing";
        
        if ($startDate && $endDate) {
            $sql .= " WHERE billing_date BETWEEN :start_date AND :end_date";
        }
        
        $sql .= " GROUP BY DATE(billing_date)
                  ORDER BY DATE(billing_date) DESC";
        
        $stmt = $this->conn->prepare($sql);
        if ($startDate && $endDate) {
            $stmt->bindParam(':start_date', $startDate);
            $stmt->bindParam(':end_date', $endDate);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDoctorPerformanceReport() {
        $sql = "SELECT 
                d.id,
                d.first_name,
                d.last_name,
                d.specialization,
                COUNT(DISTINCT a.id) as total_appointments,
                SUM(CASE WHEN a.status = 'Completed' THEN 1 ELSE 0 END) as completed_appointments,
                SUM(CASE WHEN a.status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled_appointments,
                COUNT(DISTINCT b.id) as total_bills,
                COALESCE(SUM(b.total_amount), 0) as revenue_generated
                FROM doctors d
                LEFT JOIN appointments a ON d.id = a.doctor_id
                LEFT JOIN billing b ON d.id = b.doctor_id
                GROUP BY d.id
                ORDER BY revenue_generated DESC";
        
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getInventoryStatusReport() {
        $sql = "SELECT 
                ic.category_name,
                COUNT(DISTINCT ii.id) as item_count,
                SUM(COALESCE(ib.quantity_remaining, 0)) as total_quantity,
                SUM(CASE WHEN COALESCE(ib.quantity_remaining, 0) <= ii.reorder_level THEN 1 ELSE 0 END) as low_stock_items,
                SUM(CASE WHEN ib.expiry_date <= DATE('now', '+30 days') AND ib.quantity_remaining > 0 THEN 1 ELSE 0 END) as expiring_soon
                FROM inventory_categories ic
                LEFT JOIN inventory_items ii ON ic.id = ii.category_id
                LEFT JOIN inventory_batches ib ON ii.id = ib.item_id
                GROUP BY ic.id
                ORDER BY ic.category_name";
        
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
