param([string]$Action = "help")

Write-Host "Sistema de Backup - Gestion de Barberias" -ForegroundColor Green

$BackupPath = "C:\pgsql-barberias\backups"
$ScriptsPath = Join-Path $PSScriptRoot "docker\backup"

if ($Action -eq "setup") {
    Write-Host "Configurando sistema de backup automatico..." -ForegroundColor Yellow
    
    # Verificar que los scripts existen
    $schedulerScript = Join-Path $ScriptsPath "backup-scheduler.ps1"
    Write-Host "Buscando script en: $schedulerScript" -ForegroundColor Cyan
    
    if (!(Test-Path $schedulerScript)) {
        Write-Host "Error: Script de programacion no encontrado" -ForegroundColor Red
        Write-Host "Creando directorio de scripts..." -ForegroundColor Yellow
        
        if (!(Test-Path $ScriptsPath)) {
            New-Item -ItemType Directory -Path $ScriptsPath -Force
            Write-Host "Directorio creado: $ScriptsPath" -ForegroundColor Green
        }
    } else {
        Write-Host "Script encontrado, ejecutando configuracion..." -ForegroundColor Green
        & $schedulerScript -Action "start" -BackupPath $BackupPath
    }
    
} elseif ($Action -eq "help") {
    Write-Host @"
Uso: .\backup-manager.ps1 [accion]

Acciones:
  setup   - Configurar backup automatico
  backup  - Crear backup manual
  restore - Restaurar backup
  status  - Ver estado
  help    - Mostrar ayuda

"@ -ForegroundColor Cyan
    
} else {
    Write-Host "Accion no valida: $Action" -ForegroundColor Red
    Write-Host "Use 'help' para ver opciones disponibles" -ForegroundColor Yellow
}
