@echo off
title Deploy Dev Server (citraknitt.com:81)
color 0A
echo ========================================================
echo   UPDATED DEV SERVER (citraknitt.com:81) AUTO-DEPLOY
echo ========================================================
echo.
echo Pulling latest code from branch 'dev' to server...
ssh -o StrictHostKeyChecking=no root@103.102.153.192 "cd /pejuang/development/html && git pull origin dev"
echo.
echo ========================================================
echo   SUCCESS! Silakan refresh browser di http://citraknitt.com:81
echo ========================================================
pause
