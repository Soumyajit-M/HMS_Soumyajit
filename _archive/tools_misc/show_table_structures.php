<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

$db = new Database();
$conn = $db->getConnection();

echo "=== Database Table Structures ===\n\n";

// Check billing table
echo "BILLING TABLE:\n";
$result = $conn->query('PRAGMA table_info(billing)');
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    echo "  - {$row['name']} ({$row['type']})\n";
}

echo "\nBILLING_ITEMS TABLE:\n";
$tables = $conn->query("SELECT name FROM sqlite_master WHERE type='table' AND name='billing_items'")->fetchAll();
if (count($tables) > 0) {
    $result = $conn->query('PRAGMA table_info(billing_items)');
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "  - {$row['name']} ({$row['type']})\n";
    }
} else {
    echo "  ✗ Does not exist\n";
}

echo "\nBILLING_ITEM_TRACKING TABLE:\n";
$tables = $conn->query("SELECT name FROM sqlite_master WHERE type='table' AND name='billing_item_tracking'")->fetchAll();
if (count($tables) > 0) {
    $result = $conn->query('PRAGMA table_info(billing_item_tracking)');
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "  - {$row['name']} ({$row['type']})\n";
    }
} else {
    echo "  ✗ Does not exist\n";
}

echo "\nLAB_TEST_CATALOG TABLE:\n";
$tables = $conn->query("SELECT name FROM sqlite_master WHERE type='table' AND name='lab_test_catalog'")->fetchAll();
if (count($tables) > 0) {
    $result = $conn->query('PRAGMA table_info(lab_test_catalog)');
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "  - {$row['name']} ({$row['type']})\n";
    }
    
    // Show sample tests
    echo "\n  Sample Lab Tests:\n";
    $tests = $conn->query("SELECT test_name, standard_price FROM lab_test_catalog LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($tests as $test) {
        echo "    - {$test['test_name']}: ₹{$test['standard_price']}\n";
    }
} else {
    echo "  ✗ Does not exist\n";
}
