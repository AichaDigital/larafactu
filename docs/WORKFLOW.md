# 🔄 Workflow de Desarrollo Multi-Paquete

> Guía de flujo de trabajo para desarrollo con paquetes locales en Larafactu

## 📋 Índice

1. [Arquitectura del Proyecto](#arquitectura-del-proyecto)
2. [Flujo de Desarrollo](#flujo-de-desarrollo)
3. [Problemas Conocidos](#problemas-conocidos)
4. [Timeline y Roadmap](#timeline-y-roadmap)
5. [Gestión de Documentación](#gestión-de-documentación)

---

## 🏗️ Arquitectura del Proyecto

### Estructura de Directorios

```
/Users/abkrim/
├── development/packages/aichadigital/     # 📦 Paquetes SOURCE
│   ├── larabill/                          # Core billing
│   ├── lararoi/                           # EU VAT/ROI
│   ├── lara-verifactu/                    # Spain AEAT
│   └── laratickets/                       # Support tickets
│
└── SitesLR12/larafactu/                   # 🎯 App STAGING
    └── packages/aichadigital/             # Symlinks locales
        ├── larabill -> ../../../development/packages/aichadigital/larabill
        ├── lararoi -> ../../../development/packages/aichadigital/lararoi
        ├── lara-verifactu -> ../../../development/packages/aichadigital/lara-verifactu
        └── laratickets -> ../../../development/packages/aichadigital/laratickets
```

### Paquetes y Versiones

| Paquete | Versión | Repositorio | Estado |
|---------|---------|-------------|--------|
| `aichadigital/larabill` | dev-main | GitHub | Activo |
| `aichadigital/lararoi` | dev-main | GitHub | Activo |
| `aichadigital/lara-verifactu` | dev-main | GitHub | Activo |
| `aichadigital/laratickets` | dev-main | GitHub | Activo |
| `aichadigital/lara100` | ^1.0 | Packagist | Estable |

---

## 🔄 Flujo de Desarrollo

### 1. Edición de Código

```
┌─────────────────────────────────────────────────────────────┐
│  EDICIÓN                                                     │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  📁 Editar en:                                               │
│  /Users/abkrim/SitesLR12/larafactu/packages/aichadigital/   │
│                                                              │
│  ⚡ Los cambios se reflejan INMEDIATAMENTE                   │
│     (symlinks → source real)                                 │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

### 2. Testing Local

```bash
# Desde larafactu (staging)
cd /Users/abkrim/SitesLR12/larafactu

# Tests de la app
php artisan test

# Tests de un paquete específico
cd packages/aichadigital/larabill && vendor/bin/pest
```

### 3. Consolidación (Commit)

```
┌─────────────────────────────────────────────────────────────┐
│  CONSOLIDACIÓN                                               │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  1️⃣  Commit en PAQUETE (si hubo cambios):                    │
│      cd packages/aichadigital/larabill                       │
│      git add -A && git commit -m "feat: ..." && git push     │
│                                                              │
│  2️⃣  Commit en APP (si hubo cambios):                        │
│      cd /Users/abkrim/SitesLR12/larafactu                    │
│      git add -A && git commit -m "feat: ..." && git push     │
│                                                              │
│  ⚠️  IMPORTANTE: Commits separados por repositorio           │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

### 4. Actualización de Dependencias

```bash
# Después de commits en paquetes
composer update aichadigital/larabill --no-interaction

# O todos los paquetes
composer update aichadigital/* --no-interaction
```

---

## ⚠️ Problemas Conocidos

### 1. Migraciones Duplicadas

**Síntoma**: `Table already exists` al migrar

**Causa**: Migraciones publicadas + auto-loaded desde paquete

**Solución**:

```bash
# Eliminar migraciones publicadas duplicadas
rm database/migrations/2025_11_*_create_*.php

# Re-migrar
php artisan migrate:fresh --seed
```

### 2. Orden de Migraciones

**Síntoma**: `Foreign key constraint fails`

**Causa**: Dependencias entre tablas no resueltas por orden alfabético

**Solución**: Prefijos de fecha en migraciones deben respetar dependencias

```
2024_12_01_000001_create_users_table.php        # Primero
2024_12_01_000002_create_tax_rates_table.php    # Segundo
2024_12_01_000003_create_invoices_table.php     # Tercero (depende de users)
```

### 3. Composer Path vs VCS

**Síntoma**: `Path repository does not exist` en producción

**Causa**: `composer.json` usa `path` repositories para desarrollo local

**Solución**:

```bash
# En producción, ejecutar script de conversión
php scripts/post-deploy.php
```

### 4. Cache de Composer

**Síntoma**: Cambios en paquetes no se reflejan

**Solución**:

```bash
composer clear-cache
composer dump-autoload
```

### 5. Filament Compatibility

**Síntoma**: `Type must be BackedEnum|string|null`

**Causa**: Versión específica de Filament requiere tipos exactos

**Solución**: Usar `Schema` en lugar de `Form`, declarar tipos correctos

```php
// ❌ Incorrecto
protected static ?string $navigationIcon = 'heroicon-o-users';

// ✅ Correcto
protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';
```

---

## 📅 Timeline y Roadmap

### v1.0.0 - Target: 15 Diciembre 2025

**Objetivo**: Versión estable para migración WHMCS

#### Fase Actual: ADR-001 Fiscal Architecture

- [x] Fase 1: Migraciones y modelos
- [x] Fase 2: Integración Invoice/User
- [x] Fase 3: Eliminar FiscalSettings
- [x] Fase 4: Filament Resources
- [ ] Fase 5: Testing completo e integración

#### Próximos Pasos

1. **Corregir orden de migraciones** en Larabill
2. **Probar flujo completo** en staging
3. **Desplegar en pre-producción**
4. **Documentar proceso de migración WHMCS**

### v2.0.0 - Post-Diciembre 2025

- Multi-tenancy
- Filament plugin separado
- Soporte multi-jurisdicción

---

## 📚 Gestión de Documentación

### Estructura de docs/

```
docs/
├── ADR_*.md              # Decisiones arquitectónicas (permanentes)
├── PRODUCTION_*.md       # Guías de producción (permanentes)
├── DEVELOPMENT_*.md      # Guías de desarrollo (permanentes)
├── WORKFLOW.md           # Este documento (permanente)
└── in-progress/          # Documentos temporales (limpiar regularmente)
```

### Reglas de Documentación

1. **ADR (Architectural Decision Records)**
   - Prefijo: `ADR_XXX_`
   - Permanentes, no eliminar
   - Actualizar estado: PROPOSED → ACCEPTED → DEPRECATED

2. **Guías de Producción/Desarrollo**
   - Mantener actualizadas
   - Revisar en cada release

3. **Documentos Temporales**
   - Usar `docs/in-progress/`
   - Eliminar cuando se completen
   - NO commitear resúmenes de sesiones

4. **Prohibido**
   - ❌ HOTFIX_*.md (aplicar y eliminar)
   - ❌ BUG_*.md (resolver y eliminar)
   - ❌ RESUMEN_*.md (no commitear)
   - ❌ SESION_*.md (no commitear)

---

## 🛠️ Comandos Útiles

### Desarrollo Diario

```bash
# Iniciar desarrollo
cd /Users/abkrim/SitesLR12/larafactu

# Ver estado de paquetes
ls -la packages/aichadigital/

# Tests rápidos
php artisan test --filter=InvoiceTest

# Formatear código
vendor/bin/pint --dirty
```

### Consolidación

```bash
# Pre-push protocol
./scripts/pre-push.sh

# Commit paquete
cd packages/aichadigital/larabill
git add -A && git commit -m "feat: description" && git push

# Commit app
cd /Users/abkrim/SitesLR12/larafactu
git add -A && git commit -m "feat: description" && git push
```

### Troubleshooting

```bash
# Limpiar todo y empezar de nuevo
php artisan db:wipe
composer clear-cache
composer dump-autoload
php artisan migrate --seed

# Ver migraciones pendientes
php artisan migrate:status
```

---

## 📞 Referencias

- **Linear**: https://linear.app/aichadigital/
- **GitHub Larafactu**: https://github.com/AichaDigital/larafactu
- **GitHub Larabill**: https://github.com/AichaDigital/larabill

---

*Última actualización: Noviembre 2025*

