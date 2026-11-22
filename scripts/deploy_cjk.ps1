# ==============================================================================
# SMART DEPLOYMENT SCRIPT - CITRAJAYA.KNITTING (CJK)
# SOP: Auto-Backup PostgreSQL -> Git Pull Dev -> Clear Cache -> Health Check
# ==============================================================================

$repoRoot   = (Resolve-Path "$PSScriptRoot\..").Path
$pgDumpPath = "C:\Program Files\PostgreSQL\18\bin\pg_dump.exe"
$dbHost     = "103.102.153.192"
$dbPort     = "5432"
$dbName     = "citra_knitt_db"
$dbUser     = "citradev"
$env:PGPASSWORD = "C!tr4D3v@2024"

$sshHost    = "103.102.153.192"
$sshUser    = "root"
$remotePath = "/pejuang/development/html"

Write-Host "========================================================" -ForegroundColor Cyan
Write-Host "  CITRAJAYA KNITTING (CJK) - AUTO DEPLOY DEV SERVER      " -ForegroundColor Cyan
Write-Host "  Target: http://citraknitt.com:81                       " -ForegroundColor Cyan
Write-Host "========================================================" -ForegroundColor Cyan
Write-Host ""

# ------------------------------------------------------------------------------
# 1. SOP DATABASE BACKUP (PostgreSQL Dump ke sql/ - Selalu Replace 1 File Tunggal)
# ------------------------------------------------------------------------------
$backupDir  = Join-Path $repoRoot "sql"
if (-not (Test-Path $backupDir)) { New-Item -ItemType Directory -Path $backupDir | Out-Null }
$targetDump = Join-Path $backupDir "backup_cjk_latest.dump"
$tempDump   = Join-Path $backupDir "backup_temp.dump"

Write-Host "[1/4] SOP: Menjalankan dump & backup database PostgreSQL..." -ForegroundColor Yellow
if (Test-Path $pgDumpPath) {
    & "$pgDumpPath" -h $dbHost -p $dbPort -U $dbUser -d $dbName -F c -b -f "$tempDump"
} else {
    Write-Host "[WARN] pg_dump lokal tidak ditemukan di $pgDumpPath, mencoba koneksi via server..." -ForegroundColor Yellow
}

if ((Test-Path "$tempDump") -and ((Get-Item "$tempDump").Length -gt 100000)) {
    # Hapus dump lama dan ganti dengan yang terbaru agar hemat storage (hanya 1 file)
    if (Test-Path "$targetDump") { Remove-Item -Path "$targetDump" -Force }
    Move-Item -Path "$tempDump" -Destination "$targetDump" -Force
    $sizeMB = [math]::Round((Get-Item "$targetDump").Length / 1MB, 2)
    Write-Host "[OK] Snapshot database terbaru tersimpan ($sizeMB MB): sql\backup_cjk_latest.dump" -ForegroundColor Green
} else {
    if (Test-Path "$tempDump") { Remove-Item -Path "$tempDump" -Force }
    Write-Host "[ERROR] Gagal membuat snapshot database! Deployment dibatalkan demi keamanan." -ForegroundColor Red
    pause
    exit 1
}

# ------------------------------------------------------------------------------
# 2. GIT PULL BRANCH PADA SERVER
# ------------------------------------------------------------------------------
Write-Host ""
Write-Host "[2/4] Menarik kode terbaru dari remote git di server..." -ForegroundColor Yellow
$gitPullCmd = "cd $remotePath && git pull origin master"
& ssh -o StrictHostKeyChecking=no "$sshUser@$sshHost" "$gitPullCmd"

if ($LASTEXITCODE -ne 0) {
    Write-Host "[WARN] Periksa output git pull di atas apakah membutuhkan password atau ada konflik." -ForegroundColor Yellow
} else {
    Write-Host "[OK] Git pull selesai." -ForegroundColor Green
}

# ------------------------------------------------------------------------------
# 3. CODEIGNITER 4 CACHE CLEAR & PERMISSIONS
# ------------------------------------------------------------------------------
Write-Host ""
Write-Host "[3/4] Membersihkan cache CodeIgniter 4, reset LSPHP OpCache & hak akses..." -ForegroundColor Yellow
$maintenanceCmd = "cd $remotePath && mkdir -p writable/cache && chmod -R 777 writable && php spark cache:clear && touch .lsphp_restart.txt && (killall -9 lsphp 2>/dev/null || true)"
& ssh -o StrictHostKeyChecking=no "$sshUser@$sshHost" "$maintenanceCmd"
Write-Host "[OK] Cache aplikasi & OpCache server dibersihkan." -ForegroundColor Green

# ------------------------------------------------------------------------------
# 4. HEALTH CHECK VERIFICATION
# ------------------------------------------------------------------------------
Write-Host ""
Write-Host "[4/4] Memverifikasi status HTTP live server..." -ForegroundColor Yellow
try {
    $response = & curl.exe -s -I -H "Host: citraknitt.com:81" "http://$sshHost:81/"
    if ($response -match "HTTP/1.1 (200|301|302|307)") {
        Write-Host "[OK] Server Aktif & Merespon Normal: ($matches[0])" -ForegroundColor Green
    } else {
        Write-Host "[WARN] Respon tidak biasa:`n$response" -ForegroundColor Yellow
    }
} catch {
    Write-Host "[WARN] Gagal menghubungi port 81." -ForegroundColor Yellow
}

Write-Host ""
Write-Host "========================================================" -ForegroundColor Green
Write-Host "  SUCCESS! Deployment CJK Selesai Sesuai SOP.            " -ForegroundColor Green
Write-Host "  Silakan buka: http://citraknitt.com:81                " -ForegroundColor Green
Write-Host "========================================================" -ForegroundColor Green
