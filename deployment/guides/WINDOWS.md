# Hospital Management System - Windows Desktop Deployment

# PHPDesktop Configuration

Follow these steps to deploy HMS as a Windows desktop application using PHPDesktop.

## Prerequisites

- PHPDesktop Chrome (already included in your setup)
- HMS application files (www folder)

## Directory Structure

```
phpdesktop-chrome-130.1-php-8.3/
├── phpdesktop-chrome.exe          # Main executable
├── settings.json                   # PHPDesktop configuration
├── www/                           # Your HMS application
│   ├── index.php
│   ├── dashboard.php
│   ├── config/
│   ├── classes/
│   ├── database/
│   └── ...
└── php/                           # PHP runtime
```

## Configuration

### 1. Update settings.json

Your PHPDesktop settings should include:

```json
{
  "main_window": {
    "title": "Hospital Management System 2.0",
    "icon": "www/assets/images/icon.ico",
    "default_size": [1280, 800],
    "minimum_size": [1024, 600],
    "maximum_size": [0, 0],
    "disable_maximize_button": false,
    "center_on_screen": true,
    "start_maximized": false,
    "start_fullscreen": false
  },
  "popup_window": {
    "allow_devtools": false
  },
  "web_server": {
    "listen_on": ["127.0.0.1", 8080],
    "www_directory": "www",
    "index_files": ["index.php"],
    "cgi_interpreter": "php/php-cgi.exe",
    "cgi_extensions": ["php"],
    "cgi_temp_dir": ""
  },
  "chrome": {
    "log_file": "debug.log",
    "log_severity": "info",
    "cache_path": "webcache/",
    "external_navigation": true,
    "devtools_support": false,
    "remote_debugging_port": 0,
    "context_menu": {
      "enable_menu": false,
      "navigation_items": false,
      "print": true,
      "view_source": false,
      "external_browser": false,
      "devtools": false
    }
  },
  "application": {
    "app_name": "Hospital Management System",
    "app_version": "2.0.0"
  }
}
```

### 2. Database Configuration

The SQLite database is portable - it's stored in `www/database/hms_database.sqlite`.

**Important:** For production deployment:

- Set proper file permissions on the database directory
- Enable database backups (see backup script below)
- Consider encryption for sensitive data

### 3. PHP Configuration (php.ini)

Key settings in `php/php.ini`:

```ini
; Increase limits for file uploads
upload_max_filesize = 10M
post_max_size = 10M

; Session settings
session.save_path = "tmp"
session.gc_maxlifetime = 1800

; Error handling (Production)
display_errors = Off
display_startup_errors = Off
error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT
log_errors = On
error_log = "www/logs/php_errors.log"

; Timezone
date.timezone = Asia/Kolkata

; SQLite
extension=pdo_sqlite
extension=sqlite3
```

## Building the Deployment Package

### Option 1: Standalone Executable (Recommended for Distribution)

1. **Clean the application:**

   ```
   - Remove _archive/ folder
   - Remove tools/ folder (or keep only essential migration scripts)
   - Remove Testing/ folder
   - Clear logs/ folder
   ```

2. **Create installer structure:**

   ```
   HMS-Installer/
   ├── phpdesktop-chrome.exe
   ├── settings.json
   ├── www/
   ├── php/
   ├── LICENSE.txt
   ├── README.txt
   └── Install.bat
   ```

3. **Create Install.bat:**

   ```batch
   @echo off
   echo Hospital Management System - Installation
   echo =========================================

   REM Create necessary directories
   if not exist "www\logs" mkdir www\logs
   if not exist "www\database" mkdir www\database

   REM Set permissions (using icacls)
   icacls www\logs /grant Everyone:(OI)(CI)F
   icacls www\database /grant Everyone:(OI)(CI)F

   REM Run database setup
   php\php.exe www\tools\migrate_auto_billing.php

   echo.
   echo Installation complete!
   echo Run phpdesktop-chrome.exe to start the application.
   pause
   ```

4. **Package with installer creator:**
   - Use **Inno Setup** (free): https://jrsoftware.org/isinfo.php
   - Or **NSIS** (free): https://nsis.sourceforge.io/

### Option 2: Portable Package (No Installation Required)

Create a ZIP file with:

```
HMS-Portable-Windows.zip
├── HMS.exe                 (renamed phpdesktop-chrome.exe)
├── settings.json
├── www/
├── php/
├── START.bat              (launches HMS.exe)
└── README.txt
```

**START.bat:**

```batch
@echo off
start HMS.exe
```

## Security Hardening for Production

### 1. Disable Debug Features

- Set `devtools_support: false` in settings.json
- Set `context_menu.enable_menu: false`
- Set `display_errors = Off` in php.ini

### 2. File Permissions

Restrict access to:

- config/ folder
- database/ folder
- logs/ folder

### 3. Database Encryption

For sensitive deployments, consider:

- SQLite encryption extensions (SEE, SQLCipher)
- Application-level encryption for patient data

### 4. Regular Backups

Create automatic backup script (backup.bat):

```batch
@echo off
set BACKUP_DIR=backups
set DATE=%date:~-4,4%%date:~-10,2%%date:~-7,2%_%time:~0,2%%time:~3,2%
set DATE=%DATE: =0%

if not exist "%BACKUP_DIR%" mkdir "%BACKUP_DIR%"

echo Creating backup...
xcopy /Y www\database\hms_database.sqlite "%BACKUP_DIR%\hms_backup_%DATE%.sqlite"

echo Backup complete: %BACKUP_DIR%\hms_backup_%DATE%.sqlite
```

## Distribution Checklist

Before distributing:

- [ ] Test on clean Windows machine (Windows 10/11)
- [ ] Test with antivirus enabled (Windows Defender)
- [ ] Verify database permissions
- [ ] Test all features (patients, appointments, billing, lab)
- [ ] Check auto-billing functionality
- [ ] Verify file uploads work
- [ ] Test report generation
- [ ] Include user documentation
- [ ] Add license information
- [ ] Sign executable (optional but recommended)

## System Requirements

**Minimum:**

- Windows 10 or later
- 2 GB RAM
- 500 MB disk space
- 1024x600 screen resolution

**Recommended:**

- Windows 10/11 (64-bit)
- 4 GB RAM
- 1 GB disk space
- 1920x1080 screen resolution

## Troubleshooting

**Issue:** Application won't start

- Check if port 8080 is available
- Verify PHP files exist in php/ folder
- Check settings.json syntax

**Issue:** Database errors

- Verify database file exists: www/database/hms_database.sqlite
- Check folder permissions
- Run migration script manually

**Issue:** Blank white screen

- Check www/logs/php_errors.log
- Verify index.php exists in www/

## Auto-Updates (Optional)

To implement auto-updates:

1. Host update manifest on your server
2. Add update checker to application
3. Use PHPDesktop's external navigation to download updates
4. Replace files on application restart

## Digital Signature (Recommended)

Sign your executable to avoid Windows SmartScreen warnings:

1. Get a code signing certificate
2. Use SignTool.exe:
   ```
   signtool sign /f certificate.pfx /p password /t http://timestamp.digicert.com phpdesktop-chrome.exe
   ```
