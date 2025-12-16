# TODO - Implementación ADRs

**Última actualización**: 2025-12-16  
**Deadline**: ~15 febrero 2026  
**Contexto**: Larafactu v1.0 - Staging Pre-Producción

---

## 📊 Estado General

| ADR | Título | Estado | Progreso |
|-----|--------|--------|----------|
| [ADR-001](./ADR_001_REFACTORING_FISCAL_SETTINGS.md) | Refactorización Fiscal Settings | ⚠️ PARCIAL | 60% |
| [ADR-002](./ADR_002_UUID_V7_CONSOLIDATION.md) | UUID v7 String | ⚠️ PARCIAL | 80% |
| [ADR-003](./ADR_003_USER_CUSTOMER_UNIFICATION.md) | Unificación Users/Customers | 🚧 EN PROGRESO | 60% |

---

## 🎯 Prioridades Inmediatas

### 🔴 Crítico - Esta Semana

- [x] **ADR-003**: Actualizar estado en ADR-003 (CustomerFiscalData → UserTaxProfile completado) ✅ 2025-12-16
- [x] **Tests**: Ejecutar suite completa de tests en larafactu (11 passed) ✅ 2025-12-16
- [x] **Composer**: `composer update aichadigital/*` para actualizar paquetes locales ✅ 2025-12-16
- [x] **Validación**: Verificar que Filament Resources funcionan correctamente ✅ 2025-12-16

### 🟡 Alta - Próximas 2 Semanas

- [ ] **ADR-003**: Eliminar tabla `customers` (unificar en `users`)
- [ ] **ADR-003**: Implementar `parent_user_id` en `users` (self-reference)
- [ ] **ADR-003**: Crear enum `UserRelationshipType` (DIRECT, DELEGATED)
- [ ] **ADR-001**: Implementar lógica de snapshot fiscal en Invoice
- [ ] **Tests**: Crear tests para temporalidad fiscal

### 🟢 Media - Próximo Mes

- [ ] **ADR-001**: Implementar gestión de proformas con cambio fiscal
- [ ] **Filament**: Actualizar Resources para nueva arquitectura
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

### 🚧 En Progreso

- [ ] Implementar snapshot fiscal automático en Invoice
  - [ ] Capturar `CompanyFiscalConfig` vigente en `invoice_date`
  - [ ] Capturar `UserTaxProfile` vigente en `invoice_date`
  - [ ] Guardar snapshots inmutables (encrypted)
- [ ] Gestión de cambios fiscales
  - [ ] Al crear nueva config, cerrar anterior (`valid_until = hoy`)
  - [ ] Actualizar proformas pendientes antes de conversión
  - [ ] Validar que solo hay UNA config activa

### ⏳ Pendiente

- [ ] Documentar API de CompanyFiscalConfig
- [ ] Tests de edge cases (múltiples configs, cambios retroactivos)
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

### 🚧 En Progreso

- [ ] Actualizar todos los modelos que usan UUID
  - [x] User
  - [x] Invoice
  - [ ] Ticket (si existe)
  - [ ] Otros modelos pendientes

### ⏳ Pendiente

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
- [x] Eliminar test `CustomerFiscalDataTest`
- [x] Actualizar `Invoice` model para usar `user_tax_profile_id`
- [x] Actualizar `InvoiceService` para usar `UserTaxProfile`
- [x] Actualizar `VatVerification` relación → `userTaxProfiles()`
- [x] Actualizar `ModelMappingService` → `user_tax_profile`
- [x] Añadir `Customer.currentTaxProfile()` relación
- [x] Actualizar `CustomerFactory` para auto-crear `UserTaxProfile`
- [x] Corregir `LegalEntityTypesSeeder` (JSON translatable)
- [x] Actualizar códigos legal entity types (INDIVIDUAL, LIMITED_COMPANY)
- [x] Tests de Invoice pasando (3/3)
- [x] Verificar Filament Resources funcionan (2025-12-16)
- [x] Actualizar documentación ADR-003 (2025-12-16)

### 🚧 Fase 2 - Eliminar tabla customers

- [ ] **Eliminar tabla `customers`**
  - [ ] Analizar dependencias de Customer model
  - [ ] Migrar datos de `customers` a `users`
  - [ ] Implementar `parent_user_id` en users
  - [ ] Crear enum `RelationshipType` (CLIENT, SELF, SELF_COMPANY)
  - [ ] Actualizar Invoice para usar `user_id` en lugar de `customer_id`
  - [ ] Eliminar modelo Customer
  - [ ] Eliminar migración de customers

- [ ] **Implementar arquitectura User unificada**
  - [ ] Añadir columna `parent_user_id` a users (self-reference)
  - [ ] Crear enum `UserRelationshipType` (DIRECT, DELEGATED)
  - [ ] Actualizar User model con relaciones:
    - [ ] `parent()` - Usuario padre (si es delegado)
    - [ ] `delegatedUsers()` - Usuarios delegados (hijos)
    - [ ] `taxProfiles()` - Histórico fiscal
    - [ ] `currentTaxProfile()` - Perfil fiscal activo
  - [ ] Actualizar UserFactory
  - [ ] Tests de relaciones User

### ⏳ Pendiente

- [ ] Migración de datos existentes
  - [ ] Script de migración `customers` → `users`
  - [ ] Validar integridad referencial
  - [ ] Backup antes de migración
- [ ] Actualizar Filament Resources
  - [ ] UserResource con gestión de delegados
  - [ ] UserTaxProfileResource (histórico)
  - [ ] Eliminar CustomerResource
- [ ] Documentación
  - [ ] Guía de migración para usuarios
  - [ ] API de UserTaxProfile
  - [ ] Ejemplos de uso

---

## 🧪 Testing

### ✅ Tests Pasando

- [x] SpanishB2CInvoiceTest (3/3)
- [x] UserTaxProfileTest (básico)

### 🚧 Tests Pendientes

- [ ] CompanyFiscalConfig temporalidad
- [ ] Invoice snapshot fiscal
- [ ] User relaciones (parent/delegated)
- [ ] UserTaxProfile histórico
- [ ] Migración customers → users
- [ ] Edge cases:
  - [ ] Cambio fiscal durante período de facturación
  - [ ] Múltiples configs activas (validación)
  - [ ] Proformas con cambio fiscal

---

## 📦 Paquetes (larabill)

### Estado Actual

- **Versión**: dev-main
- **Último commit**: `5b54f8e` (ADR-003 unification)
- **Tests**: Pasando en larafactu

### Próximos Pasos

1. [ ] Crear tag `v0.5.0-alpha` (ADR-003 completado parcialmente)
2. [ ] Actualizar CHANGELOG.md
3. [ ] Documentar breaking changes
4. [ ] Preparar v1.0.0 para febrero 2026

---

## 🎯 Roadmap v1.0.0 (15 Feb 2026)

### Diciembre 2025

- [x] ADR-003: CustomerFiscalData → UserTaxProfile ✅
- [ ] ADR-003: Eliminar tabla customers
- [ ] ADR-001: Snapshot fiscal en Invoice
- [ ] Tests de integración

### Enero 2026

- [ ] ADR-003: Arquitectura User unificada completa
- [ ] Filament Resources actualizados
- [ ] Migración de datos staging → producción
- [ ] Documentación completa

### Febrero 2026

- [ ] Testing exhaustivo
- [ ] Code review final
- [ ] Deploy a producción
- [ ] **v1.0.0 Release** 🎉

---

## 📝 Notas

### Decisiones Técnicas

- **UUID v7 string**: Elegido por compatibilidad FilamentPHP v4
- **UserTaxProfile**: Nombre más claro que CustomerFiscalData
- **Temporalidad**: `valid_from`/`valid_until` para inmutabilidad fiscal
- **JSON translatable**: Spatie para legal entity types

### Riesgos Identificados

1. **Migración customers → users**: Requiere planificación cuidadosa
2. **Snapshot fiscal**: Debe ser inmutable y encrypted
3. **Tests**: Cobertura de edge cases temporales crítica
4. **Performance**: Validar con 100k+ facturas

### Recursos

- [Documentación Laravel 12](https://laravel.com/docs/12.x)
- [FilamentPHP v4](https://filamentphp.com/docs/4.x)
- [Spatie Translatable](https://github.com/spatie/laravel-translatable)
- [ADRs completos](./docs/)

---

**Mantenido por**: @abkrim
**Última revisión**: 2025-12-16
**Próxima revisión**: 2025-12-23

