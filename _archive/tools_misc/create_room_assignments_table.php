<?php
require_once 'config/config.php';
require_once 'config/database.php';

$database = new Database();
$pdo = $database->getConnection();

echo "Creating room_assignments table...\n\n";

try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS room_assignments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            patient_id INTEGER NOT NULL,
            room_id INTEGER NOT NULL,
            bed_number TEXT,
            start_date DATETIME NOT NULL,
            end_date DATETIME,
            daily_rate REAL NOT NULL,
            notes TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (patient_id) REFERENCES patients (id) ON DELETE CASCADE,
            FOREIGN KEY (room_id) REFERENCES rooms (id) ON DELETE CASCADE
        )
    ");
    
    echo "✓ room_assignments table created successfully!\n\n";
    
    // Verify the table was created
    $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='room_assignments'");
    if ($stmt->fetch()) {
        echo "✓ Verified: Table exists in database\n";
        
        // Show table structure
        echo "\nTable structure:\n";
        $stmt = $pdo->query("PRAGMA table_info(room_assignments)");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $col) {
            echo "  - {$col['name']} ({$col['type']})" . ($col['notnull'] ? " NOT NULL" : "") . "\n";
        }
    }
    
    echo "\n✓ Auto-billing table setup complete!\n";
    echo "You can now use auto-billing features.\n";
    
} catch (PDOException $e) {
    echo "✗ Error creating table: " . $e->getMessage() . "\n";
}
