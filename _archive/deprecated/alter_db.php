<?php
try {
    // Direct SQLite connection
    $conn = new PDO('sqlite:database/hms_database.sqlite');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create temporary table without UNIQUE constraint on email
    $conn->exec("CREATE TABLE users_temp AS SELECT * FROM users");

    // Drop original table
    $conn->exec("DROP TABLE users");

    // Create new table without UNIQUE on email
    $conn->exec("CREATE TABLE users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        email TEXT NOT NULL,
        password TEXT NOT NULL,
        role TEXT NOT NULL CHECK (role IN ('admin', 'doctor', 'nurse', 'receptionist', 'pharmacist')),
        first_name TEXT NOT NULL,
        last_name TEXT NOT NULL,
        phone TEXT,
        address TEXT,
        profile_image TEXT,
        is_active INTEGER DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Copy data back
    $conn->exec("INSERT INTO users SELECT * FROM users_temp");

    // Drop temp table
    $conn->exec("DROP TABLE users_temp");

    echo "Database altered successfully. Email UNIQUE constraint removed.";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
