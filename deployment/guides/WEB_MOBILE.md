# Hospital Management System - Android/Web App Deployment

Deploy HMS as a Progressive Web App (PWA) that works on Android, iOS, and web browsers.

## Progressive Web App (PWA) Features

The HMS system is PWA-ready with:

- ✅ Service Worker for offline functionality
- ✅ Web App Manifest for installation
- ✅ Responsive design (works on all screen sizes)
- ✅ Touch-friendly interface
- ✅ Home screen installation

## Setup for PWA Deployment

### 1. Add PWA Meta Tags to All Pages

Add to the `<head>` section of each PHP page:

```html
<!-- PWA Meta Tags -->
<meta name="mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-status-bar-style" content="default" />
<meta name="apple-mobile-web-app-title" content="HMS 2.0" />
<meta name="application-name" content="Hospital Management System" />
<meta name="theme-color" content="#0d6efd" />
<meta name="msapplication-navbutton-color" content="#0d6efd" />
<meta name="msapplication-TileColor" content="#0d6efd" />

<!-- Manifest -->
<link rel="manifest" href="/manifest.json" />

<!-- Icons for iOS -->
<link
  rel="apple-touch-icon"
  sizes="180x180"
  href="/assets/images/icon-180x180.png"
/>
<link
  rel="apple-touch-icon"
  sizes="152x152"
  href="/assets/images/icon-152x152.png"
/>
<link
  rel="apple-touch-icon"
  sizes="144x144"
  href="/assets/images/icon-144x144.png"
/>
<link
  rel="apple-touch-icon"
  sizes="120x120"
  href="/assets/images/icon-120x120.png"
/>

<!-- Icons for Android -->
<link
  rel="icon"
  type="image/png"
  sizes="192x192"
  href="/assets/images/icon-192x192.png"
/>
<link
  rel="icon"
  type="image/png"
  sizes="512x512"
  href="/assets/images/icon-512x512.png"
/>
```

### 2. Register Service Worker

Add to the bottom of each page (before `</body>`):

```html
<script>
  // Register Service Worker
  if ("serviceWorker" in navigator) {
    window.addEventListener("load", function () {
      navigator.serviceWorker
        .register("/service-worker.js")
        .then(function (registration) {
          console.log("ServiceWorker registered:", registration.scope);
        })
        .catch(function (error) {
          console.log("ServiceWorker registration failed:", error);
        });
    });
  }

  // Prompt to install PWA
  let deferredPrompt;
  window.addEventListener("beforeinstallprompt", (e) => {
    e.preventDefault();
    deferredPrompt = e;

    // Show install button (optional)
    const installBtn = document.getElementById("installBtn");
    if (installBtn) {
      installBtn.style.display = "block";
      installBtn.addEventListener("click", () => {
        installBtn.style.display = "none";
        deferredPrompt.prompt();
        deferredPrompt.userChoice.then((choiceResult) => {
          if (choiceResult.outcome === "accepted") {
            console.log("User accepted the install prompt");
          }
          deferredPrompt = null;
        });
      });
    }
  });
</script>
```

### 3. Create App Icons

Generate icons in these sizes (place in `assets/images/`):

Required sizes:

- 72x72
- 96x96
- 128x128
- 144x144
- 152x152
- 192x192
- 384x384
- 512x512

**Using ImageMagick (if available):**

```bash
# From a single large icon (1024x1024)
convert icon.png -resize 72x72 icon-72x72.png
convert icon.png -resize 96x96 icon-96x96.png
convert icon.png -resize 128x128 icon-128x128.png
convert icon.png -resize 144x144 icon-144x144.png
convert icon.png -resize 152x152 icon-152x152.png
convert icon.png -resize 192x192 icon-192x192.png
convert icon.png -resize 384x384 icon-384x384.png
convert icon.png -resize 512x512 icon-512x512.png
```

**Online tools:**

- https://realfavicongenerator.net/
- https://www.favicon-generator.org/

## Android Installation Methods

### Method 1: Install from Web Browser (Recommended)

1. **Deploy HMS to a web server** (Apache/Nginx)
2. **Enable HTTPS** (required for PWA)
3. **Users visit the website** on Android Chrome/Edge
4. **Browser prompts "Add to Home Screen"**
5. **HMS installs like a native app**

**User Instructions:**

```
1. Open Chrome on Android
2. Visit https://yourhospital.com
3. Tap the menu (⋮) → "Add to Home screen"
4. Tap "Install" when prompted
5. App appears on home screen
```

### Method 2: TWA (Trusted Web Activity) - Native Android App

Create a native Android app wrapper using Android Studio.

**build.gradle (app level):**

```gradle
dependencies {
    implementation 'com.google.androidbrowserhelper:androidbrowserhelper:2.5.0'
}
```

**AndroidManifest.xml:**

```xml
<manifest xmlns:android="http://schemas.android.com/apk/res/android"
    package="com.hospital.hms">

    <uses-permission android:name="android.permission.INTERNET" />

    <application
        android:allowBackup="true"
        android:icon="@mipmap/ic_launcher"
        android:label="@string/app_name"
        android:theme="@style/Theme.AppCompat.NoActionBar">

        <activity android:name="com.google.androidbrowserhelper.trusted.LauncherActivity"
            android:exported="true">
            <meta-data
                android:name="android.support.customtabs.trusted.DEFAULT_URL"
                android:value="https://yourhospital.com" />

            <intent-filter>
                <action android:name="android.intent.action.MAIN" />
                <category android:name="android.intent.category.LAUNCHER" />
            </intent-filter>

            <intent-filter android:autoVerify="true">
                <action android:name="android.intent.action.VIEW" />
                <category android:name="android.intent.category.DEFAULT" />
                <category android:name="android.intent.category.BROWSABLE" />
                <data
                    android:scheme="https"
                    android:host="yourhospital.com" />
            </intent-filter>
        </activity>
    </application>
</manifest>
```

**Digital Asset Links (for verification):**
Place at `https://yourhospital.com/.well-known/assetlinks.json`:

```json
[
  {
    "relation": ["delegate_permission/common.handle_all_urls"],
    "target": {
      "namespace": "android_app",
      "package_name": "com.hospital.hms",
      "sha256_cert_fingerprints": ["YOUR_SHA256_FINGERPRINT"]
    }
  }
]
```

### Method 3: Apache Cordova/PhoneGap

Convert to hybrid app:

**Install Cordova:**

```bash
npm install -g cordova
```

**Create Cordova project:**

```bash
cordova create hms-mobile com.hospital.hms "HMS"
cd hms-mobile
```

**Copy HMS files:**

```bash
# Copy your www folder to Cordova's www folder
cp -R /path/to/hms/www/* www/
```

**Add Android platform:**

```bash
cordova platform add android
```

**Build APK:**

```bash
cordova build android --release
```

**Output:** `platforms/android/app/build/outputs/apk/release/app-release.apk`

## Web Server Deployment

### Hosting Requirements

**Minimum Requirements:**

- PHP 8.0 or higher
- SQLite support (enabled by default)
- Apache or Nginx
- 500 MB disk space
- HTTPS certificate (for PWA)

**Recommended Providers:**

- DigitalOcean ($5-10/month)
- Linode ($5-10/month)
- AWS Lightsail ($3.50-10/month)
- Hostinger ($2-5/month)
- Shared hosting with PHP 8+ support

### Quick Deployment Steps

#### 1. VPS (DigitalOcean, Linode, AWS)

**Install LAMP stack:**

```bash
# Ubuntu/Debian
sudo apt update
sudo apt install apache2 php8.3 libapache2-mod-php8.3 php8.3-sqlite3 php8.3-mbstring

# Enable Apache modules
sudo a2enmod rewrite headers
sudo systemctl restart apache2
```

**Deploy HMS:**

```bash
# Upload files
cd /var/www/html
sudo mkdir hms
# Upload your files via FTP/SFTP to /var/www/html/hms

# Set permissions
sudo chown -R www-data:www-data /var/www/html/hms
sudo chmod -R 755 /var/www/html/hms
sudo chmod -R 777 /var/www/html/hms/database
sudo chmod -R 777 /var/www/html/hms/logs

# Configure virtual host
sudo nano /etc/apache2/sites-available/hms.conf
```

**hms.conf:**

```apache
<VirtualHost *:80>
    ServerName yourhospital.com
    DocumentRoot /var/www/html/hms

    <Directory /var/www/html/hms>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/hms_error.log
    CustomLog ${APACHE_LOG_DIR}/hms_access.log combined
</VirtualHost>
```

**Enable site:**

```bash
sudo a2ensite hms.conf
sudo systemctl reload apache2
```

**Install SSL (Let's Encrypt):**

```bash
sudo apt install certbot python3-certbot-apache
sudo certbot --apache -d yourhospital.com
```

#### 2. Shared Hosting (cPanel)

1. **Upload files via FTP:**

   - Upload entire `www` folder to `public_html/hms`

2. **Set permissions via File Manager:**

   - `database/` → 777
   - `logs/` → 777

3. **Create .htaccess** (already included)

4. **Run database migration:**

   - Via SSH: `php tools/migrate_auto_billing.php`
   - Or via browser: Create `setup.php` temporarily

5. **Install SSL:**

   - Use cPanel's Let's Encrypt/AutoSSL

6. **Test:** Visit https://yourdomain.com/hms

## SSL Certificate Setup (Required for PWA)

### Option 1: Let's Encrypt (Free)

```bash
sudo certbot --apache -d yourhospital.com
```

### Option 2: Cloudflare (Free)

1. Sign up at cloudflare.com
2. Add your domain
3. Update nameservers
4. Enable SSL (Full mode)

### Option 3: Self-Signed (Development Only)

```bash
sudo openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout /etc/ssl/private/hms.key \
  -out /etc/ssl/certs/hms.crt
```

## Testing PWA Installation

### Android (Chrome):

1. Visit https://yourhospital.com
2. Wait for "Add to Home screen" prompt
3. Or: Menu → Add to Home screen
4. Tap "Install"

### iOS (Safari):

1. Visit https://yourhospital.com
2. Tap Share button
3. Tap "Add to Home Screen"
4. Tap "Add"

### Desktop (Chrome/Edge):

1. Visit https://yourhospital.com
2. Click install icon in address bar
3. Click "Install"

## Offline Functionality

The service worker enables:

- ✅ Page caching for offline viewing
- ✅ Static assets caching
- ✅ Background sync (when implemented)
- ✅ Push notifications (when configured)

**Limitations:**

- API calls require internet connection
- Database operations need server connection
- For full offline: Consider IndexedDB + sync strategy

## Performance Optimization for Mobile

### 1. Image Optimization

```bash
# Compress images
find assets/images -name "*.png" -exec pngquant --quality=65-80 {} \;
find assets/images -name "*.jpg" -exec jpegoptim --max=80 {} \;
```

### 2. Minify Resources

```bash
# Install minifiers
npm install -g uglify-js clean-css-cli

# Minify JS
uglifyjs assets/js/*.js -c -m -o assets/js/app.min.js

# Minify CSS
cleancss -o assets/css/style.min.css assets/css/style.css
```

### 3. Enable Compression

Already configured in `.htaccess` and `nginx.conf`

### 4. Lazy Loading

Add to images:

```html
<img src="image.jpg" loading="lazy" alt="Description" />
```

## Distribution Checklist

- [ ] Icons generated (all sizes)
- [ ] Manifest.json configured
- [ ] Service worker registered
- [ ] HTTPS enabled
- [ ] Meta tags added to all pages
- [ ] Tested on Android Chrome
- [ ] Tested on iOS Safari
- [ ] Tested offline functionality
- [ ] Performance optimized
- [ ] Database secured
- [ ] Error pages created
- [ ] Analytics configured (optional)

## Security for Public Web

### 1. Environment Configuration

Create `.env` file (not in git):

```
DB_PATH=database/hms_database.sqlite
SESSION_LIFETIME=1800
ENABLE_DEBUG=false
```

### 2. Input Validation

Already implemented in classes/Validation.php

### 3. SQL Injection Prevention

Using PDO prepared statements (already implemented)

### 4. XSS Prevention

Sanitize all output:

```php
echo htmlspecialchars($userInput, ENT_QUOTES, 'UTF-8');
```

### 5. CSRF Protection

Add CSRF tokens to forms (implement if needed)

### 6. Rate Limiting

Use fail2ban or CloudFlare rate limiting

## Monitoring & Analytics

### Google Analytics (Optional)

Add to all pages:

```html
<!-- Google Analytics -->
<script
  async
  src="https://www.googletagmanager.com/gtag/js?id=GA_MEASUREMENT_ID"
></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag() {
    dataLayer.push(arguments);
  }
  gtag("js", new Date());
  gtag("config", "GA_MEASUREMENT_ID");
</script>
```

### Server Monitoring

- Use Uptime Robot (free)
- Monitor disk space (database growth)
- Set up log rotation

## Backup Strategy

### Automated Database Backup

Create cron job:

```bash
# Daily backup at 2 AM
0 2 * * * /usr/bin/php /var/www/html/hms/tools/backup_database.php
```

**backup_database.php:**

```php
<?php
$backupDir = __DIR__ . '/../backups';
if (!file_exists($backupDir)) mkdir($backupDir, 0777, true);

$date = date('Y-m-d_H-i-s');
$source = __DIR__ . '/../database/hms_database.sqlite';
$dest = "$backupDir/hms_backup_$date.sqlite";

copy($source, $dest);

// Keep only last 30 days
$files = glob("$backupDir/hms_backup_*.sqlite");
usort($files, function($a, $b) { return filemtime($b) - filemtime($a); });
foreach (array_slice($files, 30) as $old) {
    unlink($old);
}
?>
```

## Troubleshooting

**Issue:** PWA not prompting to install

- Ensure HTTPS is enabled
- Check manifest.json is accessible
- Verify service worker is registered
- Check browser console for errors

**Issue:** Icons not showing

- Ensure all icon sizes exist
- Check file paths in manifest.json
- Clear browser cache

**Issue:** Offline mode not working

- Check service worker registration
- Verify HTTPS
- Check browser console for SW errors

**Issue:** Slow on mobile

- Enable compression
- Optimize images
- Minify CSS/JS
- Use CDN for Bootstrap/FontAwesome
