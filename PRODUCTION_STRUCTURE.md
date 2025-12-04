# HMS Production Structure

## 🎯 Production-Ready Project

This is a clean, production-ready Hospital Management System. All test, debug, and development files have been archived to `_archive/` directory.

---

## 📁 **DIRECTORY STRUCTURE**

### **Root Pages** (18 files)

Main application pages accessible to users:

| File               | Purpose                 |
| ------------------ | ----------------------- |
| `index.php`        | Login/Authentication    |
| `dashboard.php`    | Main dashboard          |
| `patients.php`     | Patient management      |
| `doctors.php`      | Doctor management       |
| `staff.php`        | Staff management        |
| `appointments.php` | Appointment scheduling  |
| `billing.php`      | Billing system          |
| `schedules.php`    | Doctor/staff schedules  |
| `rooms.php`        | Room and bed management |
| `laboratory.php`   | Laboratory tests        |
| `inventory.php`    | Inventory management    |
| `insurance.php`    | Insurance providers     |
| `telemedicine.php` | Telemedicine sessions   |
| `reports.php`      | Reports and analytics   |
| `settings.php`     | System settings         |
| `setup.php`        | Initial setup           |
| `logout.php`       | Logout handler          |
| `.htaccess`        | Apache configuration    |

---

### **`/api/` Directory** (26 files)

REST API endpoints for frontend communication:

**Core APIs:**

- `appointments.php` - Appointment CRUD
- `billing.php` - Manual billing operations
- `auto-billing.php` - Automatic billing triggers
- `doctors.php` - Doctor management
- `patients.php` - Patient management
- `staff.php` - Staff management
- `schedules.php` - Schedule management
- `rooms.php` - Room/bed operations
- `laboratory.php` - Lab test orders
- `inventory.php` - Inventory CRUD
- `insurance.php` - Insurance provider operations
- `telemedicine.php` - Telemedicine sessions

**Helper APIs:**

- `auth_helper.php` - Authentication utilities
- `bootstrap.php` - API initialization
- `currency.php` - Currency conversion
- `dashboard-stats.php` - Dashboard statistics
- `departments.php` - Department data
- `notifications.php` - Notification system
- `payments.php` - Payment processing
- `reports-api.php` - Report generation
- `reports-print.php` - Print reports
- `print-bill.php` - Print billing invoices
- `export.php` - Data export
- `set_setting.php` - Update settings
- `settings.php` - Fetch settings
- `test_session_api.php` - Session testing

---

### **`/classes/` Directory** (17 files)

Business logic and data models:

**Core Classes:**

- `Auth.php` - Authentication & authorization
- `Patient.php` - Patient business logic
- `Doctor.php` - Doctor operations
- `Staff.php` - Staff management
- `Appointment.php` - Appointment scheduling
- `Billing.php` - Manual billing system
- `AutoBilling.php` - Automatic billing engine
- `Schedule.php` - Scheduling logic
- `Room.php` - Room/bed management
- `Laboratory.php` - Lab test management
- `Inventory.php` - Inventory operations
- `Insurance.php` - Insurance handling
- `Telemedicine.php` - Telemedicine sessions

**Utility Classes:**

- `Dashboard.php` - Dashboard data
- `Report.php` - Report generation
- `PDFReport.php` - PDF export (TCPDF)
- `Currency.php` - Currency conversion
- `Validation.php` - Input validation

---

### **`/config/` Directory** (2 files)

System configuration:

- `config.php` - Application settings
- `database.php` - Database connection (SQLite)

---

### **`/database/` Directory**

Database files and schema:

**Active Files:**

- `hms_database.sqlite` - **PRODUCTION DATABASE**
- `schema_complete.sql` - **UNIFIED SCHEMA** (v3.0, 41 tables)
- `README.md` - Database documentation
- `MIGRATION_GUIDE.md` - Migration instructions
- `CONSOLIDATION_COMPLETE.md` - Schema consolidation notes

**Backups:**

- `backups/schema_backup_YYYYMMDD_HHMMSS/` - Schema version backups

**Key Schema Tables:**

- Patients, Doctors, Staff, Appointments
- Billing, Billing Items, Billing Item Tracking
- Rooms, Bed Assignments
- Laboratory Orders, Lab Tests
- Inventory, Inventory Transactions
- Insurance Providers, Insurance Claims
- Telemedicine Sessions, Session Vitals
- Schedules, Doctor Leaves, Staff Shifts
- Users, Settings, Notifications

---

### **`/tools/` Directory** (6 production tools)

Production-essential utilities:

**Setup Tools:**

- `import_schema.php` - Import schema to database
- `init_production_db.php` - Initialize production database
- `setup_and_start.ps1` - Windows setup script
- `start_local_server.ps1` - Start dev server

**Maintenance Tools:**

- `fix_staff_shifts_constraint.php` - Fix staff shifts table constraints
- `sync_portable.ps1` - Sync to portable app version

---

### **`/assets/` Directory**

Frontend assets:

**CSS:**

- `assets/css/` - Stylesheets (Bootstrap, custom styles)

**JavaScript:**

- `assets/js/` - Frontend logic
  - Form handlers (patients.js, doctors.js, staff.js, etc.)
  - API communication
  - UI interactions

**Images:**

- `assets/images/` - UI images and icons

---

### **`/deployment/` Directory**

Deployment documentation and scripts:

- `README.md` - Deployment overview
- `QUICK_START.md` - Quick deployment guide
- `config/` - Deployment configurations
- `guides/` - Platform-specific guides
- `scripts/` - Deployment automation

---

### **`/docs/` Directory**

Documentation:

- `README.md` - Main documentation
- `PROJECT_STRUCTURE.md` - Project organization
- `TEST_REPORT.md` - Testing results
- `guides/` - User guides
- `api/` - API documentation

---

### **`/public/` Directory**

Progressive Web App files:

- `manifest.json` - PWA manifest
- `service-worker.js` - Service worker for offline support

---

### **`/storage/` Directory**

File storage:

- `backups/` - Database backups
- `uploads/` - User uploads (documents, images)

---

### **`/.github/` Directory**

GitHub CI/CD:

- `workflows/` - GitHub Actions workflows

---

## 🔧 **PRODUCTION TOOLS USAGE**

### Initial Setup

```powershell
# Windows setup (first time)
.\tools\setup_and_start.ps1

# Or manually:
# 1. Import schema
php tools/import_schema.php

# 2. Initialize database
php tools/init_production_db.php

# 3. Start server
.\tools\start_local_server.ps1
```

### Development Server

```powershell
# Start PHP built-in server
.\tools\start_local_server.ps1

# Or manually:
php -S localhost:8000
```

### Portable App Sync

```powershell
# Sync to portable app location
.\tools\sync_portable.ps1
```

---

## 📊 **DATABASE SCHEMA**

### Unified Schema: `schema_complete.sql`

- **Version:** 3.0
- **Tables:** 41
- **Statements:** 55
- **Lines:** 1091

### Key Features:

✅ Single authoritative schema
✅ All constraints and indexes
✅ Foreign key relationships
✅ Default values and triggers
✅ CHECK constraints for data validation
✅ Auto-increment primary keys

### Schema Import:

```bash
php tools/import_schema.php
```

---

## 🏥 **BILLING SYSTEM**

### Architecture:

- **Dual System:** Manual (`Billing.php`) + Auto (`AutoBilling.php`)
- **Tables:** billing, billing_items, billing_item_tracking

### Auto-Billing Triggers:

- `trackAdmission()` - Room charges (daily)
- `trackLabTest()` - Lab test charges (one-time)
- `trackConsultation()` - Consultation fees (one-time)
- `trackMedicine()` - Medicine charges (one-time)

### Billing Lifecycle:

1. **Creation** - New bill created for patient
2. **Auto-accumulation** - Charges added automatically
3. **Finalization** - Bill marked as final
4. **Payment** - Payment processing

### Key Features:

✅ One active bill per patient
✅ Automatic total calculation
✅ Daily vs one-time charges
✅ Detailed item tracking
✅ Payment tracking

---

## 🚀 **DEPLOYMENT**

### Requirements:

- PHP 8.0+ with PDO SQLite
- SQLite3 extension
- Apache with mod_rewrite OR PHP built-in server

### Quick Deploy:

1. Upload all files except `_archive/`
2. Configure `config/config.php`
3. Import schema: `php tools/import_schema.php`
4. Access `setup.php` for initial setup
5. Login and configure settings

### Heroku Deploy:

- `Procfile` included
- See `deployment/guides/heroku.md`

---

## 📋 **WHAT'S ARCHIVED**

All files in `_archive/` are NOT required for production:

- ❌ Test frameworks (Playwright)
- ❌ Test databases
- ❌ Debug tools (27 files)
- ❌ Development dependencies (node_modules)
- ❌ Build artifacts (dist)
- ❌ Test results and logs
- ❌ One-time fix scripts

**These files are kept for reference only and are completely detached from production code.**

See `ARCHIVAL_SUMMARY.md` for complete list.

---

## ✅ **PRODUCTION CHECKLIST**

Before deploying:

- [ ] Configure `config/config.php` with production settings
- [ ] Import schema to production database
- [ ] Run `setup.php` to create admin user
- [ ] Configure settings in Settings page
- [ ] Test all modules (patients, doctors, billing, etc.)
- [ ] Set up automated backups
- [ ] Configure web server (Apache/Nginx)
- [ ] Enable HTTPS
- [ ] Set proper file permissions
- [ ] Test backup/restore procedures

---

## 📞 **SUPPORT**

For issues or questions:

1. Check documentation in `/docs/`
2. Review deployment guides in `/deployment/`
3. Check archived debug tools in `_archive/tools_archived/` if needed

---

## 🎉 **SUMMARY**

This is a **production-ready** Hospital Management System with:

- ✅ Clean directory structure
- ✅ All unrequired files archived
- ✅ Unified database schema
- ✅ Comprehensive billing system
- ✅ Complete API layer
- ✅ Modular business logic
- ✅ Deployment ready
- ✅ Well documented

**Total Production Files:** ~90 essential files
**Archived Files:** ~160+ development/test files

The system is ready for deployment and production use! 🚀
