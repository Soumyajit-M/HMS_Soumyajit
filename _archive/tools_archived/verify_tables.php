<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    // Check billing_items table
    echo "=== Checking billing_items table ===\n";
    $result = $conn->query("SELECT sql FROM sqlite_master WHERE type='table' AND name='billing_items'");
    $row = $result->fetch(PDO::FETCH_ASSOC);
    
    if ($row) {
        echo "✓ billing_items table exists\n";
        echo "Structure:\n" . $row['sql'] . "\n\n";
    } else {
        echo "✗ billing_items table does NOT exist\n\n";
    }
    
    // Check billing_item_tracking table
    echo "=== Checking billing_item_tracking table ===\n";
    $result2 = $conn->query("SELECT sql FROM sqlite_master WHERE type='table' AND name='billing_item_tracking'");
    $row2 = $result2->fetch(PDO::FETCH_ASSOC);
    
    if ($row2) {
        echo "✓ billing_item_tracking table exists\n";
        echo "Structure:\n" . $row2['sql'] . "\n\n";
    } else {
        echo "✗ billing_item_tracking table does NOT exist\n\n";
    }
    
    // Check billing table for updated_at column
    echo "=== Checking billing table columns ===\n";
    $result3 = $conn->query("PRAGMA table_info(billing)");
    $columns = $result3->fetchAll(PDO::FETCH_ASSOC);
    echo "Billing table columns:\n";
    foreach ($columns as $col) {
        echo "  - " . $col['name'] . " (" . $col['type'] . ")\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
