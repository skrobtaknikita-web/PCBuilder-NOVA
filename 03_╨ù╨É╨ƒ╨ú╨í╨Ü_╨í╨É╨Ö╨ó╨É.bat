@echo off
chcp 65001 >nul
cd /d "%~dp0client"
start "" http://127.0.0.1:5500
php -S 127.0.0.1:5500
pause
