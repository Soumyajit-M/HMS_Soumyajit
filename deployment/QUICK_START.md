# 📋 DEPLOYMENT QUICK REFERENCE

## 📁 Directory Structure

```
deployment/
├── README.md                       ← START HERE
├── guides/                         ← Platform guides
│   ├── WINDOWS.md                 → Windows desktop
│   ├── MACOS.md                   → macOS desktop
│   ├── WEB_MOBILE.md              → Web & mobile
│   ├── COMPLETE_GUIDE.md          → Full reference
│   └── CHECKLIST.md               → Step-by-step
├── config/                         ← Configuration files
│   ├── nginx.conf                 → Nginx config
│   ├── manifest.json              → PWA manifest
│   └── service-worker.js          → PWA worker
└── scripts/                        ← Setup scripts
    ├── init_production_db.php     → Production setup
    ├── migrate_auto_billing.php   → Auto-billing
    ├── verify_tables.php          → Verify DB
    └── setup_rooms.php            → Create rooms
```

---

## 🚀 Quick Commands

### Production Setup (First Time)

```bash
php deployment/scripts/init_production_db.php
```

### Verify Installation

```bash
php deployment/scripts/verify_tables.php
```

### Test Auto-Billing

```bash
php tools/test_auto_billing.php
```

---

## 🎯 Choose Platform

### 🪟 Windows Desktop

**File:** `deployment/guides/WINDOWS.md`

```powershell
# Already running! To deploy:
# See guide for installer creation
```

### 🌐 Web Server

**File:** `deployment/guides/WEB_MOBILE.md`

```bash
# Upload → Set permissions → Run setup → Install SSL
```

### 🍎 macOS

**File:** `deployment/guides/MACOS.md`

```bash
# MAMP or Docker
```

### 📱 Mobile (PWA)

**File:** `deployment/guides/WEB_MOBILE.md`

```
Deploy to HTTPS web server → Users install from browser
```

---

## 📖 Documentation Hierarchy

1. **deployment/README.md** ← Start here
2. Choose platform guide
3. Follow checklist
4. Run scripts
5. Deploy!

---

## ✅ Deployment Checklist

- [ ] Read `deployment/README.md`
- [ ] Choose platform
- [ ] Read platform guide
- [ ] Run `init_production_db.php`
- [ ] Follow platform-specific steps
- [ ] Test thoroughly
- [ ] Deploy to production

---

## 🆘 Need Help?

**Check:**

1. `deployment/guides/CHECKLIST.md` - Step-by-step
2. Platform-specific guide in `deployment/guides/`
3. `logs/php_errors.log` - Error logs
4. Run `deployment/scripts/verify_tables.php` - Check DB

---

**All documentation in deployment/ folder** 📁
