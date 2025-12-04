# HMS Database Schema - Complete Reference

## Overview

This is the **unified complete schema** for the Hospital Management System. It consolidates all previous schema files into a single authoritative source.

## Consolidated From

- `schema_sqlite.sql` (Base schema) - **Deleted, backed up**
- `auto_billing_schema.sql` (Billing extensions) - **Deleted, backed up**
- `schema_updates_v2.sql` (HMS 2.0 features) - **Deleted, backed up**

**Backups Location:** `database/backups/schema_backup_YYYYMMDD_HHMMSS/`

## Usage

### For New Installations

#### Method 1: Using import_schema.php (Recommended)

```bash
php tools/import_schema.php
```

#### Method 2: Using SQLite3 CLI

```bash
sqlite3 database/hms_database.sqlite < database/schema_complete.sql
```

#### Method 3: Using setup.php (Web-based)

Navigate to: `http://localhost:8000/setup.php`

### For Production Deployment

1. **Import Schema:**

```bash
php tools/import_schema.php
```

2. **Initialize Production Data:**

```bash
php tools/init_production_db.php
```

This will:

- Import the complete schema
- Create default admin user with random password
- Set up system settings
- Initialize default departments
- Create necessary directories
- Optimize the database

### For Portable App Users

The portable app (`dist/HMS_APP/`) automatically handles database initialization:

1. Run `HMS_Server.bat`
2. Navigate to setup page if needed
3. Default admin credentials will be displayed

## Schema Structure

### Core Modules (18 Sections)

1. **Authentication & Users** - User management and roles
2. **Departments** - Hospital departments
3. **Patient Management** - Patient records and information
4. **Doctor Management** - Doctors, schedules, and leaves
5. **Appointments** - Appointment scheduling
6. **Medical Records & Vital Signs** - Patient health data
7. **Staff Management** - Non-doctor staff
8. **Room & Bed Management** - Ward, room, and bed assignments
9. **Laboratory Module** - Lab tests and results
10. **Inventory/Supplies** - Medicine and equipment inventory
11. **Billing & Payments** - Comprehensive billing system with auto-billing
12. **Insurance Integration** - Insurance providers and claims
13. **Telemedicine Platform** - Remote consultations
14. **Reporting & Analytics** - Report templates and executions
15. **System Notifications** - In-app notifications
16. **System Settings** - Configuration management
17. **Performance Indexes** - Database optimization
18. **Default Data** - Admin user, departments, settings

## Key Features

### Auto-Billing System

- Automatic billing for bed charges (daily)
- Doctor consultation fees
- Lab test charges
- Medicine/pharmacy charges
- Procedure codes
- Payment plans/installments

### Telemedicine

- Video/audio/chat sessions
- Remote patient monitoring
- E-prescriptions with delivery tracking

### Insurance Integration

- Multiple insurance providers
- Patient insurance policies
- Claims management with workflow

### Laboratory

- Test catalog with pricing
- Lab orders with priority levels
- Sample tracking
- Result verification workflow

### Advanced Features

- Doctor schedules with time slots
- Staff shift management
- Vital signs tracking
- Immunization records
- Bed occupancy management
- Inventory batch tracking
- Report templates

## Database File Location

**Development:** `database/hms_database.sqlite`  
**Portable App:** `dist/HMS_APP/database/hms_database.sqlite`  
**Production:** As configured in `config/database.php`

## Maintenance

### Backup Database

```bash
# Manual backup
cp database/hms_database.sqlite database/backups/backup_$(date +%Y%m%d_%H%M%S).sqlite

# Or use the provided script
php tools/backup_database.php
```

### Optimize Database

```bash
sqlite3 database/hms_database.sqlite "VACUUM; ANALYZE;"
```

### View Schema

```bash
sqlite3 database/hms_database.sqlite ".schema"
```

### Export Data

```bash
sqlite3 database/hms_database.sqlite ".dump" > database/backups/full_dump.sql
```

## Migration from Old Schemas

If you have an existing database using the old schema files:

1. **Backup your current database:**

```bash
cp database/hms_database.sqlite database/hms_database_backup.sqlite
```

2. **The schema is backward compatible** - All existing tables remain functional

3. **Optional: Fresh start with new schema:**

```bash
# Backup old DB
mv database/hms_database.sqlite database/hms_database_old.sqlite

# Import new schema
php tools/import_schema.php

# Initialize
php tools/init_production_db.php
```

## Table Count & Statistics

- **Total Tables:** 40+
- **Core Tables:** 18 (users, patients, doctors, appointments, billing, etc.)
- **Extended Features:** 22+ (telemedicine, insurance, lab, inventory, staff)
- **Indexes:** 13 performance indexes

## Default Admin Credentials

After running `init_production_db.php`:

- **Username:** admin
- **Password:** (randomly generated, displayed in console)
- **Credentials saved to:** `tools/ADMIN_CREDENTIALS.txt`

⚠️ **Change the password immediately after first login!**

## Troubleshooting

### "Table already exists" errors

These are normal and ignored during import - the schema uses `CREATE TABLE IF NOT EXISTS`

### Permission errors

```bash
# Linux/Mac
chmod 777 database/
chmod 666 database/hms_database.sqlite

# Windows
# Run as Administrator or check folder permissions
```

### Database locked

- Close any SQLite browser/manager applications
- Ensure no other PHP processes are accessing the database
- Restart the PHP server

## Version History

- **v3.0** (2024-12-04) - Unified complete schema

  - Consolidated 3 separate schema files
  - Added comprehensive documentation
  - Improved auto-billing integration
  - Full HMS 2.0 feature set

- **v2.0** - HMS 2.0 with extended features
- **v1.0** - Base schema with auto-billing

## Support

For issues or questions:

1. Check `docs/` folder for documentation
2. Review `deployment/guides/` for deployment help
3. See `README.md` for project overview

## License

Part of Hospital Management System (HMS) project.
