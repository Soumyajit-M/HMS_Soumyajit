<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

$db = new Database();
$conn = $db->getConnection();

try {
    $conn->exec("UPDATE billing SET patient_id = (
        SELECT id FROM patients WHERE patients.patient_id = billing.patient_id LIMIT 1
    ) WHERE EXISTS (
        SELECT 1 FROM patients WHERE patients.patient_id = billing.patient_id
    );");

    $conn->exec("UPDATE billing SET appointment_id = (
        SELECT id FROM appointments WHERE appointments.appointment_id = billing.appointment_id LIMIT 1
    ) WHERE EXISTS (
        SELECT 1 FROM appointments WHERE appointments.appointment_id = billing.appointment_id
    );");

    echo "Billing identifiers normalized.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
