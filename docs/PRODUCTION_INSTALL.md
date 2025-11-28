# 🚀 Guía de Instalación Pre-Producción - Larafactu

## ✅ Pre-requisitos

- PHP 8.4+
- MySQL 8.0+ / PostgreSQL 15+
- Composer 2.x
- Node.js 20+ (para assets)
- Nginx / Apache con HTTPS

---

## 📦 Paso 1: Clonar Repositorio

```bash
cd /var/www  # o tu directorio de proyectos
git clone https://github.com/AichaDigital/larafactu.git
cd larafactu
```

---

## 🔧 Paso 2: Convertir Repositories (Solo Producción)

**Si instalaste vía HTTP/HTTPS (sin git)**, ejecuta el script post-deploy:

```bash
php scripts/post-deploy.php
```

Este script convierte automáticamente los repositories locales (`path`) a VCS (GitHub) para producción.

---

## 📦 Paso 3: Instalar Dependencias

```bash
# PHP dependencies
composer install --no-dev --optimize-autoloader

# Frontend assets
npm install
npm run build
```

---

## ⚙️ Paso 4: Configurar Entorno

```bash
# Copiar .env
cp .env.example .env

# Generar app key
php artisan key:generate
```

### Editar `.env`

```env
APP_NAME=Larafactu
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tudominio.com

```env
APP_NAME=Larafactu
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tudominio.com

# Admin Panel Access Control (IMPORTANTE - Seguridad)
# Emails específicos permitidos (separados por comas)
ADMIN_EMAILS=admin@tuempresa.com,manager@tuempresa.com
# O dominios completos permitidos (con @)
ADMIN_DOMAINS=@tuempresa.com,@tudominio.com
# Nota: En local development, todos los usuarios tienen acceso

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=larafactu_prod
DB_USERNAME=larafactu_user
DB_PASSWORD=tu_password_seguro

# Larabill UUID (CRÍTICO)
LARABILL_USER_ID_TYPE=uuid

# Larabill Company
LARABILL_COMPANY_VAT=ESB12345678
LARABILL_COMPANY_IS_ROI=true

# Verifactu (España)
VERIFACTU_MODE=native
VERIFACTU_ENVIRONMENT=production  # o sandbox para pruebas
VERIFACTU_NIF=ESB12345678

# Lararoi (ROI/OSS)
LARAROI_OSS_ENABLED=true
LARAROI_OPERATOR_VAT=ESB12345678

# Cache/Queue (recomendado)
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

# Mail (configurar según proveedor)
MAIL_MAILER=smtp
MAIL_HOST=smtp.tu-proveedor.com
MAIL_PORT=587
MAIL_USERNAME=tu_email
MAIL_PASSWORD=tu_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@tudominio.com
MAIL_FROM_NAME="${APP_NAME}"
```

---

## 🗄️ Paso 5: Crear Base de Datos

```sql
CREATE DATABASE larafactu_prod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'larafactu_user'@'localhost' IDENTIFIED BY 'tu_password_seguro';
GRANT ALL PRIVILEGES ON larafactu_prod.* TO 'larafactu_user'@'localhost';
FLUSH PRIVILEGES;
```

---

## 🔄 Paso 6: Instalar Larabill (IMPORTANTE)

```bash
# Instalar migraciones de Larabill en orden correcto
php artisan larabill:install --no-interaction

# Esto publicará las migraciones y preguntará si ejecutarlas
# Responder 'yes' cuando pregunte
```

**⚠️ IMPORTANTE**: En producción, **NO uses** `loadMigrationsFrom()`. El comando `larabill:install` asegura el orden correcto de las migraciones para evitar errores de FK.

---

## 📊 Paso 7: Migrar Base de Datos

```bash
# Si no se migraron en el paso anterior
php artisan migrate --force
```

---

## 👤 Paso 8: Crear Usuario Admin

```bash
php artisan tinker

# En tinker:
$admin = \App\Models\User::create([
    'name' => 'Tu Nombre',
    'email' => 'admin@tuempresa.com',
    'password' => bcrypt('password_seguro_aqui'),
    'email_verified_at' => now(),
]);

exit
```

---

## 🔐 Paso 9: Configurar Permisos

```bash
# Owner correcto
sudo chown -R www-data:www-data /var/www/larafactu

# Permisos storage y cache
sudo chmod -R 775 storage bootstrap/cache
```

---

## 🌐 Paso 10: Configurar Nginx

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name tudominio.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    
    server_name tudominio.com;
    root /var/www/larafactu/public;
    
    index index.php;
    
    # SSL certificates (Let's Encrypt recomendado)
    ssl_certificate /etc/letsencrypt/live/tudominio.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/tudominio.com/privkey.pem;
    
    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    
    charset utf-8;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }
    
    error_page 404 /index.php;
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

**Activar configuración**:
```bash
sudo ln -s /etc/nginx/sites-available/larafactu /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

---

## 🔄 Paso 11: Configurar Supervisor (Queue)

```bash
sudo nano /etc/supervisor/conf.d/larafactu-worker.conf
```

```ini
[program:larafactu-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/larafactu/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/larafactu/storage/logs/worker.log
stopwaitsecs=3600
```

**Activar**:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start larafactu-worker:*
```

---

## 📅 Paso 12: Configurar Cron (Scheduler)

```bash
sudo crontab -e -u www-data
```

Añadir:
```cron
* * * * * cd /var/www/larafactu && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🔒 Paso 13: Optimizar para Producción

```bash
# Cache configuración
php artisan config:cache

# Cache rutas
php artisan route:cache

# Cache vistas
php artisan view:cache

# Optimizar autoloader
composer dump-autoload --optimize --classmap-authoritative
```

---

## ✅ Paso 14: Verificar Instalación

```bash
# Verificar conexión DB
php artisan db:show

# Verificar tablas
php artisan db:table users

# Verificar configuración Larabill
php artisan larabill:status

# Verificar Verifactu (sandbox)
php artisan verifactu:test-connection
```

---

## 🔐 Paso 15: SSL con Let's Encrypt (recomendado)

```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d tudominio.com
sudo certbot renew --dry-run  # Test auto-renewal
```

---

## 📊 Paso 16: Monitoring (Opcional pero Recomendado)

### Laravel Pulse (incluido)
Acceder a: `https://tudominio.com/pulse`

### Laravel Telescope (desarrollo)
Solo en staging, no en producción (ya está configurado para no cargar).

---

## 🎯 URLs Importantes

- **Frontend**: https://tudominio.com
- **Admin Panel**: https://tudominio.com/admin
- **Login**: Usar el usuario admin creado en Paso 7

---

## 🐛 Troubleshooting

### Error: "No application encryption key"
```bash
php artisan key:generate
```

### Error: "Storage not writable"
```bash
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache
```

### Error: "Class not found"
```bash
composer dump-autoload
php artisan config:clear
php artisan cache:clear
```

### Queue no procesa
```bash
sudo supervisorctl status larafactu-worker:*
sudo supervisorctl restart larafactu-worker:*
```

### Logs
```bash
tail -f storage/logs/laravel.log
```

---

## 🔄 Actualización Futura

```bash
cd /var/www/larafactu

# Modo mantenimiento
php artisan down

# Pull cambios
git pull origin main

# Actualizar dependencias
composer install --no-dev --optimize-autoloader
npm install && npm run build

# Migrar DB
php artisan migrate --force

# Limpiar caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Salir de mantenimiento
php artisan up
```

---

## 📚 Documentación Adicional

- [README.md](README.md) - Documentación general
- [STAGING_SETUP.md](STAGING_SETUP.md) - Setup completo staging

---

**Última actualización**: 28 de noviembre de 2025  
**Versión**: 1.0.0-pre-production  
**Soporte**: https://github.com/AichaDigital/larafactu/issues

