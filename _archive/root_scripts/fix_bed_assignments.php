<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$db = new Database();
$conn = $db->getConnection();

$columns = [
    'expected_discharge_date' => 'DATE',
    'attending_doctor_id' => 'INTEGER'
];

foreach ($columns as $column => $type) {
    try {
        $conn->exec("ALTER TABLE bed_assignments ADD COLUMN $column $type");
        echo "Column '$column' added successfully!\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'duplicate column') !== false) {
            echo "Column '$column' already exists.\n";
        } else {
            echo "Error adding '$column': " . $e->getMessage() . "\n";
        }
    }
}
