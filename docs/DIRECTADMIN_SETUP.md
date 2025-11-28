# 🔧 Configuración DirectAdmin - Larafactu

## Entorno DirectAdmin Específico

Esta guía documenta la configuración específica para **DirectAdmin** con:
- PHP 8.4 multi-versión
- Redis por socket Unix (nivel usuario)
- Usuario sin permisos sudo

---

## 📋 1. Configurar PHP 8.4 en Shell

### Agregar a `.zshrc` (o `.bashrc`)

```bash
# Editar archivo de configuración
nano ~/.zshrc

# Añadir al final:
export PATH="/usr/local/php84/bin:$PATH"

# Guardar y recargar
source ~/.zshrc

# Verificar
php -v  # Debe mostrar PHP 8.4.x
```

---

## 🔴 2. Configurar Redis con Socket Unix

DirectAdmin configura Redis con socket Unix por usuario en lugar de TCP.

### `.env` Configuration

```env
# Session, Cache, Queue - Todo con Redis
SESSION_DRIVER=redis
BROADCAST_CONNECTION=redis
QUEUE_CONNECTION=redis
CACHE_STORE=redis

# Redis Configuration (CRÍTICO - Socket Unix)
REDIS_CLIENT=phpredis
REDIS_HOST=/home/develop/.redis/redis.sock
REDIS_PASSWORD=null
REDIS_PORT=0
```

### ⚠️ Puntos Críticos

1. **`REDIS_CLIENT=phpredis`** - Debe estar SIN comentar
2. **`REDIS_HOST=/home/USERNAME/.redis/redis.sock`** - Ruta al socket (cambia `USERNAME`)
3. **`REDIS_PORT=0`** - Debe ser `0` (NO `null`, NO vacío)

### ❌ Configuraciones que NO funcionan

```env
# ❌ MAL - Port null
REDIS_PORT=null

# ❌ MAL - Host como IP cuando usas socket
REDIS_HOST=127.0.0.1

# ❌ MAL - REDIS_SCHEME con socket
REDIS_SCHEME=unix
REDIS_PATH=/home/develop/.redis/redis.sock

# ❌ MAL - Cliente predis con socket
REDIS_CLIENT=predis
```

---

## 🎯 3. Verificar Configuración Redis

```bash
# Test conexión Redis
php artisan tinker --execute="
use Illuminate\Support\Facades\Redis;
Redis::set('test', 'works');
echo Redis::get('test');
"

# Debe imprimir: works
```

---

## 📅 4. Configurar Cron (Scheduler)

En DirectAdmin, edita el cron desde el panel o por SSH:

```bash
# Editar crontab
crontab -e

# Añadir (usa PHP 8.4 explícitamente):
* * * * * /usr/local/php84/bin/php /home/develop/domains/tudominio.com/public_html/artisan schedule:run >> /dev/null 2>&1
```

---

## 🔄 5. Configurar Queue Worker

### Opción A: Supervisor (si está disponible)

```ini
[program:larafactu-worker]
process_name=%(program_name)s_%(process_num)02d
command=/usr/local/php84/bin/php /home/develop/domains/tudominio.com/public_html/artisan queue:work redis --sleep=3 --tries=3
autostart=true
autorestart=true
user=develop
numprocs=2
redirect_stderr=true
stdout_logfile=/home/develop/domains/tudominio.com/public_html/storage/logs/worker.log
```

### Opción B: Cron alternativo (sin Supervisor)

```bash
# Ejecutar worker cada 5 minutos (no ideal pero funciona)
*/5 * * * * /usr/local/php84/bin/php /home/develop/domains/tudominio.com/public_html/artisan queue:work --stop-when-empty >> /home/develop/queue.log 2>&1
```

---

## 🗄️ 6. Base de Datos MySQL

### Crear Base de Datos desde DirectAdmin

1. Panel → MySQL Management
2. Create new Database
3. Anotar: nombre DB, usuario, password

### `.env` Configuration

```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=develop_larafactu
DB_USERNAME=develop_larafactu
DB_PASSWORD=tu_password_aqui
```

---

## 🚀 7. Instalación Completa en DirectAdmin

### Paso a Paso

```bash
# 1. Navegar al directorio público
cd ~/domains/tudominio.com/public_html

# 2. Clonar repositorio (o subir vía FTP)
git clone https://github.com/AichaDigital/larafactu.git .

# 3. Convertir repositories para producción
php scripts/post-deploy.php

# 4. Instalar dependencias (requiere token GitHub si llegaste al límite)
composer install --no-dev --optimize-autoloader

# 5. Configurar .env
cp .env.example .env
nano .env  # Configurar según esta guía

# 6. Generar key
php artisan key:generate

# 7. Instalar Larabill
php artisan larabill:install --no-interaction

# 8. Migrar base de datos
php artisan migrate --force

# 9. Crear usuario admin
php artisan tinker --execute="
\$admin = \App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@tudominio.com',
    'password' => bcrypt('password_seguro_aqui'),
    'email_verified_at' => now(),
]);
echo 'Usuario creado: ' . \$admin->email;
"

# 10. Optimizar
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 11. Permisos
chmod -R 775 storage bootstrap/cache
```

---

## 🌐 8. Configurar Virtual Host en DirectAdmin

DirectAdmin normalmente maneja esto automáticamente, pero asegúrate:

### Document Root

```
/home/develop/domains/tudominio.com/public_html/public
```

### .htaccess (ya incluido en Laravel)

Laravel incluye `.htaccess` en `public/`. Si falta:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

---

## ✅ 9. Verificación Post-Instalación

```bash
# Verificar PHP
php -v

# Verificar Composer
composer --version

# Verificar Redis
php artisan tinker --execute="Redis::ping();"

# Verificar DB
php artisan db:show

# Verificar cache
php artisan cache:clear
php artisan config:clear

# Ver logs
tail -f storage/logs/laravel.log
```

---

## 🐛 10. Troubleshooting Específico DirectAdmin

### Error: "Class 'Redis' not found"

```bash
# Verificar extensión phpredis
php -m | grep redis

# Si no está, instalar desde DirectAdmin:
# CustomBuild → PHP Extensions → phpredis
```

### Error: "Permission denied" en storage

```bash
chmod -R 775 storage bootstrap/cache
# En DirectAdmin, el usuario web es el mismo que tu usuario
```

### Error: Redis connection refused

```bash
# Verificar que Redis esté corriendo
ls -la ~/.redis/redis.sock

# Verificar permisos del socket
# Debe ser rwx para tu usuario
```

### Error: Queue no procesa

```bash
# Verificar cron
crontab -l

# Ver logs del worker
tail -f storage/logs/worker.log

# Ejecutar manualmente para debug
/usr/local/php84/bin/php artisan queue:work redis --verbose
```

---

## 📚 11. Comandos Útiles DirectAdmin

```bash
# Ver procesos PHP
ps aux | grep php

# Ver logs Apache
tail -f ~/domains/tudominio.com/logs/error.log

# Ver uso de memoria
free -h

# Limpiar todo Laravel
php artisan optimize:clear

# Reiniciar (si tienes acceso a supervisor)
supervisorctl restart larafactu-worker:*
```

---

## 🔒 12. Seguridad en DirectAdmin

```bash
# .env NO debe ser accesible desde web
# Ya está protegido por .htaccess de Laravel

# Verificar permisos
chmod 644 .env

# Activar modo mantenimiento durante updates
php artisan down

# ... hacer cambios ...

# Desactivar modo mantenimiento
php artisan up
```

---

## 📝 Notas Finales

- **PHP Path**: `/usr/local/php84/bin/php` (verificar con `which php`)
- **Redis Socket**: `~/.redis/redis.sock` (específico del usuario)
- **Document Root**: Debe apuntar a `/public`
- **Cron**: Usa ruta completa de PHP 8.4
- **Queue**: Usar cron si no hay Supervisor

---

**Última actualización**: 28 de noviembre de 2025  
**Entorno**: DirectAdmin + PHP 8.4 + Redis Socket Unix  
**Usuario**: develop (ejemplo - ajustar según tu usuario)

