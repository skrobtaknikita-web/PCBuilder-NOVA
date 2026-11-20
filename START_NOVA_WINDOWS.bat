@echo off
setlocal EnableExtensions
chcp 65001 >nul
cd /d "%~dp0"
title PCBuilder NOVA 5.0 Launcher
set "LARAGON=D:\valik\laragon"
set "PHP=php"
where php >nul 2>&1
if errorlevel 1 (
 for /d %%D in ("%LARAGON%\bin\php\php-*") do set "PHP=%%~fD\php.exe"
)
set "COMPOSER=composer"
where composer >nul 2>&1
if errorlevel 1 (
 echo [ERROR] Composer not found. Open Laragon Terminal and run composer install.
 pause
 exit /b 1
)
if not exist "vendor\autoload.php" (
 echo Installing Laravel dependencies...
 call composer install
 if errorlevel 1 (echo Composer failed. & pause & exit /b 1)
)
if not exist ".env" (
 if not exist ".env.example" (echo Missing .env.example & pause & exit /b 1)
 copy /y ".env.example" ".env" >nul
 echo Created .env from example. Set MySQL connection in .env if needed.
)
findstr /B /C:"APP_KEY=base64:" ".env" >nul 2>&1
if errorlevel 1 "%PHP%" artisan key:generate
"%PHP%" artisan optimize:clear
"%PHP%" artisan migrate --force
if errorlevel 1 (echo [ERROR] Migration failed. Start MySQL in Laragon and check .env & pause & exit /b 1)
echo.
choice /C YN /M "Import or update demo catalog and users"
if errorlevel 2 goto servers
"%PHP%" artisan db:seed --force
if errorlevel 1 (echo [ERROR] Seeding failed. & pause & exit /b 1)
:servers
start "NOVA API 8000" "%~dp0START_API.bat"
start "NOVA CLIENT 5500" "%~dp0START_CLIENT.bat"
timeout /t 3 >nul
start "" "http://127.0.0.1:5500"
echo Running. Keep both server windows open.
pause
