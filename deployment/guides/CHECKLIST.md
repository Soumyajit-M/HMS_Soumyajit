# 📋 HMS 2.0 - Deployment Checklist

Use this checklist to ensure proper deployment across all platforms.

---

## ✅ PRE-DEPLOYMENT CHECKLIST

### Code Preparation

- [ ] All features tested and working
- [ ] No PHP syntax errors (`php -l *.php`)
- [ ] Database migration tested
- [ ] Auto-billing system tested
- [ ] All API endpoints tested
- [ ] Frontend validation working
- [ ] Report generation working

### Configuration

- [ ] `config/config.php` reviewed
- [ ] Default passwords changed
- [ ] Database path configured correctly
- [ ] Session settings configured
- [ ] Timezone set correctly
- [ ] Error logging enabled
- [ ] Debug mode disabled for production

### Security

- [ ] `.htaccess` or `nginx.conf` configured
- [ ] File permissions set correctly
- [ ] Sensitive directories blocked
- [ ] SQL injection protection verified
- [ ] XSS protection implemented
- [ ] CSRF tokens added (if needed)
- [ ] Input validation working

### Database

- [ ] Database schema up to date
- [ ] Migration script tested
- [ ] Indexes created
- [ ] Sample data removed (if needed)
- [ ] Database backed up
- [ ] Auto-backup configured

### Files

- [ ] Unnecessary files removed:
  - [ ] `_archive/` folder
  - [ ] `Testing/` folder
  - [ ] `patches/` folder
  - [ ] `Project Reoprt/` folder
  - [ ] Test scripts
  - [ ] Development files
- [ ] `logs/` folder cleared
- [ ] Required directories exist:
  - [ ] `database/`
  - [ ] `logs/`
  - [ ] `backups/`
  - [ ] `uploads/`

---

## 🪟 WINDOWS DEPLOYMENT

### Prerequisites

- [ ] PHPDesktop Chrome installed
- [ ] PHP 8.3+ with SQLite support
- [ ] All files in `www/` folder

### Deployment Steps

- [ ] Database migration run
- [ ] Admin account created
- [ ] Application tested locally
- [ ] Icon configured (`settings.json`)
- [ ] Window title set

### Distribution (Optional)

- [ ] Installer created (Inno Setup/NSIS)
- [ ] Or ZIP package created
- [ ] README included
- [ ] License information added
- [ ] Shortcuts created
- [ ] Uninstaller included (if installer used)

### Testing

- [ ] Fresh install tested
- [ ] Login works
- [ ] Patient registration works
- [ ] Appointment booking works
- [ ] Billing works
- [ ] Auto-billing triggers correctly
- [ ] Reports generate correctly
- [ ] Database persists after restart

**Documentation:** `DEPLOY_WINDOWS.md`

---

## 🍎 MACOS DEPLOYMENT

### Prerequisites

- [ ] MAMP or XAMPP installed
- [ ] Or Docker installed
- [ ] PHP 8.0+ with SQLite

### MAMP Deployment

- [ ] Files copied to htdocs
- [ ] Permissions set (755/777)
- [ ] PHP configured (php.ini)
- [ ] MAMP started
- [ ] Database migration run
- [ ] Application accessible

### Docker Deployment

- [ ] Dockerfile created
- [ ] docker-compose.yml configured
- [ ] Container built
- [ ] Volume mounts set
- [ ] Port mappings configured
- [ ] Container running

### Testing

- [ ] Application loads
- [ ] Login works
- [ ] All modules functional
- [ ] Database writable
- [ ] Uploads work

**Documentation:** `DEPLOY_MACOS.md`

---

## 🌐 WEB SERVER DEPLOYMENT

### Prerequisites

- [ ] VPS or shared hosting account
- [ ] Domain name (optional but recommended)
- [ ] SSH access (for VPS)
- [ ] PHP 8.0+ with SQLite
- [ ] Apache or Nginx

### Server Setup

- [ ] LAMP/LEMP stack installed
- [ ] PHP modules installed:
  - [ ] php-sqlite3
  - [ ] php-mbstring
  - [ ] php-curl
  - [ ] php-xml
  - [ ] php-gd (for reports)
- [ ] Web server configured
- [ ] Virtual host created

### File Upload

- [ ] Files uploaded via FTP/SFTP/Git
- [ ] Correct directory structure
- [ ] File ownership set
- [ ] Permissions configured:
  - [ ] 755 for application files
  - [ ] 777 for database/
  - [ ] 777 for logs/
  - [ ] 777 for uploads/
  - [ ] 600 for config/\*.php

### Web Server Configuration

- [ ] Apache:
  - [ ] `.htaccess` in place
  - [ ] mod_rewrite enabled
  - [ ] AllowOverride set
- [ ] Nginx:
  - [ ] nginx.conf configured
  - [ ] Site enabled
  - [ ] Syntax checked

### SSL Certificate

- [ ] HTTPS enabled (mandatory for PWA)
- [ ] Certificate installed:
  - [ ] Let's Encrypt
  - [ ] Or Cloudflare
  - [ ] Or commercial SSL
- [ ] HTTP → HTTPS redirect enabled
- [ ] Certificate auto-renewal configured

### Database

- [ ] Migration script run
- [ ] Admin account created
- [ ] Database writable
- [ ] Backups configured

### Security

- [ ] Firewall configured
- [ ] SSH key authentication (not password)
- [ ] fail2ban installed (optional)
- [ ] Security headers enabled
- [ ] Directory listing disabled
- [ ] Sensitive files blocked

### Testing

- [ ] Site accessible via HTTPS
- [ ] Login works
- [ ] All pages load
- [ ] Database operations work
- [ ] File uploads work
- [ ] Auto-billing works
- [ ] Reports generate
- [ ] Error handling works

**Documentation:** `DEPLOY_WEB_ANDROID.md`

---

## 📱 MOBILE/PWA DEPLOYMENT

### Prerequisites

- [ ] Web server with HTTPS (mandatory)
- [ ] manifest.json configured
- [ ] service-worker.js included
- [ ] App icons created

### Icon Preparation

- [ ] Icons generated in required sizes:
  - [ ] 72x72
  - [ ] 96x96
  - [ ] 128x128
  - [ ] 144x144
  - [ ] 152x152
  - [ ] 192x192
  - [ ] 384x384
  - [ ] 512x512
- [ ] Icons uploaded to `assets/images/`
- [ ] Paths in manifest.json correct

### PWA Configuration

- [ ] manifest.json accessible
- [ ] Service worker registered
- [ ] PWA meta tags added to all pages
- [ ] Theme colors set
- [ ] App name configured
- [ ] Start URL correct

### Testing

- [ ] PWA installable on Android Chrome
- [ ] PWA installable on iOS Safari
- [ ] PWA installable on desktop Chrome/Edge
- [ ] Offline mode works
- [ ] Home screen icon appears
- [ ] Fullscreen mode works
- [ ] Theme color correct

### Optional: Native Android App

- [ ] TWA project created
- [ ] Digital Asset Links configured
- [ ] APK built and signed
- [ ] APK tested on device
- [ ] Google Play listing created (if publishing)

**Documentation:** `DEPLOY_WEB_ANDROID.md`

---

## 🔒 SECURITY CHECKLIST

### Application Security

- [ ] Display errors disabled in production
- [ ] Error logging enabled
- [ ] Prepared statements used (SQL injection prevention)
- [ ] Output escaped (XSS prevention)
- [ ] File upload validation
- [ ] Session security configured
- [ ] Password hashing (bcrypt/argon2)
- [ ] Admin password changed from default

### Server Security

- [ ] Firewall enabled
- [ ] Only necessary ports open (80, 443, 22)
- [ ] SSH password login disabled
- [ ] Root login disabled
- [ ] Automatic security updates enabled
- [ ] Intrusion detection (optional)
- [ ] Rate limiting configured

### File Security

- [ ] Sensitive files not web-accessible
- [ ] Config files outside web root (or blocked)
- [ ] Database file not downloadable
- [ ] Log files not accessible
- [ ] Backup files not web-accessible

### HTTPS/SSL

- [ ] Valid SSL certificate installed
- [ ] TLS 1.2+ only
- [ ] HSTS enabled
- [ ] Mixed content warnings resolved

---

## 📊 POST-DEPLOYMENT CHECKLIST

### Functional Testing

- [ ] Login/logout works
- [ ] User registration works
- [ ] Patient management works
- [ ] Doctor management works
- [ ] Appointment booking works
- [ ] Billing system works
- [ ] Auto-billing triggers:
  - [ ] On admission
  - [ ] On lab test order
  - [ ] On consultation completion
  - [ ] On medicine prescription
- [ ] Laboratory module works
- [ ] Inventory management works
- [ ] Reports generate correctly
- [ ] Settings save properly

### Performance Testing

- [ ] Page load times acceptable
- [ ] Database queries optimized
- [ ] No memory leaks
- [ ] No N+1 queries
- [ ] Images optimized
- [ ] Static assets cached

### Monitoring Setup

- [ ] Uptime monitoring configured
- [ ] Error monitoring enabled
- [ ] Log rotation configured
- [ ] Disk space monitoring
- [ ] Database size monitoring

### Backup Verification

- [ ] Backup script tested
- [ ] Automated backups scheduled
- [ ] Backup restoration tested
- [ ] Off-site backup configured (optional)
- [ ] Backup retention policy set

### Documentation

- [ ] Admin documentation provided
- [ ] User manual created (optional)
- [ ] API documentation (if needed)
- [ ] Deployment notes documented
- [ ] Known issues documented

### User Training

- [ ] Admin trained
- [ ] Staff trained on basic operations
- [ ] Support procedures established
- [ ] Contact information provided

---

## 🎯 GO-LIVE CHECKLIST

### Final Checks

- [ ] All deployment checklists completed
- [ ] All tests passed
- [ ] Backups verified
- [ ] Monitoring active
- [ ] Support plan ready
- [ ] Rollback plan documented

### Communication

- [ ] Users notified of go-live date
- [ ] Training completed
- [ ] Support channels established
- [ ] Emergency contacts shared

### Launch

- [ ] DNS updated (if needed)
- [ ] Site accessible
- [ ] SSL working
- [ ] First login successful
- [ ] Sample data entered
- [ ] Real users can access

### Post-Launch

- [ ] Monitor for 24-48 hours
- [ ] Check error logs
- [ ] Verify backups running
- [ ] Collect user feedback
- [ ] Address any issues

---

## 📞 SUPPORT & MAINTENANCE

### Daily

- [ ] Check error logs
- [ ] Verify backups completed
- [ ] Monitor disk space

### Weekly

- [ ] Review system usage
- [ ] Check for slow queries
- [ ] Review user feedback

### Monthly

- [ ] Database optimization (VACUUM)
- [ ] Update dependencies
- [ ] Security audit
- [ ] Performance review
- [ ] Backup restoration test

---

## 🆘 ROLLBACK PLAN

If deployment fails:

1. **Restore previous version:**

   ```bash
   # Restore backup
   cp backups/hms_database_backup.sqlite database/hms_database.sqlite
   ```

2. **Revert files:**

   ```bash
   # Git rollback
   git checkout previous-version
   ```

3. **Restart services:**

   ```bash
   sudo systemctl restart apache2  # or nginx
   sudo systemctl restart php-fpm
   ```

4. **Notify users**

5. **Document issue**

6. **Fix and redeploy**

---

## ✅ DEPLOYMENT SIGN-OFF

**Deployment completed by:** ********\_********  
**Date:** ********\_********  
**Platform:** [ ] Windows [ ] macOS [ ] Web [ ] Mobile  
**All checklists completed:** [ ] Yes [ ] No  
**Issues found:** ********\_********  
**Status:** [ ] Success [ ] Partial [ ] Failed

---

**For detailed platform-specific instructions, see:**

- Windows: `DEPLOY_WINDOWS.md`
- macOS: `DEPLOY_MACOS.md`
- Web/Mobile: `DEPLOY_WEB_ANDROID.md`
- Quick Start: `README_DEPLOYMENT.md`
