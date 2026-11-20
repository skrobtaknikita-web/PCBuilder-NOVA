@echo off
chcp 65001 >nul
cd /d "%~dp0"
where php >nul 2>nul || (echo PHP not found. Start Laragon terminal. & pause & exit /b 1)
where composer >nul 2>nul || (echo Composer not found. Start Laragon terminal. & pause & exit /b 1)
if not exist .env copy .env.example .env
call composer install
if errorlevel 1 (echo Composer failed & pause & exit /b 1)
php artisan key:generate
if errorlevel 1 (pause & exit /b 1)
echo Create MySQL database pc_configurator before next step.
php artisan migrate:fresh --seed
if errorlevel 1 (echo Check MySQL, DB credentials and extensions. & pause & exit /b 1)
echo Installation completed.
pause
