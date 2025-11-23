# 📦 INSTALACIÓN DE PAQUETES - Guía Completa

> **Fecha**: 2025-11-21  
> **Estado**: En desarrollo (v1.0)  
> **Branch**: `improvements/larafactu-join` (paquetes)

---

## 🎯 **Contexto**

Este documento explica cómo instalar los paquetes **Larabill**, **Lara-Verifactu** y **Laratickets** en una aplicación Laravel, tanto en modo **desarrollo local** (symlinks) como en modo **producción** (desde GitHub/Packagist).

---

## 📋 **Requisitos Previos**

- **Laravel**: >= 12.x
- **PHP**: >= 8.4
- **Base de datos**: MySQL 8.0+ / PostgreSQL 13+
- **Composer**: >= 2.0

---

## 🔧 **MODO 1: Desarrollo Local (Symlinks)**

### **Escenario**
Trabajas en los paquetes y necesitas que los cambios se reflejen automáticamente en la aplicación de testing (Larafactu).

### **Estructura de Directorios**

```
/Users/abkrim/
├── development/
│   └── packages/
│       └── aichadigital/
│           ├── larabill/          # Paquete source
│           ├── lara-verifactu/    # Paquete source
│           └── laratickets/       # Paquete source
└── SitesLR12/
    └── larafactu/                 # Aplicación Laravel (staging)
        ├── packages/
        │   └── aichadigital/      # Symlinks locales
        │       ├── larabill -> /Users/abkrim/development/packages/aichadigital/larabill
        │       ├── lara-verifactu -> /Users/abkrim/development/packages/aichadigital/lara-verifactu
        │       └── laratickets -> /Users/abkrim/development/packages/aichadigital/laratickets
        └── composer.json
```

### **Paso 1: Crear Symlinks**

```bash
cd /Users/abkrim/SitesLR12/larafactu
mkdir -p packages/aichadigital

# Crear symlinks a los paquetes source
ln -s /Users/abkrim/development/packages/aichadigital/larabill packages/aichadigital/larabill
ln -s /Users/abkrim/development/packages/aichadigital/lara-verifactu packages/aichadigital/lara-verifactu
ln -s /Users/abkrim/development/packages/aichadigital/laratickets packages/aichadigital/laratickets
```

### **Paso 2: Configurar `composer.json`**

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "./packages/aichadigital/larabill",
            "options": {
                "symlink": true
            }
        },
        {
            "type": "path",
            "url": "./packages/aichadigital/lara-verifactu",
            "options": {
                "symlink": true
            }
        },
        {
            "type": "path",
            "url": "./packages/aichadigital/laratickets",
            "options": {
                "symlink": true
            }
        }
    ],
    "require": {
        "aichadigital/larabill": "dev-main",
        "aichadigital/lara-verifactu": "dev-main",
        "aichadigital/laratickets": "dev-main"
    }
}
```

### **Paso 3: Instalar Paquetes**

```bash
# Instalar desde symlinks
composer update aichadigital/larabill aichadigital/lara-verifactu aichadigital/laratickets

# Verificar que son symlinks
ls -la vendor/aichadigital/
# Debe mostrar: larabill -> /path/to/packages/...
```

### **Paso 4: Ejecutar Instaladores**

```bash
# 1. Larabill (core billing)
php artisan larabill:install --no-migrate

# 2. Lara-Verifactu (España AEAT)
php artisan verifactu:install --no-migrate

# 3. Laratickets (support)
php artisan laratickets:install --no-migrate

# 4. Migrar todo de una vez
php artisan migrate
```

### **Ventajas**
✅ Cambios en paquetes se reflejan automáticamente  
✅ No requiere `composer update` tras cada cambio  
✅ Ideal para desarrollo activo  

### **Desventajas**
❌ Requiere estructura de directorios específica  
❌ No apto para producción  

---

## 🚀 **MODO 2: Producción (GitHub/VCS)**

### **Escenario**
Usuario final instala los paquetes desde repositorios públicos/privados.

### **Paso 1: Configurar `composer.json`**

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/aichadigital/larabill.git"
        },
        {
            "type": "vcs",
            "url": "https://github.com/aichadigital/lara-verifactu.git"
        },
        {
            "type": "vcs",
            "url": "https://github.com/aichadigital/laratickets.git"
        }
    ],
    "require": {
        "aichadigital/larabill": "^1.0",
        "aichadigital/lara-verifactu": "^1.0",
        "aichadigital/laratickets": "^1.0"
    }
}
```

### **Paso 2: Instalar con Composer**

```bash
composer require aichadigital/larabill
composer require aichadigital/lara-verifactu
composer require aichadigital/laratickets
```

### **Paso 3: Ejecutar Instaladores**

```bash
# Mismo proceso que en modo desarrollo
php artisan larabill:install
php artisan verifactu:install
php artisan laratickets:install
```

### **Ventajas**
✅ Instalación estándar Laravel  
✅ No requiere estructura especial  
✅ Funciona en cualquier entorno  

---

## 🎯 **Comandos de Instalación**

### **`php artisan larabill:install`**

**Opciones:**
- `--user-id-type=uuid_binary` - Tipo de ID para User (uuid_binary, uuid_string, int, ulid)
- `--force` - Sobrescribir migraciones existentes
- `--no-migrate` - No ejecutar migraciones automáticamente

**Qué hace:**
1. Detecta tipo de `user_id` en la tabla `users`
2. Publica migraciones **en orden correcto** (respeta FK)
3. Publica configuración `config/larabill.php`
4. Ejecuta migraciones (si no se pasa `--no-migrate`)

**Ejemplo:**
```bash
# Instalación automática completa
php artisan larabill:install

# Instalación manual (sin migrar)
php artisan larabill:install --no-migrate
php artisan migrate
```

---

### **`php artisan verifactu:install`**

**Opciones:**
- `--force` - Sobrescribir archivos existentes
- `--no-migrate` - No ejecutar migraciones automáticamente

**Qué hace:**
1. Publica migraciones de Verifactu
2. Publica configuración `config/verifactu.php`
3. Ejecuta migraciones (si no se pasa `--no-migrate`)

**Ejemplo:**
```bash
php artisan verifactu:install
```

---

### **`php artisan laratickets:install`**

**Opciones:**
- `--seed` - Seed niveles y departamentos por defecto
- `--force` - Forzar sobrescritura
- `--no-migrate` - No ejecutar migraciones automáticamente

**Qué hace:**
1. Publica migraciones de tickets
2. Publica configuración `config/laratickets.php`
3. Ejecuta migraciones (si no se pasa `--no-migrate`)
4. Seed datos iniciales (si se pasa `--seed`)

**Ejemplo:**
```bash
php artisan laratickets:install --seed
```

---

## ⚠️ **Problemas Conocidos y Soluciones**

### **1. Comando `install` no aparece en `php artisan list`**

**Causa**: Composer usa cache o VCS en lugar de symlinks.

**Solución**:
```bash
# Verificar que composer.json usa "type": "path"
# Reinstalar paquetes
rm -rf vendor/aichadigital
composer update aichadigital/larabill --no-scripts
composer dump-autoload
php artisan optimize:clear
```

---

### **2. Error "Class not found" al ejecutar comando**

**Causa**: Autoload de Composer no detecta archivos nuevos.

**Solución**:
```bash
cd /path/to/package
composer dump-autoload

cd /path/to/app
composer dump-autoload
php artisan optimize:clear
```

---

### **3. Error FK al migrar**

**Causa**: Migraciones se ejecutan en orden incorrecto.

**Solución**: Usar `--no-migrate` y ejecutar manualmente:
```bash
php artisan larabill:install --no-migrate
php artisan verifactu:install --no-migrate
php artisan laratickets:install --no-migrate

# Migrar todo junto (orden correcto garantizado)
php artisan migrate
```

---

## 🧪 **Verificación de Instalación**

### **1. Verificar comandos disponibles**

```bash
php artisan list | grep -E "(larabill|verifactu|laratickets)"
```

**Salida esperada:**
```
larabill
  larabill:detect-user-id
  larabill:install
verifactu
  verifactu:install
  verifactu:register-invoice
  verifactu:retry-failed
  verifactu:status
  verifactu:test-connection
  verifactu:verify-blockchain
laratickets
  laratickets:install
```

---

### **2. Verificar migraciones publicadas**

```bash
ls database/migrations/ | grep -E "(larabill|verifactu|ticket)"
```

---

### **3. Verificar configuraciones**

```bash
ls config/ | grep -E "(larabill|verifactu|laratickets)"
```

**Archivos esperados:**
- `config/larabill.php`
- `config/verifactu.php`
- `config/laratickets.php`

---

### **4. Verificar tablas creadas**

```bash
php artisan db:show --json | jq -r '.tables[].name' | sort
```

**Tablas esperadas (42 total):**
- **Core Laravel**: `users`, `cache`, `jobs`, `migrations`
- **Larabill**: `invoices`, `invoice_items`, `customers`, `articles`, `tax_rates`, etc.
- **Verifactu**: `verifactu_invoices`, `verifactu_registries`, `verifactu_invoice_breakdowns`
- **Laratickets**: `tickets`, `ticket_assignments`, `departments`, `escalation_requests`, etc.

---

## 📚 **Próximos Pasos (Post v1.0)**

### **Mejoras Pendientes**

1. **Publicar en Packagist**
   - Eliminar necesidad de VCS repositories
   - `composer require aichadigital/larabill` directo

2. **Skeleton de Spatie**
   - Reconstruir Larabill con skeleton oficial
   - Resolver issues de autoloading y descubrimiento de comandos

3. **Testing Multi-Escenario**
   - UUID binary vs UUID string vs Int vs ULID
   - MySQL vs PostgreSQL vs SQLite
   - Diferentes jurisdicciones fiscales

4. **Documentación Usuario Final**
   - Guías paso a paso
   - Vídeos tutoriales
   - API documentation

---

## 🐛 **Reporte de Problemas**

Si encuentras algún problema durante la instalación:

1. **Verifica versión de Laravel**: `php artisan --version`
2. **Verifica versión de PHP**: `php -v`
3. **Limpia caches**: `php artisan optimize:clear && composer dump-autoload`
4. **Revisa logs**: `tail -f storage/logs/laravel.log`
5. **Abre issue en GitHub** con:
   - Salida completa del error
   - Versión de Laravel/PHP
   - Comando ejecutado
   - Estructura de base de datos

---

**Última actualización**: 2025-11-21  
**Autor**: @abkrim (con ayuda de Claude AI)  
**Estado**: ✅ Funcional en modo desarrollo local

