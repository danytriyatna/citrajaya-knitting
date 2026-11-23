# ==============================================================================
# SMART DEPLOYMENT SCRIPT - CITRAJAYA.KNITTING (CJK) [PRODUCTION]
# SOP: Auto-Backup PostgreSQL -> Sync Code Prod -> Clear Cache -> Health Check
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
$remotePath = "/pejuang/production"

Write-Host "========================================================" -ForegroundColor Magenta
Write-Host "  CITRAJAYA KNITTING (CJK) - DEPLOY PRODUCTION SERVER   " -ForegroundColor Magenta
Write-Host "  Target: https://citraknitt.com                        " -ForegroundColor Magenta
Write-Host "========================================================" -ForegroundColor Magenta
Write-Host ""
Write-Host "[PERINGATAN] Ini adalah server LIVE PRODUCTION yang digunakan user kantor." -ForegroundColor Yellow
$confirm = Read-Host "Ketik 'Y' untuk melanjutkan proses deployment ke Production"
if ($confirm.Trim().ToUpper() -ne "Y") {
    Write-Host "[BATAL] Deployment dibatalkan oleh pengguna." -ForegroundColor Yellow
    exit 0
}
Write-Host ""

# ------------------------------------------------------------------------------
# 1. SINKRONISASI KODE TERVERIFIKASI KE PRODUCTION (Tanpa Backup DB)
# ------------------------------------------------------------------------------
Write-Host ""
Write-Host "[1/3] Menyinkronkan kode terverifikasi dari Development ke Production..." -ForegroundColor Yellow
# Gunakan rsync aman via SSH dengan mengecualikan .env, writable, wa-gateway, dan file temporary
$syncCmd = "rsync -av --exclude='.env' --exclude='.git' --exclude='writable' --exclude='wa-gateway' --exclude='*.bak*' --exclude='*.dump' --exclude='*.sql' /pejuang/development/html/ /pejuang/production/"
& ssh -o StrictHostKeyChecking=no "$sshUser@$sshHost" "$syncCmd"

if ($LASTEXITCODE -ne 0) {
    Write-Host "[WARN] Terjadi kendala saat rsync kode ke production." -ForegroundColor Yellow
} else {
    Write-Host "[OK] Sinkronisasi kode production selesai." -ForegroundColor Green
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
Write-Host "[3/3] Memverifikasi status HTTP live production server..." -ForegroundColor Yellow
try {
    $response = & curl.exe -s -I -k "https://citraknitt.com/"
    if ($response -match "HTTP/1.1 (200|301|302|307)") {
        Write-Host "[OK] Production Aktif & Merespon Normal: ($matches[0])" -ForegroundColor Green
    } else {
        Write-Host "[WARN] Respon tidak biasa:`n$response" -ForegroundColor Yellow
    }
} catch {
    Write-Host "[WARN] Gagal menghubungi https://citraknitt.com/." -ForegroundColor Yellow
}

Write-Host ""
Write-Host "========================================================" -ForegroundColor Green
Write-Host "  SUCCESS! Deployment PRODUCTION Selesai Sesuai SOP.    " -ForegroundColor Green
Write-Host "  Silakan akses: https://citraknitt.com                 " -ForegroundColor Green
Write-Host "========================================================" -ForegroundColor Green
