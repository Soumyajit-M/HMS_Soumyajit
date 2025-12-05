<?php
require_once 'config/config.php';
require_once 'config/database.php';

$database = new Database();
$pdo = $database->getConnection();

echo "Fixing staff table role constraint...\n\n";

try {
    // Start transaction
    $pdo->beginTransaction();
    
    // Create new staff table with correct constraint
    $pdo->exec("
        CREATE TABLE staff_new (
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
            certifications TEXT,
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
        )
    ");
    
    // Copy data from old table to new table
    $pdo->exec("
        INSERT INTO staff_new 
        SELECT * FROM staff
    ");
    
    // Drop old table
    $pdo->exec("DROP TABLE staff");
    
    // Rename new table to staff
    $pdo->exec("ALTER TABLE staff_new RENAME TO staff");
    
    // Commit transaction
    $pdo->commit();
    
    echo "✓ Staff table constraint fixed successfully!\n";
    echo "✓ All data preserved.\n";
    
} catch (PDOException $e) {
    $pdo->rollBack();
    echo "✗ Error: " . $e->getMessage() . "\n";
}
