# Starts the PHP development server and opens the app in the browser
param(
    [string]$HostName = "127.0.0.1",
    [int]$Port = 8000
)

$ProjectRoot = Split-Path -Parent $PSScriptRoot
$ServerUrl = "http://{0}:{1}" -f $HostName, $Port

Write-Host "Starting PHP dev server at $ServerUrl ..." -ForegroundColor Cyan
$env:CI_AUTH_BYPASS = $null

# Start PHP server in a new terminal window
$phpArgs = "-NoExit", "-Command", "Set-Location '$ProjectRoot'; php -S ${HostName}:${Port} -t ."
Start-Process powershell -ArgumentList $phpArgs

Start-Sleep -Seconds 2

# Open browser
Start-Process "$ServerUrl/"
Write-Host "Server started! Browser should open automatically." -ForegroundColor Green
