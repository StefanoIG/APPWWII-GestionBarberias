# Script para crear backup de PostgreSQL
param(
    [string]$BackupPath = "C:\pgsql-barberias\backups"
)

$ContainerName = "barberia-postgres-standalone"
$DatabaseName = "barberia_db"
$Username = "barberia_user"

# Funcion para logs con timestamp
function Write-LogMessage {
    param([string]$Message, [string]$Color = "White")
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    Write-Host "[$timestamp] $Message" -ForegroundColor $Color
}

Write-LogMessage "Iniciando proceso de backup..." "Green"

# Verificar que el contenedor este corriendo
$containerStatus = docker ps --filter "name=$ContainerName" --format "{{.Status}}"
if (-not $containerStatus) {
    Write-LogMessage "Error: El contenedor $ContainerName no esta corriendo" "Red"
    exit 1
}

Write-LogMessage "Contenedor $ContainerName esta activo" "Green"

# Crear directorio de backups si no existe
if (!(Test-Path $BackupPath)) {
    New-Item -ItemType Directory -Path $BackupPath -Force | Out-Null
    Write-LogMessage "Directorio creado: $BackupPath" "Yellow"
}

# Generar nombre de archivo con timestamp
$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
$backupFileName = "barberia_backup_$timestamp.sql"
$backupFilePath = Join-Path $BackupPath $backupFileName

try {
    Write-LogMessage "Creando backup: $backupFileName" "Yellow"
    
    # Crear backup usando pg_dump
    $dumpCommand = "pg_dump -h localhost -U $Username -d $DatabaseName --no-password"
    $result = docker exec $ContainerName bash -c $dumpCommand
    
    if ($LASTEXITCODE -eq 0) {
        # Guardar el resultado en archivo
        $result | Out-File -FilePath $backupFilePath -Encoding UTF8
        
        $fileSize = (Get-Item $backupFilePath).Length
        $fileSizeKB = [math]::Round($fileSize / 1KB, 2)
        
        Write-LogMessage "Backup creado exitosamente" "Green"
        Write-LogMessage "Archivo: $backupFileName" "Green"
        Write-LogMessage "Tamaño: $fileSizeKB KB" "Green"
        
        # Limpiar backups antiguos (mantener solo los ultimos 24)
        Write-LogMessage "Limpiando backups antiguos..." "Yellow"
        $allBackups = Get-ChildItem -Path $BackupPath -Filter "barberia_backup_*.sql" | Sort-Object LastWriteTime -Descending
        
        if ($allBackups.Count -gt 24) {
            $backupsToDelete = $allBackups | Select-Object -Skip 24
            foreach ($backup in $backupsToDelete) {
                Remove-Item $backup.FullName -Force
                Write-LogMessage "Eliminado backup antiguo: $($backup.Name)" "Gray"
            }
        }
        
        Write-LogMessage "Proceso de backup completado" "Green"
        
    } else {
        Write-LogMessage "Error al crear el backup" "Red"
        exit 1
    }
    
} catch {
    Write-LogMessage "Error durante el backup: $($_.Exception.Message)" "Red"
    exit 1
}

# Verificar integridad del backup
Write-LogMessage "Verificando integridad del backup..." "Yellow"
$content = Get-Content $backupFilePath -TotalCount 10
if ($content -match "PostgreSQL database dump") {
    Write-LogMessage "Backup valido confirmado" "Green"
} else {
    Write-LogMessage "Advertencia: No se pudo verificar la integridad del backup" "Yellow"
}
