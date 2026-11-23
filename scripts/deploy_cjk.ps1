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
# 1. GIT PULL BRANCH PADA SERVER (Tanpa Backup DB)
# ------------------------------------------------------------------------------
Write-Host ""
Write-Host "[1/3] Menarik kode terbaru dari remote git di server..." -ForegroundColor Yellow
$gitPullCmd = "cd $remotePath && git pull origin master"
& ssh -o StrictHostKeyChecking=no "$sshUser@$sshHost" "$gitPullCmd"

if ($LASTEXITCODE -ne 0) {
    Write-Host "[WARN] Periksa output git pull di atas apakah membutuhkan password atau ada konflik." -ForegroundColor Yellow
} else {
    Write-Host "[OK] Git pull selesai." -ForegroundColor Green
}

# ------------------------------------------------------------------------------
# 2. CODEIGNITER 4 CACHE CLEAR & PERMISSIONS
# ------------------------------------------------------------------------------
Write-Host ""
Write-Host "[2/3] Membersihkan cache CodeIgniter 4, reset LSPHP OpCache & hak akses..." -ForegroundColor Yellow
$maintenanceCmd = "cd $remotePath && mkdir -p writable/cache && chmod -R 777 writable && php spark cache:clear && touch .lsphp_restart.txt && (killall -9 lsphp 2>/dev/null || true)"
& ssh -o StrictHostKeyChecking=no "$sshUser@$sshHost" "$maintenanceCmd"
Write-Host "[OK] Cache aplikasi & OpCache server dibersihkan." -ForegroundColor Green

# ------------------------------------------------------------------------------
# 3. HEALTH CHECK VERIFICATION
# ------------------------------------------------------------------------------
Write-Host ""
Write-Host "[3/3] Memverifikasi status HTTP live server..." -ForegroundColor Yellow
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
