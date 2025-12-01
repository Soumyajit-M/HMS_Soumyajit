<?php
/**
 * HMS 2.0 Database Migration Script
 * Safely migrates database from HMS 1.0 to HMS 2.0
 */

// Load config first
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

class HMS2Migration {
    private $conn;
    private $logFile;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->logFile = __DIR__ . '/../logs/migration_v2_' . date('Y-m-d_His') . '.log';
        
        // Ensure logs directory exists
        if (!is_dir(__DIR__ . '/../logs')) {
            mkdir(__DIR__ . '/../logs', 0755, true);
        }
    }
    
    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] $message" . PHP_EOL;
        file_put_contents($this->logFile, $logMessage, FILE_APPEND);
        echo $logMessage;
    }
    
    public function migrate() {
        $this->log("=== HMS 2.0 Migration Started ===");
        
        try {
            // Begin transaction
            $this->conn->beginTransaction();
            
            // Backup before migration
            $this->log("Creating backup...");
            $this->createBackup();
            
            // Run migrations in order
            $this->migrateStep1_DoctorSchedules();
            $this->migrateStep2_PatientRecords();
            $this->migrateStep3_StaffManagement();
            $this->migrateStep4_RoomOccupancy();
            $this->migrateStep5_Laboratory();
            $this->migrateStep6_Inventory();
            $this->migrateStep7_EnhancedBilling();
            $this->migrateStep8_Insurance();
            $this->migrateStep9_Telemedicine();
            $this->migrateStep10_Reports();
            
            // Create indexes
            $this->createIndexes();
            
            // Commit transaction
            $this->conn->commit();
            
            $this->log("=== Migration Completed Successfully ===");
            return ['success' => true, 'message' => 'Migration completed successfully', 'log_file' => $this->logFile];
            
        } catch (Exception $e) {
            // Rollback on error
            $this->conn->rollBack();
            $this->log("ERROR: " . $e->getMessage());
            $this->log("Migration failed. Database rolled back to previous state.");
            return ['success' => false, 'message' => $e->getMessage(), 'log_file' => $this->logFile];
        }
    }
    
    private function createBackup() {
        $dbFile = __DIR__ . '/../database/hms_database.sqlite';
        $backupFile = __DIR__ . '/../database/backups/hms_database_' . date('Y-m-d_His') . '.sqlite';
        
        if (!is_dir(__DIR__ . '/../database/backups')) {
            mkdir(__DIR__ . '/../database/backups', 0755, true);
        }
        
        if (file_exists($dbFile)) {
            copy($dbFile, $backupFile);
            $this->log("Backup created: $backupFile");
        }
    }
    
    private function tableExists($tableName) {
        $stmt = $this->conn->prepare("SELECT name FROM sqlite_master WHERE type='table' AND name=:name");
        $stmt->execute([':name' => $tableName]);
        return $stmt->fetch() !== false;
    }
    
    private function columnExists($tableName, $columnName) {
        $stmt = $this->conn->prepare("PRAGMA table_info($tableName)");
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $column) {
            if ($column['name'] === $columnName) {
                return true;
            }
        }
        return false;
    }
    
    private function migrateStep1_DoctorSchedules() {
        $this->log("Step 1: Doctor Schedules & Emergency Contacts");
        
        // Add columns to doctors table if they don't exist
        if (!$this->columnExists('doctors', 'emergency_phone')) {
            $this->conn->exec("ALTER TABLE doctors ADD COLUMN emergency_phone TEXT");
            $this->log("Added emergency_phone column to doctors table");
        }
        
        if (!$this->columnExists('doctors', 'emergency_email')) {
            $this->conn->exec("ALTER TABLE doctors ADD COLUMN emergency_email TEXT");
            $this->log("Added emergency_email column to doctors table");
        }
        
        if (!$this->columnExists('doctors', 'license_number')) {
            $this->conn->exec("ALTER TABLE doctors ADD COLUMN license_number TEXT");
            $this->log("Added license_number column to doctors table");
        }
        
        // Create doctor_schedules table
        if (!$this->tableExists('doctor_schedules')) {
            $this->conn->exec("CREATE TABLE doctor_schedules (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                doctor_id INTEGER NOT NULL,
                day_of_week TEXT NOT NULL CHECK (day_of_week IN ('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')),
                start_time TEXT NOT NULL,
                end_time TEXT NOT NULL,
                slot_duration INTEGER DEFAULT 30,
                max_patients_per_slot INTEGER DEFAULT 1,
                is_active INTEGER DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
            )");
            $this->log("Created doctor_schedules table");
        }
        
        // Create doctor_leaves table
        if (!$this->tableExists('doctor_leaves')) {
            $this->conn->exec("CREATE TABLE doctor_leaves (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                doctor_id INTEGER NOT NULL,
                leave_type TEXT CHECK (leave_type IN ('vacation', 'sick', 'conference', 'emergency', 'other')),
                start_date DATE NOT NULL,
                end_date DATE NOT NULL,
                reason TEXT,
                status TEXT DEFAULT 'pending' CHECK (status IN ('pending', 'approved', 'rejected')),
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
            )");
            $this->log("Created doctor_leaves table");
        }
    }
    
    private function migrateStep2_PatientRecords() {
        $this->log("Step 2: Enhanced Patient Medical Records");
        
        // Add columns to medical_records if they don't exist
        if ($this->tableExists('medical_records')) {
            if (!$this->columnExists('medical_records', 'allergies')) {
                $this->conn->exec("ALTER TABLE medical_records ADD COLUMN allergies TEXT");
            }
            if (!$this->columnExists('medical_records', 'chronic_conditions')) {
                $this->conn->exec("ALTER TABLE medical_records ADD COLUMN chronic_conditions TEXT");
            }
            if (!$this->columnExists('medical_records', 'current_medications')) {
                $this->conn->exec("ALTER TABLE medical_records ADD COLUMN current_medications TEXT");
            }
            $this->log("Enhanced medical_records table");
        }
        
        // Create vital_signs table
        if (!$this->tableExists('vital_signs')) {
            $this->conn->exec("CREATE TABLE vital_signs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                patient_id INTEGER NOT NULL,
                appointment_id INTEGER,
                temperature REAL,
                blood_pressure_systolic INTEGER,
                blood_pressure_diastolic INTEGER,
                heart_rate INTEGER,
                respiratory_rate INTEGER,
                oxygen_saturation REAL,
                weight REAL,
                height REAL,
                bmi REAL,
                recorded_by INTEGER,
                recorded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
                FOREIGN KEY (appointment_id) REFERENCES appointments(id),
                FOREIGN KEY (recorded_by) REFERENCES users(id)
            )");
            $this->log("Created vital_signs table");
        }
        
        // Create immunizations table
        if (!$this->tableExists('immunizations')) {
            $this->conn->exec("CREATE TABLE immunizations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                patient_id INTEGER NOT NULL,
                vaccine_name TEXT NOT NULL,
                dose_number INTEGER,
                administered_date DATE NOT NULL,
                next_dose_date DATE,
                administered_by INTEGER,
                notes TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
                FOREIGN KEY (administered_by) REFERENCES users(id)
            )");
            $this->log("Created immunizations table");
        }
    }
    
    private function migrateStep3_StaffManagement() {
        $this->log("Step 3: Staff Management Module");
        
        if (!$this->tableExists('staff')) {
            $this->conn->exec("CREATE TABLE staff (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                staff_id TEXT UNIQUE NOT NULL,
                user_id INTEGER,
                first_name TEXT NOT NULL,
                last_name TEXT NOT NULL,
                role TEXT NOT NULL CHECK (role IN ('nurse', 'technician', 'receptionist', 'pharmacist', 'lab_technician', 'radiologist', 'other')),
                department_id INTEGER,
                phone TEXT NOT NULL,
                emergency_contact_name TEXT,
                emergency_contact_phone TEXT,
                email TEXT,
                qualification TEXT,
                certifications TEXT,
                license_number TEXT,
                date_of_joining DATE,
                employment_type TEXT CHECK (employment_type IN ('full-time', 'part-time', 'contract', 'temporary')),
                salary REAL,
                is_active INTEGER DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
                FOREIGN KEY (department_id) REFERENCES departments(id)
            )");
            $this->log("Created staff table");
        }
        
        if (!$this->tableExists('staff_shifts')) {
            $this->conn->exec("CREATE TABLE staff_shifts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                staff_id INTEGER NOT NULL,
                shift_date DATE NOT NULL,
                start_time TEXT NOT NULL,
                end_time TEXT NOT NULL,
                shift_type TEXT CHECK (shift_type IN ('morning', 'evening', 'night', 'full-day')),
                status TEXT DEFAULT 'scheduled' CHECK (status IN ('scheduled', 'completed', 'absent', 'cancelled')),
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (staff_id) REFERENCES staff(id) ON DELETE CASCADE
            )");
            $this->log("Created staff_shifts table");
        }
    }
    
    private function migrateStep4_RoomOccupancy() {
        $this->log("Step 4: Room Occupancy Management");
        
        if (!$this->tableExists('wards')) {
            $this->conn->exec("CREATE TABLE wards (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                ward_name TEXT NOT NULL,
                ward_type TEXT CHECK (ward_type IN ('general', 'icu', 'emergency', 'maternity', 'pediatric', 'surgery', 'private')),
                floor_number INTEGER,
                total_beds INTEGER NOT NULL,
                available_beds INTEGER NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )");
            $this->log("Created wards table");
        }
        
        if (!$this->tableExists('rooms')) {
            $this->conn->exec("CREATE TABLE rooms (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                room_number TEXT UNIQUE NOT NULL,
                ward_id INTEGER NOT NULL,
                room_type TEXT CHECK (room_type IN ('single', 'double', 'shared', 'icu', 'operation_theater')),
                floor_number INTEGER,
                total_beds INTEGER DEFAULT 1,
                available_beds INTEGER DEFAULT 1,
                amenities TEXT,
                daily_rate REAL,
                status TEXT DEFAULT 'available' CHECK (status IN ('available', 'occupied', 'maintenance', 'cleaning', 'reserved')),
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (ward_id) REFERENCES wards(id) ON DELETE CASCADE
            )");
            $this->log("Created rooms table");
        }
        
        if (!$this->tableExists('bed_assignments')) {
            $this->conn->exec("CREATE TABLE bed_assignments (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                room_id INTEGER NOT NULL,
                bed_number TEXT NOT NULL,
                patient_id INTEGER,
                admission_date DATETIME NOT NULL,
                discharge_date DATETIME,
                status TEXT DEFAULT 'occupied' CHECK (status IN ('occupied', 'available', 'reserved')),
                notes TEXT,
                assigned_by INTEGER,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
                FOREIGN KEY (patient_id) REFERENCES patients(id),
                FOREIGN KEY (assigned_by) REFERENCES users(id)
            )");
            $this->log("Created bed_assignments table");
        }
    }
    
    private function migrateStep5_Laboratory() {
        $this->log("Step 5: Laboratory Module");
        
        if (!$this->tableExists('lab_test_types')) {
            $this->conn->exec("CREATE TABLE lab_test_types (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                test_code TEXT UNIQUE NOT NULL,
                test_name TEXT NOT NULL,
                test_category TEXT,
                description TEXT,
                sample_type TEXT CHECK (sample_type IN ('blood', 'urine', 'stool', 'tissue', 'swab', 'other')),
                normal_range TEXT,
                cost REAL NOT NULL,
                turnaround_time INTEGER,
                requires_fasting INTEGER DEFAULT 0,
                is_active INTEGER DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )");
            $this->log("Created lab_test_types table");
            
            // Insert some common lab tests
            $this->conn->exec("INSERT INTO lab_test_types (test_code, test_name, test_category, sample_type, cost, turnaround_time) VALUES 
                ('CBC', 'Complete Blood Count', 'Hematology', 'blood', 25.00, 24),
                ('BMP', 'Basic Metabolic Panel', 'Chemistry', 'blood', 35.00, 24),
                ('LFT', 'Liver Function Test', 'Chemistry', 'blood', 45.00, 24),
                ('HBA1C', 'Hemoglobin A1C', 'Chemistry', 'blood', 30.00, 24),
                ('TSH', 'Thyroid Stimulating Hormone', 'Endocrinology', 'blood', 40.00, 48),
                ('UA', 'Urinalysis', 'Clinical Microscopy', 'urine', 15.00, 12)");
            $this->log("Inserted sample lab tests");
        }
        
        if (!$this->tableExists('lab_orders')) {
            $this->conn->exec("CREATE TABLE lab_orders (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                order_number TEXT UNIQUE NOT NULL,
                patient_id INTEGER NOT NULL,
                doctor_id INTEGER NOT NULL,
                appointment_id INTEGER,
                order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
                priority TEXT DEFAULT 'routine' CHECK (priority IN ('routine', 'urgent', 'stat')),
                status TEXT DEFAULT 'pending' CHECK (status IN ('pending', 'sample_collected', 'in_progress', 'completed', 'cancelled')),
                clinical_notes TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
                FOREIGN KEY (doctor_id) REFERENCES doctors(id),
                FOREIGN KEY (appointment_id) REFERENCES appointments(id)
            )");
            $this->log("Created lab_orders table");
        }
        
        if (!$this->tableExists('lab_order_tests')) {
            $this->conn->exec("CREATE TABLE lab_order_tests (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                lab_order_id INTEGER NOT NULL,
                test_type_id INTEGER NOT NULL,
                sample_collected_at DATETIME,
                sample_collected_by INTEGER,
                result_value TEXT,
                result_unit TEXT,
                result_status TEXT CHECK (result_status IN ('normal', 'abnormal', 'critical')),
                result_notes TEXT,
                tested_by INTEGER,
                verified_by INTEGER,
                completed_at DATETIME,
                FOREIGN KEY (lab_order_id) REFERENCES lab_orders(id) ON DELETE CASCADE,
                FOREIGN KEY (test_type_id) REFERENCES lab_test_types(id),
                FOREIGN KEY (sample_collected_by) REFERENCES users(id),
                FOREIGN KEY (tested_by) REFERENCES users(id),
                FOREIGN KEY (verified_by) REFERENCES users(id)
            )");
            $this->log("Created lab_order_tests table");
        }
    }
    
    private function migrateStep6_Inventory() {
        $this->log("Step 6: Inventory Management");
        
        if (!$this->tableExists('inventory_categories')) {
            $this->conn->exec("CREATE TABLE inventory_categories (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                category_name TEXT UNIQUE NOT NULL,
                description TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )");
            $this->log("Created inventory_categories table");
            
            // Insert default categories
            $this->conn->exec("INSERT INTO inventory_categories (category_name, description) VALUES 
                ('Medications', 'Pharmaceutical drugs and medicines'),
                ('Surgical Instruments', 'Surgical tools and equipment'),
                ('Consumables', 'Disposable medical supplies'),
                ('Laboratory Supplies', 'Lab equipment and reagents'),
                ('Medical Equipment', 'Durable medical equipment')");
        }
        
        if (!$this->tableExists('inventory_items')) {
            $this->conn->exec("CREATE TABLE inventory_items (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                item_code TEXT UNIQUE NOT NULL,
                item_name TEXT NOT NULL,
                category_id INTEGER NOT NULL,
                item_type TEXT CHECK (item_type IN ('medication', 'instrument', 'consumable', 'equipment', 'other')),
                description TEXT,
                unit_of_measure TEXT,
                unit_price REAL NOT NULL,
                reorder_level INTEGER DEFAULT 10,
                current_stock INTEGER DEFAULT 0,
                location TEXT,
                manufacturer TEXT,
                supplier_id INTEGER,
                requires_prescription INTEGER DEFAULT 0,
                is_active INTEGER DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (category_id) REFERENCES inventory_categories(id)
            )");
            $this->log("Created inventory_items table");
        }
        
        if (!$this->tableExists('inventory_batches')) {
            $this->conn->exec("CREATE TABLE inventory_batches (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                item_id INTEGER NOT NULL,
                batch_number TEXT NOT NULL,
                quantity INTEGER NOT NULL,
                manufacturing_date DATE,
                expiry_date DATE,
                purchase_date DATE,
                purchase_price REAL,
                current_quantity INTEGER NOT NULL,
                status TEXT DEFAULT 'active' CHECK (status IN ('active', 'expired', 'recalled', 'depleted')),
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (item_id) REFERENCES inventory_items(id) ON DELETE CASCADE
            )");
            $this->log("Created inventory_batches table");
        }
        
        if (!$this->tableExists('inventory_transactions')) {
            $this->conn->exec("CREATE TABLE inventory_transactions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                item_id INTEGER NOT NULL,
                batch_id INTEGER,
                transaction_type TEXT NOT NULL CHECK (transaction_type IN ('purchase', 'issue', 'return', 'adjustment', 'transfer', 'waste')),
                quantity INTEGER NOT NULL,
                transaction_date DATETIME DEFAULT CURRENT_TIMESTAMP,
                reference_type TEXT,
                reference_id INTEGER,
                performed_by INTEGER,
                notes TEXT,
                FOREIGN KEY (item_id) REFERENCES inventory_items(id),
                FOREIGN KEY (batch_id) REFERENCES inventory_batches(id),
                FOREIGN KEY (performed_by) REFERENCES users(id)
            )");
            $this->log("Created inventory_transactions table");
        }
    }
    
    private function migrateStep7_EnhancedBilling() {
        $this->log("Step 7: Enhanced Billing System");
        
        // Add new columns to billing table
        if (!$this->columnExists('billing', 'discount_amount')) {
            $this->conn->exec("ALTER TABLE billing ADD COLUMN discount_amount REAL DEFAULT 0");
        }
        if (!$this->columnExists('billing', 'tax_amount')) {
            $this->conn->exec("ALTER TABLE billing ADD COLUMN tax_amount REAL DEFAULT 0");
        }
        if (!$this->columnExists('billing', 'insurance_claim_amount')) {
            $this->conn->exec("ALTER TABLE billing ADD COLUMN insurance_claim_amount REAL DEFAULT 0");
        }
        if (!$this->columnExists('billing', 'payment_method')) {
            $this->conn->exec("ALTER TABLE billing ADD COLUMN payment_method TEXT");
        }
        if (!$this->columnExists('billing', 'billing_type')) {
            $this->conn->exec("ALTER TABLE billing ADD COLUMN billing_type TEXT CHECK (billing_type IN ('consultation', 'procedure', 'lab', 'pharmacy', 'room', 'other'))");
        }
        $this->log("Enhanced billing table");
        
        if (!$this->tableExists('procedure_codes')) {
            $this->conn->exec("CREATE TABLE procedure_codes (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                code TEXT UNIQUE NOT NULL,
                description TEXT NOT NULL,
                category TEXT,
                base_price REAL NOT NULL,
                is_active INTEGER DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )");
            $this->log("Created procedure_codes table");
        }
        
        if (!$this->tableExists('payment_plans')) {
            $this->conn->exec("CREATE TABLE payment_plans (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                billing_id INTEGER NOT NULL,
                total_amount REAL NOT NULL,
                down_payment REAL DEFAULT 0,
                installment_amount REAL NOT NULL,
                installment_count INTEGER NOT NULL,
                installments_paid INTEGER DEFAULT 0,
                start_date DATE NOT NULL,
                status TEXT DEFAULT 'active' CHECK (status IN ('active', 'completed', 'defaulted', 'cancelled')),
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (billing_id) REFERENCES billing(id) ON DELETE CASCADE
            )");
            $this->log("Created payment_plans table");
        }
    }
    
    private function migrateStep8_Insurance() {
        $this->log("Step 8: Insurance Integration");
        
        if (!$this->tableExists('insurance_providers')) {
            $this->conn->exec("CREATE TABLE insurance_providers (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                provider_name TEXT NOT NULL,
                provider_code TEXT UNIQUE,
                contact_person TEXT,
                phone TEXT,
                email TEXT,
                address TEXT,
                website TEXT,
                is_active INTEGER DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )");
            $this->log("Created insurance_providers table");
        }
        
        if (!$this->tableExists('patient_insurance')) {
            $this->conn->exec("CREATE TABLE patient_insurance (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                patient_id INTEGER NOT NULL,
                insurance_provider_id INTEGER NOT NULL,
                policy_number TEXT NOT NULL,
                policy_holder_name TEXT NOT NULL,
                policy_holder_relationship TEXT,
                group_number TEXT,
                coverage_start_date DATE,
                coverage_end_date DATE,
                coverage_type TEXT,
                max_coverage_amount REAL,
                deductible REAL DEFAULT 0,
                co_payment_percentage REAL DEFAULT 0,
                is_primary INTEGER DEFAULT 1,
                status TEXT DEFAULT 'active' CHECK (status IN ('active', 'expired', 'cancelled', 'suspended')),
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
                FOREIGN KEY (insurance_provider_id) REFERENCES insurance_providers(id)
            )");
            $this->log("Created patient_insurance table");
        }
        
        if (!$this->tableExists('insurance_claims')) {
            $this->conn->exec("CREATE TABLE insurance_claims (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                claim_number TEXT UNIQUE NOT NULL,
                patient_insurance_id INTEGER NOT NULL,
                billing_id INTEGER NOT NULL,
                claim_amount REAL NOT NULL,
                approved_amount REAL,
                claim_date DATE DEFAULT CURRENT_DATE,
                submission_date DATE,
                processing_date DATE,
                settlement_date DATE,
                status TEXT DEFAULT 'draft' CHECK (status IN ('draft', 'submitted', 'under_review', 'approved', 'rejected', 'settled', 'appealed')),
                rejection_reason TEXT,
                notes TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (patient_insurance_id) REFERENCES patient_insurance(id),
                FOREIGN KEY (billing_id) REFERENCES billing(id) ON DELETE CASCADE
            )");
            $this->log("Created insurance_claims table");
        }
    }
    
    private function migrateStep9_Telemedicine() {
        $this->log("Step 9: Telemedicine Platform");
        
        if (!$this->tableExists('telemedicine_sessions')) {
            $this->conn->exec("CREATE TABLE telemedicine_sessions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                session_id TEXT UNIQUE NOT NULL,
                appointment_id INTEGER NOT NULL,
                patient_id INTEGER NOT NULL,
                doctor_id INTEGER NOT NULL,
                scheduled_time DATETIME NOT NULL,
                start_time DATETIME,
                end_time DATETIME,
                session_type TEXT CHECK (session_type IN ('video', 'audio', 'chat')),
                platform TEXT,
                meeting_link TEXT,
                access_code TEXT,
                status TEXT DEFAULT 'scheduled' CHECK (status IN ('scheduled', 'waiting', 'in_progress', 'completed', 'cancelled', 'no_show')),
                session_notes TEXT,
                prescription_issued INTEGER DEFAULT 0,
                follow_up_required INTEGER DEFAULT 0,
                follow_up_date DATE,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE,
                FOREIGN KEY (patient_id) REFERENCES patients(id),
                FOREIGN KEY (doctor_id) REFERENCES doctors(id)
            )");
            $this->log("Created telemedicine_sessions table");
        }
        
        if (!$this->tableExists('remote_monitoring')) {
            $this->conn->exec("CREATE TABLE remote_monitoring (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                patient_id INTEGER NOT NULL,
                device_type TEXT CHECK (device_type IN ('blood_pressure', 'glucose', 'oximeter', 'heart_rate', 'weight', 'temperature', 'other')),
                reading_value TEXT NOT NULL,
                reading_unit TEXT,
                recorded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                alert_triggered INTEGER DEFAULT 0,
                reviewed_by INTEGER,
                reviewed_at DATETIME,
                notes TEXT,
                FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
                FOREIGN KEY (reviewed_by) REFERENCES users(id)
            )");
            $this->log("Created remote_monitoring table");
        }
        
        if (!$this->tableExists('telemedicine_prescriptions')) {
            $this->conn->exec("CREATE TABLE telemedicine_prescriptions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                session_id INTEGER NOT NULL,
                prescription_number TEXT UNIQUE NOT NULL,
                patient_id INTEGER NOT NULL,
                doctor_id INTEGER NOT NULL,
                medications TEXT NOT NULL,
                delivery_method TEXT CHECK (delivery_method IN ('pickup', 'home_delivery', 'courier')),
                delivery_address TEXT,
                delivery_status TEXT DEFAULT 'pending' CHECK (delivery_status IN ('pending', 'preparing', 'dispatched', 'delivered', 'cancelled')),
                pharmacist_notes TEXT,
                issued_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (session_id) REFERENCES telemedicine_sessions(id),
                FOREIGN KEY (patient_id) REFERENCES patients(id),
                FOREIGN KEY (doctor_id) REFERENCES doctors(id)
            )");
            $this->log("Created telemedicine_prescriptions table");
        }
    }
    
    private function migrateStep10_Reports() {
        $this->log("Step 10: Statistical Reports");
        
        if (!$this->tableExists('report_templates')) {
            $this->conn->exec("CREATE TABLE report_templates (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                report_name TEXT NOT NULL,
                report_type TEXT CHECK (report_type IN ('patient_demographics', 'revenue', 'occupancy', 'department_performance', 'doctor_performance', 'custom')),
                description TEXT,
                query_template TEXT,
                parameters TEXT,
                created_by INTEGER,
                is_public INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (created_by) REFERENCES users(id)
            )");
            $this->log("Created report_templates table");
        }
        
        if (!$this->tableExists('report_executions')) {
            $this->conn->exec("CREATE TABLE report_executions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                template_id INTEGER,
                executed_by INTEGER NOT NULL,
                parameters_used TEXT,
                execution_time DATETIME DEFAULT CURRENT_TIMESTAMP,
                file_path TEXT,
                FOREIGN KEY (template_id) REFERENCES report_templates(id),
                FOREIGN KEY (executed_by) REFERENCES users(id)
            )");
            $this->log("Created report_executions table");
        }
    }
    
    private function createIndexes() {
        $this->log("Creating performance indexes...");
        
        $indexes = [
            "CREATE INDEX IF NOT EXISTS idx_doctor_schedules_doctor ON doctor_schedules(doctor_id)",
            "CREATE INDEX IF NOT EXISTS idx_vital_signs_patient ON vital_signs(patient_id)",
            "CREATE INDEX IF NOT EXISTS idx_staff_role ON staff(role)",
            "CREATE INDEX IF NOT EXISTS idx_rooms_status ON rooms(status)",
            "CREATE INDEX IF NOT EXISTS idx_lab_orders_patient ON lab_orders(patient_id)",
            "CREATE INDEX IF NOT EXISTS idx_lab_orders_status ON lab_orders(status)",
            "CREATE INDEX IF NOT EXISTS idx_inventory_items_category ON inventory_items(category_id)",
            "CREATE INDEX IF NOT EXISTS idx_insurance_claims_status ON insurance_claims(status)",
            "CREATE INDEX IF NOT EXISTS idx_telemedicine_sessions_status ON telemedicine_sessions(status)"
        ];
        
        foreach ($indexes as $indexSQL) {
            $this->conn->exec($indexSQL);
        }
        
        $this->log("Indexes created successfully");
    }
}

// Run migration if called directly
if (php_sapi_name() === 'cli' || basename($_SERVER['PHP_SELF']) === 'migrate_v2.php') {
    $migration = new HMS2Migration();
    $result = $migration->migrate();
    
    echo "\n" . str_repeat("=", 60) . "\n";
    echo $result['success'] ? "✓ SUCCESS" : "✗ FAILED";
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "Log file: " . $result['log_file'] . "\n";
    
    exit($result['success'] ? 0 : 1);
}
?>
