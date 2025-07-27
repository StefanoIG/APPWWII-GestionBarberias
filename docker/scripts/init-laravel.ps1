# Script de inicialización para Laravel con PostgreSQL en Windows
param()

Write-Host "Iniciando configuración de Laravel..." -ForegroundColor Green

# Función para esperar a que PostgreSQL esté disponible
function Wait-ForPostgres {
    Write-Host "Esperando a que PostgreSQL esté disponible..." -ForegroundColor Yellow
    
    $maxAttempts = 30
    $attempt = 1
    
    while ($attempt -le $maxAttempts) {
        try {
            $result = docker exec barberia-postgres-standalone pg_isready -h localhost -p 5432 -U barberia_user -d barberia_db 2>$null
            if ($LASTEXITCODE -eq 0) {
                Write-Host "PostgreSQL está disponible" -ForegroundColor Green
                return $true
            }
        }
        catch {
            # Continuar con el bucle
        }
        
        Write-Host "Intento $attempt/$maxAttempts - PostgreSQL no está disponible todavía..." -ForegroundColor Yellow
        Start-Sleep -Seconds 3
        $attempt++
    }
    
    Write-Host "Error: PostgreSQL no estuvo disponible después de $maxAttempts intentos" -ForegroundColor Red
    return $false
}

# Esperar a que PostgreSQL esté listo
if (!(Wait-ForPostgres)) {
    exit 1
}

Write-Host "Configurando variables de entorno para PostgreSQL..." -ForegroundColor Yellow

# Configurar las variables de entorno en el .env si no existen
if (!(Test-Path ".env")) {
    Write-Host "Copiando .env.example a .env..." -ForegroundColor Yellow
    Copy-Item ".env.example" ".env"
}

# Generar la clave de la aplicación si no existe
$envContent = Get-Content ".env" -Raw -ErrorAction SilentlyContinue
if ($envContent -notmatch "APP_KEY=base64:") {
    Write-Host "Generando clave de aplicación..." -ForegroundColor Yellow
    docker exec barberia-app php artisan key:generate --force
}

# Configurar caché como file temporalmente para evitar problemas con DB
Write-Host "Configurando caché temporal como file..." -ForegroundColor Yellow
docker exec barberia-app sed -i 's/CACHE_STORE=database/CACHE_STORE=file/g' .env 2>$null
docker exec barberia-app bash -c 'if ! grep -q "CACHE_STORE=" .env; then echo "CACHE_STORE=file" >> .env; fi'

# Limpiar caché de configuración
Write-Host "Limpiando caché de configuración..." -ForegroundColor Yellow
docker exec barberia-app php artisan config:clear

# Verificar y crear las tablas de sistema necesarias
Write-Host "Verificando tablas de sistema..." -ForegroundColor Yellow

# Crear migraciones de sistema si no existen
docker exec barberia-app php artisan session:table 2>$null
docker exec barberia-app php artisan queue:table 2>$null
docker exec barberia-app php artisan cache:table 2>$null

# Ejecutar todas las migraciones
Write-Host "Ejecutando todas las migraciones..." -ForegroundColor Yellow
docker exec barberia-app php artisan migrate --force

Write-Host "Verificando tablas creadas..." -ForegroundColor Yellow
# Verificar que las tablas importantes existen
docker exec barberia-postgres-standalone psql -h localhost -U barberia_user -d barberia_db -c "\dt"

# Restaurar configuración de caché a database
Write-Host "Configurando caché como database..." -ForegroundColor Yellow
docker exec barberia-app sed -i 's/CACHE_STORE=file/CACHE_STORE=database/g' .env

# Limpiar y optimizar
Write-Host "Optimizando aplicación..." -ForegroundColor Yellow
docker exec barberia-app php artisan config:cache
docker exec barberia-app php artisan route:cache
docker exec barberia-app php artisan view:cache

# Crear el enlace simbólico para storage
Write-Host "Creando enlace simbólico para storage..." -ForegroundColor Yellow
docker exec barberia-app php artisan storage:link

Write-Host "Laravel configurado correctamente!" -ForegroundColor Green
