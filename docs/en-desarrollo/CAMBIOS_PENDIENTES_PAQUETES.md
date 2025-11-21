# Cambios Aplicados en Paquetes - Integración Larafactu

**Fecha**: 2025-11-20  
**Contexto**: Validación de integración end-to-end en Larafactu  
**Branch en paquetes**: `improvements/larafactu-join`

---

## 🎯 Resumen Ejecutivo

Durante la validación de integración de los paquetes en Larafactu, se detectó **1 incompatibilidad en Larabill** que fue corregida en el paquete fuente.

**Estado**: ✅ Cambios aplicados en paquete y commiteados

---

## 📦 LARABILL - Cambio Aplicado

### ✅ 1. `invoice_items` - foreignUuid Implementado

**Archivo**: `database/migrations/create_invoice_items_table.php.stub`  
**Líneas**: ~19-20  
**Branch**: `improvements/larafactu-join`  
**Commit**: `977b37f`

#### Problema Detectado

La migración usaba `binary() + foreign()` manual, causando error con UUID:

```php
// ❌ INCORRECTO (anterior)
$table->binary('invoice_id', 16);
$table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
```

**Error MySQL**:
```
SQLSTATE[HY000]: General error: 3780 Referencing column 'invoice_id' and referenced column 'id' in foreign key constraint are incompatible.
```

#### Solución Aplicada ✅

```php
// ✅ CORRECTO (actual)
$table->foreignUuid('invoice_id')->constrained('invoices')->cascadeOnDelete()->comment('UUID binary(16) parent invoice');
```

#### Commit Message
```
fix(migrations): use foreignUuid for invoice_items.invoice_id FK

- Changed from binary() + foreign() to foreignUuid()
- Fixes MySQL incompatibility error during integration testing
- Ensures proper UUID binary FK constraint creation
- Detected during Larafactu end-to-end validation
```

---

### ✅ 2. `company_template_settings` - Ya Estaba Correcto

**Archivo**: `database/migrations/create_company_template_settings_table.php.stub`  
**Estado**: ✅ El paquete YA tenía los VARCHAR con longitudes correctas

```php
// ✅ YA CORRECTO en el paquete
$table->string('setting_type', 50);
$table->string('invoice_type', 50)->default('fiscal');
$table->string('scope', 50)->default('global');
$table->string('client_id', 100)->nullable();
```

**Nota**: El problema fue en la instalación de Larafactu (publicación antigua). Corregido localmente.

---

## 🔄 Próximos Pasos en Larafactu

### Paso 1: Limpiar Migraciones Locales Modificadas

```bash
cd /Users/abkrim/SitesLR12/larafactu

# Eliminar migración local modificada
rm database/migrations/2025_11_20_165648_2024_12_01_0004_create_invoice_items_table.php
```

### Paso 2: Re-publicar desde Paquete Actualizado

```bash
# Re-publicar migraciones de Larabill
php artisan vendor:publish --tag="larabill-migrations" --force
```

### Paso 3: Migrar Limpio

```bash
# Ejecutar migraciones desde cero
php artisan migrate:fresh
```

**Resultado esperado**: ✅ Todas las migraciones deben pasar sin errores

---

## 📊 Impacto

### Compatibilidad
- ✅ UUID binary v7 (dyrynda/laravel-model-uuid)
- ✅ MySQL 5.7+, 8.0+
- ✅ MariaDB 10.2+

### Breaking Changes
- ❌ Ninguno (corrección de bug)
- ✅ Compatible con todas las configuraciones de User ID

### Tests a Revisar
- ⚠️ Tests de `InvoiceItem` creación
- ⚠️ Tests que usen FK `invoice_id`
- ⚠️ Tests de relaciones Invoice → InvoiceItems

---

## 🎯 Estado Final

| Cambio | Paquete | Estado | Commit |
|--------|---------|--------|--------|
| `invoice_items` foreignUuid | Larabill | ✅ Aplicado | 977b37f |
| `company_template_settings` VARCHAR | Larabill | ✅ Ya correcto | N/A |

---

**Última actualización**: 2025-11-20  
**Branch**: `improvements/larafactu-join`  
**Estado**: ✅ Completado en paquete

---

## 📦 LARABILL - 2 Cambios Requeridos

### 🔴 1. `invoice_items` - Incompatibilidad foreignUuid

**Archivo**: `database/migrations/create_invoice_items_table.php.stub`  
**Líneas**: ~18-19  
**Branch**: `improvements/larafactu-join`

#### Problema

La migración usa `binary()` + `foreign()` manual, pero esto causa error con UUID:

```php
// ❌ INCORRECTO (actual)
$table->binary('invoice_id', 16);
$table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
```

**Error MySQL**: 
```
SQLSTATE[HY000]: General error: 3780 Referencing column 'invoice_id' and referenced column 'id' in foreign key constraint 'invoice_items_invoice_id_foreign' are incompatible.
```

#### Solución

Usar `foreignUuid()` de Laravel para compatibilidad con UUID:

```php
// ✅ CORRECTO
$table->foreignUuid('invoice_id')->constrained('invoices')->cascadeOnDelete()->comment('UUID binary(16) parent invoice');
```

#### Justificación

- Laravel `foreignUuid()` maneja automáticamente la conversión UUID → binary(16)
- Compatible con `dyrynda/laravel-model-uuid`
- Consistente con otras FKs de UUID en el paquete (ej: `proforma_id`, `rectifies_invoice_id`)

---

### 🔴 2. `company_template_settings` - Índice Compuesto Demasiado Largo

**Archivo**: `database/migrations/create_company_template_settings_table.php.stub`  
**Líneas**: ~20-24, 28  
**Branch**: `improvements/larafactu-join`

#### Problema

El índice único compuesto excede 3072 bytes (límite MySQL):

```php
// ❌ INCORRECTO (actual)
$table->string('setting_type'); // VARCHAR(255)
$table->string('invoice_type')->default('fiscal'); // VARCHAR(255)
$table->string('scope')->default('global'); // VARCHAR(255)
$table->string('client_id')->nullable(); // VARCHAR(255)

$table->unique(['user_id', 'setting_type', 'invoice_type', 'scope', 'client_id'], 'user_setting_unique');
```

**Error MySQL**:
```
SQLSTATE[42000]: Syntax error or access violation: 1071 Specified key was too long; max key length is 3072 bytes
```

**Cálculo**:
- `user_id` (UUID binary): 16 bytes
- `setting_type` (VARCHAR 255): 255 * 4 = 1020 bytes (utf8mb4)
- `invoice_type` (VARCHAR 255): 1020 bytes
- `scope` (VARCHAR 255): 1020 bytes
- `client_id` (VARCHAR 255): 1020 bytes
- **Total**: 4096 bytes > 3072 límite ❌

#### Solución

Reducir tamaños de VARCHAR según uso real:

```php
// ✅ CORRECTO
$table->string('setting_type', 50); // 'template', 'notes', 'payment_terms'
$table->string('invoice_type', 50)->default('fiscal'); // 'fiscal', 'proforma', 'reverse-charge', 'exempt'
$table->string('scope', 50)->default('global'); // 'global', 'client', 'individual'
$table->string('client_id', 100)->nullable(); // UUID string o ID de cliente

$table->unique(['user_id', 'setting_type', 'invoice_type', 'scope', 'client_id'], 'user_setting_unique');
```

**Cálculo corregido**:
- `user_id`: 16 bytes
- `setting_type` (50): 200 bytes
- `invoice_type` (50): 200 bytes
- `scope` (50): 200 bytes
- `client_id` (100): 400 bytes
- **Total**: 1016 bytes < 3072 ✅

#### Justificación

- Los valores reales nunca exceden estos límites
- `setting_type`: max ~20 chars
- `invoice_type`: max ~25 chars
- `scope`: max ~10 chars
- `client_id`: UUID string = 36 chars
- Mejora performance del índice (más pequeño)

---

## 🔧 Protocolo de Aplicación

### Paso 1: Cambiar a directorio del paquete

```bash
cd /Users/abkrim/development/packages/aichadigital/larabill
```

### Paso 2: Verificar/crear branch

```bash
# Ver branch actual
git branch

# Si no existe improvements/larafactu-join, crearla
git checkout -b improvements/larafactu-join

# Si ya existe, cambiar a ella
git checkout improvements/larafactu-join
```

### Paso 3: Aplicar cambios

#### Cambio 1: invoice_items

```bash
# Editar migración
nano database/migrations/create_invoice_items_table.php.stub

# Buscar líneas 18-19 y reemplazar:
# - $table->binary('invoice_id', 16);
# - $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade')->comment('UUID binary(16) parent invoice');
# + $table->foreignUuid('invoice_id')->constrained('invoices')->cascadeOnDelete()->comment('UUID binary(16) parent invoice');
```

#### Cambio 2: company_template_settings

```bash
# Editar migración
nano database/migrations/create_company_template_settings_table.php.stub

# Buscar líneas 20-23 y actualizar longitudes:
# - $table->string('setting_type');
# + $table->string('setting_type', 50);
# - $table->string('invoice_type')->default('fiscal');
# + $table->string('invoice_type', 50)->default('fiscal');
# - $table->string('scope')->default('global');
# + $table->string('scope', 50)->default('global');
# - $table->string('client_id')->nullable();
# + $table->string('client_id', 100)->nullable();
```

### Paso 4: Commit en branch

```bash
git add database/migrations/create_invoice_items_table.php.stub
git add database/migrations/create_company_template_settings_table.php.stub

git commit -m "fix(migrations): UUID compatibility and index size limits

- Use foreignUuid() for invoice_items.invoice_id FK
- Reduce VARCHAR lengths in company_template_settings unique index
- Ensures MySQL 3072 bytes index limit compliance
- Detected during Larafactu integration testing"
```

### Paso 5: Push a GitHub

```bash
git push origin improvements/larafactu-join
```

### Paso 6: Actualizar en Larafactu

```bash
cd /Users/abkrim/SitesLR12/larafactu
composer update aichadigital/larabill
php artisan migrate:fresh
```

---

## ✅ Verificación Post-Aplicación

Después de aplicar los cambios en el paquete, verificar:

```bash
cd /Users/abkrim/SitesLR12/larafactu

# Limpiar migraciones locales modificadas
rm database/migrations/2025_11_20_165648_2024_12_01_0004_create_invoice_items_table.php
rm database/migrations/2025_11_20_165653_create_company_template_settings_table.php

# Volver a publicar desde paquete actualizado
php artisan vendor:publish --tag="larabill-migrations" --force

# Migrar limpio
php artisan migrate:fresh

# Debe pasar sin errores ✅
```

---

## 📊 Impacto

### Compatibilidad
- ✅ UUID binary v7 (dyrynda/laravel-model-uuid)
- ✅ MySQL 5.7+, 8.0+
- ✅ MariaDB 10.2+

### Breaking Changes
- ❌ Ninguno (correcciones de bugs)
- ✅ Compatible con todas las configuraciones de User ID (int, UUID, ULID)

### Tests Afectados
- Verificar tests de `InvoiceItem` creación
- Verificar tests de `CompanyTemplateSettings` con claves únicas

---

## 🎯 Próximos Pasos

1. ✅ Aplicar cambios en `larabill` branch `improvements/larafactu-join`
2. ⏳ Ejecutar suite de tests del paquete
3. ⏳ Actualizar CHANGELOG.md de Larabill
4. ⏳ Continuar con Fase 2 de validación (Seeders)

---

## 📝 Notas Adicionales

### Otros Cambios Locales (No requieren acción en paquetes)

1. **unit_measures** - Ya existe en paquete como stub, solo se copió
2. **Filament tables** - Son de vendor, cambios locales apropiados
3. **Orden de migraciones** - Timestamps locales, específico de esta instalación
4. **VAT verifications duplicada** - Eliminada local, paquetes OK

### Lecciones Aprendidas

1. **foreignUuid() > binary() + foreign()** para UUIDs
2. **Siempre especificar longitud VARCHAR** en índices compuestos
3. **Calcular tamaño de índices** antes de crear (especialmente utf8mb4)
4. **Tests de integración end-to-end** críticos para detectar incompatibilidades

---

**Última actualización**: 2025-11-20  
**Responsable**: Validación integración Larafactu  
**Estado**: 🟡 Pendiente aplicar en paquetes

