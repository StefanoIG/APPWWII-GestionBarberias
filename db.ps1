# Script para manejar la base de datos independiente
param(
    [switch]$Start,
    [switch]$Stop,
    [switch]$Restart,
    [switch]$Status,
    [switch]$Logs,
    [switch]$Connect,
    [switch]$Backup,
    [switch]$Help
)

$ErrorActionPreference = "Stop"

function Show-Help {
    Write-Host @"
🐘 PostgreSQL Standalone - Gestión de Base de Datos

Uso: .\db.ps1 [opciones]

Opciones:
  -Start     Iniciar PostgreSQL
  -Stop      Detener PostgreSQL
  -Restart   Reiniciar PostgreSQL
  -Status    Ver estado del contenedor
  -Logs      Ver logs de PostgreSQL
  -Connect   Conectar a la base de datos
  -Backup    Crear backup de la base de datos
  -Help      Mostrar esta ayuda

Ejemplos:
  .\db.ps1 -Start          # Iniciar base de datos
  .\db.ps1 -Connect        # Conectar a PostgreSQL
  .\db.ps1 -Backup         # Crear backup
  .\db.ps1 -Stop           # Detener base de datos
"@ -ForegroundColor Cyan
}

function Start-Database {
    Write-Host "🐘 Iniciando PostgreSQL..." -ForegroundColor Green
    docker-compose -f docker-compose-db.yml up -d
    
    Write-Host "⏳ Esperando que PostgreSQL esté listo..." -ForegroundColor Yellow
    $maxAttempts = 30
    $attempt = 1
    
    while ($attempt -le $maxAttempts) {
        try {
            $result = docker exec barberia-postgres-standalone pg_isready -U barberia_user -d barberia_db 2>$null
            if ($LASTEXITCODE -eq 0) {
                Write-Host "✅ PostgreSQL está listo!" -ForegroundColor Green
                Write-Host @"

📊 Base de datos disponible:
  • Host: localhost:5432
  • Base de datos: barberia_db
  • Usuario: barberia_user
  • Contraseña: barberia_password
  
🔧 Comandos útiles:
  • Conectar: .\db.ps1 -Connect
  • Ver logs: .\db.ps1 -Logs
  • Backup: .\db.ps1 -Backup
"@ -ForegroundColor Green
                return
            }
        }
        catch {
            # Continuar intentando
        }
        
        Start-Sleep -Seconds 2
        $attempt++
    }
    
    Write-Host "❌ PostgreSQL tardó mucho en iniciar" -ForegroundColor Red
}

function Stop-Database {
    Write-Host "🛑 Deteniendo PostgreSQL..." -ForegroundColor Yellow
    docker-compose -f docker-compose-db.yml down
    Write-Host "✅ PostgreSQL detenido" -ForegroundColor Green
}

function Restart-Database {
    Write-Host "🔄 Reiniciando PostgreSQL..." -ForegroundColor Yellow
    docker-compose -f docker-compose-db.yml restart
    Write-Host "✅ PostgreSQL reiniciado" -ForegroundColor Green
}

function Show-Status {
    Write-Host "📊 Estado de PostgreSQL:" -ForegroundColor Green
    docker-compose -f docker-compose-db.yml ps
    Write-Host ""
    Write-Host "💾 Uso de recursos:" -ForegroundColor Green
    docker stats barberia-postgres-standalone --no-stream --format "table {{.Name}}\t{{.CPUPerc}}\t{{.MemUsage}}"
}

function Show-Logs {
    Write-Host "📋 Logs de PostgreSQL:" -ForegroundColor Green
    docker-compose -f docker-compose-db.yml logs -f
}

function Connect-Database {
    Write-Host "🔌 Conectando a PostgreSQL..." -ForegroundColor Green
    docker exec -it barberia-postgres-standalone psql -U barberia_user -d barberia_db
}

function Create-Backup {
    $timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
    $backupFile = "backup_barberia_${timestamp}.sql"
    
    Write-Host "💾 Creando backup..." -ForegroundColor Yellow
    docker exec barberia-postgres-standalone pg_dump -U barberia_user -d barberia_db > $backupFile
    Write-Host "✅ Backup guardado: $backupFile" -ForegroundColor Green
}

# Función principal
if ($Help) {
    Show-Help
    return
}

try {
    if ($Start) {
        Start-Database
    }
    elseif ($Stop) {
        Stop-Database
    }
    elseif ($Restart) {
        Restart-Database
    }
    elseif ($Status) {
        Show-Status
    }
    elseif ($Logs) {
        Show-Logs
    }
    elseif ($Connect) {
        Connect-Database
    }
    elseif ($Backup) {
        Create-Backup
    }
    else {
        Show-Help
    }
}
catch {
    Write-Host "❌ Error: $_" -ForegroundColor Red
    exit 1
}
