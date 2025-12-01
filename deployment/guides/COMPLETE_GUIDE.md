# HMS 2.0 - Deployment & Production Guide

## 📦 PRODUCTION-READY FILE STRUCTURE

```
www/                                    # Root directory
│
├── 📄 CORE FRONTEND PAGES (17 files)
│   ├── index.php                       # Login/Entry point
│   ├── dashboard.php                   # Main dashboard
│   ├── patients.php                    # Patient management
│   ├── doctors.php                     # Doctor management
│   ├── staff.php                       # Staff management
│   ├── appointments.php                # Appointment scheduling
│   ├── billing.php                     # Billing & invoicing
│   ├── schedules.php                   # Doctor schedules
│   ├── rooms.php                       # Room & bed management
│   ├── laboratory.php                  # Lab tests management
│   ├── inventory.php                   # Inventory tracking
│   ├── insurance.php                   # Insurance claims
│   ├── telemedicine.php                # Telemedicine sessions
│   ├── reports.php                     # Reports & analytics
│   ├── settings.php                    # System settings
│   ├── logout.php                      # Logout handler
│   └── setup.php                       # Initial setup wizard
│
├── 📁 api/                             # Backend API endpoints
│   ├── bootstrap.php                   # API initialization
│   ├── patients.php                    # Patient CRUD API
│   ├── doctors.php                     # Doctor CRUD API
│   ├── appointments.php                # Appointments API
│   ├── billing.php                     # Billing API
│   ├── dashboard-stats.php             # Statistics API
│   ├── notifications.php               # Notifications API
│   ├── payments.php                    # Payment processing
│   └── settings.php                    # Settings API
│
├── 📁 classes/                         # PHP Business Logic
│   ├── Auth.php                        # Authentication
│   ├── Patient.php                     # Patient operations
│   ├── Doctor.php                      # Doctor operations
│   ├── Appointment.php                 # Appointment logic
│   ├── Billing.php                     # Billing logic
│   ├── Dashboard.php                   # Dashboard stats
│   ├── Laboratory.php                  # Lab management
│   ├── PDFReport.php                   # PDF generation
│   └── Validation.php                  # Input validation
│
├── 📁 config/                          # Configuration
│   ├── config.php                      # System config
│   └── database.php                    # DB connection
│
├── 📁 database/                        # Schema files
│   ├── schema.sql                      # MySQL schema
│   └── schema_sqlite.sql               # SQLite schema
│
├── 📁 assets/                          # Frontend resources
│   ├── css/
│   │   └── style.css                   # Custom styles
│   ├── js/
│   │   ├── dashboard.js
│   │   ├── patients.js
│   │   ├── doctors.js
│   │   ├── appointments.js
│   │   ├── billing.js
│   │   ├── reports.js
│   │   ├── settings.js
│   │   ├── staff.js
│   │   ├── schedules.js
│   │   ├── rooms.js
│   │   ├── laboratory.js
│   │   ├── inventory.js
│   │   ├── insurance.js
│   │   └── telemedicine.js
│   └── images/                         # Image assets
│
├── 📁 tools/                           # Maintenance utilities
│   ├── db_check.php                    # Database diagnostics
│   ├── full_smoke_test.php             # System health check
│   ├── migrate_add_columns.php         # DB migrations
│   └── cookies.txt                     # Tool config
│
├── 📁 logs/                            # Application logs (writable)
│
├── 📁 _archive/                        # Non-production files
│   ├── testing/                        # Test files
│   ├── documentation/                  # Extra docs
│   ├── deprecated/                     # Old code
│   └── temp_files/                     # Temporary files
│
├── .htaccess                           # Apache configuration
├── .gitignore                          # Git ignore rules
├── README.md                           # Main documentation
└── PROJECT_STRUCTURE.md                # This file
```

---

## 🚀 DEPLOYMENT STEPS

### 1. Pre-Deployment Checklist

**Review Configuration Files:**

```bash
✅ config/config.php - Set production values
✅ config/database.php - Database credentials
✅ .htaccess - Web server rules
```

**Database Setup:**

```bash
✅ Create production database
✅ Import schema from database/schema_sqlite.sql (or schema.sql for MySQL)
✅ Test database connection
```

**File Permissions:**

```bash
chmod 755 /var/www/html/hms              # Root directory
chmod 644 /var/www/html/hms/*.php        # PHP files
chmod 755 /var/www/html/hms/logs         # Logs directory (writable)
chmod 644 /var/www/html/hms/config/*     # Config files (read-only)
```

### 2. Files to Deploy (INCLUDE)

**Frontend Pages:**

- ✅ All 17 PHP pages in root directory

**Backend:**

- ✅ /api/ directory (all files)
- ✅ /classes/ directory (all files)
- ✅ /config/ directory (all files)
- ✅ /database/ directory (schema files only)

**Assets:**

- ✅ /assets/css/ (all files)
- ✅ /assets/js/ (all files)
- ✅ /assets/images/ (all files)

**Utilities:**

- ✅ /tools/ directory (optional for maintenance)
- ✅ /logs/ directory (create empty, ensure writable)

**Configuration:**

- ✅ .htaccess
- ✅ README.md (optional)

### 3. Files to EXCLUDE from Production

**Do NOT deploy:**

- ❌ \_archive/ directory (testing, docs, deprecated files)
- ❌ .git/ directory (version control)
- ❌ .github/ directory (GitHub workflows)
- ❌ .qodo/ directory (IDE settings)
- ❌ _.err, _.out files (log files)
- ❌ _*test.php, test*_.php (test files)
- ❌ debug*\*.php, check*\*.php (debug files)
- ❌ \*.bundle files (git bundles)

### 4. Production Configuration

**Edit config/config.php:**

```php
// Production settings
define('SITE_NAME', 'Your Hospital Name');
define('SITE_URL', 'https://yourdomain.com');
define('ADMIN_EMAIL', 'admin@yourhospital.com');

// Security
define('SESSION_TIMEOUT', 1800); // 30 minutes
define('MAX_LOGIN_ATTEMPTS', 3);

// Database (ensure correct credentials)
// File paths
define('LOG_PATH', __DIR__ . '/../logs/');
```

**Edit config/database.php:**

```php
// Production database
define('DB_HOST', 'localhost');
define('DB_NAME', 'hms_production');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'secure_password');
```

### 5. Security Hardening

**Apache (.htaccess already configured):**

```apache
# Prevent directory listing
Options -Indexes

# Protect sensitive files
<Files "config.php">
    Require all denied
</Files>

# Force HTTPS (add if needed)
# RewriteEngine On
# RewriteCond %{HTTPS} off
# RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

**PHP Security (php.ini):**

```ini
display_errors = Off
log_errors = On
error_log = /var/log/php-errors.log
expose_php = Off
session.cookie_httponly = 1
session.cookie_secure = 1
```

### 6. Post-Deployment Testing

**Run System Checks:**

```bash
1. Access: https://yourdomain.com/setup.php
2. Complete initial setup wizard
3. Test login functionality
4. Verify database connection
5. Check file permissions
6. Test all main features
7. Review error logs
```

**Health Check:**

```bash
# Access maintenance tool
https://yourdomain.com/tools/full_smoke_test.php

# Check database
https://yourdomain.com/tools/db_check.php
```

---

## 📊 PRODUCTION STATISTICS

### Deployed File Count

- **Frontend Pages:** 17 PHP files
- **API Endpoints:** 9 files
- **Classes:** 9 files
- **JavaScript:** 14 files
- **CSS:** 1 file
- **Config:** 2 files
- **Database:** 2 schema files
- **Tools:** 4 utilities

**Total Production Files:** ~58 files

### Excluded from Production

- **Testing:** 8 files
- **Documentation:** 4 files
- **Deprecated:** 4 files
- **Temporary:** 3 files

**Total Archived:** ~19 files

---

## 🔧 MAINTENANCE

### Regular Tasks

1. **Database Backups** - Daily automated backups
2. **Log Rotation** - Clean logs/ directory weekly
3. **Security Updates** - Update PHP and dependencies monthly
4. **Performance Monitoring** - Check response times weekly

### Update Procedure

1. Backup production database
2. Backup production files
3. Test updates in staging environment
4. Deploy during off-peak hours
5. Verify all functionality
6. Monitor logs for 24 hours

---

## 📞 SUPPORT

### System Requirements

- **PHP:** 8.0 or higher
- **Database:** SQLite 3.x or MySQL 5.7+
- **Web Server:** Apache 2.4+ with mod_rewrite
- **Disk Space:** Minimum 100MB
- **Memory:** Minimum 256MB PHP memory limit

### Troubleshooting

1. Check `/logs/` directory for error messages
2. Verify file permissions (755 for directories, 644 for files)
3. Ensure database connection in `config/database.php`
4. Test with `tools/full_smoke_test.php`

---

## 📝 VERSION HISTORY

- **v2.0** - Current (December 2025)
  - Full HMS 2.0 implementation
  - 15 modules active
  - SQLite database
  - Bootstrap 5.3.0 frontend
  - RESTful API architecture

**Last Updated:** December 1, 2025
