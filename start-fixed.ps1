# Script principal para iniciar la aplicación Laravel con Docker en Windows
param(
    [switch]$Build,
    [switch]$Down,
    [switch]$Fresh,
    [switch]$Logs,
    [switch]$Help
)

$ErrorActionPreference = "Stop"

function Show-Help {
    Write-Host @"
Sistema de Gestión de Barberías - Windows Docker Setup

Uso: .\start.ps1 [opciones]

Opciones:
  -Build    Construir las imágenes desde cero
  -Down     Detener y remover todos los contenedores
  -Fresh    Reinicio completo (down + build + up)
  -Logs     Mostrar logs de los contenedores
  -Help     Mostrar esta ayuda

Ejemplos:
  .\start.ps1              # Iniciar normalmente
  .\start.ps1 -Build       # Reconstruir y iniciar
  .\start.ps1 -Fresh       # Reinicio completo
  .\start.ps1 -Down        # Detener todo
  .\start.ps1 -Logs        # Ver logs
"@ -ForegroundColor Cyan
}

function Test-Docker {
    try {
        $null = docker --version
        Write-Host "Docker está disponible" -ForegroundColor Green
    }
    catch {
        Write-Host "Docker no está instalado o no está en el PATH" -ForegroundColor Red
        Write-Host "Por favor instala Docker Desktop para Windows" -ForegroundColor Yellow
        exit 1
    }
}

function Test-DockerRunning {
    try {
        $null = docker ps 2>$null
        Write-Host "Docker está ejecutándose" -ForegroundColor Green
    }
    catch {
        Write-Host "Docker no está ejecutándose" -ForegroundColor Red
        Write-Host "Por favor inicia Docker Desktop" -ForegroundColor Yellow
        exit 1
    }
}

function Stop-Application {
    Write-Host "Deteniendo aplicación..." -ForegroundColor Yellow
    docker-compose -f docker-compose.yml down
    Write-Host "Aplicación detenida" -ForegroundColor Green
}

function Build-Application {
    Write-Host "Construyendo imágenes..." -ForegroundColor Yellow
    docker-compose -f docker-compose.yml build --no-cache
    Write-Host "Imágenes construidas" -ForegroundColor Green
}

function Start-Application {
    Write-Host "Iniciando aplicación..." -ForegroundColor Green
    
    # Verificar que estemos en el directorio correcto
    if (!(Test-Path "docker-compose.yml")) {
        Write-Host "Error: No se encontró docker-compose.yml" -ForegroundColor Red
        Write-Host "Asegúrate de estar en el directorio: C:\web2\APPWWII-GestionBarberias" -ForegroundColor Yellow
        exit 1
    }
    
    # Verificar si la base de datos está corriendo
    $dbRunning = $false
    try {
        $result = docker ps --filter "name=barberia-postgres-standalone" --format "{{.Names}}"
        if ($result -eq "barberia-postgres-standalone") {
            $dbRunning = $true
        }
    }
    catch {
        # DB no está corriendo
    }
    
    if (-not $dbRunning) {
        Write-Host "La base de datos no está ejecutándose..." -ForegroundColor Yellow
        Write-Host "Iniciando PostgreSQL desde C:\pgsql-barberias..." -ForegroundColor Yellow
        
        $currentLocation = Get-Location
        try {
            Set-Location "C:\pgsql-barberias"
            & ".\db-manager.ps1" -Start
            Start-Sleep -Seconds 10
        }
        catch {
            Write-Host "Error al iniciar la base de datos desde C:\pgsql-barberias" -ForegroundColor Red
            Write-Host "Asegúrate de que existe la carpeta C:\pgsql-barberias" -ForegroundColor Yellow
            Write-Host "O inicia manualmente: cd C:\pgsql-barberias; .\db-manager.ps1 -Start" -ForegroundColor Yellow
            exit 1
        }
        finally {
            Set-Location $currentLocation
        }
    }
    else {
        Write-Host "Base de datos ya está ejecutándose" -ForegroundColor Green
    }
    
    # Iniciar servicios de la aplicación
    docker-compose -f docker-compose.yml up -d
    
    # Esperar un poco para que los servicios se inicialicen
    Write-Host "Esperando que los servicios se inicialicen..." -ForegroundColor Yellow
    Start-Sleep -Seconds 10
    
    # Ejecutar configuración inicial de Laravel
    Write-Host "Configurando Laravel..." -ForegroundColor Yellow
    & ".\docker\scripts\init-laravel.ps1"
    
    Write-Host @"

Aplicación iniciada exitosamente!

Servicios disponibles:
  • Aplicación Web: http://localhost:8000
  • Base de datos PostgreSQL: localhost:5432 (contenedor independiente)
    - Base de datos: barberia_db
    - Usuario: barberia_user
    - Contraseña: barberia_password

Comandos útiles:
  • Ver logs app: .\start.ps1 -Logs
  • Conectar DB: cd C:\pgsql-barberias; .\db-manager.ps1 -Connect
  • Detener app: .\start.ps1 -Down
  • Detener DB: cd C:\pgsql-barberias; .\db-manager.ps1 -Stop
  • Reiniciar todo: .\start.ps1 -Fresh
  
Para debugging:
  • docker-compose -f docker-compose.yml logs -f app
  • docker exec -it barberia-app bash
"@ -ForegroundColor Green
}

function Show-Logs {
    Write-Host "Mostrando logs..." -ForegroundColor Yellow
    docker-compose -f docker-compose.yml logs -f
}

# Función principal
function Main {
    Write-Host "Sistema de Gestión de Barberías - Windows Setup" -ForegroundColor Cyan
    Write-Host "===============================================" -ForegroundColor Cyan
    
    if ($Help) {
        Show-Help
        return
    }
    
    # Verificar Docker
    Test-Docker
    Test-DockerRunning
    
    try {
        if ($Down) {
            Stop-Application
            return
        }
        
        if ($Logs) {
            Show-Logs
            return
        }
        
        if ($Fresh) {
            Write-Host "Reinicio completo..." -ForegroundColor Yellow
            Stop-Application
            Build-Application
            Start-Application
            return
        }
        
        if ($Build) {
            Build-Application
        }
        
        Start-Application
    }
    catch {
        Write-Host "Error durante la ejecución: $_" -ForegroundColor Red
        Write-Host "Prueba ejecutar: .\start.ps1 -Fresh" -ForegroundColor Yellow
        exit 1
    }
}

# Ejecutar función principal
Main
