# Hospital Management System - Project Structure

## 📁 CORE APPLICATION FILES (REQUIRED)

### Frontend Pages (Main Application)

```
/
├── index.php              ✅ Login page
├── dashboard.php          ✅ Main dashboard
├── patients.php           ✅ Patient management
├── doctors.php            ✅ Doctor management
├── staff.php              ✅ Staff management
├── appointments.php       ✅ Appointment scheduling
├── billing.php            ✅ Billing & invoicing
├── schedules.php          ✅ Doctor schedules
├── rooms.php              ✅ Room & bed management
├── laboratory.php         ✅ Lab tests & orders
├── inventory.php          ✅ Inventory management
├── insurance.php          ✅ Insurance claims
├── telemedicine.php       ✅ Telemedicine sessions
├── reports.php            ✅ Reports & analytics
├── settings.php           ✅ System settings
├── logout.php             ✅ Logout handler
└── setup.php              ✅ Initial system setup
```

### Backend API Endpoints

```
/api/
├── bootstrap.php          ✅ API initialization
├── patients.php           ✅ Patient CRUD operations
├── doctors.php            ✅ Doctor CRUD operations
├── appointments.php       ✅ Appointment operations
├── billing.php            ✅ Billing operations
├── dashboard-stats.php    ✅ Dashboard statistics
├── notifications.php      ✅ Notifications API
├── payments.php           ✅ Payment processing
└── settings.php           ✅ Settings API
```

### Core Classes (Business Logic)

```
/classes/
├── Auth.php               ✅ Authentication & authorization
├── Patient.php            ✅ Patient management logic
├── Doctor.php             ✅ Doctor management logic
├── Appointment.php        ✅ Appointment logic
├── Billing.php            ✅ Billing logic
├── Dashboard.php          ✅ Dashboard statistics
├── Laboratory.php         ✅ Laboratory management
├── PDFReport.php          ✅ PDF generation
└── Validation.php         ✅ Input validation
```

### Configuration

```
/config/
├── config.php             ✅ System configuration
└── database.php           ✅ Database connection
```

### Database Schema

```
/database/
├── schema.sql             ✅ MySQL schema
└── schema_sqlite.sql      ✅ SQLite schema (current)
```

### Frontend Assets

```
/assets/
├── css/
│   └── style.css          ✅ Custom styles
├── js/
│   ├── dashboard.js       ✅ Dashboard functionality
│   ├── patients.js        ✅ Patient page scripts
│   ├── doctors.js         ✅ Doctor page scripts
│   ├── appointments.js    ✅ Appointment scripts
│   ├── billing.js         ✅ Billing scripts
│   ├── reports.js         ✅ Reports scripts
│   ├── settings.js        ✅ Settings scripts
│   ├── staff.js           ✅ Staff management
│   ├── schedules.js       ✅ Schedule management
│   ├── rooms.js           ✅ Room management
│   ├── laboratory.js      ✅ Lab management
│   ├── inventory.js       ✅ Inventory management
│   ├── insurance.js       ✅ Insurance management
│   └── telemedicine.js    ✅ Telemedicine features
└── images/                ✅ Image assets
```

### Utility Tools

```
/tools/
├── db_check.php           ✅ Database diagnostics
├── full_smoke_test.php    ✅ System health check
├── migrate_add_columns.php ✅ Database migrations
└── cookies.txt            ✅ Tool configuration
```

### System Files

```
/
├── .htaccess              ✅ Apache configuration
├── README.md              ✅ Project documentation
└── logs/                  ✅ Application logs
```

---

## 🗃️ ARCHIVED FILES (NOT REQUIRED FOR PRODUCTION)

### Testing Files (Moved to `_archive/testing/`)

```
_archive/testing/
├── Testing/               ❌ Old test suite
├── test_delete.php        ❌ Delete operation test
├── test_delete_ui.html    ❌ Delete UI test
├── test_print.php         ❌ Print test
├── check_items.php        ❌ Item verification
├── check_session.php      ❌ Session debug
├── debug_print.php        ❌ Debug utility
└── session_test.html      ❌ Session test page
```

### Documentation (Moved to `_archive/documentation/`)

```
_archive/documentation/
├── CI.md                  ❌ CI/CD documentation
├── HMS_2.0_IMPLEMENTATION_GUIDE.md  ❌ Implementation guide
├── HMS_2.0_README.md      ❌ HMS 2.0 readme
└── Project Reoprt/        ❌ Project reports
```

### Deprecated Files (Moved to `_archive/deprecated/`)

```
_archive/deprecated/
├── alter_db.php           ❌ Old database alteration
├── add_sample_items.php   ❌ Sample data generator
├── www/                   ❌ Duplicate directory
└── patches/               ❌ Old patch files
```

### Temporary Files (Moved to `_archive/temp_files/`)

```
_archive/temp_files/
├── php_server.err         ❌ Server error logs
├── php_server.out         ❌ Server output logs
└── repo-fix.bundle        ❌ Git bundle file
```

---

## 📊 PROJECT STATISTICS

### Required Files

- **Frontend Pages:** 17 files
- **API Endpoints:** 9 files
- **Core Classes:** 9 files
- **JavaScript Files:** 14 files
- **Configuration Files:** 2 files
- **Database Schema Files:** 2 files
- **Utility Tools:** 4 files

**Total Required:** ~57 core files

### Archived Files

- **Testing Files:** 8 files
- **Documentation:** 4 items
- **Deprecated:** 4 items
- **Temporary:** 3 files

**Total Archived:** ~19 non-essential files

---

## 🚀 DEPLOYMENT CHECKLIST

### Essential Directories

- ✅ `/api/` - Backend API endpoints
- ✅ `/assets/css/` - Stylesheets
- ✅ `/assets/js/` - JavaScript files
- ✅ `/assets/images/` - Image assets
- ✅ `/classes/` - PHP classes
- ✅ `/config/` - Configuration files
- ✅ `/database/` - Schema files
- ✅ `/logs/` - Application logs (writable)
- ✅ `/tools/` - Maintenance utilities

### Optional Directories

- ⚠️ `/_archive/` - Historical/development files (exclude from production)
- ⚠️ `/.git/` - Version control (exclude from production)
- ⚠️ `/.github/` - GitHub configuration (exclude from production)

### File Permissions Required

- `/logs/` - Write permission (777 or 755)
- `/config/` - Read-only recommended (644)
- `/database/` - Read permission for schema files

---

## 🔧 MAINTENANCE

### Regular Updates Required

- `schema_sqlite.sql` - Database schema changes
- `/classes/*.php` - Business logic updates
- `/api/*.php` - API endpoint modifications
- `/assets/js/*.js` - Frontend functionality

### Static Files (Rarely Change)

- `.htaccess` - Web server configuration
- `index.php` - Login page
- `setup.php` - Initial setup
- `README.md` - Documentation

---

## 📝 NOTES

1. **Database:** Currently using SQLite (`hms_database.sqlite`)
2. **Framework:** Vanilla PHP with Bootstrap 5.3.0
3. **Authentication:** Session-based with role management
4. **API:** RESTful endpoints with JSON responses
5. **Frontend:** Bootstrap + Font Awesome + Vanilla JavaScript

**Last Updated:** December 1, 2025
**Version:** HMS 2.0
