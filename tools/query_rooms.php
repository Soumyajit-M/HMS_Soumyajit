<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

$db = new Database();
$conn = $db->getConnection();

$result = $conn->query('SELECT id, room_number, ward_id, total_beds, available_beds, status FROM rooms LIMIT 10');
print_r($result->fetchAll(PDO::FETCH_ASSOC));
