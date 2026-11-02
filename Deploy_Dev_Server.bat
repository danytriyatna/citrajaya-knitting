@echo off
title Deploy Dev Server (citraknitt.com:81)
color 0A

powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\deploy_cjk.ps1"

echo.
pause
