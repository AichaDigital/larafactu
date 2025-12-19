# ADR-003: Unificación Users/Customers

> **Estado**: ✅ COMPLETADO (Fase 1 ✅ | Fase 2 ✅)
> **Fecha**: 2025-12-08
> **Actualizado**: 2025-12-19
> **Deadline**: ~15 febrero 2026
> **Ubicación canónica**: `packages/aichadigital/larabill/docs/ADR-003-user-customer-unification.md`

---

## Resumen

Este documento referencia el ADR-003 ubicado en el paquete larabill, que define la arquitectura unificada de usuarios y clientes.

**Ver documento completo**: [ADR-003 en larabill](../packages/aichadigital/larabill/docs/ADR-003-user-customer-unification.md)

## Cambios clave

### Estado de implementación (2025-12-16)

| Componente | Estado | Notas |
|------------|--------|-------|
| `UserTaxProfile` modelo | ✅ Completado | En larabill |
| `UserRelationshipType` enum | ✅ Completado | En larabill |
| `CustomerFiscalData` modelo | ✅ Eliminado | Ya no existe |
| `CustomerFiscalDataFactory` | ✅ Eliminado | Ya no existe |
| `Invoice` → `user_tax_profile_id` | ✅ Completado | FK actualizada |
| `InvoiceService` | ✅ Completado | Usa UserTaxProfile |
| `VatVerification` relación | ✅ Completado | `userTaxProfiles()` |
| `Customer.currentTaxProfile()` | ✅ Completado | Relación añadida |
| `CustomerFactory` | ✅ Eliminado | Fase 2: unificado en User |
| Tests Invoice | ✅ Pasando | 13/13 tests |
| `customers` tabla | ✅ Eliminado | Fase 2: unificado en users |
| `parent_user_id` en users | ✅ Completado | Fase 2: self-reference |
| `relationship_type` en users | ✅ Completado | DIRECT(0) / DELEGATED(1) |
| `display_name` en users | ✅ Completado | Nombre comercial |
| `legal_entity_type_code` en users | ✅ Completado | FK a legal_entity_types |
| `billable_user_id` en invoices | ✅ Completado | Reemplaza customer_id |
| User model relaciones | ✅ Completado | parentUser, delegatedUsers, taxProfiles |
| UserFactory estados | ✅ Completado | delegatedOf(), direct(), withDisplayName() |
| Filament UserResource | ✅ Completado | Con TaxProfilesRelationManager |

### Entidades eliminadas (Fase 1 + Fase 2)

| Tabla/Modelo | Razón | Estado |
|--------------|-------|--------|
| `customers` | Unificado en `users` con `parent_user_id` | ✅ Eliminado |
| `Customer` modelo | Unificado en `User` | ✅ Eliminado |
| `CustomerFactory` | Unificado en `UserFactory` | ✅ Eliminado |
| `CustomerResource` (Filament) | Unificado en `UserResource` | ✅ Eliminado |
| `customer_fiscal_data` | Reemplazado por `user_tax_profiles` | ✅ Eliminado |
| `CustomerFiscalData` modelo | Reemplazado por `UserTaxProfile` | ✅ Eliminado |
| `CustomerFiscalDataFactory` | Reemplazado | ✅ Eliminado |
| `customer_tax_profiles` | Duplicaba funcionalidad | ✅ Eliminado |
| `issuer_config` | Reemplazado por `company_fiscal_configs` | ✅ Eliminado |
| `issuer_tax_profiles` | Reemplazado por `company_fiscal_configs` | ✅ Eliminado |

### Arquitectura final

```
┌─────────────────────────────────────────────────────────────────┐
│  users                                                          │
│  - id (UUID v7 string)                                          │
│  - parent_user_id (nullable) → FK self-reference                │
│  - relationship_type (PHP Enum → unsignedTinyInteger)           │
│  - display_name (nullable) → Nombre comercial                   │
│  - legal_entity_type_code (nullable) → FK legal_entity_types    │
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
                        │
                        │ N:1
                        ▼
┌─────────────────────────────────────────────────────────────────┐
│  invoices                                                       │
│  - user_id → FK users.id (owner/issuer)                         │
│  - billable_user_id → FK users.id (user being billed)           │
│  - user_tax_profile_id → FK user_tax_profiles.id (snapshot)     │
│  - company_fiscal_config_id → FK company_fiscal_configs.id      │
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
*Actualizado: 2025-12-16 - Fase 1 completada (UserTaxProfile, CustomerFiscalData eliminado)*
*Actualizado: 2025-12-19 - Fase 2 completada (Customer eliminado, unificado en User)*
