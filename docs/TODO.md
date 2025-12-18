# TODO - Implementación ADRs

**Última actualización**: 2025-12-18
**Deadline**: ~15 febrero 2026
**Contexto**: Larafactu v1.0 - Staging Pre-Producción

---

## 📊 Estado General

| ADR | Título | Estado | Progreso |
|-----|--------|--------|----------|
| [ADR-001](./ADR_001_REFACTORING_FISCAL_SETTINGS.md) | Refactorización Fiscal Settings | ⚠️ PARCIAL | 90% |
| [ADR-002](./ADR_002_UUID_V7_CONSOLIDATION.md) | UUID v7 String | ✅ COMPLETADO | 95% |
| [ADR-003](./ADR_003_USER_CUSTOMER_UNIFICATION.md) | Unificación Users/Customers | ✅ COMPLETADO | 100% |

---

## 🎯 Prioridades Inmediatas

### 🔴 Crítico - Esta Semana

- [x] **ADR-003**: Fase 1 - UserTaxProfile reemplaza CustomerFiscalData ✅ 2025-12-16
- [x] **ADR-003**: Fase 2 - Eliminar tabla customers, unificar en users ✅ 2025-12-16
- [x] **Tests**: Suite completa pasando (13/13) ✅ 2025-12-16
- [x] **ADR-001**: Implementar snapshot fiscal automático en Invoice ✅ 2025-12-16

### 🟡 Alta - Próximas 2 Semanas

- [x] **ADR-001**: Gestión de cambios fiscales (cerrar config anterior) ✅ 2025-12-18 - Implementado en model boot()
- [x] **Tests**: Crear tests para temporalidad fiscal ✅ 2025-12-16
- [x] **ADR-001**: FiscalIntegrityChecker para detectar configs duplicadas ✅ 2025-12-18
- [ ] **Docs**: Actualizar ADR-003 con Fase 2 completada

### 🟢 Media - Próximo Mes

- [ ] **ADR-001**: Implementar gestión de proformas con cambio fiscal
- [ ] **Filament**: UserResource con gestión de delegados
- [ ] **Docs**: Actualizar documentación de API
- [ ] **Seeding**: Crear seeders de producción

---

## 📋 ADR-001: Refactorización Fiscal Settings

### ✅ Completado

- [x] Crear modelo `CompanyFiscalConfig`
- [x] Crear migración `company_fiscal_configs`
- [x] Implementar temporalidad (`valid_from`, `valid_until`)
- [x] Crear factory y seeder
- [x] Tests básicos de CompanyFiscalConfig
- [x] **Implementar snapshot fiscal automático en Invoice** ✅ 2025-12-16
  - [x] Capturar `CompanyFiscalConfig` vigente en `invoice_date`
  - [x] Capturar `UserTaxProfile` vigente en `invoice_date`
  - [x] Guardar snapshots inmutables (encrypted)
  - [x] Métodos: `generateIssuerSnapshot()`, `generateBillableUserSnapshot()`, `generateFiscalContextSnapshot()`
  - [x] Helpers: `hasEncryptedSnapshots()`, `hasFiscalSnapshots()`, `regenerateEncryptedSnapshots()`
  - [x] Auto-generación en `boot::creating`
- [x] Gestión de cambios fiscales (en model boot)
  - [x] Al crear nueva config, cerrar anterior (`valid_until = hoy - 1`)
  - [x] Método `closeActiveConfig()` en CompanyFiscalConfig
  - [x] Método `closeActiveForUser()` en UserTaxProfile

### 🚧 En Progreso

- [ ] Gestión de proformas con cambio fiscal
  - [ ] Actualizar proformas pendientes antes de conversión
  - [ ] Validar que solo hay UNA config activa

### ⏳ Pendiente

- [ ] Documentar API de CompanyFiscalConfig
- [x] Tests de edge cases (múltiples configs activas - validación) ✅ 2025-12-18
  - [x] FiscalIntegrityChecker service (32 tests)
  - [x] FiscalIntegrityBanner widget en larabill-filament
  - [x] FiscalIntegrityAlert notification
- [ ] Filament Resource para gestión de configs históricas

---

## 📋 ADR-002: UUID v7 String

### ✅ Completado

- [x] Migrar de UUID binary(16) a UUID string char(36)
- [x] Actualizar migración `users` table
- [x] Actualizar migración `sessions` table (FK constraint)
- [x] Eliminar dependencia `dyrynda/laravel-model-uuid`
- [x] Actualizar modelos para usar `Str::orderedUuid()`
- [x] Validar compatibilidad con FilamentPHP v4
- [x] User model con UUID v7
- [x] Invoice model con UUID v7

### ⏳ Pendiente (menor prioridad)

- [ ] Documentar estrategia UUID en README
- [ ] Tests de performance con UUID v7
- [ ] Validar ordenamiento cronológico en queries

---

## 📋 ADR-003: Unificación Users/Customers

### ✅ Fase 1 Completada (2025-12-16)

- [x] Crear modelo `UserTaxProfile` en larabill
- [x] Crear migración `user_tax_profiles`
- [x] Eliminar modelo `CustomerFiscalData`
- [x] Eliminar factory `CustomerFiscalDataFactory`
- [x] Actualizar `Invoice` model para usar `user_tax_profile_id`
- [x] Actualizar `InvoiceService` para usar `UserTaxProfile`
- [x] Actualizar `VatVerification` relación → `userTaxProfiles()`
- [x] Actualizar `ModelMappingService` → `user_tax_profile`
- [x] Corregir `LegalEntityTypesSeeder` (JSON translatable)
- [x] Tests de Invoice pasando

### ✅ Fase 2 Completada (2025-12-16)

- [x] Crear migración `add_billing_fields_to_users_table`
  - [x] `parent_user_id` (UUID, self-reference)
  - [x] `relationship_type` (unsignedTinyInteger → enum)
  - [x] `display_name` (nullable)
  - [x] `legal_entity_type_code` (FK)
- [x] Actualizar User model con relaciones:
  - [x] `parentUser()` - Usuario padre (si es delegado)
  - [x] `delegatedUsers()` - Usuarios delegados (hijos)
  - [x] `legalEntityType()` - Tipo de entidad legal
  - [x] `taxProfiles()` - Histórico fiscal
  - [x] `currentTaxProfile()` - Perfil fiscal activo
- [x] Helper methods: `isDirect()`, `isDelegated()`, `billableName()`, `hasDelegatedUsers()`
- [x] Actualizar UserFactory con estados `delegatedOf()`, `direct()`, `withDisplayName()`
- [x] Modificar Invoice migration: `customer_id` → `billable_user_id`
- [x] Actualizar Invoice model: `customer()` → `billableUser()`
- [x] Refactorizar InvoiceService sin Customer
- [x] Actualizar InvoiceFactory
- [x] Actualizar InvoiceResource (Filament)
- [x] Eliminar Customer model, factory, resource, migration
- [x] Actualizar LegalEntityType: `customers()` → `users()`
- [x] Actualizar ModelMappingService: eliminar 'customer'
- [x] Actualizar LarabillFilamentPlugin: eliminar CustomerResource
- [x] Actualizar tests SpanishB2CInvoiceTest
- [x] Validar con `larafactu:install --local --fresh`
- [x] Tests pasando (11/11)

### Archivos Eliminados (Fase 2)

```
packages/aichadigital/larabill/src/Models/Customer.php
packages/aichadigital/larabill/src/Database/Factories/CustomerFactory.php
packages/aichadigital/larabill/database/migrations/2025_01_25_000004_create_customers_table.php
packages/aichadigital/larabill-filament/src/Resources/CustomerResource.php
packages/aichadigital/larabill-filament/src/Resources/CustomerResource/
```

---

## 🧪 Testing

### ✅ Tests Pasando (13/13)

- [x] AdminAccessTest (8/8)
- [x] SpanishB2CInvoiceTest (5/5) - Actualizado para ADR-001 + ADR-003
  - [x] Create invoice for spanish B2C customer
  - [x] Calculates correct VAT for multiple items
  - [x] Validates Spanish DNI format
  - [x] **Generates encrypted fiscal snapshots on creation (ADR-001)** ✅ 2025-12-16
  - [x] **Creates fiscal snapshots with temporal validity (ADR-001)** ✅ 2025-12-16

### 🚧 Tests Pendientes

- [x] CompanyFiscalConfig temporalidad - Cubierto en tests existentes
- [x] Invoice snapshot fiscal ✅ 2025-12-16
- [x] FiscalIntegrityChecker (32 tests) ✅ 2025-12-18
- [ ] User relaciones (parent/delegated) - tests adicionales
- [ ] UserTaxProfile histórico - tests edge cases
- [ ] Edge cases:
  - [ ] Cambio fiscal durante período de facturación
  - [x] Múltiples configs activas (validación) ✅ 2025-12-18
  - [ ] Proformas con cambio fiscal

---

## 📦 Paquetes (larabill)

### Estado Actual

- **Versión**: dev-main
- **Tests**: Pasando en larafactu (13/13)
- **ADR-003**: Fase 1 + Fase 2 completadas
- **ADR-001**: Snapshot fiscal automático implementado

### Próximos Pasos

1. [ ] Crear tag `v0.6.0-alpha` (ADR-003 completado)
2. [ ] Actualizar CHANGELOG.md
3. [ ] Documentar breaking changes (Customer → User)
4. [ ] Preparar v1.0.0 para febrero 2026

---

## 🎯 Roadmap v1.0.0 (15 Feb 2026)

### Diciembre 2025

- [x] ADR-003: CustomerFiscalData → UserTaxProfile ✅
- [x] ADR-003: Eliminar tabla customers ✅
- [x] ADR-001: Snapshot fiscal en Invoice ✅ 2025-12-16
- [x] Tests de integración adicionales (13/13) ✅ 2025-12-16

### Enero 2026

- [ ] ADR-001: Gestión completa de cambios fiscales
- [ ] Filament Resources actualizados (UserResource delegados)
- [ ] Documentación completa
- [ ] Seeders de producción

### Febrero 2026

- [ ] Testing exhaustivo
- [ ] Code review final
- [ ] Deploy a producción
- [ ] **v1.0.0 Release**

---

## 📝 Notas

### Decisiones Técnicas

- **UUID v7 string**: Elegido por compatibilidad FilamentPHP v4
- **UserTaxProfile**: Nombre más claro que CustomerFiscalData
- **Temporalidad**: `valid_from`/`valid_until` para inmutabilidad fiscal
- **JSON translatable**: Spatie para legal entity types
- **billable_user_id**: Reemplaza customer_id en invoices (ADR-003)
- **UserRelationshipType**: DIRECT (0) / DELEGATED (1) enum

### Arquitectura Final (ADR-003)

```
┌─────────────────────────────────────────────────────────────────┐
│  users                                                          │
│  - id (UUID v7 string)                                          │
│  - parent_user_id (nullable) → FK self-reference                │
│  - relationship_type (PHP Enum → unsignedTinyInteger)           │
│  - display_name, legal_entity_type_code                         │
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
└─────────────────────────────────────────────────────────────────┘
```

### Riesgos Identificados

1. ~~**Migración customers → users**~~: ✅ Completado (no había datos legacy)
2. ~~**Snapshot fiscal**~~: ✅ Implementado - inmutable y encrypted (AES-256-CBC)
3. **Tests**: Cobertura de edge cases temporales crítica
4. **Performance**: Validar con 100k+ facturas

### Recursos

- [Documentación Laravel 12](https://laravel.com/docs/12.x)
- [FilamentPHP v4](https://filamentphp.com/docs/4.x)
- [Spatie Translatable](https://github.com/spatie/laravel-translatable)

---

**Mantenido por**: @abkrim
**Última revisión**: 2025-12-18
**Próxima revisión**: 2025-12-25

