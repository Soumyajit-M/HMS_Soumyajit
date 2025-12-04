<?php
/**
 * Auto-Billing System Migration
 * Run this file once to add auto-billing tables to your database
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    echo "=== Auto-Billing System Migration ===\n\n";
    // Ensure base billing table exists (minimal schema) for FK references
    $hasBilling = $conn->query("SELECT name FROM sqlite_master WHERE type='table' AND name='billing'")->fetch(PDO::FETCH_ASSOC);
    if (!$hasBilling) {
        echo "Creating base billing table (minimal) ...\n";
        $conn->exec("CREATE TABLE IF NOT EXISTS billing (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            patient_id INTEGER,
            total_amount DECIMAL(10,2) DEFAULT 0,
            paid_amount DECIMAL(10,2) DEFAULT 0,
            balance_amount DECIMAL(10,2) DEFAULT 0,
            payment_status VARCHAR(20) DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        echo "✓ Base billing table created\n\n";
    }
    
    // Create billing_items table
    echo "Creating billing_items table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS billing_items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        bill_id INTEGER NOT NULL,
        item_type VARCHAR(50) NOT NULL,
        description TEXT,
        unit_price DECIMAL(10,2) NOT NULL,
        quantity INTEGER DEFAULT 1,
        charge_type VARCHAR(20) DEFAULT 'one-time',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (bill_id) REFERENCES billing(id) ON DELETE CASCADE
    )";
    $conn->exec($sql);
    echo "✓ billing_items table created\n\n";
    
    // Create billing_item_tracking table
    echo "Creating billing_item_tracking table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS billing_item_tracking (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        bill_id INTEGER NOT NULL,
        billing_item_id INTEGER NOT NULL,
        item_type VARCHAR(50) NOT NULL,
        reference_id INTEGER,
        service_date TIMESTAMP,
        order_id INTEGER,
        quantity INTEGER DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (bill_id) REFERENCES billing(id) ON DELETE CASCADE,
        FOREIGN KEY (billing_item_id) REFERENCES billing_items(id) ON DELETE CASCADE
    )";
    $conn->exec($sql);
    echo "✓ billing_item_tracking table created\n\n";
    
    // Add updated_at column to billing table
    echo "Adding updated_at column to billing table...\n";
    try {
        $sql = "ALTER TABLE billing ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
        $conn->exec($sql);
        echo "✓ updated_at column added\n\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'duplicate column name') !== false) {
            echo "✓ updated_at column already exists\n\n";
        } else {
            throw $e;
        }
    }
    
    // Create indexes
    echo "Creating indexes...\n";
    try {
        $conn->exec("CREATE INDEX IF NOT EXISTS idx_billing_items_bill ON billing_items(bill_id)");
        $conn->exec("CREATE INDEX IF NOT EXISTS idx_billing_tracking_bill ON billing_item_tracking(bill_id)");
        $conn->exec("CREATE INDEX IF NOT EXISTS idx_billing_tracking_type ON billing_item_tracking(item_type, reference_id)");
        echo "✓ Indexes created\n\n";
    } catch (PDOException $e) {
        echo "⚠ Index creation warning (may already exist): " . $e->getMessage() . "\n\n";
    }
    
    // Add lab_test_catalog table if doesn't exist (for test prices)
    echo "Creating lab_test_catalog table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS lab_test_catalog (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        test_name VARCHAR(100) NOT NULL,
        test_code VARCHAR(20) UNIQUE NOT NULL,
        category VARCHAR(50),
        standard_price DECIMAL(10,2) NOT NULL,
        description TEXT,
        is_active INTEGER DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);
    echo "✓ lab_test_catalog table created\n\n";
    
    // Ensure doctors table exists (minimal) then add consultation_fee
    $hasDoctors = $conn->query("SELECT name FROM sqlite_master WHERE type='table' AND name='doctors'")->fetch(PDO::FETCH_ASSOC);
    if (!$hasDoctors) {
        echo "Creating base doctors table (minimal) ...\n";
        $conn->exec("CREATE TABLE IF NOT EXISTS doctors (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            first_name TEXT,
            last_name TEXT,
            specialization TEXT,
            email TEXT,
            phone TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        echo "✓ Base doctors table created\n\n";
    }
    echo "Adding consultation_fee column to doctors table...\n";
    try {
        $sql = "ALTER TABLE doctors ADD COLUMN consultation_fee DECIMAL(10,2) DEFAULT 500.00";
        $conn->exec($sql);
        echo "✓ consultation_fee column added\n\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'duplicate column name') !== false) {
            echo "✓ consultation_fee column already exists\n\n";
        } else {
            throw $e;
        }
    }
    
    // Ensure rooms table exists (minimal) then add charge_per_day
    $hasRooms = $conn->query("SELECT name FROM sqlite_master WHERE type='table' AND name='rooms'")->fetch(PDO::FETCH_ASSOC);
    if (!$hasRooms) {
        echo "Creating base rooms table (minimal) ...\n";
        $conn->exec("CREATE TABLE IF NOT EXISTS rooms (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            room_number TEXT,
            bed_count INTEGER DEFAULT 1,
            status TEXT DEFAULT 'available',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        echo "✓ Base rooms table created\n\n";
    }
    echo "Adding charge_per_day column to rooms table...\n";
    try {
        $sql = "ALTER TABLE rooms ADD COLUMN charge_per_day DECIMAL(10,2) DEFAULT 1000.00";
        $conn->exec($sql);
        echo "✓ charge_per_day column added\n\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'duplicate column name') !== false) {
            echo "✓ charge_per_day column already exists\n\n";
        } else {
            throw $e;
        }
    }
    
    // Ensure inventory_items table exists (minimal) then add unit_price
    $hasInventory = $conn->query("SELECT name FROM sqlite_master WHERE type='table' AND name='inventory_items'")->fetch(PDO::FETCH_ASSOC);
    if (!$hasInventory) {
        echo "Creating base inventory_items table (minimal) ...\n";
        $conn->exec("CREATE TABLE IF NOT EXISTS inventory_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            item_name TEXT,
            sku TEXT,
            quantity INTEGER DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        echo "✓ Base inventory_items table created\n\n";
    }
    echo "Adding unit_price column to inventory_items table...\n";
    try {
        $sql = "ALTER TABLE inventory_items ADD COLUMN unit_price DECIMAL(10,2) DEFAULT 0.00";
        $conn->exec($sql);
        echo "✓ unit_price column added\n\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'duplicate column name') !== false) {
            echo "✓ unit_price column already exists\n\n";
        } else {
            throw $e;
        }
    }
    
    // Insert sample lab test catalog data
    echo "Inserting sample lab test data...\n";
    $tests = [
        ['Complete Blood Count (CBC)', 'CBC001', 'Hematology', 500.00],
        ['Blood Glucose', 'BG001', 'Biochemistry', 200.00],
        ['Lipid Profile', 'LP001', 'Biochemistry', 800.00],
        ['Liver Function Test', 'LFT001', 'Biochemistry', 1000.00],
        ['Kidney Function Test', 'KFT001', 'Biochemistry', 900.00],
        ['Thyroid Profile', 'TP001', 'Endocrinology', 1200.00],
        ['Urine Routine', 'UR001', 'Microbiology', 300.00],
        ['X-Ray Chest', 'XR001', 'Radiology', 600.00],
        ['ECG', 'ECG001', 'Cardiology', 400.00],
        ['Ultrasound Abdomen', 'US001', 'Radiology', 1500.00]
    ];
    
    $insertStmt = $conn->prepare("INSERT OR IGNORE INTO lab_test_catalog (test_name, test_code, category, standard_price) VALUES (?, ?, ?, ?)");
    
    foreach ($tests as $test) {
        $insertStmt->execute($test);
    }
    echo "✓ Sample lab test data inserted\n\n";
    
    echo "=== Migration Completed Successfully! ===\n\n";
    echo "Auto-Billing System is now ready to use.\n\n";
    echo "How it works:\n";
    echo "1. When a patient is admitted to a room - Bed charges auto-added\n";
    echo "2. When a doctor consultation is completed - Consultation fee auto-added\n";
    echo "3. When a lab test is ordered - Test charges auto-added\n";
    echo "4. When medicines are prescribed - Medicine costs auto-added\n";
    echo "5. On discharge - All charges are calculated and bill is finalized\n\n";
    
} catch (PDOException $e) {
    echo "✗ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
