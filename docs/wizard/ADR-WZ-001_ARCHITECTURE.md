# ADR-WZ-001: Arquitectura del Wizard de Instalación Web

## Estado
**Aceptado** - 2026-01-07

## Contexto

Larafactu necesita un instalador web accesible que funcione **antes** de que Laravel esté completamente configurado. Esto presenta desafíos únicos:

1. No podemos depender de Laravel/Livewire porque la aplicación no está instalada
2. Necesitamos ejecutar operaciones privilegiadas (migraciones, crear archivos, etc.)
3. La seguridad es crítica - un instalador expuesto es un vector de ataque
4. Debe ser multiidioma desde el inicio (ES/EN)

## Decisión

### Arquitectura Framework-Less

Implementamos el wizard usando **PHP puro + Alpine.js**:

```
installer/
├── public/           # Entry points
├── src/              # Lógica PHP
├── templates/        # Vistas PHP
├── storage/          # Estado temporal
└── docker/           # Testing aislado
```

### Componentes Principales

#### 1. Sistema de Pasos (Steps)

Cada paso del wizard es una clase que implementa `StepInterface`:

```php
interface StepInterface
{
    public function getId(): string;
    public function validate(array $data): ValidationResult;
    public function execute(array $data): ExecutionResult;
}
```

Pasos implementados:
1. **Welcome** - Selección de idioma
2. **Requirements** - Verificación de requisitos
3. **AppKey** - Generación de clave (CRÍTICO)
4. **Database** - Configuración BD
5. **Migrations** - Ejecución de migraciones
6. **Company** - Datos fiscales
7. **Verifactu** - Integración AEAT
8. **Admin** - Creación superadmin
9. **Finalize** - Marker y resumen

#### 2. Sistema de Estado (InstallState)

Estado persistido en JSON para recuperación entre requests:

```php
class InstallState
{
    public function get(string $key, $default = null);
    public function set(string $key, $value): void;
    public function getCompletedSteps(): array;
}
```

#### 3. Validadores de Requisitos

Validadores especializados para cada requisito:
- `PhpVersionValidator` - PHP >= 8.4
- `ExtensionsValidator` - Extensiones requeridas/opcionales
- `WritablePathsValidator` - Permisos de escritura
- `DatabaseValidator` - Conexión MySQL

#### 4. Actions del Sistema

Acciones controladas con whitelists:
- `CommandRunner` - Comandos artisan limitados
- `EnvFileWriter` - Escritura segura de .env
- `KeyGenerator` - Generación APP_KEY
- `CertificateStore` - Certificados encriptados

#### 5. Sistema de Seguridad

```php
class AccessControl
{
    // Token en archivo, bloqueado por IP
    // Rate limiting: 5 intentos, 15 min lockout
    // Session timeout: 60 min
}
```

### Orden de Ejecución Crítico

```
1. APP_KEY → 2. Database → 3. Migrations → 4. Company → 5. Admin
```

**APP_KEY debe existir antes de cualquier encriptación.**

### Frontend

- **Alpine.js** para interactividad (wizard flow, validación)
- **TailwindCSS CDN** para estilos (no build necesario)
- **AJAX** para comunicación con backend

## Alternativas Consideradas

### Livewire
- ❌ Requiere Laravel funcionando
- ❌ No disponible antes de instalación

### Vue/React SPA
- ❌ Complejidad innecesaria
- ❌ Requiere build process
- ❌ Overkill para un instalador

### CLI Only
- ❌ No accesible para hosting compartido
- ❌ Requiere acceso SSH

## Consecuencias

### Positivas
- ✅ Funciona sin Laravel instalado
- ✅ Sin dependencias de build
- ✅ Seguridad controlada
- ✅ Multiidioma nativo
- ✅ Recuperación de estado

### Negativas
- ⚠️ Código separado de la aplicación principal
- ⚠️ Debe mantenerse independientemente
- ⚠️ No usa convenciones Laravel

### Mitigación de Riesgos

1. **Instalador debe eliminarse** tras uso
   - Middleware `EnsureInstallerRemoved`
   - Grace period de 24h
   - Bloqueo automático después

2. **Tests aislados en Docker**
   - No interfieren con BD de desarrollo
   - Puertos dedicados (8888, 3307)

## Referencias

- Issue: AID-39
- CLI Deprecation: AID-40 (pendiente)
- Documentación: `docs/wizard/TODO_WIZARD.md`
