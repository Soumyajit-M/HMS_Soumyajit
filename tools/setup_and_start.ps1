# Initializes the SQLite DB (if needed) then starts the server
$ErrorActionPreference = 'Stop'

Write-Host "Initializing database (if needed)..." -ForegroundColor Yellow
try {
  php "deployment/scripts/init_production_db.php"
} catch {
  Write-Warning "Database initialization script failed: $($_.Exception.Message)"
}

Write-Host "Launching server..." -ForegroundColor Yellow
& "$PSScriptRoot\start_local_server.ps1"
