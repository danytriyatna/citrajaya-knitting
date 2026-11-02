@echo off
title Install SSH Key ke Server CJK (103.102.153.192)
color 0B
echo ========================================================
echo   MENDAFTARKAN SSH KEY LAPTOP KE SERVER CJK
echo ========================================================
echo.
echo Masukkan password root server CJK satu kali ini saja:
type "%USERPROFILE%\.ssh\id_ed25519.pub" | ssh -o StrictHostKeyChecking=no root@103.102.153.192 "mkdir -p ~/.ssh && chmod 700 ~/.ssh && cat >> ~/.ssh/authorized_keys && chmod 600 ~/.ssh/authorized_keys"
echo.
if %ERRORLEVEL% EQU 0 (
    echo [OK] SSH Key berhasil terpasang! Ke depannya Deploy_Dev_Server.bat tidak akan minta password lagi.
) else (
    echo [ERROR] Gagal memasang SSH Key. Pastikan password yang dimasukkan benar.
)
pause
