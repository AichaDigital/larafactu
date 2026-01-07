# ADR-004: Sistema de Autorizacion y Permisos

**Estado**: ✅ APROBADO - EN IMPLEMENTACIÓN
**Fecha**: 2026-01-01
**Actualizado**: 2026-01-07
**Contexto**: Larafactu + Ecosistema AichaDigital
**Deadline**: ~15 febrero 2026
**Impacto**: CRITICO - Cambio arquitectonico en modelo de usuarios
**Aprobado por**: @abkrim (2026-01-07)

---

## 📊 Estado de Implementación (2026-01-07)

| Componente | Estado | Notas |
|------------|--------|-------|
| **Enums** | | |
| `AccessLevel` enum | ✅ Completado | `app/Enums/AccessLevel.php` |
| `UserType` enum | ✅ Completado | `app/Enums/UserType.php` |
| **Tablas** | | |
| `user_department_access` | ✅ Completado | Migración + Modelo |
| `user_customer_access` | ✅ Completado | Migración + Modelo |
| `api_credentials` | ⏸️ Aplazado | Para integraciones externas (Fase 5) |
| **Columnas users** | | |
| `current_tax_profile_id` | ✅ Completado | FK a user_tax_profiles |
| `user_type` | ✅ Completado | STAFF/CUSTOMER/DELEGATE |
| `is_active` | ✅ Completado | Estado de cuenta |
| `suspended_at` | ✅ Completado | Fecha suspensión |
| `is_superadmin` | ✅ Completado | Flag superadmin |
| `avatar_path` | ✅ Completado | Avatar personalizado |
| **Modelos** | | |
| `UserDepartmentAccess` | ✅ Completado | `app/Models/` |
| `UserCustomerAccess` | ✅ Completado | `app/Models/` |
| Relaciones en `User` | ✅ Completado | +métodos isStaff/isCustomer/isDelegate |
| **Policies** | | |
| `InvoicePolicy` | ✅ Completado | |
| `UserTaxProfilePolicy` | ✅ Completado | |
| `UserCustomerAccessPolicy` | ✅ Completado | |
| `ArticlePolicy` | ✅ Completado | |
| `UserPolicy` | ✅ Completado | Nuevo - 11 métodos |
| **Gates** | | |
| `access-admin` | ✅ Completado | AppServiceProvider |
| `impersonate-users` | ✅ Completado | AppServiceProvider |
| `manage-delegates` | ✅ Completado | AppServiceProvider |
| `view-invoices` | ✅ Completado | AppServiceProvider |
| **Middleware** | | |
| `EnsureUserIsAdmin` | ✅ Completado | Usa canAccessAdmin() |
| **Tests** | | |
| `UserTypeAuthorizationTest` | ✅ Completado | 22 tests |
| `UserPolicyTest` | ✅ Completado | 34 tests |
| **Deprecations** | | |
| `relationship_type` | ⚠️ Deprecated | Reemplazado por user_type |
| `UserRelationshipType` (larabill) | ⚠️ Deprecated | Usar UserType de app |
| `isAdmin()` método | ⚠️ Deprecated | Usar canAccessAdmin() |

---

## Contexto

### Problema Actual

1. **Sin diferenciacion de tipos de usuario**: No existe distincion entre staff (empleados), clientes y delegados
2. **Sin sistema de permisos**: No hay control de acceso granular por departamentos o niveles
3. **Relacion UserTaxProfile incorrecta**: Actualmente un perfil fiscal pertenece a un usuario, pero una persona real puede tener multiples cuentas de usuario

### Modelo Mental Correcto

```
UNA PERSONA REAL (Juan) = MULTIPLES CUENTAS DE USUARIO (emails diferentes)
                        = UNA IDENTIDAD FISCAL (compartida)

juan_customer@email.com  -> Usuario cliente
juan_staff@email.com     -> Usuario staff (empleado)
juan_delegado@email.com  -> Usuario delegado de otro cliente

Los tres comparten el mismo PERFIL FISCAL (UserTaxProfile)
```

---

## Decision

### 1. Tipos de Usuario (UserType Enum)

```php
namespace AichaDigital\Larabill\Enums;

enum UserType: int
{
    case STAFF = 0;      // Empleado de la empresa
    case CUSTOMER = 1;   // Cliente directo
    case DELEGATE = 2;   // Delegado de un cliente
}
```

**Caracteristicas por tipo**:

| Tipo | Acceso Admin | Acceso Cliente | parent_user_id | Puede crear delegados |
|------|--------------|----------------|----------------|----------------------|
| STAFF | Si (segun permisos) | No | null | No |
| CUSTOMER | No | Si (sus recursos) | null | Si |
| DELEGATE | No | Limitado (permisos otorgados) | UUID del CUSTOMER | No |

### 2. Niveles de Acceso (AccessLevel Enum)

```php
namespace AichaDigital\Larabill\Enums;

enum AccessLevel: int
{
    case FULL = 0;   // Lee + Escribe + Elimina + Escala
    case WRITE = 1;  // Lee + Escribe (no escala tickets a nivel superior)
    case READ = 2;   // Solo lectura (observador/aprendiz)
    case NONE = 3;   // Sin acceso (explicito)

    public function canWrite(): bool
    {
        return in_array($this, [self::FULL, self::WRITE]);
    }

    public function canDelete(): bool
    {
        return $this === self::FULL;
    }

    public function canEscalate(): bool
    {
        return $this === self::FULL;
    }
}
```

### 3. Inversion de Relacion UserTaxProfile

**ANTES (ADR-003)**:

```
user_tax_profiles.user_id -> users.id
(Un perfil PERTENECE a un user)
```

**AHORA (ADR-004)**:

```
users.current_tax_profile_id -> user_tax_profiles.id
(Un user APUNTA a su perfil activo)

user_tax_profiles.user_id se renombra a owner_user_id
(Indica quien puede editar el perfil)
```

**Diagrama**:

```
                    user_tax_profiles
                    (identidad fiscal)
                    ┌─────────────────┐
                    │ id = 1          │
                    │ owner_user_id=10│ <- Quien puede editar
                    │ tax_id=12345678A│
                    │ fiscal_name=Juan│
                    │ valid_from/until│
                    └────────┬────────┘
                             │
            ┌────────────────┼────────────────┐
            │                │                │
            ▼                ▼                ▼
    ┌───────────────┐ ┌───────────────┐ ┌───────────────┐
    │ users.id=10   │ │ users.id=20   │ │ users.id=30   │
    │ juan_customer@│ │ juan_staff@   │ │ juan_delegado@│
    │ user_type=    │ │ user_type=    │ │ user_type=    │
    │ CUSTOMER      │ │ STAFF         │ │ DELEGATE      │
    │ current_tax_  │ │ current_tax_  │ │ current_tax_  │
    │ profile_id=1  │ │ profile_id=1  │ │ profile_id=1  │
    └───────────────┘ └───────────────┘ └───────────────┘
```

### 4. Cambios en Tabla users

**Campos nuevos**:

```php
Schema::table('users', function (Blueprint $table) {
    // Tipo de usuario
    $table->unsignedTinyInteger('user_type')->default(1); // UserType::CUSTOMER

    // FK al perfil fiscal activo (OBLIGATORIO para todos)
    // Nota: El tipo de columna depende de LARABILL_USER_ID_TYPE
    $table->unsignedBigInteger('current_tax_profile_id');

    // Estado de cuenta
    $table->boolean('is_active')->default(true);
    $table->timestamp('suspended_at')->nullable();

    // Superadmin flag (opcional, solo para el/los admin principales)
    $table->boolean('is_superadmin')->default(false);

    // Indices
    $table->index('user_type');
    $table->index('current_tax_profile_id');
    $table->index('is_active');

    // FK
    $table->foreign('current_tax_profile_id')
        ->references('id')
        ->on('user_tax_profiles')
        ->restrictOnDelete(); // No permitir borrar perfil en uso
});
```

**Campo relationship_type (ADR-003)**:

Se mantiene por compatibilidad pero se depreca. La logica ahora es:

- `user_type = DELEGATE` implica `parent_user_id != null`
- `user_type = CUSTOMER` implica `parent_user_id = null`
- `user_type = STAFF` puede tener `parent_user_id = null`

### 5. Cambios en Tabla user_tax_profiles

**Renombrar columna**:

```php
Schema::table('user_tax_profiles', function (Blueprint $table) {
    $table->renameColumn('user_id', 'owner_user_id');
});
```

**Nueva relacion**:

```php
// UserTaxProfile model
public function linkedUsers(): HasMany
{
    $userModel = ModelMappingService::getModelClass('user');
    return $this->hasMany($userModel, 'current_tax_profile_id');
}

public function owner(): BelongsTo
{
    $userModel = ModelMappingService::getModelClass('user');
    return $this->belongsTo($userModel, 'owner_user_id');
}
```

### 6. Tabla departments (Catalogo de Departamentos)

```php
Schema::create('departments', function (Blueprint $table) {
    $table->string('code', 50)->primary(); // 'tech', 'admin', 'sales'
    $table->json('name'); // Translatable: {"es": "Tecnico", "en": "Technical"}
    $table->json('description')->nullable(); // Translatable
    $table->boolean('is_active')->default(true);
    $table->unsignedTinyInteger('sort_order')->default(0);
    $table->timestamps();
});
```

**Departamentos por defecto**:

| Code | Nombre ES | Nombre EN |
|------|-----------|-----------|
| tech | Tecnico | Technical |
| admin | Administracion | Administration |
| sales | Ventas | Sales |
| billing | Facturacion | Billing |
| domains | Dominios | Domains |

### 7. Tabla user_department_access (Permisos Staff)

```php
Schema::create('user_department_access', function (Blueprint $table) {
    $table->id();

    // FK a users (respeta tipo de ID del proyecto)
    MigrationHelper::foreignUserIdColumn($table, 'user_id');

    // FK a departments
    $table->string('department_code', 50);

    // Nivel de acceso en este departamento
    $table->unsignedTinyInteger('access_level'); // AccessLevel enum

    // Auditoria
    MigrationHelper::foreignUserIdColumn($table, 'granted_by', nullable: true);
    $table->timestamp('granted_at');
    $table->timestamp('expires_at')->nullable(); // Acceso temporal

    $table->timestamps();

    // Constraints
    $table->unique(['user_id', 'department_code']);
    $table->foreign('department_code')
        ->references('code')
        ->on('departments')
        ->restrictOnDelete();

    // Indices
    $table->index(['user_id', 'access_level']);
    $table->index('department_code');
});
```

**Ejemplo de uso**:

```
Staff: admin@empresa.com
├── department_code: 'tech', access_level: FULL (L3)
├── department_code: 'admin', access_level: READ (L1)
└── department_code: 'sales', access_level: WRITE (L2)

Este staff puede:
- Escalar tickets en Tecnico (FULL)
- Solo ver tickets en Administracion (READ)
- Responder tickets en Ventas, pero no escalar (WRITE)
```

### 8. Tabla user_customer_access (Permisos Delegados)

```php
Schema::create('user_customer_access', function (Blueprint $table) {
    $table->id();

    // Usuario que recibe el acceso
    MigrationHelper::foreignUserIdColumn($table, 'user_id');

    // Cliente al que tiene acceso
    MigrationHelper::foreignUserIdColumn($table, 'customer_user_id');

    // Nivel de acceso general
    $table->unsignedTinyInteger('access_level'); // AccessLevel enum

    // Permisos granulares
    $table->boolean('can_view_invoices')->default(false);
    $table->boolean('can_view_services')->default(false);
    $table->boolean('can_manage_tickets')->default(false);
    $table->boolean('can_manage_delegates')->default(false); // Puede crear sub-delegados

    // Auditoria
    MigrationHelper::foreignUserIdColumn($table, 'granted_by');
    $table->timestamp('granted_at');
    $table->timestamp('expires_at')->nullable();

    $table->timestamps();

    // Constraints
    $table->unique(['user_id', 'customer_user_id']);

    // Indices
    $table->index('user_id');
    $table->index('customer_user_id');
});
```

**Ejemplo de uso**:

```
Cliente: pepe@empresa.com (CUSTOMER)
└── Crea delegado: empleado@empresa.com (DELEGATE)
    └── user_customer_access:
        ├── user_id: empleado@empresa.com
        ├── customer_user_id: pepe@empresa.com
        ├── access_level: WRITE
        ├── can_view_invoices: false
        ├── can_view_services: true
        ├── can_manage_tickets: true
        └── can_manage_delegates: false
```

### 9. Tabla api_credentials (Integraciones Externas)

```php
Schema::create('api_credentials', function (Blueprint $table) {
    $table->uuid('id')->primary();

    $table->string('name'); // 'Jira Integration', 'Holded Contabilidad'
    $table->string('key', 64)->unique(); // API Key (hashed)
    $table->text('secret'); // API Secret (encrypted)

    // Scopes permitidos
    $table->json('scopes'); // ['invoices:read', 'customers:read']

    // Restricciones de seguridad
    $table->json('allowed_ips')->nullable(); // ['192.168.1.0/24']

    // Estado y auditoria
    $table->boolean('is_active')->default(true);
    $table->timestamp('last_used_at')->nullable();
    $table->timestamp('expires_at')->nullable();

    MigrationHelper::foreignUserIdColumn($table, 'created_by');

    $table->timestamps();
    $table->softDeletes();

    // Indices
    $table->index('is_active');
    $table->index('key');
});
```

---

## Flujos de Uso

### Login y Contexto

```
Usuario se loguea con email (unico)
         │
         ▼
    ┌────────────────────────────────────────┐
    │ Sistema lee user_type:                 │
    │                                        │
    │ STAFF → Panel Admin (segun permisos)   │
    │ CUSTOMER → Panel Cliente               │
    │ DELEGATE → Panel Cliente (limitado)    │
    └────────────────────────────────────────┘
```

### Impersonation (Solo STAFF)

```
admin@empresa.com (STAFF, level FULL en tech)
         │
         └── Quiere ver panel de "pepe@cliente.com"
             │
             ▼
         Gate::authorize('impersonate', $targetUser)
             │
             ▼
         Session::put('impersonating', $targetUser->id)
             │
             ▼
         Ve panel como Pepe (acciones logueadas como "Admin via Pepe")
```

### Escalado de Tickets

```
Ticket en departamento 'tech', asignado a nivel 1
         │
         ▼
    Staff con access_level = READ en 'tech'
    → Solo puede VER, no responder
         │
         ▼
    Staff con access_level = WRITE en 'tech'
    → Puede responder, NO puede escalar
         │
         ▼
    Staff con access_level = FULL en 'tech'
    → Puede responder, escalar, reasignar departamento
```

### Cambio de Perfil Fiscal

```
Juan cambia direccion fiscal
         │
         ▼
    user_tax_profiles.id = 1 → valid_until = hoy
    user_tax_profiles.id = 2 → nueva direccion, valid_from = manana
         │
         ▼
    UPDATE users SET current_tax_profile_id = 2
    WHERE current_tax_profile_id = 1
         │
         ▼
    Todos los users de Juan (3 cuentas) apuntan al nuevo perfil
```

---

## Impacto en Codigo Existente

### Modelos Afectados

1. **User**: Nuevos campos, nueva relacion `currentTaxProfile()`
2. **UserTaxProfile**: Renombrar `user_id` a `owner_user_id`, nueva relacion `linkedUsers()`
3. **Invoice**: Mantiene `billable_user_id` y `user_tax_profile_id` (sin cambios)

### Traits Afectados

1. **HasUserRelationships**: Añadir metodos para user_type
2. **HasUserRelation**: Sin cambios (usado para FKs)

### Services Afectados

1. **InvoiceService**: Verificar que obtiene perfil correcto via `current_tax_profile_id`
2. **FiscalIntegrityChecker**: Adaptar a nueva estructura

### Tests a Actualizar

1. Tests de Invoice que usan UserTaxProfile
2. Tests de User con relaciones
3. Nuevos tests para permisos y departamentos

---

## Plan de Implementacion

### Fase 1: Enums y Migraciones Base ✅ COMPLETADA

- [x] Crear UserType enum → `app/Enums/UserType.php`
- [x] Crear AccessLevel enum → `app/Enums/AccessLevel.php`
- [x] Migracion: current_tax_profile_id en users ✅
- [x] Migracion: Renombrar user_tax_profiles.user_id a owner_user_id ✅

### Fase 2: Tablas de Permisos ✅ COMPLETADA

- [x] Tabla departments (via laratickets)
- [x] Tabla user_department_access ✅
- [x] Tabla user_customer_access ✅
- [x] Modelos UserDepartmentAccess, UserCustomerAccess ✅

### Fase 3: Columnas users y Modelos ✅ COMPLETADA (2026-01-07)

- [x] Migración: user_type, is_active, suspended_at, is_superadmin ✅
- [x] Actualizar User model con user_type cast y métodos ✅
- [x] Métodos: isStaff(), isCustomer(), isDelegate(), isSuperadmin() ✅
- [x] Métodos: isAccountActive(), suspend(), reactivate() ✅
- [x] canAccessAdmin() como reemplazo de isAdmin() ✅
- [x] UserFactory states: staff(), customer(), delegate(), superadmin() ✅
- [x] Tests de user_type (22 tests) ✅

### Fase 4: Gates y Policies ✅ COMPLETADA (2026-01-07)

- [x] InvoicePolicy ✅
- [x] UserTaxProfilePolicy ✅
- [x] UserCustomerAccessPolicy ✅
- [x] ArticlePolicy ✅
- [x] UserPolicy (11 métodos) ✅ NUEVO
- [x] Gate access-admin ✅
- [x] Gate impersonate-users ✅
- [x] Gate manage-delegates ✅
- [x] Gate view-invoices ✅
- [x] Middleware EnsureUserIsAdmin actualizado ✅
- [x] Tests de autorización (34 tests) ✅

### Fase 5: API Credentials e Integraciones ⏸️ APLAZADA

- [ ] Tabla api_credentials
- [ ] Modelo ApiCredential
- [ ] Middleware de autenticación API
- [ ] Tests

> **Nota**: Fase 5 aplazada para implementar cuando se necesiten integraciones externas.

---

## Compatibilidad con Sanctum

Para autenticacion API se usa Laravel Sanctum (ya incluido en Laravel 12):

```php
// Personal Access Tokens para apps propias (mobile, SPA)
$token = $user->createToken('mobile-app', ['invoices:read', 'tickets:write']);

// Verificar abilities
if ($user->tokenCan('invoices:read')) {
    // ...
}
```

Para integraciones externas (Jira, contabilidad), se usa la tabla `api_credentials` con validacion propia.

---

## Alternativas Consideradas

### Spatie Permission

**Rechazada** porque:

- Añade tablas propias (`roles`, `permissions`, `model_has_roles`)
- No se adapta bien al modelo de departamentos + niveles
- Acoplamiento innecesario para un paquete agnostico

### Bouncer

**Rechazada** porque:

- Menos mantenido
- Sin plugin oficial para Filament
- Similar problema de acoplamiento

### Laravel nativo con tablas custom

**ELEGIDA** porque:

- Control total sobre la estructura
- Agnostico al frontend
- Adaptado a necesidades especificas (departamentos, niveles, delegados)
- Sin dependencias externas

---

## Riesgos Identificados

1. **Migracion de datos**: Si hay users existentes, necesitan current_tax_profile_id
   - Mitigacion: Script de migracion que crea perfil si no existe

2. **Breaking changes en relaciones**: taxProfiles() cambia de significado
   - Mitigacion: Mantener metodo con deprecation warning

3. **Tests existentes**: Pueden fallar por cambio de relaciones
   - Mitigacion: Actualizar tests en paralelo con cambios

4. **Performance**: Mas JOINs en queries de usuario
   - Mitigacion: Indices adecuados, eager loading

---

## Referencias

- ADR-001: CompanyFiscalConfig (vigente)
- ADR-002: UUID v7 string (vigente)
- ADR-003: Unificacion Users/Customers (parcialmente superseded)
- Laravel Sanctum: https://laravel.com/docs/12.x/sanctum
- Laravel Authorization: https://laravel.com/docs/12.x/authorization

---

*ADR creado: 2026-01-01*
*Autor: Claude (AI Assistant)*
*Revisor: @abkrim*
