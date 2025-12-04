-- ============================================
-- Hospital Management System - Complete Database Schema
-- SQLite Version - Unified Schema
-- ============================================
-- This schema consolidates:
--   - schema_sqlite.sql (base schema)
--   - auto_billing_schema.sql (billing extensions)
--   - schema_updates_v2.sql (HMS 2.0 features)
--
-- Generated: 2024-12-04
-- Backups: database/backups/schema_backup_YYYYMMDD_HHMMSS/
-- ============================================

-- ============================================
-- 1. CORE AUTHENTICATION & USER MANAGEMENT
-- ============================================

-- Users table for authentication
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    email TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL,
    role TEXT NOT NULL CHECK (
        role IN (
            'admin',
            'doctor',
            'nurse',
            'receptionist',
            'pharmacist'
        )
    ),
    first_name TEXT NOT NULL,
    last_name TEXT NOT NULL,
    phone TEXT,
    address TEXT,
    profile_image TEXT,
    is_active INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- 2. DEPARTMENTS
-- ============================================

-- Departments table
CREATE TABLE departments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    description TEXT,
    head_doctor_id INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (head_doctor_id) REFERENCES doctors (id)
);

-- ============================================
-- 3. PATIENT MANAGEMENT
-- ============================================

-- Patients table
CREATE TABLE patients (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    patient_id TEXT UNIQUE NOT NULL,
    first_name TEXT NOT NULL,
    last_name TEXT NOT NULL,
    email TEXT,
    phone TEXT NOT NULL,
    date_of_birth DATE NOT NULL,
    gender TEXT NOT NULL CHECK (
        gender IN ('male', 'female', 'other')
    ),
    address TEXT,
    emergency_contact_name TEXT,
    emergency_contact_phone TEXT,
    emergency_contact_email TEXT,
    blood_type TEXT,
    allergies TEXT,
    medical_history TEXT,
    insurance_provider TEXT,
    insurance_number TEXT,
    profile_image TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- 4. DOCTOR MANAGEMENT
-- ============================================

-- Doctors table
CREATE TABLE doctors (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    doctor_id TEXT UNIQUE NOT NULL,
    specialization TEXT NOT NULL,
    qualification TEXT,
    experience_years INTEGER,
    consultation_fee REAL,
    available_days TEXT,
    available_time_start TEXT,
    available_time_end TEXT,
    bio TEXT,
    emergency_phone TEXT,
    emergency_email TEXT,
    license_number TEXT,
    created_at DATETIME,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
);

-- Doctor schedules table for detailed time slot management
CREATE TABLE doctor_schedules (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    doctor_id INTEGER NOT NULL,
    day_of_week TEXT NOT NULL CHECK (
        day_of_week IN (
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
            'Friday',
            'Saturday',
            'Sunday'
        )
    ),
    start_time TEXT NOT NULL,
    end_time TEXT NOT NULL,
    slot_duration INTEGER DEFAULT 30, -- minutes
    max_patients_per_slot INTEGER DEFAULT 1,
    is_active INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (doctor_id) REFERENCES doctors (id) ON DELETE CASCADE
);

-- Doctor leave/unavailability tracking
CREATE TABLE doctor_leaves (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    doctor_id INTEGER NOT NULL,
    leave_type TEXT CHECK (
        leave_type IN (
            'vacation',
            'sick',
            'conference',
            'emergency',
            'other'
        )
    ),
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    reason TEXT,
    status TEXT DEFAULT 'pending' CHECK (
        status IN (
            'pending',
            'approved',
            'rejected'
        )
    ),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (doctor_id) REFERENCES doctors (id) ON DELETE CASCADE
);

-- ============================================
-- 5. APPOINTMENTS
-- ============================================

-- Appointments table
CREATE TABLE appointments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    appointment_id TEXT UNIQUE NOT NULL,
    patient_id INTEGER NOT NULL,
    doctor_id INTEGER NOT NULL,
    department_id INTEGER,
    appointment_date DATE NOT NULL,
    appointment_time TEXT NOT NULL,
    appointment_type TEXT DEFAULT 'routine' CHECK (
        appointment_type IN (
            'routine',
            'emergency',
            'follow-up'
        )
    ),
    status TEXT DEFAULT 'scheduled' CHECK (
        status IN (
            'scheduled',
            'confirmed',
            'in_progress',
            'completed',
            'cancelled'
        )
    ),
    reason TEXT,
    notes TEXT,
    created_by INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients (id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors (id) ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES departments (id),
    FOREIGN KEY (created_by) REFERENCES users (id)
);

-- ============================================
-- 6. MEDICAL RECORDS & VITAL SIGNS
-- ============================================

-- Medical records table
CREATE TABLE medical_records (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    patient_id INTEGER NOT NULL,
    doctor_id INTEGER NOT NULL,
    appointment_id INTEGER,
    diagnosis TEXT,
    symptoms TEXT,
    treatment TEXT,
    prescription TEXT,
    vital_signs TEXT,
    lab_results TEXT,
    follow_up_date DATE,
    allergies TEXT,
    chronic_conditions TEXT,
    current_medications TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients (id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors (id) ON DELETE CASCADE,
    FOREIGN KEY (appointment_id) REFERENCES appointments (id)
);

-- Vital signs tracking
CREATE TABLE vital_signs (
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
    FOREIGN KEY (patient_id) REFERENCES patients (id) ON DELETE CASCADE,
    FOREIGN KEY (appointment_id) REFERENCES appointments (id),
    FOREIGN KEY (recorded_by) REFERENCES users (id)
);

-- Immunization records
CREATE TABLE immunizations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    patient_id INTEGER NOT NULL,
    vaccine_name TEXT NOT NULL,
    dose_number INTEGER,
    administered_date DATE NOT NULL,
    next_dose_date DATE,
    administered_by INTEGER,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients (id) ON DELETE CASCADE,
    FOREIGN KEY (administered_by) REFERENCES users (id)
);

-- ============================================
-- 7. STAFF MANAGEMENT
-- ============================================

CREATE TABLE staff (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    staff_id TEXT UNIQUE NOT NULL,
    user_id INTEGER,
    first_name TEXT NOT NULL,
    last_name TEXT NOT NULL,
    role TEXT NOT NULL CHECK (
        role IN (
            'nurse',
            'technician',
            'receptionist',
            'pharmacist',
            'lab_technician',
            'radiologist',
            'other'
        )
    ),
    department_id INTEGER,
    phone TEXT NOT NULL,
    emergency_contact_name TEXT,
    emergency_contact_phone TEXT,
    email TEXT,
    qualification TEXT,
    certifications TEXT, -- JSON array
    license_number TEXT,
    date_of_joining DATE,
    employment_type TEXT CHECK (
        employment_type IN (
            'full-time',
            'part-time',
            'contract',
            'temporary'
        )
    ),
    salary REAL,
    is_active INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL,
    FOREIGN KEY (department_id) REFERENCES departments (id)
);

-- Staff schedules/shifts
CREATE TABLE staff_shifts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    staff_id INTEGER NOT NULL,
    shift_date DATE NOT NULL,
    start_time TEXT NOT NULL,
    end_time TEXT NOT NULL,
    shift_type TEXT,
    status TEXT DEFAULT 'scheduled',
    assigned_ward TEXT,
    created_at DATETIME,
    FOREIGN KEY (staff_id) REFERENCES staff (id) ON DELETE CASCADE
);

-- ============================================
-- 8. ROOM & BED MANAGEMENT
-- ============================================

CREATE TABLE wards (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ward_name TEXT NOT NULL,
    ward_type TEXT CHECK (
        ward_type IN (
            'general',
            'icu',
            'emergency',
            'maternity',
            'pediatric',
            'surgery',
            'private'
        )
    ),
    floor_number INTEGER,
    total_beds INTEGER NOT NULL,
    available_beds INTEGER NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE rooms (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    room_number TEXT UNIQUE NOT NULL,
    ward_id INTEGER NOT NULL,
    room_type TEXT CHECK (
        room_type IN (
            'single',
            'double',
            'shared',
            'icu',
            'operation_theater'
        )
    ),
    floor_number INTEGER,
    total_beds INTEGER DEFAULT 1,
    available_beds INTEGER DEFAULT 1,
    amenities TEXT, -- JSON array
    daily_rate REAL,
    charge_per_day REAL, -- Backward compatibility alias for daily_rate
    status TEXT DEFAULT 'available' CHECK (
        status IN (
            'available',
            'occupied',
            'maintenance',
            'cleaning',
            'reserved'
        )
    ),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ward_id) REFERENCES wards (id) ON DELETE CASCADE
);

CREATE TABLE bed_assignments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    room_id INTEGER NOT NULL,
    bed_number TEXT NOT NULL,
    patient_id INTEGER,
    admission_date DATETIME NOT NULL,
    discharge_date DATETIME,
    status TEXT DEFAULT 'occupied' CHECK (
        status IN (
            'occupied',
            'available',
            'reserved'
        )
    ),
    notes TEXT,
    assigned_by INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (room_id) REFERENCES rooms (id) ON DELETE CASCADE,
    FOREIGN KEY (patient_id) REFERENCES patients (id),
    FOREIGN KEY (assigned_by) REFERENCES users (id)
);

-- ============================================
-- 9. LABORATORY MODULE
-- ============================================

CREATE TABLE lab_test_types (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    test_code TEXT UNIQUE NOT NULL,
    test_name TEXT NOT NULL,
    test_category TEXT,
    description TEXT,
    sample_type TEXT CHECK (
        sample_type IN (
            'blood',
            'urine',
            'stool',
            'tissue',
            'swab',
            'other'
        )
    ),
    normal_range TEXT,
    cost REAL NOT NULL,
    turnaround_time INTEGER, -- in hours
    requires_fasting INTEGER DEFAULT 0,
    is_active INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE lab_orders (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    order_number TEXT UNIQUE NOT NULL,
    patient_id INTEGER NOT NULL,
    doctor_id INTEGER NOT NULL,
    appointment_id INTEGER,
    order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    priority TEXT DEFAULT 'routine' CHECK (
        priority IN ('routine', 'urgent', 'stat')
    ),
    status TEXT DEFAULT 'pending' CHECK (
        status IN (
            'pending',
            'sample_collected',
            'in_progress',
            'completed',
            'cancelled'
        )
    ),
    clinical_notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients (id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors (id),
    FOREIGN KEY (appointment_id) REFERENCES appointments (id)
);

CREATE TABLE lab_order_tests (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    lab_order_id INTEGER NOT NULL,
    test_type_id INTEGER NOT NULL,
    sample_collected_at DATETIME,
    sample_collected_by INTEGER,
    result_value TEXT,
    result_unit TEXT,
    result_status TEXT CHECK (
        result_status IN (
            'normal',
            'abnormal',
            'critical'
        )
    ),
    result_notes TEXT,
    tested_by INTEGER,
    verified_by INTEGER,
    completed_at DATETIME,
    FOREIGN KEY (lab_order_id) REFERENCES lab_orders (id) ON DELETE CASCADE,
    FOREIGN KEY (test_type_id) REFERENCES lab_test_types (id),
    FOREIGN KEY (sample_collected_by) REFERENCES users (id),
    FOREIGN KEY (tested_by) REFERENCES users (id),
    FOREIGN KEY (verified_by) REFERENCES users (id)
);

-- ============================================
-- 10. INVENTORY/SUPPLIES MANAGEMENT
-- ============================================

CREATE TABLE inventory_categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    category_name TEXT UNIQUE NOT NULL,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE inventory_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    item_code TEXT UNIQUE NOT NULL,
    item_name TEXT NOT NULL,
    category_id INTEGER NOT NULL,
    item_type TEXT CHECK (
        item_type IN (
            'medication',
            'instrument',
            'consumable',
            'equipment',
            'other'
        )
    ),
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
    FOREIGN KEY (category_id) REFERENCES inventory_categories (id)
);

CREATE TABLE inventory_batches (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    item_id INTEGER NOT NULL,
    batch_number TEXT NOT NULL,
    quantity INTEGER NOT NULL,
    manufacturing_date DATE,
    expiry_date DATE,
    purchase_date DATE,
    purchase_price REAL,
    current_quantity INTEGER NOT NULL,
    status TEXT DEFAULT 'active' CHECK (
        status IN (
            'active',
            'expired',
            'recalled',
            'depleted'
        )
    ),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES inventory_items (id) ON DELETE CASCADE
);

CREATE TABLE inventory_transactions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    item_id INTEGER NOT NULL,
    batch_id INTEGER,
    transaction_type TEXT NOT NULL CHECK (
        transaction_type IN (
            'purchase',
            'issue',
            'return',
            'adjustment',
            'transfer',
            'waste'
        )
    ),
    quantity INTEGER NOT NULL,
    transaction_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    reference_type TEXT, -- 'billing', 'patient', 'department'
    reference_id INTEGER,
    performed_by INTEGER,
    notes TEXT,
    FOREIGN KEY (item_id) REFERENCES inventory_items (id),
    FOREIGN KEY (batch_id) REFERENCES inventory_batches (id),
    FOREIGN KEY (performed_by) REFERENCES users (id)
);

-- Legacy inventory table for backwards compatibility
CREATE TABLE inventory (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    item_name TEXT NOT NULL,
    category TEXT,
    description TEXT,
    quantity INTEGER NOT NULL,
    unit_price REAL,
    supplier TEXT,
    expiry_date DATE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- 11. BILLING & PAYMENTS (COMPREHENSIVE)
-- ============================================

-- Billing table with all enhancements
CREATE TABLE billing (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    bill_number TEXT UNIQUE NOT NULL,
    patient_id INTEGER NOT NULL,
    appointment_id INTEGER,
    total_amount REAL NOT NULL,
    paid_amount REAL DEFAULT 0,
    balance_amount REAL,
    discount_amount REAL DEFAULT 0,
    tax_amount REAL DEFAULT 0,
    insurance_claim_amount REAL DEFAULT 0,
    payment_method TEXT,
    payment_status TEXT DEFAULT 'pending' CHECK (
        payment_status IN (
            'pending',
            'partial',
            'paid',
            'overdue'
        )
    ),
    billing_type TEXT CHECK (
        billing_type IN (
            'consultation',
            'procedure',
            'lab',
            'pharmacy',
            'room',
            'other'
        )
    ),
    due_date DATE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients (id) ON DELETE CASCADE,
    FOREIGN KEY (appointment_id) REFERENCES appointments (id)
);

-- Billing items table (unified from both schemas)
CREATE TABLE billing_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    bill_id INTEGER NOT NULL,
    billing_id INTEGER, -- For backwards compatibility
    item_name TEXT,
    item_type TEXT, -- 'Bed Charge', 'Lab Test', 'Doctor Consultation', 'Medicine', 'Procedure'
    description TEXT,
    unit_price REAL NOT NULL,
    quantity INTEGER DEFAULT 1,
    total_price REAL,
    charge_type TEXT DEFAULT 'one-time', -- 'one-time' or 'daily'
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bill_id) REFERENCES billing (id) ON DELETE CASCADE,
    FOREIGN KEY (billing_id) REFERENCES billing (id) ON DELETE CASCADE
);

-- Billing item tracking table to link items to their sources
CREATE TABLE billing_item_tracking (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    bill_id INTEGER NOT NULL,
    billing_item_id INTEGER NOT NULL,
    item_type TEXT NOT NULL, -- 'admission', 'lab_test', 'consultation', 'medicine'
    reference_id INTEGER, -- ID from source table (room_id, test_id, doctor_id, item_id)
    service_date DATETIME,
    order_id INTEGER, -- Link to lab_orders or other order tables
    quantity INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bill_id) REFERENCES billing (id) ON DELETE CASCADE,
    FOREIGN KEY (billing_item_id) REFERENCES billing_items (id) ON DELETE CASCADE
);

-- Payments table
CREATE TABLE payments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    payment_id TEXT UNIQUE NOT NULL,
    billing_id INTEGER NOT NULL,
    amount REAL NOT NULL,
    payment_method TEXT NOT NULL CHECK (
        payment_method IN (
            'cash',
            'card',
            'bank_transfer',
            'insurance'
        )
    ),
    payment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    transaction_id TEXT,
    notes TEXT,
    created_by INTEGER,
    FOREIGN KEY (billing_id) REFERENCES billing (id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users (id)
);

-- Procedure codes for standardized billing
CREATE TABLE procedure_codes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    code TEXT UNIQUE NOT NULL,
    description TEXT NOT NULL,
    category TEXT,
    base_price REAL NOT NULL,
    is_active INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Payment plans for installments
CREATE TABLE payment_plans (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    billing_id INTEGER NOT NULL,
    total_amount REAL NOT NULL,
    down_payment REAL DEFAULT 0,
    installment_amount REAL NOT NULL,
    installment_count INTEGER NOT NULL,
    installments_paid INTEGER DEFAULT 0,
    start_date DATE NOT NULL,
    status TEXT DEFAULT 'active' CHECK (
        status IN (
            'active',
            'completed',
            'defaulted',
            'cancelled'
        )
    ),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (billing_id) REFERENCES billing (id) ON DELETE CASCADE
);

-- ============================================
-- 12. INSURANCE INTEGRATION
-- ============================================

CREATE TABLE insurance_providers (
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
);

CREATE TABLE patient_insurance (
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
    status TEXT DEFAULT 'active' CHECK (
        status IN (
            'active',
            'expired',
            'cancelled',
            'suspended'
        )
    ),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients (id) ON DELETE CASCADE,
    FOREIGN KEY (insurance_provider_id) REFERENCES insurance_providers (id)
);

CREATE TABLE insurance_claims (
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
    status TEXT DEFAULT 'draft' CHECK (
        status IN (
            'draft',
            'submitted',
            'under_review',
            'approved',
            'rejected',
            'settled',
            'appealed'
        )
    ),
    rejection_reason TEXT,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_insurance_id) REFERENCES patient_insurance (id),
    FOREIGN KEY (billing_id) REFERENCES billing (id) ON DELETE CASCADE
);

-- ============================================
-- 13. TELEMEDICINE PLATFORM
-- ============================================

CREATE TABLE telemedicine_sessions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    session_id TEXT UNIQUE NOT NULL,
    appointment_id INTEGER NOT NULL,
    patient_id INTEGER NOT NULL,
    doctor_id INTEGER NOT NULL,
    scheduled_time DATETIME NOT NULL,
    start_time DATETIME,
    end_time DATETIME,
    session_type TEXT CHECK (
        session_type IN ('video', 'audio', 'chat')
    ),
    platform TEXT, -- 'webrtc', 'zoom', 'jitsi', etc.
    meeting_link TEXT,
    access_code TEXT,
    status TEXT DEFAULT 'scheduled' CHECK (
        status IN (
            'scheduled',
            'waiting',
            'in_progress',
            'completed',
            'cancelled',
            'no_show'
        )
    ),
    session_notes TEXT,
    prescription_issued INTEGER DEFAULT 0,
    follow_up_required INTEGER DEFAULT 0,
    follow_up_date DATE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (appointment_id) REFERENCES appointments (id) ON DELETE CASCADE,
    FOREIGN KEY (patient_id) REFERENCES patients (id),
    FOREIGN KEY (doctor_id) REFERENCES doctors (id)
);

CREATE TABLE remote_monitoring (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    patient_id INTEGER NOT NULL,
    device_type TEXT CHECK (
        device_type IN (
            'blood_pressure',
            'glucose',
            'oximeter',
            'heart_rate',
            'weight',
            'temperature',
            'other'
        )
    ),
    reading_value TEXT NOT NULL,
    reading_unit TEXT,
    recorded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    alert_triggered INTEGER DEFAULT 0,
    reviewed_by INTEGER,
    reviewed_at DATETIME,
    notes TEXT,
    FOREIGN KEY (patient_id) REFERENCES patients (id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users (id)
);

CREATE TABLE telemedicine_prescriptions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    session_id INTEGER NOT NULL,
    prescription_number TEXT UNIQUE NOT NULL,
    patient_id INTEGER NOT NULL,
    doctor_id INTEGER NOT NULL,
    medications TEXT NOT NULL, -- JSON array
    delivery_method TEXT CHECK (
        delivery_method IN (
            'pickup',
            'home_delivery',
            'courier'
        )
    ),
    delivery_address TEXT,
    delivery_status TEXT DEFAULT 'pending' CHECK (
        delivery_status IN (
            'pending',
            'preparing',
            'dispatched',
            'delivered',
            'cancelled'
        )
    ),
    pharmacist_notes TEXT,
    issued_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES telemedicine_sessions (id),
    FOREIGN KEY (patient_id) REFERENCES patients (id),
    FOREIGN KEY (doctor_id) REFERENCES doctors (id)
);

-- ============================================
-- 14. REPORTING & ANALYTICS
-- ============================================

-- Report templates for saved queries
CREATE TABLE report_templates (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    report_name TEXT NOT NULL,
    report_type TEXT CHECK (
        report_type IN (
            'patient_demographics',
            'revenue',
            'occupancy',
            'department_performance',
            'doctor_performance',
            'custom'
        )
    ),
    description TEXT,
    query_template TEXT,
    parameters TEXT, -- JSON
    created_by INTEGER,
    is_public INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users (id)
);

-- Saved report executions
CREATE TABLE report_executions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    template_id INTEGER,
    executed_by INTEGER NOT NULL,
    parameters_used TEXT, -- JSON
    execution_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    file_path TEXT,
    FOREIGN KEY (template_id) REFERENCES report_templates (id),
    FOREIGN KEY (executed_by) REFERENCES users (id)
);

-- ============================================
-- 15. SYSTEM NOTIFICATIONS & LOGS
-- ============================================

-- Notifications table
CREATE TABLE notifications (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    title TEXT NOT NULL,
    message TEXT NOT NULL,
    type TEXT DEFAULT 'info' CHECK (
        type IN (
            'info',
            'warning',
            'success',
            'error'
        )
    ),
    is_read INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
);

-- AI Assistant logs table
CREATE TABLE ai_assistant_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    query TEXT NOT NULL,
    response TEXT NOT NULL,
    context TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
);

-- ============================================
-- 16. SYSTEM SETTINGS
-- ============================================

-- System settings table
CREATE TABLE system_settings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    setting_key TEXT UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type TEXT DEFAULT 'string',
    description TEXT,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- 17. PERFORMANCE INDEXES
-- ============================================

-- Core indexes
CREATE INDEX IF NOT EXISTS idx_billing_items_bill ON billing_items (bill_id);

CREATE INDEX IF NOT EXISTS idx_billing_tracking_bill ON billing_item_tracking (bill_id);

CREATE INDEX IF NOT EXISTS idx_billing_tracking_type ON billing_item_tracking (item_type, reference_id);

CREATE INDEX IF NOT EXISTS idx_doctor_schedules_doctor ON doctor_schedules (doctor_id);

CREATE INDEX IF NOT EXISTS idx_vital_signs_patient ON vital_signs (patient_id);

CREATE INDEX IF NOT EXISTS idx_staff_role ON staff (role);

CREATE INDEX IF NOT EXISTS idx_rooms_status ON rooms (status);

CREATE INDEX IF NOT EXISTS idx_lab_orders_patient ON lab_orders (patient_id);

CREATE INDEX IF NOT EXISTS idx_lab_orders_status ON lab_orders (status);

CREATE INDEX IF NOT EXISTS idx_inventory_items_category ON inventory_items (category_id);

CREATE INDEX IF NOT EXISTS idx_insurance_claims_status ON insurance_claims (status);

CREATE INDEX IF NOT EXISTS idx_telemedicine_sessions_status ON telemedicine_sessions (status);

-- ============================================
-- 18. DEFAULT DATA
-- ============================================

-- Insert default admin user
INSERT INTO
    users (
        username,
        email,
        password,
        role,
        first_name,
        last_name
    )
VALUES (
        'admin',
        'admin@hospital.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'admin',
        'System',
        'Administrator'
    );

-- Insert default departments
INSERT INTO
    departments (name, description)
VALUES (
        'Cardiology',
        'Heart and cardiovascular diseases'
    ),
    (
        'Neurology',
        'Brain and nervous system disorders'
    ),
    (
        'Orthopedics',
        'Bone and joint problems'
    ),
    (
        'Pediatrics',
        'Children healthcare'
    ),
    (
        'Emergency',
        'Emergency medical care'
    ),
    (
        'General Medicine',
        'General health issues'
    );

-- Insert default system settings
INSERT INTO
    system_settings (
        setting_key,
        setting_value,
        description
    )
VALUES (
        'hospital_name',
        'City General Hospital',
        'Hospital name'
    ),
    (
        'hospital_address',
        '123 Medical Street, City, State 12345',
        'Hospital address'
    ),
    (
        'hospital_phone',
        '+1-234-567-8900',
        'Hospital contact number'
    ),
    (
        'currency',
        'USD',
        'Default currency'
    ),
    (
        'timezone',
        'UTC',
        'System timezone'
    );