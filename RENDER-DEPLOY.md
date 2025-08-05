# Guía de Despliegue en Render

## Pasos para desplegar en Render:

### 1. Crear Base de Datos PostgreSQL
- Ve a tu dashboard de Render
- Crea una nueva base de datos PostgreSQL 
- Copia la **Internal Database URL**

### 2. Crear Web Service
- Conecta tu repositorio de GitHub
- Selecciona **Docker** como runtime
- En la sección **Advanced**, agrega estas variables de entorno:

| Variable | Valor |
|----------|-------|
| `DATABASE_URL` | La Internal Database URL que copiaste |
| `DB_CONNECTION` | `pgsql` |
| `APP_KEY` | Ejecuta `php artisan key:generate --show` localmente y copia el resultado |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | Tu URL de Render (ej: `https://tu-app.onrender.com`) |
| `ASSET_URL` | Tu URL de Render (ej: `https://tu-app.onrender.com`) |

### 3. Configurar Dockerfile
- Asegúrate de usar `Dockerfile.render` como nombre del Dockerfile
- O renombra `Dockerfile.render` a `Dockerfile` para el deploy

### 4. Variables de entorno adicionales (opcionales)
```
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu-email@gmail.com
MAIL_PASSWORD=tu-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@tu-app.com
```

## Diferencias entre Local y Render:

- **Local (Docker)**: Usa HTTPS con certificados auto-firmados en puerto 8443
- **Render**: Usa HTTPS administrado por Render en puerto 80 (redirigido a 443)
- **Detección automática**: El código detecta `APP_ENV=production` para aplicar configuraciones de Render

## Archivos importantes:
- `Dockerfile.render`: Dockerfile optimizado para Render
- `nginx.conf`: Configuración de nginx para producción
- `deploy.sh`: Script que se ejecuta al iniciar en Render
- `.dockerignore`: Archivos que no se incluyen en la imagen Docker
- `.env.render`: Ejemplo de variables de entorno para Render

## Comandos útiles para generar APP_KEY:
```bash
php artisan key:generate --show
```

## Testing local del Dockerfile de Render:
```bash
docker build -f Dockerfile.render -t barberia-render .
docker run -p 80:80 barberia-render
```
