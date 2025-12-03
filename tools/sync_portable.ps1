$ErrorActionPreference = 'Stop'
param(
    [string]$Destination = (Join-Path $PSScriptRoot '..\dist\HMS_APP' | Resolve-Path -ErrorAction SilentlyContinue),
    [switch]$PreservePhp
)

function Write-Info($msg) { Write-Host $msg -ForegroundColor Cyan }
function Write-Ok($msg) { Write-Host $msg -ForegroundColor Green }
function Write-Warn($msg) { Write-Host $msg -ForegroundColor Yellow }

$src = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
if (-not $Destination) { $Destination = Join-Path $src 'dist\HMS_APP' }
$dst = $Destination

Write-Info "Syncing portable app"
Write-Host "  Source:      $src"
Write-Host "  Destination: $dst"

New-Item -ItemType Directory -Path $dst -Force | Out-Null

# Exclusions
$xd = @(
    (Join-Path $src '.git'),
    (Join-Path $src 'dist'),
    (Join-Path $src 'logs'),
    (Join-Path $src 'storage\backups')
)
if ($PreservePhp) {
    $xd += (Join-Path $dst 'php')
}

# Use robocopy for efficient mirroring
$robolog = Join-Path $env:TEMP ("portable_sync_" + [guid]::NewGuid() + ".log")
$xdArgs = @(); foreach ($d in $xd) { if (Test-Path $d) { $xdArgs += @('/XD', $d) } }

# Mirror but exclude some heavy or generated folders
$args = @(
    '"' + $src + '"',
    '"' + $dst + '"',
    '/MIR', '/R:1', '/W:2', '/NFL', '/NDL', '/NP', '/XO', '/XJ'
) + $xdArgs + @('/LOG:' + $robolog)

Write-Info "Running robocopy..."
Start-Process -FilePath robocopy -ArgumentList $args -Wait -NoNewWindow

if (Test-Path $robolog) {
    $summary = Get-Content $robolog | Select-String -Pattern 'Dirs :|Files :|Bytes :|Times :'
    if ($summary) { Write-Host ($summary | Out-String) }
}

# Ensure launcher exists next to portable folder if syncing to non-default location
if ($dst -notlike (Join-Path $src 'dist\HMS_APP')) {
    $launcherSrc = Join-Path $src 'dist\HMS_Server.bat'
    if (Test-Path $launcherSrc) {
        $launcherDst = Join-Path (Split-Path $dst -Parent) 'HMS_Server.bat'
        Copy-Item $launcherSrc $launcherDst -Force
        Write-Ok "Updated launcher at: $launcherDst"
    }
}

Write-Ok "Portable app sync complete."
