# Comandos útiles para desarrollo con Docker en Windows
param(
    [string]$Command,
    [string[]]$Args
)

$ErrorActionPreference = "Stop"

function Show-Help {
    Write-Host @"
🛠️  Comandos de Desarrollo - Sistema de Barberías

Uso: .\dev.ps1 <comando> [argumentos]

Comandos Laravel:
  artisan <comando>        Ejecutar comando Artisan
  migrate                  Ejecutar migraciones
  migrate-fresh           Recrear base de datos
  seed                    Ejecutar seeders
  tinker                  Iniciar Laravel Tinker
  cache-clear             Limpiar todos los caches
  logs                    Ver logs de Laravel

Comandos Base de Datos:
  db-connect              Conectar a PostgreSQL
  db-backup               Hacer backup de la DB
  db-restore <archivo>    Restaurar backup

Comandos Docker:
  shell                   Acceder al contenedor de la app
  nginx-shell             Acceder al contenedor de nginx
  restart                 Reiniciar servicios
  status                  Ver estado de contenedores
  clean                   Limpiar imágenes no utilizadas

Comandos de Testing:
  test                    Ejecutar tests
  test-unit              Ejecutar solo tests unitarios
  test-feature           Ejecutar solo tests de features

Ejemplos:
  .\dev.ps1 artisan make:controller TestController
  .\dev.ps1 migrate-fresh
  .\dev.ps1 db-connect
  .\dev.ps1 shell
"@ -ForegroundColor Cyan
}

function Invoke-ArtisanCommand {
    param([string[]]$ArtisanArgs)
    $command = "php artisan " + ($ArtisanArgs -join " ")
    Write-Host "🚀 Ejecutando: $command" -ForegroundColor Green
    docker exec -it barberia-app $command.Split(' ')
}

function Invoke-DatabaseConnect {
    Write-Host "🐘 Conectando a PostgreSQL..." -ForegroundColor Green
    docker exec -it barberia-postgres psql -U barberia_user -d barberia_db
}

function Invoke-Shell {
    Write-Host "🐚 Accediendo al contenedor de la aplicación..." -ForegroundColor Green
    docker exec -it barberia-app bash
}

function Invoke-NginxShell {
    Write-Host "🌐 Accediendo al contenedor de Nginx..." -ForegroundColor Green
    docker exec -it barberia-nginx sh
}

function Show-Status {
    Write-Host "📊 Estado de los contenedores:" -ForegroundColor Green
    docker-compose -f doker-compose.yml ps
    Write-Host ""
    Write-Host "🔍 Uso de recursos:" -ForegroundColor Green
    docker stats --no-stream --format "table {{.Name}}\t{{.CPUPerc}}\t{{.MemUsage}}\t{{.NetIO}}"
}

function Invoke-Restart {
    Write-Host "🔄 Reiniciando servicios..." -ForegroundColor Yellow
    docker-compose -f doker-compose.yml restart
    Write-Host "✅ Servicios reiniciados" -ForegroundColor Green
}

function Invoke-Clean {
    Write-Host "🧹 Limpiando imágenes no utilizadas..." -ForegroundColor Yellow
    docker image prune -f
    docker volume prune -f
    Write-Host "✅ Limpieza completada" -ForegroundColor Green
}

function Show-Logs {
    Write-Host "📋 Mostrando logs de Laravel..." -ForegroundColor Green
    docker exec barberia-app tail -f /var/www/storage/logs/laravel.log
}

function Invoke-CacheClear {
    Write-Host "🧹 Limpiando todos los caches..." -ForegroundColor Yellow
    docker exec barberia-app php artisan cache:clear
    docker exec barberia-app php artisan config:clear
    docker exec barberia-app php artisan route:clear
    docker exec barberia-app php artisan view:clear
    Write-Host "✅ Caches limpiados" -ForegroundColor Green
}

function Invoke-DatabaseBackup {
    $timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
    $backupFile = "backup_barberia_${timestamp}.sql"
    
    Write-Host "💾 Creando backup de la base de datos..." -ForegroundColor Yellow
    docker exec barberia-postgres pg_dump -U barberia_user -d barberia_db > $backupFile
    Write-Host "✅ Backup guardado como: $backupFile" -ForegroundColor Green
}

function Invoke-DatabaseRestore {
    param([string]$BackupFile)
    
    if (!(Test-Path $BackupFile)) {
        Write-Host "❌ Archivo de backup no encontrado: $BackupFile" -ForegroundColor Red
        return
    }
    
    Write-Host "📥 Restaurando backup de la base de datos..." -ForegroundColor Yellow
    Get-Content $BackupFile | docker exec -i barberia-postgres psql -U barberia_user -d barberia_db
    Write-Host "✅ Backup restaurado exitosamente" -ForegroundColor Green
}

function Invoke-Tests {
    param([string]$TestType = "")
    
    $command = "php artisan test"
    switch ($TestType) {
        "unit" { $command += " --testsuite=Unit" }
        "feature" { $command += " --testsuite=Feature" }
    }
    
    Write-Host "🧪 Ejecutando tests..." -ForegroundColor Green
    docker exec barberia-app $command.Split(' ')
}

# Función principal
switch ($Command.ToLower()) {
    "artisan" { Invoke-ArtisanCommand $Args }
    "migrate" { Invoke-ArtisanCommand @("migrate") }
    "migrate-fresh" { 
        Invoke-ArtisanCommand @("migrate:fresh")
        Invoke-ArtisanCommand @("db:seed")
    }
    "seed" { Invoke-ArtisanCommand @("db:seed") }
    "tinker" { docker exec -it barberia-app php artisan tinker }
    "cache-clear" { Invoke-CacheClear }
    "logs" { Show-Logs }
    "db-connect" { Invoke-DatabaseConnect }
    "db-backup" { Invoke-DatabaseBackup }
    "db-restore" { 
        if ($Args.Count -gt 0) { 
            Invoke-DatabaseRestore $Args[0] 
        } else { 
            Write-Host "❌ Especifica el archivo de backup" -ForegroundColor Red 
        }
    }
    "shell" { Invoke-Shell }
    "nginx-shell" { Invoke-NginxShell }
    "restart" { Invoke-Restart }
    "status" { Show-Status }
    "clean" { Invoke-Clean }
    "test" { Invoke-Tests }
    "test-unit" { Invoke-Tests "unit" }
    "test-feature" { Invoke-Tests "feature" }
    default { Show-Help }
}
