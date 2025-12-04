<?php
/**
 * Import full SQLite schema from database/schema_complete.sql
 * Usage: php tools/import_schema.php
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

echo "=== HMS 2.0 - Complete Schema Import ===\n\n";

try {
    $db = new Database();
    $conn = $db->getConnection();
    echo "Connected to database successfully.\n\n";

    $schemaPath = __DIR__ . '/../database/schema_complete.sql';
    if (!file_exists($schemaPath)) {
        throw new Exception('Schema file not found: ' . $schemaPath);
    }

    $sql = file_get_contents($schemaPath);
    if ($sql === false || trim($sql) === '') {
        throw new Exception('Schema file is empty or unreadable');
    }

    // Execute statements separated by ; while ignoring empty lines and comments
    $statements = preg_split('/;\s*\n/', $sql);
    $applied = 0;
    foreach ($statements as $stmt) {
        $clean = trim($stmt);
        if ($clean === '' || preg_match('/^--|^\/\*/', $clean)) {
            continue;
        }
        try {
            $conn->exec($clean);
            $applied++;
        } catch (PDOException $e) {
            // Continue on duplicate/exists errors
            if (strpos($e->getMessage(), 'already exists') !== false) {
                continue;
            }
            throw $e;
        }
    }

    echo "✓ Schema import completed (" . $applied . " statements applied)\n\n";
    echo "You can now run: php tools/init_production_db.php\n";
    echo "to set defaults and create the admin user.\n\n";
    echo "=== Done ===\n";
} catch (Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n" . ($e instanceof Exception ? $e->getTraceAsString() : '') . "\n";
    exit(1);
}
?>
