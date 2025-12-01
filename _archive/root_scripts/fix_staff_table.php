<?php
require_once 'config/config.php';
require_once 'config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    echo "Checking staff table structure...\n\n";
    
    // Get current columns
    $result = $conn->query("PRAGMA table_info(staff)");
    $columns = $result->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Current columns:\n";
    foreach ($columns as $col) {
        echo "- {$col['name']} ({$col['type']})\n";
    }
    echo "\n";
    
    // Check if department column exists
    $hasColumn = false;
    foreach ($columns as $col) {
        if ($col['name'] === 'department') {
            $hasColumn = true;
            break;
        }
    }
    
    if (!$hasColumn) {
        echo "Adding 'department' column...\n";
        $conn->exec("ALTER TABLE staff ADD COLUMN department TEXT");
        echo "✓ Column added successfully!\n\n";
    } else {
        echo "✓ Column 'department' already exists!\n\n";
    }
    
    // Check for other commonly needed columns
    $neededColumns = [
        'certification' => 'TEXT',
        'salary' => 'DECIMAL(10,2)',
        'emergency_contact' => 'TEXT',
        'hire_date' => 'DATE'
    ];
    
    foreach ($neededColumns as $colName => $colType) {
        $exists = false;
        foreach ($columns as $col) {
            if ($col['name'] === $colName) {
                $exists = true;
                break;
            }
        }
        
        if (!$exists) {
            echo "Adding '$colName' column...\n";
            $conn->exec("ALTER TABLE staff ADD COLUMN $colName $colType");
            echo "✓ Column '$colName' added successfully!\n";
        }
    }
    
    echo "\n✓ Staff table structure updated!\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
