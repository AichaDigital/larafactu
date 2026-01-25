---
status: VIGENTE
date: 2026-01-02
---

# ADR-006: Consolidación y Estado Actual del Proyecto

**Fecha**: 2026-01-02
**Actualizado**: 2026-01-07
**Contexto**: Larafactu - Consolidacion post-refactor
**Objetivo**: Documento unico de referencia para el estado real del proyecto

---

## Proposito

Este ADR consolida el estado real del proyecto tras los refactors de ADR-001 a ADR-005. Los ADRs anteriores contienen informacion historica pero sus checkboxes y planes estan desactualizados. Este documento es la fuente de verdad actual.

---

## Estado de ADRs Anteriores

| ADR | Titulo | Estado | Notas |
|-----|--------|--------|-------|
| ADR-001 | CompanyFiscalConfig | ✅ COMPLETADO | Modelo funcionando |
| ADR-002 | UUID v7 | ✅ COMPLETADO | Implementado en todo el sistema |
| ADR-003 | Users/Customers Unification | ⚠️ PARCIALMENTE SUPERSEDED | `relationship_type` deprecated por ADR-004 |
| ADR-004 | Authorization | ✅ COMPLETADO (Fases 1-4) | Fase 5 (api_credentials) aplazada |
| ADR-005 | Filament Deprecation | ✅ COMPLETADO | Stack DaisyUI funcionando |

---

## Arquitectura Actual (Enero 2026)

### Modelo de Datos Principal

```
users
  - id (UUID v7 string)
  - parent_user_id (self-reference, nullable)
  - relationship_type (unsignedTinyInteger) ⚠️ DEPRECATED por user_type
  - user_type (unsignedTinyInteger) 🆕 STAFF(0)/CUSTOMER(1)/DELEGATE(2)
  - current_tax_profile_id (FK -> user_tax_profiles.id, nullable)
  - display_name, legal_entity_type_code
  - is_active (boolean) 🆕 Estado de cuenta
  - suspended_at (timestamp) 🆕 Fecha suspensión
  - is_superadmin (boolean) 🆕 Flag superadmin
  - avatar_path (varchar) 🆕 Avatar personalizado

user_tax_profiles
  - id (bigint)
  - owner_user_id (UUID) <- RENOMBRADO de user_id (ADR-004)
  - fiscal_name, tax_id, address, city, state, zip_code, country_code
  - is_company, is_eu_vat_registered, is_exempt_vat
  - valid_from, valid_until (temporalidad)
  - is_active

user_preferences
  - user_id (UUID)
  - locale, theme, timezone
  - notifications (JSON)

user_department_access
  - user_id (UUID)
  - department_id (FK -> departments.id de laratickets)
  - access_level (unsignedTinyInteger)
  - granted_by, granted_at, expires_at

company_fiscal_configs
  - Configuracion fiscal del emisor (empresa)
  - Temporalidad con valid_from/valid_until
```

### Stack Tecnologico

- **Backend**: Laravel 12 + Livewire 3
- **Frontend**: TailwindCSS 4 + DaisyUI 5 + Alpine.js
- **Temas**: cupcake (light), abyss (dark), corporate, sunset
- **Testing**: Pest + Pest Browser (Playwright)
- **BD**: MySQL (SQLite para tests)

### Componentes Implementados

**Livewire (app/Livewire/)**:

- Auth: Login, Register, ForgotPassword, ResetPassword
- Invoices: InvoiceList, InvoiceCreate, InvoiceEdit, InvoiceShow
- Customers: CustomerList, CustomerCreate, CustomerEdit
- Articles: ArticleList, ArticleCreate, ArticleEdit
- Profile: ProfileEdit
- ThemeSelector

**UI Components (resources/views/components/)**:

- ui/: button, card, alert, badge, modal, table, stats, stat, loading, empty-state (10)
- forms/: input, select, textarea, checkbox, toggle, label (6)
- layouts/: app, guest, sidebar

**Enums (app/Enums/)**:

- UserType: STAFF(0), CUSTOMER(1), DELEGATE(2)
- AccessLevel: FULL(0), WRITE(1), READ(2), NONE(3)

**Policies (app/Policies/)**:

- InvoicePolicy
- UserTaxProfilePolicy
- ArticlePolicy

**Middleware (app/Http/Middleware/)**:

- ApplyUserPreferences
- EnsureUserIsAdmin

---

## Funcionalidades Completadas

### Gestion de Facturas

- [x] Listado con filtros (ano, serie, estado, busqueda)
- [x] Creacion con cliente y lineas dinamicas
- [x] Edicion (solo borradores)
- [x] Vista detallada con items y resumen fiscal

### Gestion de Clientes

- [x] Listado con filtros (tipo, busqueda)
- [x] Creacion con UserTaxProfile
- [x] Edicion con versionado de perfiles fiscales

### Gestion de Articulos

- [x] Listado con filtros
- [x] Creacion/edicion con nombres translatables

### Autenticacion

- [x] Login con remember me
- [x] Registro con validacion
- [x] Recuperacion de contrasena
- [x] Perfil de usuario (datos, contrasena, preferencias)

### Sistema de Temas

- [x] Theme switcher persistente
- [x] API endpoint /api/theme
- [x] Preferencias en user_preferences

### Panel Admin (basico)

- [x] Middleware EnsureUserIsAdmin
- [x] Rutas protegidas /admin/*
- [x] Dashboard basico
- [x] Listado de usuarios basico

### Testing

- [x] Tests Feature para rutas
- [x] Tests Browser con Playwright (welcome, auth pages)
- [x] CI configurado (excluye browser tests)

---

## Funcionalidades Pendientes

### Autorizacion Completa (ADR-004 en implementación)

- [x] Tabla user_customer_access (permisos delegados) ✅
- [x] Tabla user_department_access (permisos staff) ✅
- [ ] Columnas user_type, is_active, suspended_at, is_superadmin en users
- [ ] Tabla api_credentials (API keys externas)
- [x] Sistema de impersonation (staff ve como cliente) ✅ ImpersonationService
- [ ] Gates completos en AuthServiceProvider
- [ ] Tests de autorizacion
- [ ] UserPolicy completa

### Panel Admin Completo

- [ ] CRUD completo de usuarios con roles
- [ ] Asignacion de permisos por departamento
- [ ] Gestion de delegados
- [ ] Dashboard con estadisticas reales
- [ ] Logs de actividad

### Facturacion Avanzada

- [ ] Generacion de PDF
- [ ] Envio por email
- [ ] Series de facturacion configurables
- [ ] Facturas rectificativas
- [ ] Integracion Verifactu (AEAT)

### Integraciones

- [ ] API REST documentada
- [ ] Webhooks
- [ ] Integracion contabilidad (Holded, etc)

### Tickets (laratickets)

- [ ] UI de tickets en larafactu
- [ ] Asignacion por departamentos
- [ ] Escalado segun AccessLevel

---

## Proximos Pasos Recomendados

### Prioridad Alta

1. Completar user_customer_access para delegados
2. Panel Admin funcional con gestion de usuarios
3. Generacion de PDF de facturas

### Prioridad Media

4. Sistema de impersonation
5. API REST basica
6. Tests de autorizacion

### Prioridad Baja

7. Integracion Verifactu
8. UI de tickets
9. Integraciones externas

---

## Notas Tecnicas

### Relacion owner_user_id

La columna `user_tax_profiles.owner_user_id` (antes `user_id`) indica quien puede editar el perfil fiscal. Multiples usuarios pueden compartir el mismo perfil via `users.current_tax_profile_id`.

### Paquetes Agnosticos

Los paquetes (larabill, laratickets, etc.) NO implementan autorizacion. Solo exponen modelos y servicios. La autorizacion se implementa en larafactu usando Policies y Gates nativos de Laravel.

### MigrationHelper

Usar `AichaDigital\Larabill\Support\MigrationHelper::userIdColumn()` para crear columnas FK a users, garantizando compatibilidad con UUID.

---

*ADR creado: 2026-01-02*
*Autor: @abkrim con asistencia Claude*
