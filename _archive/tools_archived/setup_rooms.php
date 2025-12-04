<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

$database = new Database();
$conn = $database->getConnection();

// Check/create wards first
$stmt = $conn->query("SELECT COUNT(*) as count FROM wards");
$wardCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

if ($wardCount == 0) {
    echo "Creating default wards...\n";
    $conn->exec("INSERT INTO wards (ward_name, floor_number, total_beds, available_beds, ward_type) 
                 VALUES ('General Ward 1', 1, 20, 20, 'general')");
    $conn->exec("INSERT INTO wards (ward_name, floor_number, total_beds, available_beds, ward_type) 
                 VALUES ('Private Ward', 2, 10, 10, 'private')");
    $conn->exec("INSERT INTO wards (ward_name, floor_number, total_beds, available_beds, ward_type) 
                 VALUES ('ICU Ward', 3, 5, 5, 'icu')");
    echo "✓ Wards created\n\n";
}

// Check rooms
$stmt = $conn->query("SELECT id, room_number, room_type, charge_per_day FROM rooms LIMIT 3");
$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($rooms) > 0) {
    echo "Rooms in database:\n";
    foreach ($rooms as $r) {
        $charge = $r['charge_per_day'] ?? 0;
        echo "  ID: {$r['id']}, Number: {$r['room_number']}, Type: {$r['room_type']}, Charge: ₹{$charge}\n";
    }
    
    // Update rooms with charge if NULL
    if ($rooms[0]['charge_per_day'] === null || $rooms[0]['charge_per_day'] == 0) {
        echo "\nUpdating rooms with default charges...\n";
        $conn->exec("UPDATE rooms SET charge_per_day = 500 WHERE room_type = 'General' AND (charge_per_day IS NULL OR charge_per_day = 0)");
        $conn->exec("UPDATE rooms SET charge_per_day = 1000 WHERE room_type = 'Private' AND (charge_per_day IS NULL OR charge_per_day = 0)");
        $conn->exec("UPDATE rooms SET charge_per_day = 2000 WHERE room_type = 'ICU' AND (charge_per_day IS NULL OR charge_per_day = 0)");
        echo "✓ Rooms updated with charges\n";
    }
} else {
    echo "No rooms found. Creating test rooms...\n";
    
    // Get ward IDs
    $stmt = $conn->query("SELECT id, ward_type FROM wards");
    $wards = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $wardMap = [];
    foreach ($wards as $w) {
        $wardMap[$w['ward_type']] = $w['id'];
    }
    
    $generalWardId = $wardMap['general'] ?? 1;
    $privateWardId = $wardMap['private'] ?? 2;
    $icuWardId = $wardMap['icu'] ?? 3;
    
    $conn->exec("INSERT INTO rooms (room_number, ward_id, room_type, status, charge_per_day) VALUES ('101', $generalWardId, 'single', 'available', 500)");
    $conn->exec("INSERT INTO rooms (room_number, ward_id, room_type, status, charge_per_day) VALUES ('102', $generalWardId, 'double', 'available', 400)");
    $conn->exec("INSERT INTO rooms (room_number, ward_id, room_type, status, charge_per_day) VALUES ('103', $generalWardId, 'shared', 'available', 300)");
    $conn->exec("INSERT INTO rooms (room_number, ward_id, room_type, status, charge_per_day) VALUES ('301', $icuWardId, 'icu', 'available', 2000)");
    echo "✓ Test rooms created\n";
}
