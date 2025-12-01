<?php
// HMS Configuration
define('SITE_NAME', 'Hospital Management System');
define('SITE_URL', 'http://localhost/hms_project');
define('ADMIN_EMAIL', 'admin@hospital.com');

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'hms_database');
define('DB_USER', 'root');
define('DB_PASS', '');

// Security
define('SECRET_KEY', 'hms_secret_key_2024');
define('ENCRYPTION_METHOD', 'AES-256-CBC');

// File Upload
define('UPLOAD_PATH', 'uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB

// Pagination
define('RECORDS_PER_PAGE', 10);

// PDF Configuration
define('PDF_TEMP_PATH', 'temp/');

// Email Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your_email@gmail.com');
define('SMTP_PASSWORD', 'your_app_password');

// Timezone
date_default_timezone_set('UTC');

// Error Reporting (disable in production)
error_reporting(E_ALL);
// Don't display errors to clients; log them to a file instead
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/php_errors.log');
?>
