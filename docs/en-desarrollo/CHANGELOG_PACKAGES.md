# 📝 Changelog - Branch `improvements/larafactu-join`

> **Propósito**: Registro de todos los cambios aplicados en los paquetes para permitir instalación limpia en Larafactu.

---

## 📦 **aichadigital/larabill**

### Commits en `improvements/larafactu-join`

#### 1. **fix(migrations): correct invoice_items FK to use foreignUuid**
- **SHA**: [pendiente confirmar]
- **Fecha**: 2025-11-20
- **Problema**: `invoice_items.invoice_id` usaba `binary()` + `foreign()` incompatible con UUID
- **Solución**: Cambiar a `foreignUuid('invoice_id')`
- **Archivos**:
  - `database/migrations/create_invoice_items_table.php.stub`

#### 2. **fix(migrations): reduce column lengths in company_template_settings**
- **SHA**: [pendiente confirmar]
- **Fecha**: 2025-11-20
- **Problema**: Índice único compuesto excedía 3072 bytes en MySQL
- **Solución**: Reducir longitud de columnas `string` en índice
- **Archivos**:
  - `database/migrations/create_company_template_settings_table.php.stub`
- **Cambios**:
  ```php
  // Antes
  $table->string('setting_type');
  $table->string('invoice_type')->default('fiscal');
  $table->string('scope')->default('global');
  $table->string('client_id')->nullable();
  
  // Después
  $table->string('setting_type', 50);
  $table->string('invoice_type', 50)->default('fiscal');
  $table->string('scope', 50)->default('global');
  $table->string('client_id', 100)->nullable();
  ```

#### 3. **feat(install): add LarabillInstallCommand**
- **SHA**: b191a1c
- **Fecha**: 2025-11-21
- **Problema**: No existía comando de instalación automatizado
- **Solución**: Crear `php artisan larabill:install` que:
  - Detecta tipo de `user_id`
  - Publica migraciones en orden correcto
  - Publica configuración
  - Ejecuta migraciones (opcional)
- **Archivos**:
  - `src/Console/LarabillInstallCommand.php` (nuevo)

#### 4. **fix(install): register command manually in boot()**
- **SHA**: 62c0f99
- **Fecha**: 2025-11-21
- **Problema**: `hasCommand()` no funciona porque Larabill no usa Spatie skeleton
- **Solución**: Registro manual en `boot()` del ServiceProvider
- **Archivos**:
  - `src/LarabillServiceProvider.php`
- **Cambios**:
  ```php
  public function boot(): void
  {
      parent::boot();
      $this->registerEventListeners();
      
      // Register install command manually
      if ($this->app->runningInConsole()) {
          $this->commands([
              \AichaDigital\Larabill\Console\LarabillInstallCommand::class,
          ]);
      }
  }
  ```

---

## 📦 **aichadigital/lara-verifactu**

### Commits en `improvements/larafactu-join`

#### 1. **feat(install): add VerifactuInstallCommand**
- **SHA**: [pendiente confirmar]
- **Fecha**: 2025-11-21
- **Problema**: Migraciones no se publicaban automáticamente con `vendor:publish`
- **Solución**: Crear `php artisan verifactu:install`
- **Archivos**:
  - `src/Console/VerifactuInstallCommand.php` (nuevo)

#### 2. **fix(install): register command manually in packageBooted()**
- **SHA**: c4a0fe4
- **Fecha**: 2025-11-21
- **Problema**: Comando no se descubría automáticamente
- **Solución**: Registro manual en `packageBooted()`
- **Archivos**:
  - `src/LaraVerifactuServiceProvider.php`

---

## 📦 **aichadigital/laratickets**

### Estado
✅ **No requiere cambios** - El paquete funciona correctamente en su estado actual.

---

## 📦 **aichadigital/lararoi**

### Estado
✅ **No requiere cambios** - Paquete de lógica pura sin migraciones propias.

---

## 🔄 **Pendientes de Aplicar**

### **Larabill**

| # | Problema | Estado | Prioridad |
|---|----------|--------|-----------|
| 1 | Migración `create_users_table` duplicada | 🔄 Pendiente | Alta |
| 2 | Orden incorrecto: `commissions` antes de `articles` | 🔄 Pendiente | Alta |
| 3 | Orden incorrecto: `add_v040_fields` antes de `create_invoices` | 🔄 Pendiente | Alta |
| 4 | Orden incorrecto: `invoices` antes de `user_tax_profiles` | 🔄 Pendiente | Alta |
| 5 | Stub `unit_measures` no se publica | 🔄 Pendiente | Media |
| 6 | Stub `tax_categories` no se publica | 🔄 Pendiente | Media |

**Solución propuesta**: El comando `larabill:install` ya maneja el orden correcto, pero los stubs deberían corregirse para instalaciones manuales.

---

## 🎯 **Testing de los Cambios**

### **Verificación en Larafactu**

```bash
# 1. Limpiar instalación previa
php artisan db:wipe --force
mysql larafactu < database/dumps/00_laravel_core_base.sql

# 2. Reinstalar paquetes
composer update aichadigital/larabill aichadigital/lara-verifactu aichadigital/laratickets

# 3. Ejecutar instaladores
php artisan larabill:install
php artisan verifactu:install
php artisan laratickets:install

# 4. Verificar tablas (debe ser 42)
php artisan db:show --json | jq -r '.tables[].name' | wc -l
```

### **Resultado Esperado**
✅ Todas las migraciones se ejecutan sin errores  
✅ 42 tablas creadas correctamente  
✅ Sin errores de FK  
✅ Sin errores de índices largos  

---

## 📋 **Checklist para Merge a `main`**

### **Larabill**
- [x] Corregir FK de `invoice_items`
- [x] Reducir longitud de índice en `company_template_settings`
- [x] Implementar `LarabillInstallCommand`
- [x] Registrar comando en ServiceProvider
- [ ] Corregir orden de timestamps en migraciones
- [ ] Eliminar migración duplicada de `users`
- [ ] Asegurar publicación de `unit_measures` y `tax_categories`
- [ ] Tests de instalación en Laravel limpio
- [ ] Actualizar README con instrucciones de instalación

### **Lara-Verifactu**
- [x] Implementar `VerifactuInstallCommand`
- [x] Registrar comando en ServiceProvider
- [ ] Tests de instalación en Laravel limpio
- [ ] Actualizar README con instrucciones de instalación

### **Laratickets**
- [ ] Tests de instalación en Laravel limpio
- [ ] Actualizar README con instrucciones de instalación

---

## 🚀 **Release Plan**

### **v0.9.0 - Alpha Release** (Estimado: 2025-11-25)
- Todas las correcciones de FK y migraciones
- Comandos `install` funcionales en todos los paquetes
- Documentación básica de instalación

### **v1.0.0 - Stable Release** (Objetivo: 2025-12-15)
- Testing exhaustivo en múltiples escenarios
- Documentación completa
- Ejemplos de uso
- Migración WHMCS funcional

---

**Última actualización**: 2025-11-21  
**Branch activo**: `improvements/larafactu-join`  
**Estado**: ✅ Funcional en staging (Larafactu)

