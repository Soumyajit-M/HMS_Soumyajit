-- HMS 2.0 Database Schema Updates
-- Comprehensive Hospital Management Features

-- ============================================
-- 1. DOCTOR SCHEDULES & EMERGENCY CONTACTS
-- ============================================

-- Add emergency contact fields to doctors table
ALTER TABLE doctors ADD COLUMN emergency_phone TEXT;

ALTER TABLE doctors ADD COLUMN emergency_email TEXT;

ALTER TABLE doctors ADD COLUMN license_number TEXT;

-- Doctor schedules table for detailed time slot management
CREATE TABLE IF NOT EXISTS doctor_schedules (
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
CREATE TABLE IF NOT EXISTS doctor_leaves (
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
-- 2. ENHANCED PATIENT MEDICAL HISTORY
-- ============================================

-- Medical history records (already exists, enhanced)
ALTER TABLE medical_records ADD COLUMN allergies TEXT;

ALTER TABLE medical_records ADD COLUMN chronic_conditions TEXT;

ALTER TABLE medical_records ADD COLUMN current_medications TEXT;

-- Vital signs tracking
CREATE TABLE IF NOT EXISTS vital_signs (
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
CREATE TABLE IF NOT EXISTS immunizations (
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
-- 3. STAFF MANAGEMENT
-- ============================================

CREATE TABLE IF NOT EXISTS staff (
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
CREATE TABLE IF NOT EXISTS staff_shifts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    staff_id INTEGER NOT NULL,
    shift_date DATE NOT NULL,
    start_time TEXT NOT NULL,
    end_time TEXT NOT NULL,
    shift_type TEXT CHECK (
        shift_type IN (
            'morning',
            'evening',
            'night',
            'full-day'
        )
    ),
    status TEXT DEFAULT 'scheduled' CHECK (
        status IN (
            'scheduled',
            'completed',
            'absent',
            'cancelled'
        )
    ),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (staff_id) REFERENCES staff (id) ON DELETE CASCADE
);

-- ============================================
-- 4. ROOM OCCUPANCY MANAGEMENT
-- ============================================

CREATE TABLE IF NOT EXISTS wards (
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

CREATE TABLE IF NOT EXISTS rooms (
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

CREATE TABLE IF NOT EXISTS bed_assignments (
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
-- 5. LABORATORY MODULE
-- ============================================

CREATE TABLE IF NOT EXISTS lab_test_types (
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

CREATE TABLE IF NOT EXISTS lab_orders (
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

CREATE TABLE IF NOT EXISTS lab_order_tests (
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
-- 6. INVENTORY/SUPPLIES MANAGEMENT
-- ============================================

CREATE TABLE IF NOT EXISTS inventory_categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    category_name TEXT UNIQUE NOT NULL,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS inventory_items (
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

CREATE TABLE IF NOT EXISTS inventory_batches (
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

CREATE TABLE IF NOT EXISTS inventory_transactions (
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

-- ============================================
-- 7. ENHANCED BILLING
-- ============================================

-- Billing items already exists, enhance billing table
ALTER TABLE billing ADD COLUMN discount_amount REAL DEFAULT 0;

ALTER TABLE billing ADD COLUMN tax_amount REAL DEFAULT 0;

ALTER TABLE billing ADD COLUMN insurance_claim_amount REAL DEFAULT 0;

ALTER TABLE billing ADD COLUMN payment_method TEXT;

ALTER TABLE billing
ADD COLUMN billing_type TEXT CHECK (
    billing_type IN (
        'consultation',
        'procedure',
        'lab',
        'pharmacy',
        'room',
        'other'
    )
);

-- Procedure codes for standardized billing
CREATE TABLE IF NOT EXISTS procedure_codes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    code TEXT UNIQUE NOT NULL,
    description TEXT NOT NULL,
    category TEXT,
    base_price REAL NOT NULL,
    is_active INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Payment plans for installments
CREATE TABLE IF NOT EXISTS payment_plans (
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
-- 8. INSURANCE INTEGRATION
-- ============================================

CREATE TABLE IF NOT EXISTS insurance_providers (
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

CREATE TABLE IF NOT EXISTS patient_insurance (
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

CREATE TABLE IF NOT EXISTS insurance_claims (
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
-- 9. TELEMEDICINE PLATFORM
-- ============================================

CREATE TABLE IF NOT EXISTS telemedicine_sessions (
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

CREATE TABLE IF NOT EXISTS remote_monitoring (
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

CREATE TABLE IF NOT EXISTS telemedicine_prescriptions (
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
-- 10. STATISTICAL REPORTS SUPPORT
-- ============================================

-- Report templates for saved queries
CREATE TABLE IF NOT EXISTS report_templates (
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
CREATE TABLE IF NOT EXISTS report_executions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    template_id INTEGER,
    executed_by INTEGER NOT NULL,
    parameters_used TEXT, -- JSON
    execution_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    file_path TEXT,
    FOREIGN KEY (template_id) REFERENCES report_templates (id),
    FOREIGN KEY (executed_by) REFERENCES users (id)
);

-- Create indexes for performance
CREATE INDEX IF NOT EXISTS idx_doctor_schedules_doctor ON doctor_schedules (doctor_id);

CREATE INDEX IF NOT EXISTS idx_vital_signs_patient ON vital_signs (patient_id);

CREATE INDEX IF NOT EXISTS idx_staff_role ON staff (role);

CREATE INDEX IF NOT EXISTS idx_rooms_status ON rooms (status);

CREATE INDEX IF NOT EXISTS idx_lab_orders_patient ON lab_orders (patient_id);

CREATE INDEX IF NOT EXISTS idx_lab_orders_status ON lab_orders (status);

CREATE INDEX IF NOT EXISTS idx_inventory_items_category ON inventory_items (category_id);

CREATE INDEX IF NOT EXISTS idx_insurance_claims_status ON insurance_claims (status);

CREATE INDEX IF NOT EXISTS idx_telemedicine_sessions_status ON telemedicine_sessions (status);