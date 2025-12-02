# Deploy to Railway (PHP + SQLite)

## 1) Connect the repo

- Create a new Railway project → New Service → Deploy from GitHub → select this repo.
- Railway detects PHP via Nixpacks.

## 2) Start command

- This repo includes a `Procfile`:
  - `web: php -S 0.0.0.0:$PORT -t .`
- No build step necessary.

## 3) Environment variables (optional)

- `APP_DEBUG=0` (set `1` only when debugging; do not leave enabled)
- Cookie/session settings are default-safe; you usually don’t need to change them.

## 4) Database (SQLite)

- Quick start (non‑persistent):
  - The SQLite file in `database/hms_database.sqlite` is part of the repo.
  - Every redeploy replaces the filesystem; data won’t persist across deployments.
- Recommended (persistent):
  - Add a Railway Volume (e.g., mount to `/data`).
  - Move/point the SQLite DB to the volume path (requires a small code change to read path from an env var). If you want this, ask the assistant to add `DATABASE_PATH` support in `config/database.php`.

## 5) Initialize admin user (first run)

- Open a Railway shell (or exec) and run:

```
php tools/init_production_db.php
```

- This creates tables if missing and prints the generated admin password (also writes `tools/ADMIN_CREDENTIALS.txt`).

## 6) Access the app

- Once deploy completes, open the Railway service URL.
- Log in with the admin credentials created in step 5 and change the password immediately.

## 7) Troubleshooting

- If login doesn’t stick, ensure you’re accessing over HTTPS (Railway default) and try again.
- Confirm that `database/hms_database.sqlite` exists and is writable. For persistence, use a Volume as described above.
- Check Railway logs for PHP errors.
