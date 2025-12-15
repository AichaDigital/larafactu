# ADR 001: Refactorización de FiscalSettings - Separación Empresa vs Usuario

**Estado**: ⚠️ **PARCIALMENTE SUPERSEDED**
**Fecha**: 2025-11-28
**Contexto**: Staging Pre-Producción (antes del 15 dic 2025)
**Impacto**: 🔴 **CRÍTICO** - Cambio arquitectónico fundamental
**Aprobado por**: @abkrim

> **NOTA (2025-12-08)**: La sección de `CustomerFiscalData` ha sido superseded por
> [ADR-003: Unificación Users/Customers](../packages/aichadigital/larabill/docs/ADR-003-user-customer-unification.md)
>
> - `CompanyFiscalConfig` → **VIGENTE** (este ADR)
> - `CustomerFiscalData` → **SUPERSEDED** por `UserTaxProfile` (ADR-003)

---

## 📋 Contexto

### Problema Actual

Larabill actualmente asocia `FiscalSettings` directamente a `user_id`, lo cual genera confusión arquitectónica:

1. **`FiscalSettings` mixto**: Mezcla configuraciones de la **empresa** (emisor de facturas) con configuraciones fiscales de **clientes/usuarios**
2. **Sin temporalidad**: No hay control de vigencia temporal de identidades fiscales
3. **Inmutabilidad comprometida**: Cambios fiscales podrían afectar facturas históricas
4. **Proformas sin migración**: No hay mecanismo para actualizar proformas a nueva identidad fiscal

### Arquitectura Objetivo

Separar claramente dos conceptos:

1. **`CompanyFiscalConfig`** (Empresa - Emisor)
   - Identidad fiscal de la empresa que emite facturas
   - Validez temporal (`valid_from`, `valid_until`)
   - CIF, razón social, domicilio fiscal, configuración IVA
   - **No asociada a `user_id`** (es global al sistema)

2. **`CustomerFiscalData`** (Cliente - Receptor)
   - Datos fiscales del cliente/usuario
   - Histórico temporal de cambios fiscales
   - Aplica **hacia adelante**, nunca hacia atrás
   - **Sí asociada a `user_id`**

---

## 🎯 Decisión Propuesta

### 1. Crear Nueva Tabla: `company_fiscal_configs`

**Propósito**: Configuración fiscal de la **empresa emisora**

#### Columnas:

```php
Schema::create('company_fiscal_configs', function (Blueprint $table) {
    $table->id();
    
    // Identidad fiscal de la empresa
    $table->string('business_name'); // Razón social
    $table->string('tax_id'); // CIF/NIF (ESB12345678)
    $table->string('legal_entity_type'); // SL, SA, Autónomo, etc.
    
    // Domicilio fiscal
    $table->string('address');
    $table->string('city');
    $table->string('state')->nullable();
    $table->string('zip_code');
    $table->string('country_code', 2); // ES, FR, etc.
    
    // Configuración fiscal
    $table->boolean('is_oss')->default(false); // Operador OSS
    $table->boolean('is_roi')->default(false); // Operador intracomunitario
    $table->string('currency', 3)->default('EUR');
    $table->string('fiscal_year_start', 5)->default('01-01'); // MM-DD
    
    // Validez temporal (CRÍTICO)
    $table->date('valid_from'); // Inicio de vigencia
    $table->date('valid_until')->nullable(); // Fin de vigencia (null = actual)
    
    // Estado
    $table->boolean('is_active')->default(true);
    $table->text('notes')->nullable(); // Ej: "Cambio por fusión empresarial"
    
    $table->timestamps();
    $table->softDeletes();
    
    // Índices
    $table->index(['valid_from', 'valid_until', 'is_active']);
    $table->index('tax_id');
});
```

#### Reglas de Negocio:

1. **Solo UNA config activa** con `valid_until = null` en cualquier momento
2. **Al crear nueva config**: La anterior debe tener `valid_until = hoy`
3. **Facturas emitidas**: Toman config vigente en `invoice_date`. Siguen siendoo INTOCABLES y asociadas a sus datos.
4. **Proformas**: Se actualizan automáticamente a nueva config al cambiarse la fiscalidad de la empresa. Trabajo via Job y con vigalancia de que se umpla el 100% de los objetivos.

---

### 2. Crear Nueva Tabla: `customer_fiscal_data`

**Propósito**: Histórico fiscal de **clientes/usuarios**

#### Columnas:

```php
Schema::create('customer_fiscal_data', function (Blueprint $table) {
    $table->id();
    $table->uuid('user_id'); // FK a users (el cliente)
    
    // Identidad fiscal del cliente
    $table->string('fiscal_name'); // Nombre fiscal (puede diferir de user.name)
    $table->string('tax_id')->nullable(); // NIF/CIF del cliente
    $table->string('legal_entity_type')->nullable(); // SL, Autónomo, Particular
    
    // Domicilio fiscal
    $table->string('address')->nullable();
    $table->string('city')->nullable();
    $table->string('state')->nullable();
    $table->string('zip_code')->nullable();
    $table->string('country_code', 2)->default('ES');
    
    // Configuración fiscal
    $table->boolean('is_company')->default(false); // Empresa vs Particular
    $table->boolean('is_eu_vat_registered')->default(false); // Registro IVA intracomunitario
    $table->boolean('is_exempt_vat')->default(false); // Exento de IVA
    
    // Validez temporal (CRÍTICO)
    $table->date('valid_from'); // Inicio de vigencia
    $table->date('valid_until')->nullable(); // Fin de vigencia (null = actual)
    
    // Estado
    $table->boolean('is_active')->default(true);
    $table->text('notes')->nullable(); // Ej: "Cambio de domicilio fiscal"
    
    $table->timestamps();
    $table->softDeletes();
    
    // Índices
    $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
    $table->index(['user_id', 'valid_from', 'valid_until', 'is_active']);
    $table->index('tax_id');
});
```

#### Reglas de Negocio:

1. **Histórico temporal**: Un usuario puede tener múltiples registros fiscales
2. **Solo UNA config activa** por usuario con `valid_until = null`
3. **Cambios fiscales**: Crean nuevo registro, cierran el anterior con `valid_until`
4. **Facturas**: Usan config del cliente vigente en `invoice_date`
5. **Proformas**: Se actualizan a nueva config antes de convertirse en factura

---

### 3. Deprecar: `fiscal_settings` (actual)

**¿Qué hacer con la tabla actual?**

- Eliminacion de la tabla, modelos, observers, y cualque tema realacionao con ella.

---

## 🔄 Flujo de Uso

### Cambio de Identidad Fiscal de la Empresa

```php
// Empresa cambia de CIF o razón social
CompanyFiscalConfig::createNew([
    'business_name' => 'Nueva Razón Social S.L.',
    'tax_id' => 'ESB98765432',
    'valid_from' => '2025-01-01',
    'notes' => 'Fusión con otra sociedad',
]);

// Automáticamente:
// 1. Config anterior recibe valid_until = '2024-12-31'
// 2. Config nueva es la activa
// 3. Facturas nuevas usan nueva identidad
// 4. Facturas antiguas mantienen identidad original
// 5. Factura proforma (no pagadas cambian a la nueva identidad)
```

### Cambio de Datos Fiscales del Cliente

```php
// Cliente cambia de domicilio fiscal
$user->updateFiscalData([
    'address' => 'Nueva Calle 123',
    'city' => 'Barcelona',
    'zip_code' => '08001',
    'valid_from' => '2025-02-01',
    'notes' => 'Traslado de sede social',
]);

// Automáticamente:
// 1. Config anterior recibe valid_until = '2025-01-31'
// 2. Config nueva es la activa
// 3. Proformas pendientes se actualizan antes de convertirse en factura.
// 4. Facturas emitidas antes de la fecha NO cambian
```

### Emisión de Factura

```php
// Al crear factura
$invoice = Invoice::create([
    'user_id' => $user->id,
    'invoice_date' => '2025-03-15',
    // ...
]);

// Automáticamente:
// 1. Carga CompanyFiscalConfig vigente en 2025-03-15
// 2. Carga CustomerFiscalData del usuario vigente en 2025-03-15
// 3. Guarda snapshot de ambas configs (inmutable)
```

---

## 📊 Impacto en el Código Existente

### Modelos Afectados

1. **`Invoice`**:
   - Añadir relaciones: `companyFiscalConfig()`, `customerFiscalData()`
   - Snapshot fiscal al crear factura
   
2. **`FiscalSettings`** (deprecar y eliminar):
   - Mover lógica de empresa → `CompanyFiscalConfig`
   - Mover lógica de cliente → `CustomerFiscalData`
   
3. **`User`**:
   - Nueva relación: `fiscalData()` (hasMany con histórico)
   - Helper: `currentFiscalData()` (vigente actual)

### Migraciones

1. **Nueva**: `create_company_fiscal_configs_table.php`
2. **Nueva**: `create_customer_fiscal_data_table.php`
3. **Migración de datos**: `migrate_fiscal_settings_to_new_structure.php`
4. **Deprecación**: Marcar `fiscal_settings` como legacy

---

## 🎯 Beneficios

### ✅ Ventajas

1. **Separación clara de responsabilidades**: Empresa ≠ Cliente
2. **Temporalidad explícita**: Histórico fiscal completo
3. **Inmutabilidad garantizada**: Facturas históricas inalterables
4. **Auditoría fiscal**: Trazabilidad de cambios
5. **Compliance**: Cumplimiento normativo España/EU
6. **Flexibilidad**: Soporte multi-empresa (futuro)

### ⚠️ Desventajas

1. **Complejidad inicial**: Más tablas, más lógica
2. **Migración de datos**: No requiere
3. **Retrocompatibilidad**: No hay apps
4. **Testing extensivo**: Escenarios temporales complejos

---

## 🚀 Plan de Implementación

### Fase 1: Preparación (Semana 1)
- [ ] Crear documento ADR (este)
- [ ] Validar arquitectura con stakeholders
- [ ] Diseño detallado de migraciones

### Fase 2: Implementación (Semana 2)
- [ ] Crear modelos: `CompanyFiscalConfig`, `CustomerFiscalData`
- [ ] Escribir tests unitarios y de integración
- [ ] Crear factories y seeders

### Fase 3: Migración (Semana 3)
- [ ] Script de migración de datos `fiscal_settings` → nuevas tablas
- [ ] Tests de migración (verificar integridad)
- [ ] Documentación de API

### Fase 4: Integración (Semana 4)
- [ ] Adaptar `Invoice` model
- [ ] Actualizar Filament Resources
- [ ] Tests end-to-end

### Fase 5: Validación (Semana 5)
- [ ] Code review completo
- [ ] Testing en staging
- [ ] Documentación de usuario
- [ ] **Deadline**: 15 dic 2025 (v1.0.0)

---

## 🔍 Alternativas Consideradas

### Alternativa 1: Mantener `fiscal_settings` con flag `type`

```php
// fiscal_settings con columna 'type' => 'company' | 'customer'
$table->enum('type', ['company', 'customer']);
```

**Rechazada**: Mezcla conceptos diferentes, dificulta queries, no es semántico

### Alternativa 2: Tabla única con timestamps de vigencia

```php
// fiscal_settings con valid_from/valid_until
$table->date('valid_from');
$table->date('valid_until')->nullable();
```

**Rechazada**: No diferencia empresa vs cliente, genera ambigüedad

### Alternativa 3: JSON en Invoice para snapshot

```php
// Guardar config fiscal como JSON en invoice
$table->json('company_fiscal_config');
$table->json('customer_fiscal_data');
```

**Rechazada**: Dificulta queries, reporting, auditoría, no es relacional

---

## 📝 Preguntas Abiertas

1. **¿Multi-empresa?**: ¿Soportar múltiples empresas emisoras en v1.0?
   - **Respuesta propuesta**: NO, una sola empresa en v1.0 sin idea de cambio.

2. **¿Proformas?**: ¿Cómo actualizar proformas a nueva config?
   - **Respuesta propuesta**: Trigger automático para modificar la proforma.

3. **¿Facturas rectificativas?**: ¿Usan config de factura original o config actual?
   - **Respuesta propuesta**: Config de la factura original (inmutabilidad). As facturas rectifciativas, no hemos llegaod a ellas. PEro en la configruacion europea es especifico. Es un todo aruiqtectural que ya llegara.
4. **¿PDFs históricos?**: ¿Regenerar PDFs con identidad histórica? Los PDF de las proforma son pdf que se generan al vuelo cuando se solicitan. Las facturas, son guardadas en disco, y con se repite una y otra vez, ya quedan INMUTABLES.
   - **Respuesta propuesta**: SÍ, PDFs deben reflejar identidad fiscal vigente en fecha de emisión

---

## 🎯 Decisión Final

**Estado**: ✅ **APROBADO Y VALIDADO**

**Recomendación**: ✅ **Implementar refactorización completa**

**Justificación**:
- Arquitectura limpia y escalable
- Compliance fiscal garantizado
- Inmutabilidad de facturas GARANTIZADA desde creación
- Proformas actualizables ANTES de convertirse en facturas
- Preparado para v2.0 (multi-empresa)
- Costo de implementación asumible (5 semanas < deadline 15 dic)

**Próximo paso**: 
1. ✅ Validar con @abkrim: **VALIDADO**
2. ✅ Ajustes al ADR: **COMPLETADO**
3. 🚀 Comenzar Fase 1: **READY TO START**

---

**Autor**: Claude (AI Assistant)  
**Revisor**: @abkrim  
**Fecha**: 2025-11-28

