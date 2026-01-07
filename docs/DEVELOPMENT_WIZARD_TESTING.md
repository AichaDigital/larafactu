# Testing del Wizard de Instalación en Desarrollo

> Esta guía explica cómo probar el wizard de instalación en un entorno de desarrollo aislado.

## Principio Fundamental

**NUNCA probar el wizard en la instalación de desarrollo principal (`larafactu.test`).**

El wizard está diseñado para instalaciones nuevas. Probarlo en tu entorno de desarrollo principal podría:
- Sobrescribir tu `.env`
- Eliminar/recrear tablas
- Corromper datos de desarrollo

---

## Métodos de Testing

### Método 1: Docker (Recomendado)

El wizard incluye una configuración Docker para testing aislado.

#### Requisitos

- Docker Desktop instalado y ejecutándose
- Puertos disponibles: 8888 (web), 3307 (MySQL)

#### Estructura Docker

```
installer/docker/
├── docker-compose.yml    # Servicios: nginx, php-fpm, mysql
├── Dockerfile            # PHP 8.4 + todas las extensiones
└── nginx.conf            # Configuración Nginx
```

#### Directorio de Trabajo Recomendado

Usa un directorio dedicado para pruebas Docker (NO `/tmp` que se limpia al reiniciar):

```bash
# Crear estructura (una vez)
mkdir -p ~/SitesDocker

# Clonar para pruebas
cd ~/SitesDocker
git clone https://github.com/AichaDigital/larafactu.git larafactu-wizard-test
```

#### Ejecutar Tests

```bash
# Ir al directorio de prueba
cd ~/SitesDocker/larafactu-wizard-test/installer/docker

# Primera vez: construir imagen
docker-compose build

# Levantar servicios
docker-compose up -d

# Ver logs (opcional)
docker-compose logs -f

# Obtener token de acceso
docker-compose exec php-fpm cat /var/www/installer/storage/.token

# Acceder al wizard
open http://localhost:8888/

# Parar servicios (mantiene datos)
docker-compose stop

# Parar y limpiar todo (reset completo)
docker-compose down -v
```

#### Actualizar después de cambios

```bash
cd ~/SitesDocker/larafactu-wizard-test
git pull origin main

# Reconstruir si cambió Dockerfile
docker-compose build --no-cache
docker-compose up -d
```

#### Puertos y Credenciales

| Servicio | Puerto Host | Puerto Container |
|----------|-------------|------------------|
| Nginx    | 8888        | 80               |
| MySQL    | 3307        | 3306             |

Estos puertos están elegidos para no conflictuar con servicios locales (Herd, Elasticsearch).

**Credenciales MySQL para el wizard:**

| Campo | Valor |
|-------|-------|
| Host | `mysql` (nombre del servicio Docker) |
| Puerto | `3306` (interno, no 3307) |
| Base de datos | `larafactu_test` |
| Usuario | `larafactu` |
| Contraseña | `larafactu` |

> **Nota**: Desde el contenedor PHP, el host es `mysql` (nombre del servicio), NO `127.0.0.1`.
> El puerto 3307 es solo para acceso externo desde tu máquina host.

### Método 2: Instalación Limpia con PHP Built-in

Si prefieres probar sin Docker (más rápido para cambios pequeños):

```bash
# Crear directorio de prueba
mkdir -p ~/SitesDocker/larafactu-php-test
cd ~/SitesDocker/larafactu-php-test

# Clonar el proyecto
git clone https://github.com/aichadigital/larafactu.git .

# Configurar servidor PHP built-in
cd installer/public
php -S localhost:9000

# Acceder
open http://localhost:9000/
```

**Nota**: Este método usa TU instalación local de PHP, no la del Dockerfile.

### Método 3: Subdirectorio en Herd

Si usas Laravel Herd:

```bash
# Crear site separado para testing
cd ~/Herd
mkdir larafactu-wizard-test
cd larafactu-wizard-test

# Copiar proyecto limpio
git clone https://github.com/aichadigital/larafactu.git .

# Herd lo detectará automáticamente como larafactu-wizard-test.test
# Acceder a: https://larafactu-wizard-test.test/installer/public/
```

---

## Tests Unitarios del Wizard

Los tests del wizard usan PHPUnit (no Pest, ya que está fuera de Laravel).

```bash
cd /Users/abkrim/SitesLR12/larafactu/installer

# Instalar dependencias (si hubiera composer.json propio)
# composer install

# Ejecutar tests
php vendor/bin/phpunit

# O usando el script de test
./test.sh
```

### Tests Disponibles

```
installer/tests/
├── Unit/
│   ├── TranslatorTest.php     # Tests de i18n
│   └── InstallStateTest.php   # Tests de estado
└── Integration/
    └── (pendientes)
```

---

## Flujo de Desarrollo del Wizard

### Cuando modificas código del wizard:

1. **Editar** en `/Users/abkrim/SitesLR12/larafactu/installer/`
2. **Probar** usando Docker o instalación temporal
3. **Tests** unitarios si aplica
4. **Commit** los cambios

### Estructura de commits:

```bash
# Ejemplo
git add installer/
git commit -m "feat(installer): add database validation step"
```

---

## Debugging

### Ver logs del wizard

```bash
# En Docker
docker-compose logs -f installer-test

# Estado de instalación
cat installer/storage/install_session.json

# Intentos fallidos
cat installer/storage/failed_attempts.log
```

### Reset del wizard para re-testing

```bash
# Eliminar estado
rm -f installer/storage/install_session.json
rm -f installer/storage/.token
rm -f installer/storage/failed_attempts.log
rm -f installer/.done

# En Docker, también limpiar volúmenes
docker-compose down -v
```

---

## Checklist de Testing

Antes de considerar el wizard listo para producción:

### Funcionalidad

- [ ] Paso 1: Cambio de idioma funciona (ES↔EN)
- [ ] Paso 2: Detecta PHP < 8.4 como error
- [ ] Paso 2: Detecta extensiones faltantes
- [ ] Paso 2: Detecta permisos incorrectos
- [ ] Paso 3: Genera APP_KEY válida
- [ ] Paso 4: Conexión MySQL exitosa
- [ ] Paso 4: Conexión MySQL fallida muestra error correcto
- [ ] Paso 5: Migraciones se ejecutan
- [ ] Paso 5: Fresh elimina tablas existentes
- [ ] Paso 6: Validación de NIF/CIF
- [ ] Paso 7: Upload de certificado funciona
- [ ] Paso 7: Skip de Verifactu funciona
- [ ] Paso 8: Validación de contraseña
- [ ] Paso 9: Marker se crea en DB

### Seguridad

- [ ] Token requerido para acceso
- [ ] IP locking funciona
- [ ] Rate limiting funciona (5 intentos)
- [ ] Session timeout funciona (60 min)
- [ ] Certificados se encriptan correctamente

### Post-instalación

- [ ] Middleware bloquea después de 24h
- [ ] Eliminar installer/ permite acceso normal

---

## Notas Importantes

1. **El wizard NO usa Laravel** - Es PHP puro para funcionar antes de la instalación
2. **Los tests del wizard son independientes** de los tests de Laravel
3. **Siempre probar en entorno aislado** - Nunca en desarrollo principal
4. **Docker es el método más seguro** - Entorno completamente aislado

---

**Documento**: DEVELOPMENT_WIZARD_TESTING.md  
**Última actualización**: 2026-01-07

