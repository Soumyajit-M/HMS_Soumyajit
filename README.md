# Hospital Management System (HMS) 2.0

A PHP + SQLite HMS with Patients, Doctors, Appointments, Billing, Reporting, and Settings. Currency support with optional live conversion; CSV and printable PDF-ready reports.

## Quick Start (Windows)

```powershell
# From repo root
php -v
php tools\setup_and_start.ps1
```

Manual start:

```powershell
php -S 127.0.0.1:8000 -t .
Start-Process http://127.0.0.1:8000/
```

If first time, initialize database:

```powershell
php deployment\scripts\init_production_db.php
```

## Deployment

- Start here: `deployment/README.md` and `deployment/QUICK_START.md`
- Platform guides in `deployment/guides/` (Windows, Web/Mobile)
- Database lives at `database/hms_database.sqlite`

## Reports & Exports

- Printable: `/api/reports-print.php?type=billing|appointments|patients&start=YYYY-MM-DD&end=YYYY-MM-DD&doctor_id=&department_id=`
- CSV: `/api/export.php?type=billing|appointments|patients&start=...&end=...&doctor_id=&department_id=`

## CI

GitHub Actions lints PHP, initializes SQLite, boots a PHP server, runs smoke tests, and performs HTTP checks. Artifacts (logs, downloads, server log) are uploaded.

## Project Report

Use `docs/PROJECT_REPORT_TEMPLATE.md` as your submission template. A full-length HTML report is available at `_archive/documentation/Project Reoprt/Project_Report.html`.
