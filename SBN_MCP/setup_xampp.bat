@echo off
REM ============================================================
REM Script de Configuración para XAMPP - Windows
REM Sistema de Gestión de Bienes Nacionales
REM Maternidad Concepción Palacios
REM ============================================================

echo.
echo ============================================================
echo  Sistema de Gestion de Bienes Nacionales
echo  Maternidad Concepcion Palacios
echo  Configuracion para XAMPP
echo ============================================================
echo.

REM Forzar el uso del PHP de XAMPP para evitar conflictos con versiones globales (como PHP 8.5 de WinGet)
set "PHP_EXE=C:\xampp\php\php.exe"
set "XAMPP_PHP_PATH=C:\xampp\php"
set "PATH=%XAMPP_PHP_PATH%;%PATH%"

REM Verificar si XAMPP está instalado
if not exist "C:\xampp\mysql\bin\mysql.exe" (
    echo [ERROR] XAMPP no encontrado en C:\xampp
    echo Por favor instale XAMPP primero desde https://www.apachefriends.org/
    pause
    exit /b 1
)

echo [OK] XAMPP encontrado
echo.

REM Verificar si MySQL está ejecutándose
echo Verificando MySQL...
C:\xampp\mysql\bin\mysqladmin -u root ping >nul 2>&1
if errorlevel 1 (
    echo [ERROR] MySQL no esta ejecutandose
    echo Por favor inicie MySQL desde el Panel de Control de XAMPP
    pause
    exit /b 1
)

echo [OK] MySQL esta ejecutandose
echo.

REM Crear base de datos y importar esquema
echo Creando base de datos...
C:\xampp\mysql\bin\mysql -u root -e "CREATE DATABASE IF NOT EXISTS hospital_bienes CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
if errorlevel 1 (
    echo [ERROR] No se pudo crear la base de datos
    pause
    exit /b 1
)

echo [OK] Base de datos creada
echo.

REM Importar esquema MySQL
echo Importando esquema de base de datos...
C:\xampp\mysql\bin\mysql -u root hospital_bienes < sql\hospital_bienes_DEFINITIVO.sql
if errorlevel 1 (
    echo [ERROR] No se pudo importar el esquema
    pause
    exit /b 1
)

echo [OK] Esquema importado correctamente
echo.

REM Verificar extensiones PHP requeridas
echo Verificando extensiones PHP...
"%PHP_EXE%" -m | findstr /i "pdo_mysql" >nul
if errorlevel 1 (
    echo [ADVERTENCIA] La extension pdo_mysql no esta habilitada
    echo Por favor habilite extension=pdo_mysql en C:\xampp\php\php.ini
)

"%PHP_EXE%" -m | findstr /i "mbstring" >nul
if errorlevel 1 (
    echo [ADVERTENCIA] La extension mbstring no esta habilitada
    echo Por favor habilite extension=mbstring en C:\xampp\php\php.ini
)

"%PHP_EXE%" -m | findstr /i "json" >nul
if errorlevel 1 (
    echo [ADVERTENCIA] La extension json no esta habilitada
    echo Por favor habilite extension=json en C:\xampp\php\php.ini
)

"%PHP_EXE%" -m | findstr /i "gd" >nul
if errorlevel 1 (
    echo [ADVERTENCIA] La extension gd no esta habilitada
    echo Por favor habilite extension=gd en C:\xampp\php\php.ini (requerida por PhpSpreadsheet)
)

"%PHP_EXE%" -m | findstr /i "zip" >nul
if errorlevel 1 (
    echo [ADVERTENCIA] La extension zip no esta habilitada
    echo Por favor habilite extension=zip en C:\xampp\php\php.ini
)

echo [OK] Extensiones PHP verificadas
echo.

REM Crear directorios necesarios
echo Creando directorios...
if not exist "uploads" mkdir uploads
if not exist "logs" mkdir logs
if not exist "reports" mkdir reports

echo [OK] Directorios creados
echo.

REM Verificar archivo .env
if not exist ".env" (
    echo [ADVERTENCIA] Archivo .env no encontrado
    echo Creando archivo .env por defecto...
    copy .env.example .env >nul 2>&1
    if not exist ".env" (
        echo [ERROR] No se pudo crear el archivo .env
        pause
        exit /b 1
    )
)

echo [OK] Archivo .env verificado
echo.

REM Encontrar la ubicación de composer.phar
set "COMPOSER_PHAR="
for /f "delims=" %%i in ('where composer 2^>nul') do (
    set "COMPOSER_PHAR=%%i"
    goto :found_composer_phar
)

:found_composer_phar
if not defined COMPOSER_PHAR (
    echo [ADVERTENCIA] El comando 'composer' no se encontro en el PATH.
    echo Intentando buscar composer.phar en una ubicacion comun...
    if exist "C:\ProgramData\ComposerSetup\bin\composer.phar" (
        set "COMPOSER_PHAR=C:\ProgramData\ComposerSetup\bin\composer.phar"
    ) else if exist "%XAMPP_PHP_PATH%\composer.phar" (
        set "COMPOSER_PHAR=%XAMPP_PHP_PATH%\composer.phar"
    ) else (
        echo [ERROR] No se pudo encontrar composer.phar.
        echo Por favor, asegurese de que Composer este instalado y en su PATH, o coloque composer.phar en C:\xampp\php\
        pause
        exit /b 1
    )
) else (
    echo [OK] Composer encontrado en: %COMPOSER_PHAR%
)

echo Instalando/Actualizando dependencias con Composer...
    "%PHP_EXE%" "%COMPOSER_PHAR%" update --no-dev --optimize-autoloader
    if errorlevel 1 (
        echo [ADVERTENCIA] Error al instalar dependencias
        echo Puede intentar ejecutar: composer install manualmente
    ) else (
        echo [OK] Dependencias instaladas
    )
)

echo.
echo ============================================================
echo  Configuracion completada!
echo ============================================================
echo.
echo  Para acceder al sistema:
echo  1. Inicie Apache y MySQL desde XAMPP Control Panel
echo  2. Abra su navegador en: http://localhost/SBN_MCP/public/
 echo     (Asegurese de incluir la barra diagonal al final)
echo  3. Credenciales de administrador:
echo     - Usuario: admin
echo     - Contrasena: Admin_bn
echo     (El sistema pedira cambiar la clave en el primer login)
echo.
echo  NOTA: Cambie la contrasena del administrador despues del primer login
echo.
echo  NOTA: Si usa PowerShell, ejecute: .\setup_xampp.bat
echo.
pause
