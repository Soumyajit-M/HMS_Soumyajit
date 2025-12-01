<?php
require_once 'config/config.php';
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

echo "Checking doctor_schedules table structure:\n\n";

$result = $db->query("PRAGMA table_info(doctor_schedules)");
$columns = $result->fetchAll(PDO::FETCH_ASSOC);

if (empty($columns)) {
    echo "Table doctor_schedules does not exist!\n";
} else {
    echo "Columns in doctor_schedules table:\n";
    foreach ($columns as $column) {
        echo "- " . $column['name'] . " (" . $column['type'] . ")\n";
    }
}

echo "\n\nAdding missing is_available column...\n";

try {
    $db->exec("ALTER TABLE doctor_schedules ADD COLUMN is_available INTEGER DEFAULT 1");
    echo "is_available column added successfully!\n";
} catch (PDOException $e) {
    echo "is_available: " . $e->getMessage() . "\n";
}

echo "\nAdding missing room_number column...\n";

try {
    $db->exec("ALTER TABLE doctor_schedules ADD COLUMN room_number TEXT");
    echo "room_number column added successfully!\n";
} catch (PDOException $e) {
    echo "room_number: " . $e->getMessage() . "\n";
}

echo "\n\nUpdated table structure:\n";
$result = $db->query("PRAGMA table_info(doctor_schedules)");
$columns = $result->fetchAll(PDO::FETCH_ASSOC);

foreach ($columns as $column) {
    echo "- " . $column['name'] . " (" . $column['type'] . ")\n";
}
