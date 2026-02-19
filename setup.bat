@echo off
echo ================================================
echo Restaurant System - Complete Database Setup
echo ================================================
echo.
echo Detecting MySQL installation...
echo.

REM Try to find MySQL in common XAMPP/WAMP locations
set MYSQL_PATH=
set MYSQL_FOUND=0

REM Check XAMPP
if exist "C:\xampp\mysql\bin\mysql.exe" (
    set MYSQL_PATH=C:\xampp\mysql\bin\mysql.exe
    set MYSQL_FOUND=1
    echo Found XAMPP MySQL at: C:\xampp\mysql\bin\
)

REM Check WAMP 64-bit
if %MYSQL_FOUND%==0 (
    if exist "C:\wamp64\bin\mysql\mysql8*\bin\mysql.exe" (
        for /d %%i in ("C:\wamp64\bin\mysql\mysql8*") do (
            set MYSQL_PATH=%%i\bin\mysql.exe
            set MYSQL_FOUND=1
            echo Found WAMP MySQL at: %%i\bin\
        )
    )
)

REM Check WAMP 32-bit
if %MYSQL_FOUND%==0 (
    if exist "C:\wamp\bin\mysql\mysql*\bin\mysql.exe" (
        for /d %%i in ("C:\wamp\bin\mysql\mysql*") do (
            set MYSQL_PATH=%%i\bin\mysql.exe
            set MYSQL_FOUND=1
            echo Found WAMP MySQL at: %%i\bin\
        )
    )
)

REM Check system PATH
if %MYSQL_FOUND%==0 (
    where mysql >nul 2>nul
    if %ERRORLEVEL%==0 (
        set MYSQL_PATH=mysql
        set MYSQL_FOUND=1
        echo Found MySQL in system PATH
    )
)

if %MYSQL_FOUND%==0 (
    echo ================================================
    echo MySQL NOT FOUND!
    echo ================================================
    echo.
    echo Please use phpMyAdmin instead:
    echo.
    echo 1. Open: http://localhost/phpmyadmin
    echo 2. Click "Import" tab
    echo 3. Choose file: database\complete-setup.sql
    echo 4. Click "Go" button
    echo 5. Wait for success message
    echo.
    echo OR manually add MySQL to PATH and run again.
    echo.
    pause
    exit /b 1
)

echo.
set /p DB_USER="MySQL Username (default: root): "
if "%DB_USER%"=="" set DB_USER=root

set /p DB_PASS="MySQL Password (press Enter if none): "

echo.
echo Running database setup...
echo.

"%MYSQL_PATH%" -u %DB_USER% -p%DB_PASS% < database\complete-setup.sql

if errorlevel 1 (
    echo.
    echo ================================================
    echo ERROR: Database setup failed!
    echo ================================================
    echo.
    echo Common issues:
    echo  1. Wrong username/password
    echo  2. MySQL service not running
    echo  3. No permission to create databases
    echo.
    echo Try using phpMyAdmin instead:
    echo  - Open: http://localhost/phpmyadmin
    echo  - Import: database\complete-setup.sql
    echo.
    pause
    exit /b 1
)

echo.
echo Creating upload directories...
if not exist "frontend\assets\uploads" mkdir "frontend\assets\uploads"
if not exist "frontend\assets\images" mkdir "frontend\assets\images"

echo.
echo ================================================
echo Database Setup Complete! ✓
echo ================================================
echo.
echo Database: restaurant_system
echo.
echo Users created (password: admin123):
echo  - admin      (Full access)
echo  - manager    (Analytics + Reports)
echo  - waiter1    (Orders + Tables)
echo  - chef1      (Kitchen only)
echo.
echo Sample data:
echo  - 4 categories, 18 menu items
echo  - 4 sample orders
echo  - 4 customer feedbacks
echo.
echo Next: Run start.bat to launch server
echo Then: Open http://localhost:8000
echo.
pause
