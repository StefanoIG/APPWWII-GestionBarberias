# Script para programar backups automaticos de PostgreSQL cada hora
param(
    [string]$Action = "start",
    [string]$BackupPath = "C:\pgsql-barberias\backups"
)

$JobName = "PostgreSQL-Backup-Barberia"
$ScriptPath = Join-Path $PSScriptRoot "create-backup.ps1"

function Start-BackupScheduler {
    Write-Host "Configurando backup automatico cada hora..." -ForegroundColor Green
    
    # Crear directorio de backups si no existe
    if (!(Test-Path $BackupPath)) {
        New-Item -ItemType Directory -Path $BackupPath -Force
        Write-Host "Directorio de backups creado: $BackupPath" -ForegroundColor Yellow
    }
    
    # Eliminar tarea existente si existe
    try {
        Unregister-ScheduledTask -TaskName $JobName -Confirm:$false -ErrorAction SilentlyContinue
    } catch {
        # No importa si no existe
    }
    
    # Crear nueva tarea programada
    $TaskAction = New-ScheduledTaskAction -Execute "PowerShell.exe" -Argument "-ExecutionPolicy Bypass -File `"$ScriptPath`" -BackupPath `"$BackupPath`""
    $Trigger = New-ScheduledTaskTrigger -Once -At (Get-Date) -RepetitionInterval (New-TimeSpan -Hours 1) -RepetitionDuration (New-TimeSpan -Days 365)
    $Settings = New-ScheduledTaskSettingsSet -AllowStartIfOnBatteries -DontStopIfGoingOnBatteries -StartWhenAvailable
    $Principal = New-ScheduledTaskPrincipal -UserId $env:USERNAME -LogonType Interactive -RunLevel Highest
    
    Register-ScheduledTask -TaskName $JobName -Action $TaskAction -Trigger $Trigger -Settings $Settings -Principal $Principal -Description "Backup automatico de PostgreSQL para Barberia cada hora"
    
    Write-Host "Tarea programada creada exitosamente: $JobName" -ForegroundColor Green
    Write-Host "Backups se ejecutaran cada hora" -ForegroundColor Yellow
    Write-Host "Backups se guardaran en: $BackupPath" -ForegroundColor Yellow
    
    # Ejecutar primer backup inmediatamente
    Write-Host "Ejecutando primer backup..." -ForegroundColor Yellow
    & $ScriptPath -BackupPath $BackupPath
}

function Stop-BackupScheduler {
    Write-Host "Deteniendo backup automatico..." -ForegroundColor Yellow
    
    try {
        Unregister-ScheduledTask -TaskName $JobName -Confirm:$false
        Write-Host "Tarea programada eliminada: $JobName" -ForegroundColor Green
    } catch {
        Write-Host "Error al eliminar la tarea: $($_.Exception.Message)" -ForegroundColor Red
    }
}

function Get-BackupStatus {
    Write-Host "Estado del sistema de backup:" -ForegroundColor Cyan
    
    # Verificar si la tarea existe
    $task = Get-ScheduledTask -TaskName $JobName -ErrorAction SilentlyContinue
    if ($task) {
        Write-Host "Tarea programada: ACTIVA" -ForegroundColor Green
        Write-Host "Proxima ejecucion: $($task.Triggers[0].StartBoundary)" -ForegroundColor Yellow
        Write-Host "Frecuencia: Cada hora" -ForegroundColor Yellow
    } else {
        Write-Host "Tarea programada: NO CONFIGURADA" -ForegroundColor Red
    }
    
    # Verificar backups existentes
    if (Test-Path $BackupPath) {
        $backups = Get-ChildItem -Path $BackupPath -Filter "*.sql" | Sort-Object LastWriteTime -Descending
        Write-Host "Directorio de backups: $BackupPath" -ForegroundColor Yellow
        Write-Host "Backups encontrados: $($backups.Count)" -ForegroundColor Yellow
        
        if ($backups.Count -gt 0) {
            Write-Host "Ultimo backup: $($backups[0].LastWriteTime)" -ForegroundColor Green
            Write-Host "Archivo: $($backups[0].Name)" -ForegroundColor Green
        }
    } else {
        Write-Host "Directorio de backups no existe: $BackupPath" -ForegroundColor Red
    }
}

# Ejecutar accion solicitada
switch ($Action.ToLower()) {
    "start" { Start-BackupScheduler }
    "stop" { Stop-BackupScheduler }
    "status" { Get-BackupStatus }
    default { 
        Write-Host "Accion no valida. Use: start, stop, status" -ForegroundColor Red
        exit 1
    }
}
