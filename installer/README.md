# Larafactu Web Installer

**FOR STAGING AND DEVELOPMENT ONLY - NOT FOR PRODUCTION**

---

# Instalador Web Larafactu

**SOLO PARA STAGING Y DESARROLLO - NO PARA PRODUCCION**

---

## Purpose / Proposito

This web-based installer provides a guided wizard to set up Larafactu in staging and development environments. It handles database configuration, environment setup, migrations, and initial admin user creation.

Este instalador web proporciona un asistente guiado para configurar Larafactu en entornos de staging y desarrollo. Gestiona la configuracion de base de datos, configuracion del entorno, migraciones y creacion del usuario administrador inicial.

---

## Quick Start

### Docker (Testing Environment)

The fastest way to test the installer:

```bash
cd installer
./test.sh
```

Then open http://localhost:8889 in your browser.

**Docker credentials (predefined):**

- MySQL Host: `mysql`
- Port: `3306`
- Database: `larafactu_test`
- Username: `larafactu`
- Password: `larafactu`

### Server (Staging Environment)

1. Upload the application files to your server
2. Create an access token:

```bash
php -r "echo bin2hex(random_bytes(16));"
```

3. Store the token securely (you'll need it to access the installer)
4. Navigate to `/installer/public/` in your browser
5. Enter the token when prompted
6. Follow the wizard steps

---

## Inicio Rapido

### Docker (Entorno de Pruebas)

La forma mas rapida de probar el instalador:

```bash
cd installer
./test.sh
```

Luego abre http://localhost:8889 en tu navegador.

**Credenciales Docker (predefinidas):**

- Host MySQL: `mysql`
- Puerto: `3306`
- Base de datos: `larafactu_test`
- Usuario: `larafactu`
- Contrasena: `larafactu`

### Servidor (Entorno de Staging)

1. Sube los archivos de la aplicacion a tu servidor
2. Crea un token de acceso:

```bash
php -r "echo bin2hex(random_bytes(16));"
```

3. Guarda el token de forma segura (lo necesitaras para acceder al instalador)
4. Navega a `/installer/public/` en tu navegador
5. Introduce el token cuando se solicite
6. Sigue los pasos del asistente

---

## Database Configuration Options

The installer supports three database configuration modes:

### MySQL (Docker)

- **When available**: Only shown when `INSTALLER_ENV=testing` or `INSTALLER_ENV=docker`
- **Configuration**: Predefined values from docker-compose.yml
- **Use case**: Testing the installer in Docker environment
- **No user input required**: Values are automatically applied

### MySQL (Custom)

- **Always available**: Shown in all environments
- **Configuration**: User provides host, port, database, username, password
- **Use case**: Staging servers with existing MySQL installations
- **Validates connection**: Tests credentials before proceeding

### SQLite

- **Always available**: Shown in all environments
- **Configuration**: User provides file path (default: `database/database.sqlite`)
- **Use case**: Simple deployments, development, or when MySQL is not available
- **Auto-creates file**: The database file is created automatically

---

## Opciones de Configuracion de Base de Datos

El instalador soporta tres modos de configuracion de base de datos:

### MySQL (Docker)

- **Cuando esta disponible**: Solo se muestra cuando `INSTALLER_ENV=testing` o `INSTALLER_ENV=docker`
- **Configuracion**: Valores predefinidos de docker-compose.yml
- **Caso de uso**: Probar el instalador en entorno Docker
- **Sin entrada del usuario**: Los valores se aplican automaticamente

### MySQL (Custom)

- **Siempre disponible**: Se muestra en todos los entornos
- **Configuracion**: El usuario proporciona host, puerto, base de datos, usuario, contrasena
- **Caso de uso**: Servidores de staging con instalaciones MySQL existentes
- **Valida conexion**: Prueba las credenciales antes de continuar

### SQLite

- **Siempre disponible**: Se muestra en todos los entornos
- **Configuracion**: El usuario proporciona la ruta del archivo (por defecto: `database/database.sqlite`)
- **Caso de uso**: Despliegues simples, desarrollo, o cuando MySQL no esta disponible
- **Crea archivo automaticamente**: El archivo de base de datos se crea automaticamente

---

## Wizard Steps

1. **Welcome** - Introduction and language selection
2. **Requirements** - PHP version and extensions check
3. **Dependencies** - Composer install
4. **App Key** - Generate Laravel application key
5. **Database** - Configure database connection (Docker/MySQL/SQLite)
6. **Migrations** - Run database migrations
7. **Company** - Fiscal configuration (CIF, address, etc.)
8. **Verifactu** - AEAT integration settings (optional)
9. **Admin** - Create superadmin user
10. **Finalize** - Complete installation

---

## Pasos del Asistente

1. **Bienvenida** - Introduccion y seleccion de idioma
2. **Requisitos** - Verificacion de version PHP y extensiones
3. **Dependencias** - Instalacion de Composer
4. **Clave App** - Generar clave de aplicacion Laravel
5. **Base de Datos** - Configurar conexion (Docker/MySQL/SQLite)
6. **Migraciones** - Ejecutar migraciones de base de datos
7. **Empresa** - Configuracion fiscal (CIF, direccion, etc.)
8. **Verifactu** - Configuracion integracion AEAT (opcional)
9. **Admin** - Crear usuario superadministrador
10. **Finalizar** - Completar instalacion

---

## Environment Detection

The installer automatically detects the environment:

| Detection Method | Mode | Available DB Options |
|------------------|------|---------------------|
| `INSTALLER_ENV=testing` or `docker` | Docker | Docker MySQL (recommended), Custom MySQL, SQLite |
| `/var/www/larafactu` exists | Docker | Docker MySQL (recommended), Custom MySQL, SQLite |
| Other | Server/Local | Custom MySQL (recommended), SQLite |

---

## Deteccion de Entorno

El instalador detecta automaticamente el entorno:

| Metodo de Deteccion | Modo | Opciones de BD Disponibles |
|---------------------|------|---------------------------|
| `INSTALLER_ENV=testing` o `docker` | Docker | Docker MySQL (recomendado), Custom MySQL, SQLite |
| `/var/www/larafactu` existe | Docker | Docker MySQL (recomendado), Custom MySQL, SQLite |
| Otro | Servidor/Local | Custom MySQL (recomendado), SQLite |

---

## ID Type Selection

During database configuration, you must choose an ID type for users and entities:

- **UUID v7** (Recommended): Universal unique identifiers. More secure and scalable.
- **Integer** (Legacy): Auto-incremental numeric IDs. For compatibility with legacy systems.

**This selection cannot be changed after installation.**

---

## Seleccion de Tipo de ID

Durante la configuracion de base de datos, debes elegir un tipo de ID para usuarios y entidades:

- **UUID v7** (Recomendado): Identificadores unicos universales. Mas seguros y escalables.
- **Integer** (Clasico): IDs numericos auto-incrementales. Para compatibilidad con sistemas legacy.

**Esta seleccion NO se puede cambiar despues de la instalacion.**

---

## Essential Data Seeding

After running migrations, the installer automatically seeds essential lookup data:

- **LegalEntityTypesSeeder**: Spanish legal entity types (S.L., S.A., etc.)
- **TaxRatesSeeder**: Spanish VAT rates (21%, 10%, 4%, 0%)
- **TaxGroupsSeeder**: Tax groupings for invoicing
- **UnitMeasuresSeeder**: Standard unit measures (units, hours, kg, etc.)

This data is required for the application to function properly. Additional demo data can be optionally installed by checking "Run seeders" during migration step.

---

## Datos Esenciales Sembrados

Despues de ejecutar las migraciones, el instalador siembra automaticamente datos esenciales:

- **LegalEntityTypesSeeder**: Tipos de entidad legal espanolas (S.L., S.A., etc.)
- **TaxRatesSeeder**: Tipos de IVA espanoles (21%, 10%, 4%, 0%)
- **TaxGroupsSeeder**: Agrupaciones de impuestos para facturacion
- **UnitMeasuresSeeder**: Unidades de medida estandar (unidades, horas, kg, etc.)

Estos datos son necesarios para que la aplicacion funcione correctamente. Datos de demostracion adicionales pueden instalarse opcionalmente marcando "Ejecutar seeders" durante el paso de migraciones.

---

## Security Notes

- The installer is protected by an access token
- Sessions expire after 30 minutes of inactivity
- After installation, access to the installer is blocked
- Remove or restrict access to `/installer/` in production

---

## Notas de Seguridad

- El instalador esta protegido por un token de acceso
- Las sesiones expiran despues de 30 minutos de inactividad
- Despues de la instalacion, el acceso al instalador se bloquea
- Elimina o restringe el acceso a `/installer/` en produccion

---

## File Structure

```
installer/
├── docker/                 # Docker testing environment
│   ├── docker-compose.yml
│   ├── Dockerfile
│   └── nginx.conf
├── public/                 # Web entry point
│   ├── index.php
│   ├── api.php
│   └── assets/
├── src/                    # Application logic
│   ├── bootstrap.php
│   ├── Environment/        # Environment detection
│   ├── Steps/              # Wizard steps
│   ├── Validators/         # Input validators
│   ├── Actions/            # Execution actions
│   └── ...
├── templates/              # View templates
│   ├── layout.php
│   └── steps/
├── storage/                # Session storage
└── test.sh                 # Docker testing script
```

---

## Troubleshooting

### Docker MySQL connection fails

Ensure the MySQL container is healthy:

```bash
docker-compose ps
```

Wait for MySQL health check to pass (can take 30-60 seconds on first start).

### Permission errors

Ensure the web server user can write to:

- `storage/` directory in the installer
- `.env` file in Larafactu root
- `database/` directory (for SQLite)

### Session expired

Increase session timeout or complete the installation faster. The wizard saves progress, so you can start from where you left off.

---

## Solucion de Problemas

### Falla la conexion Docker MySQL

Asegurate de que el contenedor MySQL esta saludable:

```bash
docker-compose ps
```

Espera a que pase el health check de MySQL (puede tardar 30-60 segundos en el primer inicio).

### Errores de permisos

Asegurate de que el usuario del servidor web puede escribir en:

- Directorio `storage/` en el instalador
- Archivo `.env` en la raiz de Larafactu
- Directorio `database/` (para SQLite)

### Sesion expirada

Aumenta el timeout de sesion o completa la instalacion mas rapido. El asistente guarda el progreso, asi que puedes continuar desde donde lo dejaste.

---

## Version

Installer Version: 1.0.0

Compatible with: Larafactu (Laravel 12)
