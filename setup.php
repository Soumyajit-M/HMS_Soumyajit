<?php
require_once 'config/config.php';
require_once 'config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();

    // Read the schema file
    $schema = file_get_contents('database/schema_sqlite.sql');

    // Execute the schema
    $conn->exec($schema);

    echo "Database setup completed successfully!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
