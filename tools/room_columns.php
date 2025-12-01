<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

$db = new Database();
$conn = $db->getConnection();

$result = $conn->query('PRAGMA table_info(rooms)');
print_r($result->fetchAll(PDO::FETCH_ASSOC));
