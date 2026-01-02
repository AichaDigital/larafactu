# ADR-005: Deprecacion de Filament y Adopcion de Blade+Livewire+DaisyUI

**Estado**: APROBADO
**Fecha**: 2026-01-01
**Contexto**: Larafactu + Ecosistema AichaDigital
**Impacto**: CRITICO - Cambio arquitectonico completo del frontend
**Aprobado por**: @abkrim

---

## Contexto

### Situacion Actual

El proyecto larafactu y su ecosistema de paquetes utilizan Filament 4 como framework de administracion. Esta decision inicial se tomo para acelerar el desarrollo, pero ha generado:

1. **Deuda tecnica significativa**: Filament impone su propio paradigma de componentes, formularios y tablas
2. **Acoplamiento excesivo**: Los paquetes *-filament dependen fuertemente del ecosistema Filament
3. **Duplicacion de esfuerzo**: Mantener paquetes base + paquetes filament para cada dominio
4. **Limitaciones de personalizacion**: El diseño final no puede apartarse del look Filament
5. **Curva de aprendizaje**: Desarrolladores deben aprender Filament ademas de Laravel/Livewire

### Paquetes Afectados

| Paquete | Estado Actual | Accion |
|---------|---------------|--------|
| larabill-filament | En desarrollo | ABANDONAR |
| lara-verifactu-filament | En desarrollo | ABANDONAR |
| laratickets-filament | En desarrollo | ABANDONAR |
| lararoi-filament | En desarrollo | ABANDONAR |

---

## Decision

### 1. Abandonar Filament

- Remover dependencias de Filament de larafactu
- Marcar todos los paquetes *-filament como ABANDONADOS
- No invertir mas tiempo en desarrollo Filament

### 2. Adoptar Stack Blade + Livewire + DaisyUI

**Stack elegido**:

- **Blade**: Templates nativos de Laravel (NO Volt)
- **Livewire 3**: Componentes reactivos donde sea necesario
- **TailwindCSS 4**: Framework CSS utility-first
- **DaisyUI 5**: Componentes predefinidos sobre Tailwind
- **Alpine.js**: Interactividad ligera (incluido con Livewire)

**Razon de NO usar Volt**:

Volt mezcla logica PHP y markup en un solo archivo. Preferimos separacion clara:

- Blade para templates (*.blade.php)
- Livewire classes para logica (app/Livewire/*.php)
- Componentes anonimos para UI reutilizable

### 3. Sistema de Temas

**Temas seleccionados**:

| Rol | Tema | Tipo |
|-----|------|------|
| Light default | cupcake | Claro, amigable |
| Dark default | abyss | Oscuro profundo |
| Light alternativo | corporate | Profesional |
| Dark alternativo | sunset | Oscuro calido |

**Configuracion CSS**:

```css
@import "tailwindcss";
@plugin "daisyui" {
  themes: cupcake --default, abyss --prefersdark, corporate, sunset;
}
```

### 4. Preferencias de Usuario

Crear sistema de preferencias persistentes:

```php
// Migracion
Schema::create('user_preferences', function (Blueprint $table) {
    $table->id();
    MigrationHelper::foreignUserIdColumn($table, 'user_id');
    $table->string('locale', 5)->default('es');
    $table->string('theme', 20)->default('cupcake');
    $table->string('timezone', 50)->default('Europe/Madrid');
    $table->json('notifications')->nullable();
    $table->timestamps();

    $table->unique('user_id');
});
```

**Flujo de tema**:

1. Usuario autenticado: leer de `user_preferences.theme`
2. Usuario anonimo: leer de cookie `theme`
3. Sin preferencia: usar default segun `prefers-color-scheme`

### 5. Arquitectura de Componentes

**Estructura de directorios**:

```
resources/
├── views/
│   ├── components/         # Componentes Blade anonimos
│   │   ├── layouts/
│   │   │   ├── app.blade.php
│   │   │   └── guest.blade.php
│   │   ├── ui/             # Componentes DaisyUI wrapper
│   │   │   ├── button.blade.php
│   │   │   ├── card.blade.php
│   │   │   ├── modal.blade.php
│   │   │   └── ...
│   │   └── forms/          # Componentes de formulario
│   │       ├── input.blade.php
│   │       ├── select.blade.php
│   │       └── ...
│   ├── livewire/           # Templates Livewire
│   └── pages/              # Paginas estaticas
app/
├── Livewire/               # Componentes Livewire
│   ├── Invoices/
│   ├── Customers/
│   └── ...
├── View/
│   └── Components/         # Componentes Blade con logica
```

---

## Impacto en ADR-004

El ADR-004 (Autorizacion y Permisos) requiere revision:

### Lo que se mantiene:

1. **owner_user_id en user_tax_profiles**: Cambio correcto, independiente de UI
2. **UserType y AccessLevel enums**: Conceptualmente validos
3. **Modelo de permisos por departamento**: Necesario para el negocio

### Lo que cambia:

1. **Ubicacion del codigo de autorizacion**: Debe moverse de larabill a larafactu
2. **Department model**: Ya existe en laratickets, conflicto detectado
3. **Gates y Policies**: Se implementan en la aplicacion, no en paquetes

### Nuevo enfoque:

- Los paquetes (larabill, laratickets, etc.) son **agnosticos** a autorizacion
- La aplicacion (larafactu) implementa autorizacion usando Gates/Policies nativas
- Los paquetes exponen interfaces/contratos que la aplicacion puede usar

---

## Plan de Implementacion

### Fase 1: Limpieza y Preparacion - COMPLETADA

- [x] Marcar paquetes *-filament como ABANDONADOS (README)
- [x] Crear branch `feature/remove-filament` en larafactu (trabajando en main)
- [x] Remover dependencias Filament de composer.json
- [x] Limpiar codigo ADR-004 mal ubicado en larabill
- [x] Actualizar welcome.blade.php y post-deploy.php (quitar referencias *-filament)

### Fase 2: Infraestructura Base - COMPLETADA

- [x] Configurar TailwindCSS 4 + DaisyUI 5
- [x] Crear layouts base (app, guest)
- [x] Implementar theme switcher (componente Livewire + API endpoint)
- [x] Crear layout Nexus-style con sidebar colapsable (toggle directo + hover mode)
- [x] Crear componentes UI base (wrappers DaisyUI)
  - UI: button, card, alert, badge, modal, table, stats, stat, loading, empty-state
  - Forms: input, select, textarea, checkbox, toggle
  - Icons: search, email, user, lock

### Fase 3: Sistema de Preferencias - COMPLETADA

- [x] Migracion user_preferences
- [x] Modelo UserPreference
- [x] Middleware ApplyUserPreferences
- [x] Componente selector de tema (livewire:theme-selector)
- [x] API endpoint /api/theme para persistencia

### Fase 4: Reconstruccion de Vistas - COMPLETADA

- [x] Dashboard principal (basico)
- [x] Gestion de facturas (listado, crear, editar, ver)
- [x] Gestion de clientes (listado, crear, editar)
- [x] Gestion de articulos (listado, crear, editar)
- [x] Autenticacion (login via Livewire)
- [x] Registro de usuarios
- [x] Recuperacion de contrasena (forgot-password + reset-password)
- [x] Perfil de usuario (edicion, cambio contrasena, preferencias)

Componentes de facturas implementados:

- InvoiceList: Listado con filtros por ano, serie, estado y busqueda
- InvoiceCreate: Creacion con seleccion de cliente y lineas dinamicas
- InvoiceEdit: Edicion solo para facturas en borrador
- InvoiceShow: Vista detallada con items y resumen fiscal

Componentes de articulos implementados:

- ArticleList: Listado con filtros por tipo, categoria, estado
- ArticleCreate: Creacion con codigo, nombre translatable, precio base
- ArticleEdit: Edicion de articulo existente

Componentes de clientes implementados:

- CustomerList: Listado con filtros por tipo (empresa/particular), busqueda por nombre/email/NIF
- CustomerCreate: Creacion de usuario + perfil fiscal usando UserTaxProfile model
- CustomerEdit: Edicion con versionado automatico de perfiles fiscales

Componente de perfil implementado:

- ProfileEdit: Edicion de nombre/email, cambio de contrasena, preferencias (tema, idioma, zona horaria)

Componentes de autenticacion implementados:

- Login: Inicio de sesion con remember me
- Register: Registro con validacion y terminos
- ForgotPassword: Solicitud de enlace de recuperacion
- ResetPassword: Restablecimiento de contrasena con token

### Fase 5: Autorizacion en Aplicacion - COMPLETADA

- [x] Middleware EnsureUserIsAdmin
- [x] Rutas admin protegidas con ['auth', 'admin']
- [x] Crear enums UserType/AccessLevel en app/Enums/
- [x] Crear Policies para modelos principales (Invoice, UserTaxProfile, Article)
- [x] Implementar Gates en AuthServiceProvider (access-admin, impersonate, manage-users, view-reports, manage-settings)
- [x] Resolver conflicto Department: usar laratickets Department + crear user_department_access en larafactu

**Resolucion del conflicto Department**:

El ADR-004 proponia una tabla `departments` con `code` como PK. Sin embargo, laratickets ya tiene una tabla `departments` con `id` integer. La solucion adoptada:

1. **Reutilizar** laratickets Department (ya existe, funciona para routing de tickets)
2. **Crear** `user_department_access` en larafactu que referencia `departments.id`
3. **No modificar** laratickets - mantener paquete agnostico

Archivos creados en Fase 5:

- `app/Enums/UserType.php` - STAFF, CUSTOMER, DELEGATE
- `app/Enums/AccessLevel.php` - FULL, WRITE, READ, NONE
- `app/Policies/InvoicePolicy.php`
- `app/Policies/UserTaxProfilePolicy.php`
- `app/Policies/ArticlePolicy.php`
- `app/Providers/AuthServiceProvider.php`
- `app/Models/UserDepartmentAccess.php`
- `database/migrations/2026_01_02_000001_create_user_department_access_table.php`

---

## Alternativas Consideradas

### Mantener Filament

**Rechazada** porque:

- Deuda tecnica creciente
- Dependencia de roadmap externo
- Limitaciones de diseño

### Laravel Breeze con Blade

**Considerada** pero insuficiente:

- No incluye sistema de componentes rico
- Requeriria construir todo desde cero

### Laravel Jetstream

**Rechazada** porque:

- Demasiado opinionado
- Incluye features no necesarias (Teams)
- Usa Inertia o Livewire de forma rigida

### Blade + Livewire + DaisyUI

**ELEGIDA** porque:

- Control total del diseño
- Componentes predefinidos aceleran desarrollo
- Sistema de temas integrado
- Paradigma familiar (Blade es nativo Laravel)
- Flexibilidad para añadir reactividad donde se necesite

---

## Riesgos Identificados

1. **Tiempo de desarrollo**: Reconstruir UI lleva tiempo
   - Mitigacion: Fase incremental, componentes reutilizables

2. **Tests rotos**: Cambios en estructura rompen tests de UI
   - Mitigacion: Priorizar tests de logica, actualizar tests UI gradualmente

3. **Curva de aprendizaje DaisyUI**: Nuevo paradigma de componentes
   - Mitigacion: Documentacion de temas, MCP daisyui-blueprint

4. **Compatibilidad con paquetes base**: larabill, laratickets pueden tener views
   - Mitigacion: Los paquetes no deben tener views, solo logica

---

## Referencias

- DaisyUI: https://daisyui.com/
- DaisyUI Theme Generator: https://daisyui.com/theme-generator/
- Livewire 3: https://livewire.laravel.com/
- TailwindCSS 4: https://tailwindcss.com/
- ADR-004: Sistema de Autorizacion (parcialmente superseded)

---

*ADR creado: 2026-01-01*
*Autor: @abkrim con asistencia Claude*
