<?php
require_once 'config/config.php';
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

echo "Creating beds table...\n\n";

// Create beds table
$sql = "CREATE TABLE IF NOT EXISTS beds (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    bed_number TEXT UNIQUE NOT NULL,
    ward_name TEXT NOT NULL,
    bed_type TEXT NOT NULL,
    status TEXT DEFAULT 'available' CHECK (status IN ('available', 'occupied', 'maintenance')),
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
)";

try {
    $db->exec($sql);
    echo "✓ Beds table created successfully\n";
} catch (PDOException $e) {
    echo "Error creating beds table: " . $e->getMessage() . "\n";
}

// Create bed_assignments table
echo "\nCreating bed_assignments table...\n";

$sql = "CREATE TABLE IF NOT EXISTS bed_assignments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    bed_id INTEGER NOT NULL,
    patient_id INTEGER NOT NULL,
    diagnosis TEXT,
    admitted_by TEXT,
    admitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    discharged_at DATETIME,
    discharge_notes TEXT,
    status TEXT DEFAULT 'active' CHECK (status IN ('active', 'discharged')),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bed_id) REFERENCES beds(id) ON DELETE CASCADE,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
)";

try {
    $db->exec($sql);
    echo "✓ Bed_assignments table created successfully\n";
} catch (PDOException $e) {
    echo "Error creating bed_assignments table: " . $e->getMessage() . "\n";
}

// Insert sample bed data
echo "\nInserting sample bed data...\n";

$sampleBeds = [
    ['BED-101', 'General Ward', 'Standard'],
    ['BED-102', 'General Ward', 'Standard'],
    ['BED-103', 'General Ward', 'Standard'],
    ['BED-201', 'ICU', 'ICU'],
    ['BED-202', 'ICU', 'ICU'],
    ['BED-301', 'Private Ward', 'VIP'],
    ['BED-302', 'Private Ward', 'VIP'],
    ['BED-401', 'Pediatric Ward', 'Pediatric'],
    ['BED-402', 'Pediatric Ward', 'Pediatric'],
    ['BED-501', 'Maternity Ward', 'Maternity']
];

$stmt = $db->prepare("INSERT INTO beds (bed_number, ward_name, bed_type) VALUES (?, ?, ?)");

foreach ($sampleBeds as $bed) {
    try {
        $stmt->execute($bed);
        echo "✓ Added bed: {$bed[0]}\n";
    } catch (PDOException $e) {
        echo "Error adding bed {$bed[0]}: " . $e->getMessage() . "\n";
    }
}

echo "\n✓ Database setup complete!\n";
