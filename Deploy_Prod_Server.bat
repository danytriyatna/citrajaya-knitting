@echo off
title Deploy Production Server (citraknitt.com)
color 0D

powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\deploy_prod.ps1"

echo.
pause
