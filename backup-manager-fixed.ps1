# Script de gestión completa de backups para la Barbería
param(
    [string]$Action = "help",  # help, setup, backup, restore, status, schedule
    [string]$BackupFile = "",
    [switch]$Force
)

$BackupPath = "C:\pgsql-barberias\backups"
$ScriptsPath = Join-Path $PSScriptRoot "docker\backup"

function Show-Help {
    Write-Host @"
🏪 Sistema de Backup - Gestión de Barberías

Uso: .\backup-manager.ps1 [acción] [opciones]

Acciones disponibles:
  setup        Configurar backup automático cada hora
  backup       Crear backup manual inmediato
  restore      Restaurar desde backup más reciente
  status       Ver estado del sistema de backups
  schedule     Administrar programación de backups
  help         Mostrar esta ayuda

Opciones:
  -BackupFile  Especificar archivo de backup específico para restaurar
  -Force       Forzar operación sin confirmación

Ejemplos:
  .\backup-manager.ps1 setup                    # Configurar backups automáticos
  .\backup-manager.ps1 backup                   # Crear backup ahora
  .\backup-manager.ps1 restore                  # Restaurar último backup
  .\backup-manager.ps1 restore -BackupFile "barberia_backup_20250724_140000.sql"
  .\backup-manager.ps1 status                   # Ver estado
  .\backup-manager.ps1 schedule                 # Administrar programación

"@ -ForegroundColor Cyan
}

function Setup-BackupSystem {
    Write-Host "🚀 Configurando sistema de backup automático..." -ForegroundColor Green
    
    # Verificar que los scripts existen
    $schedulerScript = Join-Path $ScriptsPath "backup-scheduler.ps1"
    if (!(Test-Path $schedulerScript)) {
        Write-Host "❌ Error: Script de programación no encontrado" -ForegroundColor Red
        Write-Host "📁 Buscando en: $schedulerScript" -ForegroundColor Yellow
        return
    }
    
    # Ejecutar configuración del programador
    & $schedulerScript -Action "start" -BackupPath $BackupPath
    
    Write-Host @"

✅ Sistema de backup configurado exitosamente!

📅 Configuración:
  • Frecuencia: Cada hora
  • Directorio: $BackupPath
  • Retención: 24 backups (últimas 24 horas)
  • Auto-restauración: Al iniciar contenedor

🎯 Próximos pasos:
  • Los backups se crearán automáticamente cada hora
  • Al reiniciar el contenedor, se restaurará el backup más reciente
  • Use '.\backup-manager.ps1 status' para monitorear

"@ -ForegroundColor Green
}

function Create-ManualBackup {
    Write-Host "💾 Creando backup manual..." -ForegroundColor Yellow
    
    $backupScript = Join-Path $ScriptsPath "create-backup.ps1"
    if (!(Test-Path $backupScript)) {
        Write-Host "❌ Error: Script de backup no encontrado" -ForegroundColor Red
        return
    }
    
    & $backupScript -BackupPath $BackupPath
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✅ Backup manual completado" -ForegroundColor Green
    } else {
        Write-Host "❌ Error en backup manual" -ForegroundColor Red
    }
}

function Restore-FromBackup {
    Write-Host "📥 Iniciando restauración..." -ForegroundColor Yellow
    
    $restoreScript = Join-Path $ScriptsPath "restore-backup.ps1"
    if (!(Test-Path $restoreScript)) {
        Write-Host "❌ Error: Script de restauración no encontrado" -ForegroundColor Red
        return
    }
    
    if ($BackupFile) {
        & $restoreScript -BackupPath $BackupPath -BackupFile $BackupFile -Force:$Force
    } else {
        & $restoreScript -BackupPath $BackupPath -Force:$Force
    }
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✅ Restauración completada" -ForegroundColor Green
    } else {
        Write-Host "❌ Error en restauración" -ForegroundColor Red
    }
}

function Show-BackupStatus {
    Write-Host "📊 Estado del Sistema de Backup" -ForegroundColor Cyan
    Write-Host "================================" -ForegroundColor Cyan
    
    $schedulerScript = Join-Path $ScriptsPath "backup-scheduler.ps1"
    if (Test-Path $schedulerScript) {
        & $schedulerScript -Action "status" -BackupPath $BackupPath
    } else {
        Write-Host "❌ Scripts de backup no encontrados" -ForegroundColor Red
        Write-Host "📁 Ejecute 'setup' para configurar el sistema" -ForegroundColor Yellow
    }
    
    # Información adicional del contenedor
    Write-Host "`n🐳 Estado del contenedor PostgreSQL:" -ForegroundColor Cyan
    $containerStatus = docker ps --filter "name=barberia-postgres-standalone" --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"
    if ($containerStatus) {
        Write-Host $containerStatus -ForegroundColor Green
    } else {
        Write-Host "❌ Contenedor PostgreSQL no está ejecutándose" -ForegroundColor Red
    }
}

function Manage-Schedule {
    Write-Host "📅 Gestión de Programación de Backups" -ForegroundColor Cyan
    Write-Host "=====================================" -ForegroundColor Cyan
    
    $schedulerScript = Join-Path $ScriptsPath "backup-scheduler.ps1"
    if (!(Test-Path $schedulerScript)) {
        Write-Host "❌ Error: Script de programación no encontrado" -ForegroundColor Red
        return
    }
    
    Write-Host "Opciones disponibles:"
    Write-Host "1. Iniciar programación automática"
    Write-Host "2. Detener programación automática"
    Write-Host "3. Ver estado actual"
    Write-Host "4. Salir"
    
    $choice = Read-Host "`nSeleccione una opción (1-4)"
    
    switch ($choice) {
        "1" { 
            & $schedulerScript -Action "start" -BackupPath $BackupPath
            Write-Host "✅ Programación iniciada" -ForegroundColor Green
        }
        "2" { 
            & $schedulerScript -Action "stop" -BackupPath $BackupPath
            Write-Host "✅ Programación detenida" -ForegroundColor Green
        }
        "3" { 
            & $schedulerScript -Action "status" -BackupPath $BackupPath
        }
        "4" { 
            Write-Host "👋 Saliendo..." -ForegroundColor Yellow
        }
        default { 
            Write-Host "❌ Opción no válida" -ForegroundColor Red
        }
    }
}

# Función principal
switch ($Action.ToLower()) {
    "setup" { Setup-BackupSystem }
    "backup" { Create-ManualBackup }
    "restore" { Restore-FromBackup }
    "status" { Show-BackupStatus }
    "schedule" { Manage-Schedule }
    "help" { Show-Help }
    default { 
        Write-Host "❌ Acción no válida: $Action" -ForegroundColor Red
        Show-Help
    }
}
