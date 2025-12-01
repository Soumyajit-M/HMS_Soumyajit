# Continuous Integration (CI)

This repository includes a GitHub Actions workflow at `.github/workflows/ci.yml` that performs two main checks on push and pull requests:

- PHP linting across all `.php` files.
- Starting a PHP built-in server and running the project smoke test `tools/full_smoke_test.php` against a fresh SQLite database.

How the CI prepares the environment

- Installs PHP 8.3 using `shivammathur/setup-php`.
- Installs `sqlite3` (on Ubuntu runners), then creates a fresh database at `www/database/hms_database.sqlite` using the schema file `database/schema_sqlite.sql`.
- Starts the PHP built-in webserver with document root `www/` and runs the smoke test script.

Run the smoke test locally (Windows PowerShell)

```powershell
# Create fresh database
if (Test-Path "www\database\hms_database.sqlite") { Remove-Item "www\database\hms_database.sqlite" }
sqlite3.exe "www\database\hms_database.sqlite" < "database\schema_sqlite.sql"

# Start PHP built-in server (background)
# $p = Start-Process -PassThru -FilePath php -ArgumentList '-S','127.0.0.1:8000' -WorkingDirectory (Get-Location)

# Run smoke test
php tools/full_smoke_test.php

# Stop server when done
# Stop-Process -Id $p.Id -ErrorAction SilentlyContinue
```

Notes

- The smoke test expects writable `www/database/` and `www/logs/` directories. Ensure those exist and are writable.
- If running on Linux (CI runner), the workflow installs `sqlite3` before creating the DB.
- If you want me to push the repository to a remote, provide the remote URL (or allow me to add one) and I can push the commits.

Questions or next steps

- Add secrets or protected branches before pushing to a public remote.
- I can create a minimal GitHub Actions badge snippet for `README.md` if you want.
