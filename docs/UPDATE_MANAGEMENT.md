# 🔄 Gestión de Actualizaciones en Producción

## Problema: Archivos Modificados en Producción

Cuando despliegas en producción, ciertos archivos se modifican:
- `composer.json` (por `post-deploy.php`)
- Configs publicados
- Assets compilados

Esto causa **conflictos en `git pull`** en actualizaciones futuras.

---

## ✅ Solución 1: Script de Deploy Automatizado (RECOMENDADO)

Usa el script `scripts/deploy.sh` que maneja todo automáticamente:

```bash
# En producción
chmod +x scripts/deploy.sh
./scripts/deploy.sh
```

### Lo que hace el script:

1. ✅ **Backup automático** (.env, composer.json, database)
2. ✅ **Modo mantenimiento** (evita errores durante update)
3. ✅ **Stash de cambios locales** (guarda modificaciones)
4. ✅ **Git reset hard** (actualiza a última versión)
5. ✅ **Post-deploy** (convierte repositories)
6. ✅ **Composer install** (dependencias actualizadas)
7. ✅ **Migrations** (opcional, con confirmación)
8. ✅ **Cache rebuild** (optimiza rendimiento)
9. ✅ **Salir de mantenimiento**

### Ventajas:
- Sin conflictos git
- Backups automáticos
- Rollback fácil si algo falla
- Cero downtime (modo mantenimiento)

---

## 🔧 Solución 2: Flujo Manual

Si prefieres control manual:

### Actualización Normal

```bash
# 1. Modo mantenimiento
php artisan down

# 2. Backup
cp .env .env.backup.$(date +%Y%m%d)
cp composer.json composer.json.backup

# 3. Stash cambios locales
git stash

# 4. Pull última versión
git pull origin main

# 5. Si hay conflictos, resetear
git reset --hard origin/main

# 6. Restaurar .env si se borró
cp .env.backup.$(date +%Y%m%d) .env

# 7. Post-deploy
php scripts/post-deploy.php

# 8. Actualizar dependencias
composer install --no-dev --optimize-autoloader

# 9. Migrations
php artisan migrate --force

# 10. Cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 11. Salir de mantenimiento
php artisan up
```

---

## 📋 Archivos que se Modifican en Producción

### ✅ Ignorados por Git (seguros)

Estos **NO causan conflictos**:

```
.env                    # Ignorado
composer.lock           # Ignorado
package-lock.json       # Ignorado
node_modules/           # Ignorado
vendor/                 # Ignorado
storage/                # Ignorado
public/build/           # Ignorado
```

### ⚠️ Modificados por Deploy (causan conflictos)

Estos **SÍ causan conflictos** si no se manejan:

```
composer.json           # Modificado por post-deploy.php
                        # (convierte path → vcs repositories)
```

### 🔒 Solución Permanente

**Opción A**: Mantener `composer.json` con VCS en repo

Modificar el `composer.json` en el repositorio para que ya tenga VCS:

```json
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/AichaDigital/larabill"
    },
    ...
]
```

**Desventaja**: Local dev necesitaría symlinks manualmente.

**Opción B**: Usar script de deploy (actual)

Mantener `composer.json` con `path` en repo y que `post-deploy.php` lo convierta cada vez.

**Ventaja**: Funciona tanto en local como en producción.

---

## 🚨 Rollback en Caso de Problemas

Si algo sale mal después de actualizar:

```bash
# 1. Modo mantenimiento
php artisan down

# 2. Ver commits recientes
git log --oneline -5

# 3. Volver a commit anterior
git reset --hard <commit-hash>

# 4. Restaurar .env del backup
cp ../larafactu-backups/.env.TIMESTAMP .env

# 5. Reinstalar dependencias
composer install --no-dev

# 6. Limpiar cache
php artisan config:clear
php artisan cache:clear

# 7. Salir de mantenimiento
php artisan up
```

### Restaurar Base de Datos

```bash
# Si hiciste backup antes del update
mysql -u usuario -p database_name < ../larafactu-backups/db_TIMESTAMP.sql
```

---

## 📊 Estrategias por Entorno

### Local Development (con symlinks)

```bash
# Pull sin problemas (path repositories funcionan)
git pull origin main
composer update
php artisan migrate
```

### Staging/Pre-producción

```bash
# Usar script deploy
./scripts/deploy.sh
```

### Producción

```bash
# Usar script deploy + backup DB
./scripts/deploy.sh

# O manual con más cuidado
# (seguir pasos de Solución 2)
```

---

## ⚙️ Configuración Git para Producción

### Ignorar cambios locales en composer.json

Si NO quieres usar el script y prefieres mantener cambios locales:

```bash
# En producción, después de post-deploy
git update-index --assume-unchanged composer.json
```

**Desventaja**: No verás cambios en `composer.json` del repo.

### Revertir si necesitas actualizar composer.json:

```bash
git update-index --no-assume-unchanged composer.json
```

---

## 🎯 Best Practices

1. **SIEMPRE usa el script `deploy.sh`** en producción
2. **Backup antes de actualizar** (automático en script)
3. **Modo mantenimiento** durante updates
4. **Test en staging primero** antes de producción
5. **Ten plan de rollback** listo
6. **Monitorea logs** después del update: `tail -f storage/logs/laravel.log`

---

## 🔍 Verificar Estado Git en Producción

```bash
# Ver archivos modificados
git status

# Ver diferencias
git diff composer.json

# Ver último commit
git log -1

# Ver stash (cambios guardados)
git stash list

# Ver contenido del stash
git stash show -p
```

---

## 📞 Soporte

Si tienes problemas con actualizaciones:

1. **Revisa logs**: `storage/logs/laravel.log`
2. **Verifica git status**: `git status`
3. **Usa el script**: `./scripts/deploy.sh`
4. **Rollback si es crítico**: Ver sección Rollback arriba

---

**Última actualización**: 28 de noviembre de 2025  
**Script de deploy**: `scripts/deploy.sh`  
**Backups**: `../larafactu-backups/`

