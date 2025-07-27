# Script para restaurar desde el backup más reciente
param(
    [string]$BackupPath = "C:\pgsql-barberias\backups",
    [string]$BackupFile = "",
    [switch]$Force
)

$ContainerName = "barberia-postgres-standalone"
$DatabaseName = "barberia_db"
$Username = "barberia_user"

# Función para logs con timestamp
function Write-LogMessage {
    param([string]$Message, [string]$Color = "White")
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    Write-Host "[$timestamp] $Message" -ForegroundColor $Color
}

Write-LogMessage "🔄 Iniciando proceso de restauración..." "Green"

# Verificar que el contenedor esté corriendo
$containerStatus = docker ps --filter "name=$ContainerName" --format "{{.Status}}"
if (-not $containerStatus) {
    Write-LogMessage "❌ Error: El contenedor $ContainerName no está corriendo" "Red"
    exit 1
}

# Determinar qué backup usar
if ($BackupFile) {
    $backupToRestore = Join-Path $BackupPath $BackupFile
    if (!(Test-Path $backupToRestore)) {
        Write-LogMessage "❌ Error: El archivo de backup especificado no existe: $BackupFile" "Red"
        exit 1
    }
} else {
    # Buscar el backup más reciente
    $backups = Get-ChildItem -Path $BackupPath -Filter "barberia_backup_*.sql" | Sort-Object LastWriteTime -Descending
    if ($backups.Count -eq 0) {
        Write-LogMessage "❌ Error: No se encontraron backups en $BackupPath" "Red"
        exit 1
    }
    $backupToRestore = $backups[0].FullName
    Write-LogMessage "📋 Usando backup más reciente: $($backups[0].Name)" "Yellow"
}

# Verificar que el archivo existe y es válido
if (!(Test-Path $backupToRestore)) {
    Write-LogMessage "❌ Error: El archivo de backup no existe: $backupToRestore" "Red"
    exit 1
}

$fileSize = (Get-Item $backupToRestore).Length
$fileSizeKB = [math]::Round($fileSize / 1KB, 2)
Write-LogMessage "📄 Archivo de backup: $(Split-Path $backupToRestore -Leaf)" "Green"
Write-LogMessage "📊 Tamaño: $fileSizeKB KB" "Green"

# Confirmar restauración si no se usa -Force
if (-not $Force) {
    Write-LogMessage "⚠️ ADVERTENCIA: Esta operación eliminará todos los datos actuales de la base de datos" "Red"
    $confirmation = Read-Host "¿Desea continuar? (s/N)"
    if ($confirmation -ne "s" -and $confirmation -ne "S") {
        Write-LogMessage "❌ Operación cancelada por el usuario" "Yellow"
        exit 0
    }
}

try {
    Write-LogMessage "🗑️ Eliminando base de datos actual..." "Yellow"
    
    # Terminar conexiones activas
    $killConnections = @"
SELECT pg_terminate_backend(pg_stat_activity.pid)
FROM pg_stat_activity
WHERE pg_stat_activity.datname = '$DatabaseName'
  AND pid <> pg_backend_pid();
"@
    
    docker exec $ContainerName psql -h localhost -U $Username -d postgres -c $killConnections
    
    # Eliminar y recrear la base de datos
    docker exec $ContainerName psql -h localhost -U $Username -d postgres -c "DROP DATABASE IF EXISTS $DatabaseName;"
    docker exec $ContainerName psql -h localhost -U $Username -d postgres -c "CREATE DATABASE $DatabaseName;"
    
    Write-LogMessage "✅ Base de datos recreada" "Green"
    
    Write-LogMessage "📥 Restaurando desde backup..." "Yellow"
    
    # Leer el contenido del backup y ejecutarlo
    $backupContent = Get-Content $backupToRestore -Raw
    
    # Usar un archivo temporal en el contenedor
    $tempFile = "/tmp/restore_backup.sql"
    
    # Copiar el backup al contenedor
    $backupContent | docker exec -i $ContainerName tee $tempFile > $null
    
    # Ejecutar la restauración
    docker exec $ContainerName psql -h localhost -U $Username -d $DatabaseName -f $tempFile
    
    if ($LASTEXITCODE -eq 0) {
        Write-LogMessage "✅ Restauración completada exitosamente" "Green"
        
        # Limpiar archivo temporal
        docker exec $ContainerName rm $tempFile
        
        # Verificar algunas tablas importantes
        Write-LogMessage "🔍 Verificando restauración..." "Yellow"
        $tableCount = docker exec $ContainerName psql -h localhost -U $Username -d $DatabaseName -t -c "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'public';"
        Write-LogMessage "📊 Tablas restauradas: $($tableCount.Trim())" "Green"
        
    } else {
        Write-LogMessage "❌ Error durante la restauración" "Red"
        exit 1
    }
    
} catch {
    Write-LogMessage "❌ Error durante la restauración: $($_.Exception.Message)" "Red"
    exit 1
}

Write-LogMessage "✅ Proceso de restauración completado" "Green"
