# Database Schema Consolidation - Migration Guide

## What Changed (2024-12-04)

### Deleted Files (Backed up to `database/backups/schema_backup_20241204_234007/`)

- ❌ `database/schema_sqlite.sql`
- ❌ `database/auto_billing_schema.sql`
- ❌ `database/schema_updates_v2.sql`

### New Files

- ✅ `database/schema_complete.sql` - **Unified complete schema**
- ✅ `database/README.md` - Comprehensive schema documentation

### Updated Files

- 🔧 `tools/import_schema.php` - Now imports `schema_complete.sql`
- 🔧 `tools/init_production_db.php` - Simplified to use complete schema
- 🔧 `setup.php` - Uses `schema_complete.sql`
- 🔧 `.github/workflows/ci.yml` - CI uses new schema
- 🔧 `deployment/README.md` - Updated deployment docs
- 🔧 `deployment/QUICK_START.md` - Updated quick reference

## Migration Instructions

### ✅ For New Installations

**No action needed!** Just use the new workflow:

```bash
# Import complete schema
php tools/import_schema.php

# Initialize production data (admin user, settings, etc.)
php tools/init_production_db.php
```

### ✅ For Existing Installations

**Your database is safe!** The new schema is backward compatible.

#### Option 1: Keep existing database (Recommended)

```bash
# No action needed - your existing database continues to work
# All tables from old schemas are included in the new schema
```

#### Option 2: Fresh installation with new schema

```bash
# 1. Backup existing database
cp database/hms_database.sqlite database/hms_database_backup_$(date +%Y%m%d).sqlite

# 2. Remove old database
rm database/hms_database.sqlite

# 3. Import new schema
php tools/import_schema.php

# 4. Initialize production
php tools/init_production_db.php
```

### ✅ For Railway/Cloud Deployments

After pushing these changes:

```bash
# SSH into Railway shell and run:
php tools/import_schema.php
php tools/init_production_db.php
```

### ✅ For Portable App Users

The portable app will automatically use the new schema. Just update:

```bash
# From repository root
.\tools\sync_portable.ps1

# Or from portable app folder
.\Update_Portable.bat
```

## What's in the New Schema

The unified `schema_complete.sql` includes **all features** from the three old schemas:

### From schema_sqlite.sql (Base)

- Users & authentication
- Patients management
- Doctors management
- Appointments
- Medical records
- Billing & payments
- Inventory
- Notifications
- System settings

### From auto_billing_schema.sql

- Billing items tracking
- Automatic bed charges
- Lab test billing
- Doctor consultation billing
- Medicine/pharmacy billing
- Daily charge calculations

### From schema_updates_v2.sql (HMS 2.0)

- Doctor schedules & leaves
- Vital signs tracking
- Immunization records
- Staff management with shifts
- Wards & room management
- Bed assignments
- Laboratory module (test catalog, orders, results)
- Advanced inventory (categories, batches, transactions)
- Insurance integration (providers, policies, claims)
- Telemedicine platform (sessions, remote monitoring, e-prescriptions)
- Report templates & executions
- Procedure codes
- Payment plans

## Schema Organization

The new schema is organized into **18 logical sections**:

1. Authentication & User Management
2. Departments
3. Patient Management
4. Doctor Management
5. Appointments
6. Medical Records & Vital Signs
7. Staff Management
8. Room & Bed Management
9. Laboratory Module
10. Inventory/Supplies Management
11. Billing & Payments (Comprehensive)
12. Insurance Integration
13. Telemedicine Platform
14. Reporting & Analytics
15. System Notifications & Logs
16. System Settings
17. Performance Indexes
18. Default Data

## Benefits

### ✅ Simplified Maintenance

- One file instead of three
- Easier to version control
- Clear structure with comments

### ✅ Better Performance

- All indexes consolidated
- Optimized table creation order
- Foreign key relationships clear

### ✅ Easier Deployment

- Single schema import command
- No need to run multiple migration scripts
- Reduced chance of errors

### ✅ Complete Documentation

- Comprehensive README in `database/README.md`
- Inline comments explaining each section
- Clear changelog header

## Troubleshooting

### Q: Will my existing database still work?

**A:** Yes! The new schema is fully backward compatible. All tables from the old schemas are included.

### Q: Do I need to migrate my data?

**A:** No. If you have an existing database, keep using it. The schema files are only for creating new databases.

### Q: What happened to migrate_auto_billing.php?

**A:** It's no longer needed. The auto-billing tables are now in `schema_complete.sql` and automatically created by `import_schema.php`.

### Q: Can I still access the old schemas?

**A:** Yes! They're backed up in `database/backups/schema_backup_20241204_234007/`

### Q: How do I verify my database has all tables?

```bash
php deployment/scripts/verify_tables.php
```

### Q: My production database is missing some tables

```bash
# This is safe to run - it only adds missing tables
php tools/init_production_db.php
```

## Rollback (If Needed)

If you need to rollback to the old schemas:

```bash
# 1. Copy backup files back
cp database/backups/schema_backup_20241204_234007/*.sql database/

# 2. Revert the tool changes via git
git checkout HEAD~1 -- tools/import_schema.php tools/init_production_db.php setup.php

# 3. Use old workflow
php tools/import_schema.php
php tools/migrate_auto_billing.php
```

## Support

- **Schema Documentation:** `database/README.md`
- **Deployment Guides:** `deployment/guides/`
- **Project Documentation:** `docs/`

## Changelog

### v3.0 (2024-12-04) - Schema Consolidation

- ✅ Merged 3 schemas into `schema_complete.sql`
- ✅ Created comprehensive documentation
- ✅ Updated all tools and deployment scripts
- ✅ Backed up old schemas
- ✅ Simplified initialization workflow
- ✅ Improved CI/CD pipeline

### v2.0 - HMS 2.0 Features

- Added schema_updates_v2.sql with extended features

### v1.0 - Initial Release

- Base schema_sqlite.sql
- Auto-billing schema additions
