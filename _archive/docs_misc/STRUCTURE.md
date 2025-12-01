# 🏥 HMS 2.0 - Clean Project Structure

**Organized Directory Layout**

---

## 📁 Directory Structure

```
www/                                    # Application Root
│
├── 📂 public/                          # Public assets (PWA files)
│   ├── manifest.json                   # PWA manifest
│   └── service-worker.js               # PWA service worker
│
├── 📂 api/                             # REST API Endpoints
│   ├── bootstrap.php                   # API initialization
│   ├── patients.php                    # Patient operations
│   ├── doctors.php                     # Doctor operations
│   ├── appointments.php                # Appointment management
│   ├── billing.php                     # Billing operations
│   ├── dashboard-stats.php             # Dashboard statistics
│   ├── notifications.php               # Notifications
│   ├── payments.php                    # Payment processing
│   └── settings.php                    # System settings
│
├── 📂 classes/                         # Business Logic Layer
│   ├── Auth.php                        # Authentication
│   ├── Patient.php                     # Patient management
│   ├── Doctor.php                      # Doctor management
│   ├── Appointment.php                 # Appointments
│   ├── Billing.php                     # Billing
│   ├── AutoBilling.php                 # 💰 Auto-billing system
│   ├── Dashboard.php                   # Dashboard logic
│   ├── Laboratory.php                  # Lab management
│   ├── Insurance.php                   # Insurance claims
│   ├── PDFReport.php                   # PDF generation
│   └── Validation.php                  # Input validation
│
├── 📂 config/                          # Configuration
│   ├── config.php                      # System configuration
│   └── database.php                    # Database connection
│
├── 📂 assets/                          # Frontend Assets
│   ├── css/                            # Stylesheets
│   │   └── style.css
│   ├── js/                             # JavaScript files
│   │   ├── dashboard.js
│   │   ├── patients.js
│   │   ├── doctors.js
│   │   ├── appointments.js
│   │   ├── billing.js
│   │   ├── reports.js
│   │   └── settings.js
│   └── images/                         # Images & icons
│
├── 📂 database/                        # Database Files
│   ├── hms_database.sqlite             # Main SQLite database
│   ├── schema.sql                      # Database schema
│   ├── schema_sqlite.sql               # SQLite schema
│   └── auto_billing_schema.sql         # Auto-billing schema
│
├── 📂 storage/                         # 🆕 Storage Directory
│   ├── backups/                        # Database backups
│   ├── uploads/                        # File uploads
│   │   ├── patients/                   # Patient documents
│   │   ├── reports/                    # Generated reports
│   │   └── documents/                  # Other documents
│   └── logs/                           # → Moved from root
│       └── php_errors.log
│
├── 📂 tools/                           # Utility Scripts
│   ├── migrate_auto_billing.php        # Auto-billing migration
│   ├── verify_tables.php               # Database verification
│   ├── test_auto_billing.php           # Auto-billing tests
│   ├── setup_rooms.php                 # Room setup
│   ├── check_*.php                     # Various checks
│   └── db_check.php                    # Database diagnostics
│
├── 📂 deployment/                      # 🚀 Deployment Package
│   ├── README.md                       # Deployment overview
│   ├── QUICK_START.md                  # Quick reference
│   ├── guides/                         # Platform guides
│   │   ├── WINDOWS.md                  # Windows deployment
│   │   ├── MACOS.md                    # macOS deployment
│   │   ├── WEB_MOBILE.md               # Web & mobile
│   │   ├── COMPLETE_GUIDE.md           # Full reference
│   │   └── CHECKLIST.md                # Deployment checklist
│   ├── config/                         # Config templates
│   │   ├── nginx.conf                  # Nginx configuration
│   │   ├── nginx-sample.conf           # Nginx sample
│   │   ├── manifest.json               # PWA manifest template
│   │   └── service-worker.js           # Service worker template
│   └── scripts/                        # Setup scripts
│       ├── init_production_db.php      # Production setup
│       ├── migrate_auto_billing.php    # Migration script
│       ├── verify_tables.php           # Verification
│       └── setup_rooms.php             # Room creation
│
├── 📂 docs/                            # 📖 Documentation
│   ├── README.md                       # Main documentation
│   ├── PROJECT_STRUCTURE.md            # Project structure
│   ├── guides/                         # User guides
│   │   └── AUTO_BILLING.md             # Auto-billing guide
│   └── api/                            # API documentation (future)
│
├── 📂 _archive/                        # 🗄️ Archived/Old Files
│   ├── testing/                        # Test files
│   ├── documentation/                  # Old docs
│   ├── deprecated/                     # Deprecated code
│   └── temp_files/                     # Temporary files
│
├── 📄 Core Application Pages           # Main PHP Pages
│   ├── index.php                       # 🔐 Login/Entry point
│   ├── dashboard.php                   # 📊 Main dashboard
│   ├── patients.php                    # 👥 Patient management
│   ├── doctors.php                     # 🩺 Doctor management
│   ├── staff.php                       # 👨‍💼 Staff management
│   ├── appointments.php                # 📅 Appointment scheduling
│   ├── billing.php                     # 💰 Billing & invoicing
│   ├── schedules.php                   # 🕐 Doctor schedules
│   ├── rooms.php                       # 🏥 Room & bed management
│   ├── laboratory.php                  # 🧪 Lab tests
│   ├── inventory.php                   # 📦 Inventory tracking
│   ├── insurance.php                   # 💳 Insurance claims
│   ├── telemedicine.php                # 📱 Telemedicine
│   ├── reports.php                     # 📈 Reports & analytics
│   ├── settings.php                    # ⚙️ System settings
│   ├── logout.php                      # 🚪 Logout handler
│   └── setup.php                       # 🛠️ Initial setup wizard
│
└── 📄 Configuration Files              # Root Config
    ├── .htaccess                       # Apache configuration
    ├── .gitignore                      # Git ignore rules
    └── CI.md                           # Continuous Integration
```

---

## 📋 Directory Purposes

### Core Directories

| Directory       | Purpose                               | Permissions |
| --------------- | ------------------------------------- | ----------- |
| **public/**     | PWA files, publicly accessible assets | 755         |
| **api/**        | REST API endpoints for AJAX calls     | 755         |
| **classes/**    | Business logic, models, services      | 755         |
| **config/**     | Configuration files (SECURE)          | 600         |
| **assets/**     | CSS, JS, images for frontend          | 755         |
| **database/**   | SQLite database and schemas           | 700         |
| **storage/**    | Uploads, backups, logs (WRITABLE)     | 777         |
| **tools/**      | Admin utilities and scripts           | 755         |
| **deployment/** | Deployment guides and scripts         | 755         |
| **docs/**       | Documentation and guides              | 755         |
| **\_archive/**  | Old/deprecated files (safe to delete) | 755         |

### New Additions

**storage/** - Centralized storage for:

- Database backups
- File uploads
- Generated reports
- Application logs

**public/** - PWA files:

- manifest.json
- service-worker.js

**docs/** - All documentation:

- User guides
- API documentation
- Project documentation

---

## 🔒 Security Notes

### Protected Directories

These should NOT be web-accessible:

- `config/` - Contains database credentials
- `database/` - Contains SQLite database file
- `classes/` - Business logic
- `storage/` - Sensitive uploads and logs
- `tools/` - Admin scripts

### Already Protected By:

- `.htaccess` (Apache) - Blocks access to sensitive dirs
- `nginx.conf` (Nginx) - Blocks access patterns
- File permissions - Restrictive on config files

---

## 📊 File Count Summary

```
Frontend Pages:    17 PHP files
API Endpoints:     9 files
Business Classes:  11 PHP classes
Deployment Guides: 5 comprehensive guides
Config Files:      4 templates
Setup Scripts:     4 production scripts
Documentation:     3 main documents
Total Structure:   Clean, organized, production-ready
```

---

## 🚀 Quick Access

### For Development:

- **Main App:** `index.php`
- **API Docs:** `docs/api/` (to be created)
- **Database:** `database/hms_database.sqlite`
- **Logs:** `storage/logs/php_errors.log`

### For Deployment:

- **Start Here:** `deployment/README.md`
- **Quick Ref:** `deployment/QUICK_START.md`
- **Guides:** `deployment/guides/`
- **Scripts:** `deployment/scripts/`

### For Documentation:

- **Overview:** `docs/README.md`
- **Structure:** `docs/PROJECT_STRUCTURE.md`
- **Auto-Billing:** `docs/guides/AUTO_BILLING.md`

---

## 🧹 Cleanup Recommendations

### Safe to Delete (After Backup):

```
_archive/          # Old files (already archived)
tools/check_*.php  # One-time check scripts (after verification)
```

### Keep for Production:

```
api/               # Required
classes/           # Required
config/            # Required
database/          # Required
assets/            # Required
storage/           # Required
deployment/        # For deployment
All *.php pages    # Required
.htaccess          # Required (Apache)
```

---

## 📦 Deployment Package

All deployment resources organized in:

```
deployment/
├── guides/      → Platform-specific instructions
├── config/      → Configuration templates
└── scripts/     → Setup automation
```

**See:** `deployment/README.md` for complete deployment instructions.

---

## ✅ Benefits of This Structure

1. **Clear Organization** - Each directory has a specific purpose
2. **Security** - Sensitive files properly protected
3. **Scalability** - Easy to add new features
4. **Maintainability** - Logical file placement
5. **Deployment Ready** - All resources organized
6. **Documentation** - Centralized docs folder
7. **Storage Management** - Dedicated storage directory

---

## 🔄 Migration Notes

### Files Moved:

- Documentation → `docs/`
- PWA files → `public/`
- Logs → `storage/logs/` (recommended)
- Backups → `storage/backups/` (recommended)

### Files Organized:

- Deployment guides → `deployment/guides/`
- Config templates → `deployment/config/`
- Setup scripts → `deployment/scripts/`

---

**Last Updated:** December 2025  
**Version:** 2.0.0  
**Status:** ✅ Production Ready
