<?php
require_once 'config/config.php';
require_once 'config/database.php';

$database = new Database();
$pdo = $database->getConnection();

$stmt = $pdo->query('SELECT * FROM departments ORDER BY name');
$departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($departments, JSON_PRETTY_PRINT);
