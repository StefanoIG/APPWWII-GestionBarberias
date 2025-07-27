# 🏪 Sistema de Gestión de Barberías - Windows Docker Setup

## 📋 Requisitos Previos

### Software Necesario
- **Windows 10/11** (versión 1903 o superior)
- **Docker Desktop para Windows** - [Descargar aquí](https://www.docker.com/products/docker-desktop/)
- **PowerShell 5.1** o superior (ya incluido en Windows)
- **Git para Windows** - [Descargar aquí](https://git-scm.com/download/win)

### Configuración de Docker Desktop
1. Instalar Docker Desktop
2. Asegurar que esté habilitado el backend de WSL2
3. Verificar que Docker esté ejecutándose

## 🚀 Instalación y Configuración

### 1. Clonar el Repositorio
```powershell
cd C:\web2
git clone <tu-repositorio> APPWWII-GestionBarberias
cd APPWWII-GestionBarberias
```

### 2. Configuración de Permisos PowerShell
```powershell
# Ejecutar como Administrador (una sola vez)
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
```

### 3. Iniciar la Aplicación
```powershell
# Inicio normal
.\start.ps1

# Primer inicio o reconstrucción completa
.\start.ps1 -Fresh

# Solo construir imágenes
.\start.ps1 -Build
```

## 🛠️ Comandos Disponibles

### Scripts Principales

#### `start.ps1` - Gestión de la Aplicación
```powershell
.\start.ps1           # Iniciar aplicación
.\start.ps1 -Build    # Construir y iniciar
.\start.ps1 -Fresh    # Reinicio completo
.\start.ps1 -Down     # Detener aplicación
.\start.ps1 -Logs     # Ver logs
.\start.ps1 -Help     # Ayuda
```

#### `dev.ps1` - Comandos de Desarrollo
```powershell
# Laravel Artisan
.\dev.ps1 artisan migrate
.\dev.ps1 artisan make:controller TestController
.\dev.ps1 migrate-fresh
.\dev.ps1 seed

# Base de Datos
.\dev.ps1 db-connect
.\dev.ps1 db-backup
.\dev.ps1 db-restore backup_file.sql

# Docker y Shell
.\dev.ps1 shell           # Acceder al contenedor de la app
.\dev.ps1 nginx-shell     # Acceder al contenedor de nginx
.\dev.ps1 status          # Ver estado de contenedores
.\dev.ps1 restart         # Reiniciar servicios

# Testing
.\dev.ps1 test            # Ejecutar todos los tests
.\dev.ps1 test-unit       # Solo tests unitarios
.\dev.ps1 test-feature    # Solo tests de features

# Mantenimiento
.\dev.ps1 cache-clear     # Limpiar caches
.\dev.ps1 logs            # Ver logs de Laravel
.\dev.ps1 clean           # Limpiar imágenes Docker
```

## 🌐 Acceso a la Aplicación

Una vez iniciada la aplicación, estará disponible en:
- **Aplicación Web**: http://localhost:8000
- **Base de Datos PostgreSQL**: localhost:5432
  - Base de datos: `barberia_db`
  - Usuario: `barberia_user`
  - Contraseña: `barberia_password`

## 📁 Estructura del Proyecto

```
C:\web2\APPWWII-GestionBarberias\
├── docker/
│   ├── scripts/
│   │   ├── init-laravel.sh     # Script Linux (mantener)
│   │   └── init-laravel.ps1    # Script Windows
│   ├── php/
│   │   └── local.ini           # Configuración PHP
│   └── ngninx/
│       └── app.conf            # Configuración Nginx
├── start.ps1                   # Script principal
├── dev.ps1                     # Comandos de desarrollo
├── doker-compose.yml           # Configuración Docker
└── Dockerfile                  # Imagen de la aplicación
```

## 🐛 Solución de Problemas Comunes

### Docker no está ejecutándose
```powershell
# Verificar estado de Docker
docker --version
docker ps

# Si no funciona, iniciar Docker Desktop manualmente
```

### Error de permisos en PowerShell
```powershell
# Ejecutar como Administrador
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
```

### Contenedores no se inician correctamente
```powershell
# Reinicio completo
.\start.ps1 -Down
.\start.ps1 -Fresh
```

### Problemas de base de datos
```powershell
# Verificar conexión
.\dev.ps1 db-connect

# Recrear migraciones
.\dev.ps1 migrate-fresh
```

### Limpiar Docker completamente
```powershell
# Detener todo
.\start.ps1 -Down

# Limpiar imágenes y volúmenes
.\dev.ps1 clean

# O limpieza completa de Docker
docker system prune -a --volumes
```

## 📊 Monitoreo y Logs

### Ver logs en tiempo real
```powershell
# Logs de todos los servicios
.\start.ps1 -Logs

# Logs solo de la aplicación
docker-compose -f doker-compose.yml logs -f app

# Logs de Laravel específicamente
.\dev.ps1 logs
```

### Estado de contenedores
```powershell
.\dev.ps1 status
```

## 🔧 Desarrollo y Debugging

### Acceder al contenedor
```powershell
# Shell de la aplicación
.\dev.ps1 shell

# Una vez dentro del contenedor
php artisan tinker
tail -f storage/logs/laravel.log
```

### Comandos útiles de Laravel
```powershell
.\dev.ps1 artisan route:list
.\dev.ps1 artisan config:cache
.\dev.ps1 cache-clear
```

## 💾 Backup y Restauración

### Crear backup
```powershell
.\dev.ps1 db-backup
```

### Restaurar backup
```powershell
.\dev.ps1 db-restore backup_barberia_20250722_143000.sql
```

## 📝 Notas Importantes

1. **Rutas**: El proyecto debe estar en `C:\web2\APPWWII-GestionBarberias`
2. **Docker Desktop**: Debe estar ejecutándose antes de usar los scripts
3. **PowerShell**: Usar Windows PowerShell o PowerShell 7
4. **Firewall**: Docker Desktop puede requerir permisos de firewall
5. **WSL2**: Recomendado tener WSL2 habilitado para mejor rendimiento

## 🆘 Obtener Ayuda

```powershell
# Ayuda de scripts
.\start.ps1 -Help
.\dev.ps1

# Ver logs para debugging
.\start.ps1 -Logs
```
