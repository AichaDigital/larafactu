---
status: VIGENTE
date: 2025-12-07
---

# ADR-002: Migración a UUID v7 String y Consolidación de Entidades

> **Fecha**: 2025-12-07
> **Actualizado**: 2025-12-15
> **Contexto**: Larafactu + Ecosistema AichaDigital
> **Deadline**: ~15 febrero 2026
>
> **NOTA (2025-12-15)**: Timeline extendido. Unificación fiscal en progreso.
>
> **NOTA (2025-12-08)**: La sección de entidades fiscales ha sido actualizada por
> [ADR-003: Unificación Users/Customers](../packages/aichadigital/larabill/docs/ADR-003-user-customer-unification.md)
>
> - Cambios UUID → **VIGENTE** (este ADR)
> - `CustomerFiscalData` → **EN PROCESO DE ELIMINACIÓN**, será reemplazada por `UserTaxProfile`
> - `UserTaxProfile` → **MODELO ACTIVO** para histórico fiscal de usuarios

---

## Contexto

El sistema utilizaba UUID binario (binary(16)) para optimizar almacenamiento (~55% menos espacio). Sin embargo, se detectaron incompatibilidades críticas con FilamentPHP v4 + Livewire que impedían el correcto funcionamiento de la interfaz de administración.

Adicionalmente, existían entidades fiscales duplicadas que causaban confusión y mantenimiento innecesario.

## Decisión

### 1. UUID: Migrar de binary(16) a char(36)

**Se elimina soporte para UUID binario** en favor de UUID v7 string:

```php
// ANTES (eliminado)
$table->binary('id', 16)->primary();
// Config: LARABILL_USER_ID_TYPE=uuid_binary

// DESPUÉS
$table->uuid('id')->primary();
// Config: LARABILL_USER_ID_TYPE=uuid
```

**Motivos:**

- FilamentPHP v4 + Livewire no serializa correctamente UUID binario
- POC validado: 100,000 facturas con UUID v7 string funciona correctamente
- Laravel 12 tiene soporte nativo para UUID v7 (`Str::orderedUuid()`)
- El paquete `dyrynda/laravel-model-uuid` ya no es necesario

**Opciones de configuración mantenidas:**

- `int` - Auto-increment (para proyectos simples)
- `uuid` - UUID v7 string (recomendado)
- `ulid` - ULID como alternativa

### 2. Eliminar dependencia `dyrynda/laravel-model-uuid`

Laravel 12 proporciona soporte nativo:

```php
use Illuminate\Support\Str;

// UUID v7 ordenado cronológicamente
$uuid = (string) Str::orderedUuid();
```

### 3. Consolidar entidades fiscales

> ⚠️ **TABLA ACTUALIZADA POR ADR-003** - Ver nota al inicio del documento

**Entidades ELIMINADAS (corregido por ADR-003):**

| Entidad | Tabla | Razón |
|---------|-------|-------|
| `IssuerConfig` | `issuer_config` | Reemplazado por CompanyFiscalConfig |
| `IssuerTaxProfile` | `issuer_tax_profiles` | Reemplazado por CompanyFiscalConfig |
| `CustomerTaxProfile` | `customer_tax_profiles` | Duplicaba CustomerFiscalData |
| `Customer` | `customers` | **ADR-003**: Unificado en `users` con `parent_user_id` |
| `CustomerFiscalData` | `customer_fiscal_data` | **ADR-003**: Renombrado a `UserTaxProfile` |

**Entidades ACTIVAS (corregido por ADR-003):**

| Entidad | Tabla | Propósito |
|---------|-------|-----------|
| `CompanyFiscalConfig` | `company_fiscal_configs` | Configuración fiscal del emisor con temporalidad |
| `User` | `users` | Entidad unificada con `parent_user_id` para clientes delegados |
| `UserTaxProfile` | `user_tax_profiles` | Histórico fiscal de cualquier User (directo o delegado) |
| `LegalEntityType` | `legal_entity_types` | Catálogo de tipos de entidad jurídica |

### 4. Limpiar Invoice

**Columna eliminada:**

```php
// Eliminar de invoices
$table->dropColumn('tax_profile_id');
```

**Columnas a actualizar (en progreso):**

- `company_fiscal_config_id` → Snapshot del emisor ✅
- `customer_fiscal_data_id` → **RENOMBRAR** a `user_tax_profile_id` (pendiente)
- `customer_id` → **ELIMINAR** (unificado en users con parent_user_id)

## Consecuencias

### Positivas

- FilamentPHP v4 funciona correctamente con todos los modelos UUID
- Reducción de complejidad: menos entidades, menos confusión
- Eliminación de dependencia externa (`dyrynda/laravel-model-uuid`)
- Código más simple en HasUuid trait
- Laravel 12 nativo, sin hacks

### Negativas

- Aumento de almacenamiento: ~20 bytes por registro UUID (aceptable)
- Breaking change para proyectos que usaban `uuid_binary`
- Migraciones de datos necesarias si hubiera producción (no aplica)

### Neutrales

- Performance de queries similar (UUID v7 mantiene ordenación cronológica)
- Los paquetes mantienen agnosticismo (int/uuid/ulid)

## Implementación

Ver `docs/PLAN_REFACTOR_INTEGRAL.md` para el plan detallado de ejecución.

### Orden de cambios

1. Documentación (este ADR y actualizaciones)
2. Larabill (core package)
3. Laratickets
4. Paquetes Filament
5. Larafactu (aplicación)

## Referencias

- `docs/PLAN_REFACTOR_INTEGRAL.md` - Plan de ejecución completo
- `docs/database/ANALYSIS-DUPLICATED-ENTITIES.md` - Análisis de entidades duplicadas
- `docs/database/RELATIONSHIPS.md` - Diagrama de relaciones actualizado

---

*ADR creado: 2025-12-07*
