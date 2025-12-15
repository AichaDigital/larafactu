# ADR-003: Unificación Users/Customers

> **Estado**: 🚧 EN IMPLEMENTACIÓN
> **Fecha**: 2025-12-08
> **Actualizado**: 2025-12-15
> **Deadline**: ~15 febrero 2026
> **Ubicación canónica**: `packages/aichadigital/larabill/docs/ADR-003-user-customer-unification.md`

---

## Resumen

Este documento referencia el ADR-003 ubicado en el paquete larabill, que define la arquitectura unificada de usuarios y clientes.

**Ver documento completo**: [ADR-003 en larabill](../packages/aichadigital/larabill/docs/ADR-003-user-customer-unification.md)

## Cambios clave

### Estado de implementación (2025-12-15)

| Componente | Estado | Notas |
|------------|--------|-------|
| `UserTaxProfile` modelo | ✅ Creado | En larabill |
| `UserRelationshipType` enum | ✅ Creado | En larabill |
| `CustomerFiscalData` modelo | ⏳ Pendiente eliminar | Invoice aún lo usa |
| `Invoice` → `user_tax_profile_id` | ⏳ Pendiente | Renombrar FK |
| `customers` tabla | ⏳ Pendiente eliminar | Unificar en users |

### Entidades a eliminar (en progreso)

| Tabla | Razón | Estado |
|-------|-------|--------|
| `customers` | Unificado en `users` con `parent_user_id` | ⏳ Pendiente |
| `customer_fiscal_data` | Renombrado a `user_tax_profiles` | ⏳ Pendiente |
| `customer_tax_profiles` | Duplicaba funcionalidad | ✅ Eliminado |
| `issuer_config` | Reemplazado por `company_fiscal_configs` | ✅ Eliminado |
| `issuer_tax_profiles` | Reemplazado por `company_fiscal_configs` | ✅ Eliminado |

### Arquitectura actual

```
┌─────────────────────────────────────────────────────────────────┐
│  users                                                          │
│  - id (UUID v7 string)                                          │
│  - parent_user_id (nullable) → FK self-reference                │
│  - relationship_type (PHP Enum → unsignedTinyInteger)           │
│                                                                 │
│  parent_user_id = NULL   → Cliente directo de la Empresa        │
│  parent_user_id = X      → Cliente del User X (delegado)        │
└─────────────────────────────────────────────────────────────────┘
                        │
                        │ 1:N
                        ▼
┌─────────────────────────────────────────────────────────────────┐
│  user_tax_profiles (histórico fiscal)                           │
│  - user_id → FK users.id                                        │
│  - fiscal_name, tax_id, address, country_code...                │
│  - is_company, is_eu_vat_registered, is_exempt_vat              │
│  - valid_from / valid_until (temporalidad)                      │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│  company_fiscal_configs (emisor único)                          │
│  - Configuración fiscal del tenedor del software                │
│  - Sin cambios respecto a ADR-001                               │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│  legal_entity_types (catálogo)                                  │
│  - Tipos de entidad jurídica (S.L., S.A., Autónomo...)          │
│  - Translatable (spatie/laravel-translatable)                   │
│  - Sin columna is_company (el tipo ya lo define)                │
└─────────────────────────────────────────────────────────────────┘
```

### PHP Enum: UserRelationshipType

```php
enum UserRelationshipType: int implements HasLabel, HasColor, HasIcon
{
    case DIRECT = 0;      // Cliente directo de la Empresa
    case DELEGATED = 1;   // Cliente de un User (facturación delegada)
}
```

## Documentos relacionados

- [ADR-001](./ADR_001_REFACTORING_FISCAL_SETTINGS.md) - CompanyFiscalConfig (vigente)
- [ADR-002](./ADR_002_UUID_V7_CONSOLIDATION.md) - UUID v7 string (vigente)
- [ADR-003 completo](../packages/aichadigital/larabill/docs/ADR-003-user-customer-unification.md) - Documento canónico

---

*Documento de referencia creado: 2025-12-08*
*Actualizado: 2025-12-15 - Estado de implementación añadido*
