# ⚡ Guía Rápida - Desarrollo Diario

> **Comandos esenciales para el desarrollo día a día con Larafactu + Paquetes**

---

## 🚀 **Comandos Más Usados**

### **Instalación Completa desde 0**

```bash
# Resetear base de datos
php artisan db:wipe --force
mysql larafactu < database/dumps/00_laravel_core_base.sql

# Reinstalar paquetes
composer update aichadigital/larabill aichadigital/lara-verifactu aichadigital/laratickets

# Instalar todo
php artisan larabill:install
php artisan verifactu:install
php artisan laratickets:install

# Verificar (debe ser 42 tablas)
php artisan db:show --json | jq -r '.tables[].name' | wc -l
```

---

### **Cambios en Paquetes (Desarrollo Local)**

```bash
# Después de cambiar código en /Users/abkrim/development/packages/aichadigital/larabill/

cd /Users/abkrim/SitesLR12/larafactu

# Limpiar caches
composer dump-autoload
php artisan optimize:clear

# Probar cambios
php artisan test --filter=NombreTest
```

---

### **Limpiar Todo**

```bash
# Limpiar todos los caches
php artisan optimize:clear
composer dump-autoload
rm -rf bootstrap/cache/*.php

# Limpiar base de datos
php artisan db:wipe --force

# Limpiar migraciones publicadas (si necesitas reinstalar)
rm database/migrations/2024*.php
rm database/migrations/2025*.php
```

---

## 🧪 **Testing**

### **Tests Rápidos**

```bash
# Todos los tests
php artisan test

# Test específico
php artisan test --filter=InvoiceTest

# Con coverage
composer test-coverage

# Parallel (más rápido)
composer test-parallel
```

---

### **Tests en Paquetes**

```bash
# Larabill
cd /Users/abkrim/development/packages/aichadigital/larabill
composer test

# Lara-Verifactu
cd /Users/abkrim/development/packages/aichadigital/lara-verifactu
composer test

# Laratickets
cd /Users/abkrim/development/packages/aichadigital/laratickets
composer test
```

---

## 🔍 **Debugging**

### **Ver Logs en Tiempo Real**

```bash
# Laravel logs
tail -f storage/logs/laravel.log

# O con Laravel Pail (mejor)
php artisan pail
```

---

### **Ver Queries SQL**

```bash
# Tinker
php artisan tinker

# En tinker:
DB::enableQueryLog();
# ... ejecutar código ...
dd(DB::getQueryLog());
```

---

### **Inspeccionar Base de Datos**

```bash
# Ver todas las tablas
php artisan db:show --json | jq -r '.tables[].name' | sort

# Ver schema de una tabla
php artisan db:table invoices

# Ver número de registros
mysql larafactu -e "SELECT COUNT(*) FROM invoices"
```

---

## 📝 **Git Workflow**

### **Commits en Paquetes**

```bash
# Larabill
cd /Users/abkrim/development/packages/aichadigital/larabill
git add -A
git commit -m "fix(migrations): descripción del cambio"
git push origin improvements/larafactu-join

# Lara-Verifactu
cd /Users/abkrim/development/packages/aichadigital/lara-verifactu
git add -A
git commit -m "feat(install): descripción del cambio"
git push origin improvements/larafactu-join
```

---

### **Commits en Larafactu**

```bash
cd /Users/abkrim/SitesLR12/larafactu
git add -A
git commit -m "docs: actualizar documentación de instalación"
git push origin testing/mode-full-hoster
```

---

## 🔧 **Troubleshooting Común**

### **Comando `install` no aparece**

```bash
# Verificar symlinks
ls -la vendor/aichadigital/larabill/src/Console/

# Reinstalar paquete
rm -rf vendor/aichadigital/larabill
composer update aichadigital/larabill

# Limpiar caches
composer dump-autoload
php artisan optimize:clear

# Verificar
php artisan list | grep larabill
```

---

### **Error de FK al migrar**

```bash
# Usar --no-migrate y migrar manualmente
php artisan larabill:install --no-migrate
php artisan verifactu:install --no-migrate
php artisan laratickets:install --no-migrate

php artisan migrate
```

---

### **Cambios no se reflejan**

```bash
# Verificar que estás usando path repositories
cat composer.json | jq '.repositories[] | select(.type == "path")'

# Debe mostrar los 3 paquetes con "type": "path"

# Si no, cambiar y reinstalar
composer update aichadigital/larabill --prefer-source
```

---

### **Composer muy lento**

```bash
# Usar cache
composer install --prefer-dist

# O actualizar solo un paquete
composer update aichadigital/larabill --no-scripts --quiet
```

---

## 📊 **Estado del Proyecto**

### **Ver Versiones Instaladas**

```bash
composer show aichadigital/larabill
composer show aichadigital/lara-verifactu
composer show aichadigital/laratickets
```

---

### **Ver Branch Activo en Paquetes**

```bash
cd /Users/abkrim/development/packages/aichadigital/larabill && git branch --show-current
cd /Users/abkrim/development/packages/aichadigital/lara-verifactu && git branch --show-current
cd /Users/abkrim/development/packages/aichadigital/laratickets && git branch --show-current
```

---

### **Ver Commits Pendientes**

```bash
cd /Users/abkrim/development/packages/aichadigital/larabill
git status
git log origin/main..HEAD --oneline
```

---

## 🎯 **Atajos Útiles**

### **Alias de Bash (Opcional)**

Agregar a `~/.bashrc` o `~/.zshrc`:

```bash
# Larafactu
alias lf='cd /Users/abkrim/SitesLR12/larafactu'
alias lftest='cd /Users/abkrim/SitesLR12/larafactu && php artisan test'
alias lfreset='cd /Users/abkrim/SitesLR12/larafactu && php artisan db:wipe --force && mysql larafactu < database/dumps/00_laravel_core_base.sql'

# Paquetes
alias pkg='cd /Users/abkrim/development/packages/aichadigital'
alias pkgbill='cd /Users/abkrim/development/packages/aichadigital/larabill'
alias pkgveri='cd /Users/abkrim/development/packages/aichadigital/lara-verifactu'
alias pkgtick='cd /Users/abkrim/development/packages/aichadigital/laratickets'

# Composer
alias cu='composer update --no-scripts --quiet'
alias cda='composer dump-autoload'
```

---

## 📚 **Documentación Relacionada**

- [INSTALACION_PAQUETES.md](./INSTALACION_PAQUETES.md) - Guía completa de instalación
- [INTEGRACION_PAQUETES.md](./INTEGRACION_PAQUETES.md) - Problemas y soluciones
- [CHANGELOG_PACKAGES.md](./CHANGELOG_PACKAGES.md) - Historial de cambios
- [README.md](./README.md) - Índice general

---

**Última actualización**: 2025-11-21  
**Tip**: Guarda este archivo en favoritos para consulta rápida

