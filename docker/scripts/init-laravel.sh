#!/bin/bash

# Script de inicialización para Laravel con PostgreSQL
set -e

echo "🚀 Iniciando configuración de Laravel..."

# Función para esperar a que PostgreSQL esté disponible
wait_for_postgres() {
    echo "⏳ Esperando a que PostgreSQL esté disponible..."
    
    max_attempts=30
    attempt=1
    
    while [ $attempt -le $max_attempts ]; do
        if pg_isready -h postgres -p 5432 -U barberia_user -d barberia_db >/dev/null 2>&1; then
            echo "✅ PostgreSQL está disponible"
            return 0
        fi
        
        echo "Intento $attempt/$max_attempts - PostgreSQL no está disponible todavía..."
        sleep 3
        attempt=$((attempt + 1))
    done
    
    echo "❌ Error: PostgreSQL no estuvo disponible después de $max_attempts intentos"
    exit 1
}

# Esperar a que PostgreSQL esté listo
wait_for_postgres

echo "🔧 Configurando variables de entorno para PostgreSQL..."

# Configurar las variables de entorno en el .env si no existen
if [ ! -f .env ]; then
    echo "📁 Copiando .env.example a .env..."
    cp .env.example .env
fi

# Generar la clave de la aplicación si no existe
if ! grep -q "APP_KEY=base64:" .env; then
    echo "🔑 Generando clave de aplicación..."
    php artisan key:generate --force
fi

# Configurar caché como file temporalmente para evitar problemas con DB
echo "🗂️ Configurando caché temporal como file..."
sed -i 's/CACHE_STORE=database/CACHE_STORE=file/g' .env || echo "CACHE_STORE=file" >> .env

# Limpiar caché de configuración
echo "🧹 Limpiando caché de configuración..."
php artisan config:clear

# Verificar y crear las tablas de sistema necesarias
echo "📋 Verificando tablas de sistema..."

# Crear migraciones de sistema si no existen
php artisan session:table 2>/dev/null || echo "Migración de sessions ya existe"
php artisan queue:table 2>/dev/null || echo "Migración de queue ya existe"  
php artisan cache:table 2>/dev/null || echo "Migración de cache ya existe"

# Ejecutar todas las migraciones
echo "🔄 Ejecutando todas las migraciones..."
php artisan migrate --force

echo "🔍 Verificando tablas creadas..."
# Verificar que las tablas importantes existen
PGPASSWORD=$DB_PASSWORD psql -h $DB_HOST -U $DB_USERNAME -d $DB_DATABASE -c "\dt" || echo "No se pudieron listar las tablas"

# Verificar específicamente la tabla sessions
PGPASSWORD=$DB_PASSWORD psql -h $DB_HOST -U $DB_USERNAME -d $DB_DATABASE -c "SELECT to_regclass('sessions');" || echo "Tabla sessions no encontrada"

# Restaurar configuración de caché a database
echo "💾 Configurando caché como database..."
sed -i 's/CACHE_STORE=file/CACHE_STORE=database/g' .env

# Limpiar y optimizar
echo "🧹 Optimizando aplicación..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Crear el enlace simbólico para storage
echo "🔗 Creando enlace simbólico para storage..."
php artisan storage:link || true

echo "✅ Laravel configurado correctamente!"

echo "🚀 Iniciando PHP-FPM..."
exec php-fpm
