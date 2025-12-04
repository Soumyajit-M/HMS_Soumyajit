<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

$db = new Database();
$conn = $db->getConnection();

$table = $argv[1] ?? 'billing';

$stmt = $conn->query('SELECT * FROM ' . $table);
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
