<?php
// Fix staff_shifts table CHECK constraint issue
$databases = [
    __DIR__ . '/../database/hms_database.sqlite',
    'F:/New folder/HMS_APP/database/hms_database.sqlite'
];

foreach ($databases as $dbPath) {
    if (!file_exists($dbPath)) {
        echo "Skipping $dbPath (not found)\n";
        continue;
    }
    
    echo "Processing: $dbPath\n";
    
    try {
        $pdo = new PDO('sqlite:' . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create new table without strict CHECK constraints
        $pdo->exec("CREATE TABLE staff_shifts_new (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            staff_id INTEGER NOT NULL,
            shift_date DATE NOT NULL,
            start_time TEXT NOT NULL,
            end_time TEXT NOT NULL,
            shift_type TEXT,
            status TEXT DEFAULT 'scheduled',
            assigned_ward TEXT,
            created_at DATETIME,
            FOREIGN KEY (staff_id) REFERENCES staff (id) ON DELETE CASCADE
        )");
        
        // Copy data
        $pdo->exec("INSERT INTO staff_shifts_new SELECT * FROM staff_shifts");
        
        // Drop old table
        $pdo->exec("DROP TABLE staff_shifts");
        
        // Rename new table
        $pdo->exec("ALTER TABLE staff_shifts_new RENAME TO staff_shifts");
        
        echo "✓ Successfully updated staff_shifts table\n\n";
        
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n\n";
    }
}

echo "Done!\n";
