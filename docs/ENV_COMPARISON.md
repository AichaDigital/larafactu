# 🔧 Comparación de `.env`: Local vs Producción

Este documento muestra las **diferencias críticas** entre configuraciones de desarrollo local y producción.

## ⚠️ NUNCA copies tu `.env` local a producción

Cada entorno tiene necesidades diferentes de seguridad, logging, cache y acceso.

---

## 📊 Tabla Comparativa

| Variable | 🏠 Local (Desarrollo) | 🚀 Producción | ¿Por qué? |
|----------|----------------------|---------------|-----------|
| **APP_ENV** | `local` | `production` | Cambia comportamiento de cache, logs, admin access |
| **APP_DEBUG** | `true` | `false` | ⚠️ `true` expone código fuente, queries SQL, stack traces |
| **APP_URL** | `https://larafactu.test` | `https://tudominio.com` | URLs en emails, assets, redirects |
| **LOG_LEVEL** | `debug` | `error` | `debug` genera archivos de log ENORMES |
| **LOG_STACK** | `single` | `daily` | `daily` rota logs automáticamente |
| **MAIL_MAILER** | `log` | `smtp` | `log` no envía emails reales (solo logging) |
| **CACHE_STORE** | `file` | `redis` | Redis es más rápido y escalable |
| **SESSION_DRIVER** | `file` | `redis` | Redis permite múltiples servidores |
| **QUEUE_CONNECTION** | `sync` | `redis` | `sync` ejecuta jobs en el momento (no asíncrono) |
| **ADMIN_EMAILS** | *(opcional)* | **OBLIGATORIO** | Sin esto: 403 Forbidden para todos |
| **ADMIN_DOMAINS** | *(opcional)* | **OBLIGATORIO** | Sin esto: 403 Forbidden para todos |

---

## 🏠 Ejemplo: `.env` Local

```env
# === Local Development ===
APP_NAME=Larafactu
APP_ENV=local                     # ✅ Permite acceso a todos los usuarios
APP_DEBUG=true                    # ✅ Stack traces visibles (OK en local)
APP_URL=https://larafactu.test/

APP_LOCALE=en
APP_FALLBACK_LOCALE=en

LOG_CHANNEL=stack
LOG_STACK=single                  # ✅ Un solo archivo de log (fácil debug)
LOG_LEVEL=debug                   # ✅ Todo logeado (útil en desarrollo)

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=larafactu
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=file               # ✅ Archivos locales (simple)
CACHE_STORE=file                  # ✅ Archivos locales (simple)
QUEUE_CONNECTION=sync             # ✅ Jobs síncronos (debug fácil)

MAIL_MAILER=log                   # ✅ No envía emails (logging solo)
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

# Admin access: NO requerido en local
# ADMIN_EMAILS=                   # ✅ Todos tienen acceso
# ADMIN_DOMAINS=                  # ✅ Todos tienen acceso

LARABILL_USER_ID_TYPE=uuid
```

---

## 🚀 Ejemplo: `.env` Producción

```env
# === Production ===
APP_NAME=Larafactu
APP_ENV=production                # ⚠️ CRÍTICO: Valida admin access
APP_DEBUG=false                   # ⚠️ CRÍTICO: Oculta código fuente
APP_TIMEZONE=UTC
APP_URL=https://larafactu.com

APP_LOCALE=es
APP_FALLBACK_LOCALE=en

LOG_CHANNEL=stack
LOG_STACK=daily                   # ⚠️ Rotación automática (ahorra espacio)
LOG_LEVEL=error                   # ⚠️ Solo errores críticos (ahorra espacio)

DB_CONNECTION=mysql
DB_HOST=localhost
DB_SOCKET=/var/lib/mysql/mysql.sock
DB_PORT=3306
DB_DATABASE=larafactu_prod
DB_USERNAME=larafactu_user
DB_PASSWORD=SECURE_PASSWORD_HERE

SESSION_DRIVER=redis              # ⚠️ Redis (escalabilidad)
CACHE_STORE=redis                 # ⚠️ Redis (performance)
QUEUE_CONNECTION=redis            # ⚠️ Redis (jobs asíncronos)

# Redis (DirectAdmin: socket Unix)
REDIS_CLIENT=phpredis
REDIS_HOST=/home/usuario/.redis/redis.sock
REDIS_PORT=0

MAIL_MAILER=smtp                  # ⚠️ SMTP real (envía emails)
MAIL_HOST=smtp.tuservidor.com
MAIL_PORT=587
MAIL_USERNAME=facturacion@tudominio.com
MAIL_PASSWORD=EMAIL_PASSWORD_HERE
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="facturacion@larafactu.com"
MAIL_FROM_NAME="${APP_NAME}"

# Admin access: OBLIGATORIO en producción
ADMIN_EMAILS=admin@tuempresa.com,ceo@tuempresa.com
ADMIN_DOMAINS=@tuempresa.com,@partner.com

LARABILL_USER_ID_TYPE=uuid
LARABILL_COMPANY_VAT=ESB12345678
LARABILL_COMPANY_IS_ROI=true

VERIFACTU_MODE=native
VERIFACTU_ENVIRONMENT=production
VERIFACTU_NIF=ESB12345678
```

---

## 🔐 Admin Access: Local vs Producción

### 🏠 En Local (`APP_ENV=local`)

```php
// app/Models/User.php
public function canAccessPanel(Panel $panel): bool
{
    // En local, TODOS tienen acceso
    if (App::environment('local')) {
        return true; // ✅ Sin validación
    }
    
    // ...
}
```

**Resultado:**
- ✅ `admin@example.com` → Acceso permitido
- ✅ `test@example.com` → Acceso permitido
- ✅ `hacker@malicious.com` → Acceso permitido (⚠️ OK en local)

---

### 🚀 En Producción (`APP_ENV=production`)

```php
// app/Models/User.php
public function canAccessPanel(Panel $panel): bool
{
    // Producción: valida contra ADMIN_EMAILS y ADMIN_DOMAINS
    return $this->isAllowedAdminUser();
}
```

**Con `.env`:**
```env
ADMIN_EMAILS=admin@example.com,ceo@example.com
ADMIN_DOMAINS=@example.com,@partner.com
```

**Resultado:**
- ✅ `admin@example.com` → Acceso (email exacto)
- ✅ `ceo@example.com` → Acceso (email exacto)
- ✅ `anyone@example.com` → Acceso (dominio)
- ✅ `john@partner.com` → Acceso (dominio)
- ❌ `hacker@malicious.com` → 403 Forbidden

---

## 🚨 Errores Comunes

### ❌ Error 1: Copiar `.env` local a producción

```bash
# NUNCA HAGAS ESTO:
scp .env user@servidor:/var/www/app/.env
```

**Resultado:**
- APP_DEBUG=true → Expone código fuente 💥
- MAIL_MAILER=log → Los usuarios no reciben emails 💥
- SESSION_DRIVER=file → No escala en múltiples servidores 💥

---

### ❌ Error 2: Olvidar `ADMIN_EMAILS` o `ADMIN_DOMAINS`

```env
# .env en producción (MAL)
APP_ENV=production
APP_DEBUG=false
# ADMIN_EMAILS=         # ⚠️ Vacío o sin configurar
# ADMIN_DOMAINS=        # ⚠️ Vacío o sin configurar
```

**Resultado:**
- ❌ Todos los usuarios: **403 Forbidden**
- ❌ Ni siquiera el usuario que creaste puede entrar
- ❌ Tienes que editar el `.env` desde SSH para arreglarlo

**Solución:**
```env
ADMIN_EMAILS=tu@correo.com
# O
ADMIN_DOMAINS=@tudominio.com
```

---

### ❌ Error 3: `APP_DEBUG=true` en producción

```env
# .env en producción (PELIGROSO)
APP_ENV=production
APP_DEBUG=true          # ⚠️ NUNCA HACER ESTO
```

**Resultado:**
- 💥 Stack traces visibles para usuarios
- 💥 Rutas de archivos del servidor expuestas
- 💥 Queries SQL visibles (posible SQLi)
- 💥 Variables de entorno expuestas (credenciales)

**Ejemplo de lo que se expone:**
```
PDOException: SQLSTATE[42S02]: Base table or view not found
/var/www/larafactu.com/vendor/laravel/framework/src/Illuminate/Database/Connection.php:824
DB_PASSWORD=SUPER_SECRET_PASSWORD_123
AWS_ACCESS_KEY_ID=AKIAIOSFODNN7EXAMPLE
```

---

## ✅ Checklist: Antes de Deployment

- [ ] `APP_ENV=production` (no `local`)
- [ ] `APP_DEBUG=false` (no `true`)
- [ ] `APP_URL` correcto (tu dominio real)
- [ ] `LOG_LEVEL=error` (no `debug`)
- [ ] `MAIL_MAILER=smtp` (no `log`)
- [ ] `ADMIN_EMAILS` o `ADMIN_DOMAINS` configurado
- [ ] `DB_PASSWORD` seguro (no vacío)
- [ ] `REDIS_*` configurado (si usas Redis)
- [ ] Variables de Larabill (`LARABILL_USER_ID_TYPE`, etc.)

---

## 📝 Comandos Útiles

### Verificar Configuración Actual

```bash
# Ver APP_ENV
php artisan config:show app.env

# Ver APP_DEBUG
php artisan config:show app.debug

# Ver ADMIN_EMAILS
php artisan config:show app.admin_emails

# Ver ADMIN_DOMAINS
php artisan config:show app.admin_domains

# Ver toda la config de app
php artisan config:show app
```

### Probar Admin Access en Tinker

```bash
php artisan tinker

# Crear usuario de prueba
$user = \App\Models\User::factory()->create([
    'email' => 'test@example.com'
]);

# Probar acceso
$panel = app(\Filament\Panel::class);
$user->canAccessPanel($panel); // true o false

exit
```

---

## 🔗 Referencias

- [PRODUCTION_INSTALL.md](./PRODUCTION_INSTALL.md) - Guía completa de instalación
- [ADMIN_ACCESS_CONTROL.md](./ADMIN_ACCESS_CONTROL.md) - Control de acceso al panel
- [README.md](../README.md) - Documentación principal
- [Laravel Deployment](https://laravel.com/docs/11.x/deployment) - Docs oficiales

---

**🎯 Regla de Oro:** Si tienes dudas, revisa esta tabla. Nunca copies `.env` entre entornos sin revisar.

