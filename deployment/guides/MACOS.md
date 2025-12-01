# Hospital Management System - macOS Deployment Guide

Deploy HMS on macOS using MAMP, XAMPP, or native PHP server.

## Method 1: MAMP (Recommended for Desktop App)

### Prerequisites

- Download MAMP from https://www.mamp.info/
- macOS 10.14 or later

### Installation Steps

1. **Install MAMP:**

   - Download and install MAMP (free version is sufficient)
   - Launch MAMP application

2. **Copy HMS files:**

   ```bash
   # Navigate to MAMP's htdocs folder
   cd /Applications/MAMP/htdocs

   # Create HMS directory
   sudo mkdir hms

   # Copy your HMS files
   sudo cp -R /path/to/www/* /Applications/MAMP/htdocs/hms/

   # Set permissions
   sudo chmod -R 755 /Applications/MAMP/htdocs/hms
   sudo chmod -R 777 /Applications/MAMP/htdocs/hms/database
   sudo chmod -R 777 /Applications/MAMP/htdocs/hms/logs
   ```

3. **Configure MAMP:**

   - Open MAMP
   - Go to Preferences → Web Server
   - Set Document Root to `/Applications/MAMP/htdocs/hms`
   - Set Apache Port to 8080 (or 80)
   - Go to Preferences → PHP
   - Select PHP 8.3.x
   - Click OK

4. **Update PHP Configuration:**
   Edit `/Applications/MAMP/bin/php/php8.3.x/conf/php.ini`:

   ```ini
   upload_max_filesize = 10M
   post_max_size = 10M
   max_execution_time = 300
   date.timezone = Asia/Kolkata
   display_errors = Off
   log_errors = On
   error_log = /Applications/MAMP/htdocs/hms/logs/php_errors.log

   extension=pdo_sqlite
   extension=sqlite3
   ```

5. **Start MAMP:**
   - Click "Start Servers"
   - Access HMS at: http://localhost:8080

### Create macOS Application Bundle (Optional)

Create a standalone app icon:

1. **Create app structure:**

   ```bash
   mkdir -p ~/HMS.app/Contents/MacOS
   mkdir -p ~/HMS.app/Contents/Resources
   ```

2. **Create launcher script** (`~/HMS.app/Contents/MacOS/HMS`):

   ```bash
   #!/bin/bash

   # Start MAMP servers
   /Applications/MAMP/bin/start.sh

   # Wait for servers to start
   sleep 3

   # Open HMS in default browser
   open http://localhost:8080
   ```

3. **Make executable:**

   ```bash
   chmod +x ~/HMS.app/Contents/MacOS/HMS
   ```

4. **Create Info.plist** (`~/HMS.app/Contents/Info.plist`):

   ```xml
   <?xml version="1.0" encoding="UTF-8"?>
   <!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
   <plist version="1.0">
   <dict>
       <key>CFBundleExecutable</key>
       <string>HMS</string>
       <key>CFBundleName</key>
       <string>Hospital Management System</string>
       <key>CFBundleIdentifier</key>
       <string>com.hospital.hms</string>
       <key>CFBundleVersion</key>
       <string>2.0.0</string>
       <key>CFBundleIconFile</key>
       <string>icon.icns</string>
       <key>LSMinimumSystemVersion</key>
       <string>10.14</string>
   </dict>
   </plist>
   ```

5. **Add app icon:**
   - Convert your icon to .icns format
   - Place in `~/HMS.app/Contents/Resources/icon.icns`

## Method 2: XAMPP

### Installation Steps

1. **Install XAMPP:**

   - Download from https://www.apachefriends.org/
   - Install to `/Applications/XAMPP`

2. **Copy HMS files:**

   ```bash
   sudo cp -R /path/to/www /Applications/XAMPP/xamppfiles/htdocs/hms
   sudo chmod -R 755 /Applications/XAMPP/xamppfiles/htdocs/hms
   sudo chmod -R 777 /Applications/XAMPP/xamppfiles/htdocs/hms/database
   sudo chmod -R 777 /Applications/XAMPP/xamppfiles/htdocs/hms/logs
   ```

3. **Configure PHP:**
   Edit `/Applications/XAMPP/xamppfiles/etc/php.ini`:

   ```ini
   upload_max_filesize = 10M
   post_max_size = 10M
   date.timezone = Asia/Kolkata
   extension=pdo_sqlite
   extension=sqlite3
   ```

4. **Start XAMPP:**

   ```bash
   sudo /Applications/XAMPP/xamppfiles/xampp start
   ```

5. **Access HMS:**
   Open http://localhost/hms

## Method 3: Native PHP Server (Development Only)

For development or testing:

```bash
# Navigate to HMS directory
cd /path/to/www

# Start PHP built-in server
php -S localhost:8080 -t .

# Access at http://localhost:8080
```

**Note:** Not recommended for production use.

## Method 4: Docker (Cross-Platform)

### Create Dockerfile

**Dockerfile:**

```dockerfile
FROM php:8.3-apache

# Install required PHP extensions
RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite

# Enable Apache modules
RUN a2enmod rewrite headers

# Copy application files
COPY www/ /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/database \
    && chmod -R 777 /var/www/html/logs

# Configure PHP
RUN echo "upload_max_filesize = 10M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 10M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "date.timezone = Asia/Kolkata" >> /usr/local/etc/php/conf.d/timezone.ini

EXPOSE 80

CMD ["apache2-foreground"]
```

**docker-compose.yml:**

```yaml
version: "3.8"

services:
  hms:
    build: .
    ports:
      - "8080:80"
    volumes:
      - ./www/database:/var/www/html/database
      - ./www/logs:/var/www/html/logs
    restart: unless-stopped
    environment:
      - TZ=Asia/Kolkata
```

### Run with Docker:

```bash
# Build and start
docker-compose up -d

# Access at http://localhost:8080

# Stop
docker-compose down
```

## Database Setup

After installation, run the migration:

```bash
# For MAMP
/Applications/MAMP/bin/php/php8.3.x/bin/php /Applications/MAMP/htdocs/hms/tools/migrate_auto_billing.php

# For XAMPP
/Applications/XAMPP/xamppfiles/bin/php /Applications/XAMPP/xamppfiles/htdocs/hms/tools/migrate_auto_billing.php
```

## Creating DMG Distribution (macOS Installer)

### Prerequisites:

- Create DMG (macOS utility)
- Or use `hdiutil` command

### Steps:

1. **Prepare package folder:**

   ```bash
   mkdir HMS-macOS
   cp -R /Applications/MAMP/htdocs/hms HMS-macOS/
   cp DEPLOY_MACOS.md HMS-macOS/README.txt
   ```

2. **Create DMG:**

   ```bash
   hdiutil create -volname "HMS 2.0" -srcfolder HMS-macOS -ov -format UDZO HMS-2.0-macOS.dmg
   ```

3. **Distribute DMG file**

## Security Considerations

### 1. File Permissions

```bash
# Set restrictive permissions
chmod 755 /path/to/hms
chmod 644 /path/to/hms/**/*.php
chmod 600 /path/to/hms/config/*.php
chmod 700 /path/to/hms/database
chmod 666 /path/to/hms/database/*.sqlite
```

### 2. Firewall

```bash
# Allow only local access
# In MAMP, bind to 127.0.0.1 only
```

### 3. SSL Certificate (for HTTPS)

```bash
# Use Let's Encrypt or self-signed certificate
# Configure in Apache settings
```

## Troubleshooting

**Issue:** Permission denied errors

```bash
sudo chmod -R 777 /path/to/hms/database
sudo chmod -R 777 /path/to/hms/logs
```

**Issue:** SQLite not found

- Ensure SQLite extension is enabled in php.ini
- Check: `php -m | grep sqlite`

**Issue:** Port already in use

- Change Apache port in MAMP/XAMPP preferences
- Or kill conflicting process: `sudo lsof -i :8080`

## System Requirements

**Minimum:**

- macOS 10.14 Mojave or later
- 2 GB RAM
- 500 MB disk space

**Recommended:**

- macOS 11 Big Sur or later
- 4 GB RAM
- 1 GB disk space

## Auto-Start on Login (Optional)

### Using launchd:

Create `~/Library/LaunchAgents/com.hospital.hms.plist`:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
<dict>
    <key>Label</key>
    <string>com.hospital.hms</string>
    <key>ProgramArguments</key>
    <array>
        <string>/Applications/MAMP/bin/start.sh</string>
    </array>
    <key>RunAtLoad</key>
    <true/>
    <key>KeepAlive</key>
    <false/>
</dict>
</plist>
```

Load the agent:

```bash
launchctl load ~/Library/LaunchAgents/com.hospital.hms.plist
```

## Performance Optimization

### 1. Enable OPcache

In php.ini:

```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
```

### 2. Increase PHP Limits

```ini
memory_limit = 256M
max_execution_time = 300
```

### 3. Database Optimization

```bash
# Run VACUUM periodically
sqlite3 database/hms_database.sqlite "VACUUM;"
```
