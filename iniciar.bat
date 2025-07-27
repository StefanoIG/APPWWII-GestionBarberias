@echo off
setlocal EnableDelayedExpansion

echo.
echo ===============================================
echo   Sistema de Gestion de Barberias - Windows
echo ===============================================
echo.

REM Verificar si estamos en el directorio correcto
if not exist "doker-compose.yml" (
    echo ERROR: No se encontro doker-compose.yml
    echo Asegurate de estar en: C:\web2\APPWWII-GestionBarberias
    pause
    exit /b 1
)

REM Verificar Docker
docker --version >nul 2>&1
if !ERRORLEVEL! neq 0 (
    echo ERROR: Docker no esta instalado o no esta en el PATH
    echo Por favor instala Docker Desktop para Windows
    pause
    exit /b 1
)

REM Verificar Docker ejecutandose
docker ps >nul 2>&1
if !ERRORLEVEL! neq 0 (
    echo ERROR: Docker no esta ejecutandose
    echo Por favor inicia Docker Desktop
    pause
    exit /b 1
)

echo Iniciando aplicacion...
echo.

REM Ejecutar PowerShell script
powershell -ExecutionPolicy Bypass -File "start.ps1"

if !ERRORLEVEL! equ 0 (
    echo.
    echo ================================
    echo   Aplicacion iniciada con exito
    echo ================================
    echo.
    echo Aplicacion Web: http://localhost:8000
    echo Base de datos: localhost:5432
    echo.
    echo Presiona cualquier tecla para abrir el navegador...
    pause >nul
    start http://localhost:8000
) else (
    echo.
    echo ERROR: Hubo un problema al iniciar la aplicacion
    echo.
)

pause
