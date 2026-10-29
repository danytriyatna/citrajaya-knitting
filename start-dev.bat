@echo off
set "PATH=C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64;C:\laragon\bin\composer;%PATH%"
echo Starting CJK (CodeIgniter 4) Dev Server on http://localhost:8085...
php spark serve --port 8085
