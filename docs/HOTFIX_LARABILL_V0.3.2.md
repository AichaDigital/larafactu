# HOTFIX Requerido: Larabill v0.3.2

**Fecha de detección**: 2025-10-13  
**Versión afectada**: v0.3.1  
**Severidad**: Media - Requiere intervención manual del usuario  
**Estado del paquete**: ✅ v0.3.1 instalado y funcionando (con fixes manuales)

---

## 📋 Problemas Identificados

### Problema 1: Índices Duplicados en Stubs (CRÍTICO)

**Descripción**: Los stubs no fueron actualizados cuando se implementó `MigrationHelper::userIdColumn()`, que automáticamente añade un índice en `user_id`.

**Archivos afectados**:
- `database/migrations/create_invoices_table.php.stub`
- `database/migrations/create_user_tax_infos_table.php.stub`

**Error producido**:
```
SQLSTATE[42000]: Syntax error or access violation: 1061 Duplicate key name 'invoices_user_id_index'
```

**Causa root**: 
Los stubs contienen `$table->index(['user_id']);` pero `MigrationHelper::userIdColumn()` ya añade ese índice automáticamente.

---

### Problema 2: Columnas String Demasiado Largas (CRÍTICO)

**Descripción**: El stub `create_company_template_settings_table.php.stub` define columnas `string()` sin límite de longitud, causando que la clave única compuesta supere el límite de MySQL (3072 bytes).

**Archivo afectado**:
- `database/migrations/create_company_template_settings_table.php.stub`

**Error producido**:
```
SQLSTATE[42000]: Syntax error or access violation: 1071 Specified key was too long; max key length is 3072 bytes
```

**Causa root**:
```php
// ❌ ANTES - Sin límite
$table->string('setting_type');
$table->string('invoice_type')->default('fiscal');
$table->string('scope')->default('global');
$table->string('client_id')->nullable();

// Clave única demasiado larga:
$table->unique(['user_id', 'setting_type', 'invoice_type', 'scope', 'client_id']);
```

---

## 🔧 Fixes Requeridos en el Paquete

### Fix 1: Actualizar `create_invoices_table.php.stub`

**Archivo**: `database/migrations/create_invoices_table.php.stub`

**Línea ~39**: Eliminar índice duplicado

```diff
-            // Indexes
+            // Indexes (user_id index added automatically by MigrationHelper)
             $table->index(['number']);
-            $table->index(['user_id']);
             $table->index(['status']);
             $table->index(['type', 'status']);
```

---

### Fix 2: Actualizar `create_user_tax_infos_table.php.stub`

**Archivo**: `database/migrations/create_user_tax_infos_table.php.stub`

**Línea ~34**: Eliminar índice duplicado

```diff
-            // Indexes
+            // Indexes (user_id index added automatically by MigrationHelper)
-            $table->index(['user_id']);
             $table->index(['is_current']);
             $table->index(['user_id', 'is_current']);
             $table->unique(['user_id', 'is_current']);
```

---

### Fix 3: Actualizar `create_company_template_settings_table.php.stub`

**Archivo**: `database/migrations/create_company_template_settings_table.php.stub`

**Líneas ~20-23**: Limitar longitud de columnas string

```diff
             // Agnostic user_id - auto-detects User model ID type (for multi-user support)
             MigrationHelper::userIdColumn($table);
-            $table->string('setting_type'); // 'template', 'notes', 'payment_terms'
-            $table->string('invoice_type')->default('fiscal'); // 'fiscal', 'proforma', 'reverse-charge', 'exempt'
-            $table->string('scope')->default('global'); // 'global', 'client', 'individual'
-            $table->string('client_id')->nullable(); // For client-specific settings
+            $table->string('setting_type', 50); // 'template', 'notes', 'payment_terms'
+            $table->string('invoice_type', 50)->default('fiscal'); // 'fiscal', 'proforma', 'reverse-charge', 'exempt'
+            $table->string('scope', 50)->default('global'); // 'global', 'client', 'individual'
+            $table->string('client_id', 100)->nullable(); // For client-specific settings
             $table->text('value'); // Setting value
```

**Justificación de longitudes**:
- `setting_type` (50): Valores cortos como 'template', 'notes', 'payment_terms'
- `invoice_type` (50): Valores cortos como 'fiscal', 'proforma', 'reverse-charge'
- `scope` (50): Valores cortos como 'global', 'client', 'individual'
- `client_id` (100): Puede ser UUID, ULID o ID numérico largo

**Cálculo del tamaño de índice único**:
- UUID binary (16) + 50 + 50 + 50 + 100 = 266 bytes × 4 (utf8mb4) = **1,064 bytes** ✅ (< 3072)

---

## 📊 Stubs a Revisar/Actualizar

### Stubs Afectados (Requieren Fix)

| Stub | Problema | Fix Aplicado | Líneas |
|------|----------|--------------|--------|
| `create_invoices_table.php.stub` | Índice duplicado `user_id` | Eliminar `$table->index(['user_id']);` | ~39 |
| `create_user_tax_infos_table.php.stub` | Índice duplicado `user_id` | Eliminar `$table->index(['user_id']);` | ~34 |
| `create_company_template_settings_table.php.stub` | Columnas string sin límite | Añadir límites: 50, 50, 50, 100 | ~20-23 |

### Stubs OK (No Requieren Cambios)

| Stub | Estado |
|------|--------|
| `create_invoice_items_table.php.stub` | ✅ OK - No tiene `user_id` |
| `create_tax_rates_table.php.stub` | ✅ OK - No tiene `user_id` |
| `create_vat_verifications_table.php.stub` | ✅ OK - No tiene `user_id` |

---

## 🧪 Testing del Hotfix

### Proceso de Verificación

```bash
# 1. Aplicar los 3 fixes en los stubs del paquete

# 2. En un proyecto limpio con User UUID binary:
php artisan larabill:detect-user-id --update-env
php artisan vendor:publish --tag=larabill-migrations --force
php artisan migrate:fresh --seed

# Resultado esperado:
# ✅ 8 migraciones publicadas (sin errores)
# ✅ 11 migraciones ejecutadas (3 Laravel + 8 Larabill)
# ✅ Usuario de prueba creado
# ✅ Sin errores de índices duplicados
# ✅ Sin errores de claves demasiado largas
```

---

## 📝 Escenarios de Usuario Afectados

### Escenario A: User con UUID Binary (Nuestro Caso)
**Configuración**: `LARABILL_USER_ID_TYPE=uuid_binary`

**Problemas encontrados**:
1. ✅ `MigrationHelper` detecta correctamente el tipo
2. ❌ Índice duplicado en `invoices` (Fix 1 requerido)
3. ❌ Índice duplicado en `user_tax_infos` (Fix 2 requerido)
4. ❌ Columnas demasiado largas en `company_template_settings` (Fix 3 requerido)

### Escenario B: User con ULID Binary
**Configuración**: `LARABILL_USER_ID_TYPE=ulid_binary`

**Problemas esperados**: Los mismos (ULID binary también usa `binary(26)`)

### Escenario C: User con UUID String
**Configuración**: `LARABILL_USER_ID_TYPE=uuid`

**Problemas esperados**:
1. ✅ `MigrationHelper` detecta correctamente
2. ❌ Índice duplicado (Fix 1 y 2 requeridos)
3. ❌ Columnas demasiado largas (Fix 3 requerido)

### Escenario D: User con Int (Default)
**Configuración**: `LARABILL_USER_ID_TYPE=int` (o sin configurar)

**Problemas esperados**:
1. ✅ `MigrationHelper` usa `unsignedBigInteger`
2. ❌ Índice duplicado (Fix 1 y 2 requeridos)
3. ❌ Columnas demasiado largas (Fix 3 requerido)

**Conclusión**: Los 3 fixes son necesarios en **TODOS los escenarios**.

---

## 🎯 Prioridad de Fixes

### Alta Prioridad (Bloquean instalación)
- ✅ Fix 1: Índice duplicado en `invoices` - **Impide migrate**
- ✅ Fix 2: Índice duplicado en `user_tax_infos` - **Impide migrate**
- ✅ Fix 3: Columnas largas en `company_template_settings` - **Impide migrate**

### Estado Actual
- v0.3.1: ✅ Stubs publicables (Fix del error v0.3.0)
- v0.3.2: ⚠️ Requerido para fixes de índices y longitudes

---

## 📋 Checklist para v0.3.2

### En el Paquete

- [ ] Aplicar Fix 1 en `create_invoices_table.php.stub`
- [ ] Aplicar Fix 2 en `create_user_tax_infos_table.php.stub`
- [ ] Aplicar Fix 3 en `create_company_template_settings_table.php.stub`
- [ ] Ejecutar tests del paquete: `composer test`
- [ ] Verificar en proyecto limpio con UUID binary
- [ ] Verificar en proyecto limpio con int (default)
- [ ] Update CHANGELOG.md
- [ ] Commit: `fix: remove duplicate user_id indexes and limit string lengths in stubs`
- [ ] Tag: `v0.3.2`
- [ ] Push

### Después del Release

- [ ] Update en proyecto `larafactu`: `composer update aichadigital/larabill`
- [ ] Eliminar migraciones publicadas: `rm database/migrations/2025_10_13_145*`
- [ ] Republicar: `php artisan vendor:publish --tag=larabill-migrations --force`
- [ ] Verificar: Debe funcionar sin fixes manuales
- [ ] Test: `php artisan migrate:fresh --seed`

---

## 💡 Lecciones Aprendidas

### Para Futuras Actualizaciones del Paquete

1. **Sincronizar migraciones y stubs**: Cuando se actualiza una migración real, actualizar su stub correspondiente
2. **Testing con diferentes tipos de User ID**: Probar con `int`, `uuid`, `uuid_binary`, `ulid`, `ulid_binary`
3. **CI/CD**: Añadir tests automatizados que publiquen y ejecuten migraciones
4. **Límites de columnas**: Siempre especificar límites en columnas `string()` que participan en índices
5. **Documentar breaking changes**: El CHANGELOG debe ser explícito sobre índices automáticos

---

## 📚 Referencias

- **CHANGELOG v0.3.0**: "Removed Duplicate Indexes: user_id index now added automatically by MigrationHelper"
- **MySQL Docs**: Index key length limit: 3072 bytes (utf8mb4: 4 bytes/char)
- **MigrationHelper**: `src/Support/MigrationHelper.php` línea 47 (añade índice automáticamente)

---

## ✅ Estado Final del Proyecto Larafactu

```
✅ v0.3.1 instalado
✅ Fixes manuales aplicados en migraciones publicadas
✅ 11 migraciones ejecutadas correctamente
✅ Usuario de prueba creado (UUID binary)
✅ Todas las tablas funcionando
✅ Base de datos lista para testing del paquete

PENDIENTE:
⏳ Aplicar fixes en el paquete → v0.3.2
⏳ Actualizar y verificar sin fixes manuales
```

---

**Tiempo estimado para v0.3.2**: 15 minutos (3 cambios triviales + testing)

