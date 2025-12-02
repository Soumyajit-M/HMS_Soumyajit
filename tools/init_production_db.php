<?php
/**
 * Production Database Initialization Script
 * 
 * This script initializes a clean production database without test data.
 * Run this ONCE when deploying to production for the first time.
 * 
 * Usage: php tools/init_production_db.php
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

echo "=== HMS 2.0 - Production Database Initialization ===\n\n";

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    echo "Connected to database successfully.\n\n";
    
    // Check if database is already initialized
    $tables = $conn->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($tables) > 5) {
        echo "⚠️  WARNING: Database appears to already contain data.\n";
        echo "Found " . count($tables) . " tables.\n\n";
        echo "Do you want to continue? This will NOT delete existing data.\n";
        echo "It will only add missing tables and columns. (y/n): ";
        
        $handle = fopen("php://stdin", "r");
        $line = trim(fgets($handle));
        fclose($handle);
        
        if (strtolower($line) !== 'y') {
            echo "\nAborted by user.\n";
            exit(0);
        }
        echo "\n";
    }
    
    // 1. Run auto-billing migration
    echo "Step 1: Running auto-billing migration...\n";
    include __DIR__ . '/migrate_auto_billing.php';
    echo "\n";
    
    // 2. Ensure users table exists, then add default admin user if not exists
    echo "Step 2: Checking for admin user...\n";
    $hasUsers = $conn->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'")->fetch(PDO::FETCH_ASSOC);
    if (!$hasUsers) {
        echo "Creating base users table (minimal) ...\n";
        $conn->exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            email TEXT,
            role TEXT DEFAULT 'user',
            status TEXT DEFAULT 'active',
            first_name TEXT,
            last_name TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        echo "✓ Base users table created\n\n";
    }
    $adminCheck = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'")->fetch(PDO::FETCH_ASSOC);
    
    if ($adminCheck['count'] == 0) {
        echo "Creating default admin user...\n";
        
        // Generate secure random password
        $defaultPassword = bin2hex(random_bytes(8));
        $hashedPassword = password_hash($defaultPassword, PASSWORD_DEFAULT);
        
        $conn->exec("INSERT INTO users (username, password, email, role, status, created_at) 
                     VALUES ('admin', '$hashedPassword', 'admin@hospital.com', 'admin', 'active', datetime('now'))");
        
        echo "✓ Admin user created\n";
        echo "\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "⚠️  IMPORTANT: Save these credentials!\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "Username: admin\n";
        echo "Password: $defaultPassword\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "\nCHANGE THIS PASSWORD immediately after first login!\n\n";
        
        // Save credentials to file
        $credFile = __DIR__ . '/ADMIN_CREDENTIALS.txt';
        file_put_contents($credFile, "HMS 2.0 - Default Admin Credentials\n\nUsername: admin\nPassword: $defaultPassword\n\nCHANGE THIS PASSWORD IMMEDIATELY!\n\nGenerated: " . date('Y-m-d H:i:s'));
        echo "Credentials saved to: $credFile\n\n";
    } else {
        echo "✓ Admin user already exists\n\n";
    }
    
    // 3. Initialize system settings
    echo "Step 3: Initializing system settings...\n";
    
    $settingsTable = "CREATE TABLE IF NOT EXISTS system_settings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        setting_key TEXT UNIQUE NOT NULL,
        setting_value TEXT,
        setting_type TEXT DEFAULT 'string',
        description TEXT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($settingsTable);
    
    $defaultSettings = [
        ['hospital_name', 'Hospital Management System 2.0', 'string', 'Hospital name'],
        ['hospital_address', '', 'string', 'Hospital address'],
        ['hospital_phone', '', 'string', 'Contact phone number'],
        ['hospital_email', 'info@hospital.com', 'string', 'Contact email'],
        ['currency_symbol', '₹', 'string', 'Currency symbol'],
        ['currency_default', 'INR', 'string', 'Default currency code'],
        ['currency_live_conversion', '0', 'boolean', 'Enable live currency conversion'],
        ['date_format', 'Y-m-d', 'string', 'Date format'],
        ['timezone', 'Asia/Kolkata', 'string', 'Timezone'],
        ['session_timeout', '1800', 'integer', 'Session timeout in seconds'],
        ['enable_notifications', '1', 'boolean', 'Enable notifications'],
        ['enable_email', '0', 'boolean', 'Enable email notifications'],
        ['max_upload_size', '10', 'integer', 'Max upload size in MB'],
        ['auto_billing', '1', 'boolean', 'Enable auto-billing system']
    ];
    
    $stmt = $conn->prepare("INSERT OR IGNORE INTO system_settings (setting_key, setting_value, setting_type, description) VALUES (?, ?, ?, ?)");
    foreach ($defaultSettings as $setting) {
        $stmt->execute($setting);
    }
    echo "✓ System settings initialized\n\n";
    
    // 4. Set up default consultation fees for doctors
    echo "Step 4: Setting default consultation fees...\n";
    $conn->exec("UPDATE doctors SET consultation_fee = 500 WHERE consultation_fee IS NULL OR consultation_fee = 0");
    echo "✓ Default consultation fees set (₹500)\n\n";
    
    // 5. Set up default room charges
    echo "Step 5: Setting default room charges...\n";
    $conn->exec("UPDATE rooms SET charge_per_day = 1000 WHERE charge_per_day IS NULL OR charge_per_day = 0");
    echo "✓ Default room charges set (₹1000/day)\n\n";
    
    // 6. Create necessary directories
    echo "Step 6: Creating directory structure...\n";
    $dirs = [
        __DIR__ . '/../logs',
        __DIR__ . '/../backups',
        __DIR__ . '/../uploads',
        __DIR__ . '/../uploads/patients',
        __DIR__ . '/../uploads/reports',
        __DIR__ . '/../uploads/documents'
    ];
    
    foreach ($dirs as $dir) {
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
            echo "✓ Created: " . basename($dir) . "/\n";
        } else {
            echo "  Already exists: " . basename($dir) . "/\n";
        }
    }
    echo "\n";
    
    // 7. Set file permissions (Unix-like systems only)
    if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
        echo "Step 7: Setting file permissions (Linux/Mac)...\n";
        chmod(__DIR__ . '/../database', 0777);
        chmod(__DIR__ . '/../logs', 0777);
        chmod(__DIR__ . '/../backups', 0777);
        chmod(__DIR__ . '/../uploads', 0777);
        echo "✓ Permissions set\n\n";
    }
    
    // 8. Database optimization
    echo "Step 8: Optimizing database...\n";
    $conn->exec("VACUUM");
    $conn->exec("ANALYZE");
    echo "✓ Database optimized\n\n";
    
    // Final summary
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "✅ Production Database Initialization Complete!\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    
    echo "Database Statistics:\n";
    $tableCount = $conn->query("SELECT COUNT(*) as count FROM sqlite_master WHERE type='table'")->fetch(PDO::FETCH_ASSOC);
    echo "  Total tables: " . $tableCount['count'] . "\n";
    
    $dbSize = filesize(__DIR__ . '/../database/hms_database.sqlite');
    echo "  Database size: " . number_format($dbSize / 1024, 2) . " KB\n\n";
    
    echo "Next Steps:\n";
    echo "1. Login with the admin credentials above\n";
    echo "2. Change the admin password immediately\n";
    echo "3. Configure system settings in Settings page\n";
    echo "4. Add hospital information\n";
    echo "5. Create additional users\n";
    echo "6. Start using the system!\n\n";
    
    echo "Production Checklist:\n";
    echo "  [ ] Change admin password\n";
    echo "  [ ] Update hospital information in settings\n";
    echo "  [ ] Configure email settings (if needed)\n";
    echo "  [ ] Set up automated backups\n";
    echo "  [ ] Review security settings\n";
    echo "  [ ] Test all modules\n";
    echo "  [ ] Train staff on system usage\n\n";
    
    echo "For deployment guides, see:\n";
    echo "  - README_DEPLOYMENT.md (Quick start)\n";
    echo "  - DEPLOY_WINDOWS.md (Windows deployment)\n";
    echo "  - DEPLOY_MACOS.md (macOS deployment)\n";
    echo "  - DEPLOY_WEB_ANDROID.md (Web & Mobile)\n\n";
    
    echo "System is ready for production use! 🎉\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
