# TODO - Wizard de Instalación Web

> Documento de seguimiento del wizard de instalación de Larafactu.
> Issue Linear: AID-39

## Estado General: En Desarrollo

---

## Fase 1: Estructura Base ✅ COMPLETADA

- [x] Crear directorio `installer/`
- [x] Entry points (`index.php`, `api.php`)
- [x] Sistema de estado (`InstallState`)
- [x] Clase base `AbstractStep`
- [x] Sistema i18n (ES/EN) - `Translator`
- [x] Helpers globales
- [x] Clases de seguridad (`AccessControl`, `AccessResult`)
- [x] Interfaces de pasos (`StepInterface`, `ValidationResult`, `ExecutionResult`)
- [x] Registry de pasos (`StepRegistry`)

## Fase 2: Validadores ✅ COMPLETADA

- [x] `ValidatorInterface`
- [x] `ValidatorResult`
- [x] `PhpVersionValidator`
- [x] `ExtensionsValidator`
- [x] `WritablePathsValidator`
- [x] `DatabaseValidator`

## Fase 3: Actions del Sistema ✅ COMPLETADA

- [x] `ActionInterface`
- [x] `ActionResult`
- [x] `EnvFileWriter`
- [x] `KeyGenerator` (APP_KEY)
- [x] `CommandRunner` (whitelist)
- [x] `MigrationRunner`
- [x] `CertificateStore` (certificados encriptados)
- [x] `Encryption` (AES-256-GCM)

## Fase 4: Pasos del Wizard ✅ COMPLETADA

- [x] Step 1: `WelcomeStep` - Bienvenida + idioma
- [x] Step 2: `RequirementsStep` - Verificación de requisitos
- [x] Step 3: `DependenciesStep` - Instalación de dependencias (composer install)
- [x] Step 4: `AppKeyStep` - Generación APP_KEY (CRÍTICO - antes de encriptación)
- [x] Step 5: `DatabaseStep` - Configuración BD + selección tipo ID (UUID/Integer)
- [x] Step 6: `MigrationsStep` - Migraciones
- [x] Step 7: `CompanyStep` - Datos fiscales completos
- [x] Step 8: `VerifactuStep` - Configuración Verifactu + certificados
- [x] Step 9: `AdminStep` - Superadmin
- [x] Step 10: `FinalizeStep` - Marker en settings + resumen

## Fase 5: Templates y Frontend ✅ COMPLETADA

- [x] Template principal (`wizard.php`) con Alpine.js
- [x] Templates de pasos (welcome, requirements, appkey, database, migrations, company, verifactu, admin, finalize)
- [x] Templates de acceso (token-form, access-denied, session-expired)
- [x] Templates de estado (installer-blocked, installer-complete)
- [x] TailwindCSS CDN
- [x] Alpine.js integración

## Fase 6: Traducciones ✅ COMPLETADA

- [x] Archivo ES completo (`es.json`)
- [x] Archivo EN completo (`en.json`)
- [x] Todas las claves para todos los pasos

## Fase 7: Seguridad Post-Instalación ✅ COMPLETADA

- [x] Middleware Laravel `EnsureInstallerRemoved`
- [x] Vista de error para installer no eliminado
- [x] Sistema de grace period (24h)
- [x] Bloqueo automático tras grace period
- [x] Token de acceso con IP locking
- [x] Rate limiting de intentos fallidos
- [x] Session timeout (60 min)

## Fase 8: Configuración Docker para Testing 🔄 PENDIENTE

- [x] `docker-compose.yml` (puertos 8888/3307)
- [x] `Dockerfile` PHP 8.4
- [x] `nginx.conf`
- [x] `test.sh` script
- [x] `phpunit.xml`
- [x] Bootstrap de tests
- [ ] Tests unitarios completos
- [ ] Tests de integración

## Fase 9: Integración con App 🔄 PENDIENTE

- [ ] Registrar middleware en `bootstrap/app.php`
- [ ] Verificar seeders base compatibles
- [ ] Documentar proceso de instalación
- [ ] Actualizar README principal

## Fase 10: Deprecación CLI Installer ✅ COMPLETADO

- [x] Marcar `php artisan larafactu:install` como deprecated
- [x] Eliminar CLI installer (AID-40)
- [x] Eliminar scripts bin/ relacionados (fresh-install.sh, local-install.sh, test-install.sh)
- [x] Actualizar documentacion para apuntar al wizard web

---

## Notas Técnicas

### Orden de Ejecución Crítico

1. **Dependencias** (composer install) - Sin esto, artisan no funciona
2. **APP_KEY** debe generarse ANTES de cualquier operación de encriptación
3. **Base de datos** debe estar configurada ANTES de migraciones (incluye tipo ID: UUID/Integer)
4. **Migraciones** ANTES de crear empresa/admin
5. **Verifactu** es OPCIONAL (se puede saltar)

### Seguridad del Installer

- Token almacenado en `installer/storage/.token`
- IP bloqueada tras 5 intentos fallidos
- Sesión expira en 60 minutos
- Grace period de 24h para eliminar installer
- Certificados encriptados con AES-256-GCM

### Estructura de Archivos

```
installer/
├── public/
│   ├── index.php          # Entry point
│   ├── api.php            # AJAX endpoint
│   └── assets/
│       ├── i18n/          # Traducciones JSON
│       └── styles.css
├── src/
│   ├── Actions/           # Acciones del sistema
│   ├── I18n/              # Traductor
│   ├── Security/          # Control de acceso
│   ├── Session/           # Estado de instalación
│   ├── Steps/             # Pasos del wizard
│   └── Validators/        # Validadores de requisitos
├── storage/               # Datos temporales
├── templates/             # Vistas PHP
├── docker/                # Configuración Docker
└── tests/                 # Tests PHPUnit
```

---

## Issues Relacionadas

- **AID-39**: Creación del wizard (actual)
- **AID-41**: Deprecación CLI installer - https://linear.app/aichadigital/issue/AID-41
