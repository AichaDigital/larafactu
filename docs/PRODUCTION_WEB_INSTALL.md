# Guía de Instalación Web de Larafactu

> Esta guía describe el proceso de instalación de Larafactu usando el wizard web.

## Índice

1. [Requisitos Previos](#requisitos-previos)
2. [Descarga del Proyecto](#descarga-del-proyecto)
3. [Acceso al Wizard](#acceso-al-wizard)
4. [Pasos del Wizard](#pasos-del-wizard)
5. [Post-Instalación](#post-instalación)
6. [Solución de Problemas](#solución-de-problemas)

---

## Requisitos Previos

### Servidor Web

- **PHP** >= 8.4 con las siguientes extensiones:
  - pdo_mysql
  - openssl
  - mbstring
  - tokenizer
  - xml
  - ctype
  - json
  - bcmath
  - fileinfo
  - curl

- **MySQL** >= 8.0 o MariaDB >= 10.6

- **Servidor Web**: Apache/Nginx configurado para Laravel

### Permisos de Escritura

Los siguientes directorios deben ser escribibles:

```
storage/
storage/app/
storage/framework/
storage/framework/cache/
storage/framework/sessions/
storage/framework/views/
storage/logs/
bootstrap/cache/
```

---

## Descarga del Proyecto

### Opción A: Desde Release (Producción)

1. Descargar el ZIP de la última release desde GitHub
2. Extraer en el directorio de hosting
3. Configurar el document root hacia `/public`

```bash
# Ejemplo
unzip larafactu-v1.0.0.zip -d /var/www/larafactu
cd /var/www/larafactu
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Opción B: Desde Git (Desarrollo/Staging)

```bash
git clone https://github.com/aichadigital/larafactu.git
cd larafactu
composer install --no-dev --optimize-autoloader
chmod -R 775 storage bootstrap/cache
```

---

## Acceso al Wizard

### 1. Obtener el Token de Acceso

El wizard genera un token de seguridad en su primer acceso. Este token está en:

```
installer/storage/.token
```

Abra este archivo y copie el token (formato UUID).

**Por SSH:**
```bash
cat installer/storage/.token
```

**Por FTP:**
Navegue a `installer/storage/` y abra `.token` con un editor de texto.

### 2. Acceder al Wizard

Abra en su navegador:

```
https://su-dominio.com/installer/public/
```

Introduzca el token cuando se le solicite.

### 3. Seguridad del Token

- El token se bloquea a su IP después del primer acceso válido
- Máximo 5 intentos fallidos → bloqueo de 15 minutos
- La sesión expira después de 60 minutos de inactividad

---

## Pasos del Wizard

### Paso 1: Bienvenida

- Seleccione idioma (Español/English)
- Revise la lista de configuraciones que se realizarán

### Paso 2: Verificación de Requisitos

El sistema verificará:

- ✅ Versión de PHP (>= 8.4)
- ✅ Extensiones requeridas
- ✅ Permisos de escritura

**Si hay errores**: Corrija los requisitos indicados y recargue la página.

### Paso 3: Clave de Aplicación

- Se genera automáticamente una clave de encriptación AES-256
- **CRÍTICO**: Esta clave se usa para encriptar todos los datos sensibles
- Si ya existe una clave válida, puede conservarla o regenerarla

### Paso 4: Base de Datos

Configure la conexión MySQL:

| Campo | Descripción | Ejemplo |
|-------|-------------|---------|
| Host | Servidor MySQL | `localhost` o `127.0.0.1` |
| Puerto | Puerto MySQL | `3306` |
| Base de datos | Nombre de la BD | `larafactu` |
| Usuario | Usuario MySQL | `larafactu_user` |
| Contraseña | Contraseña MySQL | `********` |

La opción "Crear base de datos si no existe" intentará crear la BD si tiene permisos.

### Paso 5: Migraciones

- Se crean todas las tablas necesarias
- **Fresh**: Elimina tablas existentes y las recrea (solo para instalación limpia)
- **Seeders**: Opcional, añade datos de demostración

### Paso 6: Datos de Empresa

Configure los datos fiscales de su empresa:

| Campo | Descripción | Requerido |
|-------|-------------|-----------|
| Razón social | Nombre legal de la empresa | ✅ |
| Nombre comercial | Nombre de marca | ❌ |
| NIF/CIF | Identificación fiscal | ✅ |
| Dirección | Dirección fiscal completa | ✅ |
| Email | Email de facturación | ✅ |
| Operador ROI | Si opera intracomunitariamente | ❌ |
| VAT EU | Número VAT europeo (si ROI) | Condicional |

### Paso 7: Verifactu (Opcional)

Configure la integración con el sistema Verifactu de la AEAT:

- **Nativo**: Comunicación directa con AEAT usando su certificado digital
- **API**: Usar servicio API externo
- **Deshabilitado**: Configurar más tarde

Si selecciona "Nativo", deberá subir su certificado digital (.p12, .pfx, etc.) y su contraseña. El certificado se almacena **encriptado**.

### Paso 8: Administrador

Cree la cuenta del superadministrador:

- Nombre
- Email (será el login)
- Contraseña (mínimo 8 caracteres, mayúscula, minúscula, número)

### Paso 9: Finalización

- Se crea el marcador de instalación en la base de datos
- Se optimizan los cachés
- Se muestra el resumen de la instalación

---

## Post-Instalación

### ⚠️ CRÍTICO: Eliminar el Directorio Installer

**Debe eliminar el directorio `installer/` inmediatamente después de completar la instalación.**

```bash
rm -rf installer/
```

El sistema tiene protecciones:
- **Grace period de 24 horas**: Muestra advertencias
- **Bloqueo automático**: Después de 24h sin eliminar, la aplicación se bloquea

### Configuración Adicional

Después de la instalación, puede configurar desde el panel de administración:

- Tipos impositivos adicionales
- Plantillas de facturas
- Configuración de email
- Usuarios adicionales

---

## Solución de Problemas

### "Token inválido"

- Verifique que está copiando el token completo desde `.token`
- El token distingue mayúsculas/minúsculas
- Si cambió de IP, regenere el token eliminando `.token`

### "Acceso bloqueado"

- Espere 15 minutos, o
- Elimine `installer/storage/failed_attempts.log`

### "Sesión expirada"

- La sesión dura 60 minutos
- Elimine `.token` y recargue para obtener un nuevo token

### Error de conexión a base de datos

- Verifique que MySQL está ejecutándose
- Verifique usuario/contraseña
- Verifique que el usuario tiene permisos CREATE DATABASE (si usa esa opción)

### Errores de permisos

```bash
# Linux/Mac
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Solo storage del installer
chmod -R 755 installer/storage
```

### El wizard ya se completó pero necesito reinstalar

1. Elimine el archivo `installer/.done`
2. Opcional: Elimine `installer/storage/install_session.json`
3. Acceda de nuevo al wizard

---

## Documentación Relacionada

- [ADR-WZ-001: Arquitectura del Wizard](wizard/ADR-WZ-001_ARCHITECTURE.md)
- [TODO Wizard](wizard/TODO_WIZARD.md)
- [Instalación CLI (Deprecated)](PRODUCTION_INSTALL.md)

---

**Versión del Wizard**: 1.0.0  
**Última actualización**: 2026-01-07

