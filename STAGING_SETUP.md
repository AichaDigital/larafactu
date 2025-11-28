# 🚀 Larafactu - Proyecto Staging/Pre-Producción

> **Plataforma de Facturación y Billing para Hosting + Cumplimiento Fiscal España (Verifactu)**

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat&logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.4+-777BB4?style=flat&logo=php)](https://php.net)
[![Filament](https://img.shields.io/badge/Filament-4.x-FFAA00?style=flat)](https://filamentphp.com)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

---

## 📋 Sobre el Proyecto

**Larafactu** es un proyecto de **staging/pre-producción** que sirve como:

1. **Entorno de pruebas** para los paquetes en desarrollo:
   - `aichadigital/larabill` - Sistema completo de facturación y billing
   - `aichadigital/lararoi` - Lógica fiscal ROI/OSS para operadores intracomunitarios
   - `aichadigital/lara-verifactu` - Integración con AEAT Verifactu (España)
   - `aichadigital/laratickets` - Sistema de tickets de soporte
   - `aichadigital/lara100` - Gestión monetaria sin float (base 100)

2. **Demostración funcional** del ecosistema completo de paquetes trabajando juntos

3. **Base para migración WHMCS** → Larafactu (objetivo v1.0 - 15 dic 2025)

### 🎯 Mercado Objetivo (v1.0)

- **Industria**: Empresas de hosting (dominios, VPS, servidores dedicados)
- **Región**: España + Operadores Intracomunitarios (OSS/ROI)
- **Fiscal**: Cumplimiento Verifactu AEAT
- **Migración**: Desde WHMCS

---

## 🏗️ Arquitectura Técnica

### UUID Strategy

**Decisión técnica (Nov 2025)**: Migración de UUID Binary → **UUID String Nativo**

| Aspecto | UUID Binary (v0.x) | UUID String (v1.0+) |
|---------|-------------------|---------------------|
| **Storage** | `varbinary(16)` | `char(36)` |
| **Paquete** | dyrynda/laravel-model-uuid | Laravel nativo |
| **Filament** | ❌ Problemas serialización | ✅ Compatible |
| **Debugging** | ❌ Difícil (hex) | ✅ Legible |
| **Tech Debt** | ❌ Dependencia externa | ✅ Cero |

**Implementación**:
```php
use AichaDigital\Larabill\Concerns\HasUuid;

class Invoice extends Model
{
    use HasUuid;  // UUID v7 nativo de Laravel
}
```

### Stack Tecnológico

```
Laravel 12.39 (PHP 8.4.15)
├── Filament 4.1     → Admin Panel
├── Livewire 3.6     → Reactivity
├── Tailwind 4.1     → CSS
├── Pest 4.1         → Testing (Browser + Unit)
└── MySQL 8.0        → Database
```

### Paquetes en Desarrollo (Symlinks)

```bash
/Users/abkrim/
├── development/packages/aichadigital/  # Source
│   ├── larabill/
│   ├── lararoi/
│   ├── lara-verifactu/
│   └── laratickets/
└── SitesLR12/larafactu/                # Staging
    └── packages/aichadigital/          # Symlinks
        ├── larabill -> /Users/.../larabill
        ├── lararoi -> /Users/.../lararoi
        ├── lara-verifactu -> /Users/.../lara-verifactu
        └── laratickets -> /Users/.../laratickets
```

**Ventaja**: Cambios en paquetes se reflejan **inmediatamente** sin `composer update`

---

## 🚀 Instalación Pre-Producción

### Requisitos

- PHP 8.4+
- MySQL 8.0+
- Composer 2.x
- Node.js 20+

### 1. Clonar y Configurar

```bash
git clone https://github.com/tuorg/larafactu.git
cd larafactu
composer install
npm install && npm run build
cp .env.example .env
php artisan key:generate
```

### 2. Configurar Base de Datos

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=larafactu
DB_USERNAME=root
DB_PASSWORD=

# Larabill UUID Type (IMPORTANTE)
LARABILL_USER_ID_TYPE=uuid
```

### 3. Migrar y Seedear

```bash
php artisan migrate:fresh --seed
```

**Resultado**: Se crean automáticamente:
- ✅ Admin: `admin@example.com` / `password`
- ✅ Test: `test@example.com` / `password`
- ✅ Configuración fiscal básica

### 4. Acceder

- **Frontend**: http://localhost (landing page bilingüe)
- **Admin Panel**: http://localhost/admin
- **Credenciales**: `admin@example.com` / `password`

---

## 👥 Usuarios de Desarrollo

### Sistema de Seeding Automático

El proyecto incluye `DevelopmentSeeder` que se ejecuta automáticamente en local/testing:

```bash
# Opción 1: Con migrate:fresh
php artisan migrate:fresh --seed

# Opción 2: Solo seed
php artisan dev:seed

# Opción 3: Fresh + seed en un comando
php artisan dev:seed --fresh
```

### Usuarios Creados

| Email | Password | Rol | Uso |
|-------|----------|-----|-----|
| `admin@example.com` | `password` | Admin | Panel Filament |
| `test@example.com` | `password` | Cliente | Testing API/Frontend |

**Protección**: Los seeders solo funcionan en entornos `local` y `testing`

---

## 📦 Configuración de Paquetes

### Larabill

```env
LARABILL_USER_ID_TYPE=uuid
LARABILL_COMPANY_VAT=ESB12345678
LARABILL_COMPANY_IS_ROI=true
```

### Verifactu

```env
VERIFACTU_MODE=native
VERIFACTU_ENVIRONMENT=sandbox
VERIFACTU_NIF=ESB12345678
```

### Lararoi

```env
LARAROI_OSS_ENABLED=true
LARAROI_OPERATOR_VAT=ESB12345678
```

---

## 🛠️ Comandos Útiles

### Desarrollo

```bash
# Seed de desarrollo (solo usuarios y fiscal)
php artisan dev:seed

# Formatear código
composer pint

# Tests
php artisan test
composer test-coverage

# Limpiar caché
php artisan config:clear && php artisan cache:clear
```

### Larabill

```bash
php artisan larabill:install    # Setup inicial
php artisan larabill:status     # Estado del sistema
```

### Verifactu

```bash
php artisan verifactu:test-connection
php artisan verifactu:register
php artisan verifactu:status
php artisan verifactu:verify-blockchain
```

---

## 🧪 Testing

### Cobertura Pragmática

- **Paquetes**: 80-95% (crítico)
- **Staging**: 60-70% (integración)

### Ejecutar Tests

```bash
# Todos los tests
php artisan test

# Con cobertura
composer test-coverage

# Filtrar
php artisan test --filter=Invoice
```

### Tests de Navegador (Pest 4)

```php
it('puede crear una factura', function () {
    $page = visit('/admin/invoices/create');
    
    $page->fill('prefix', 'FAC')
         ->select('serie', 'INVOICE')
         ->click('Crear')
         ->assertSee('Factura creada');
});
```

---

## 📊 Base de Datos

### Tablas Principales

**Core**:
- `users` (UUID string, char(36))
- `sessions` (user_id string para UUID)

**Larabill**:
- `invoices` (UUID primary key)
- `invoice_items` (integer PK, UUID FK)
- `customers`, `tax_rates`, `tax_groups`
- `company_fiscal_configs`

**Verifactu**:
- `verifactu_invoices`
- `verifactu_registries`
- `verifactu_invoice_breakdowns`

**Laratickets**:
- `tickets` (UUID primary key)
- `ticket_assignments`, `escalation_requests`

---

## 🎨 Frontend

### Landing Page Bilingüe (ES/EN)

- **URL**: `/` (index)
- **Diseño**: Oscuro, moderno, responsive
- **Contenido**: Descripción del proyecto, stack, enlaces útiles
- **i18n**: Español e Inglés con toggle

### Admin Panel (Filament 4)

- **URL**: `/admin`
- **Recursos**:
  - ✅ Invoices (CRUD completo + Items)
  - ✅ Fiscal Settings
  - 🚧 Customers (próximamente)
  - 🚧 Products (próximamente)

---

## 🔒 Seguridad Pre-Producción

### Checklist

- [ ] Cambiar `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] Contraseñas seguras (no `password`)
- [ ] Configurar `SANCTUM_STATEFUL_DOMAINS`
- [ ] SSL/HTTPS obligatorio
- [ ] Rate limiting configurado
- [ ] Backups automáticos de DB

### Protecciones Activas

```php
// DevelopmentSeeder solo en local/testing
if (! app()->environment(['local', 'testing'])) {
    $this->command->error('❌ DevelopmentSeeder can only run in local/testing.');
    return;
}
```

---

## 📝 Notas de Versión

### v1.0.0-staging (Actual)

**✅ Completado**:
- Migración UUID Binary → UUID String
- InvoiceResource completo con RelationManager
- DevelopmentSeeder automático
- Tests pasando (3/3 Invoice tests)
- Paquetes actualizados en GitHub

**🚧 En Progreso**:
- Landing page bilingüe
- CustomerResource
- Widgets/Stats para dashboard

**📅 Roadmap v1.0 (15 dic 2025)**:
- [ ] WHMCS migration tool
- [ ] Customer portal
- [ ] Email templates
- [ ] PDF generation
- [ ] Payment gateways

---

## 🐛 Troubleshooting

### Error: "Data too long for column 'user_id'"

**Solución**: La columna user_id debe ser `char(36)` no `bigint`:

```sql
ALTER TABLE invoices MODIFY COLUMN user_id CHAR(36) NULL;
```

### Error: "Malformed UTF-8 characters"

**Causa**: Sesiones con binary UUID cuando User usa string UUID

**Solución**:
```bash
php artisan tinker --execute="DB::table('sessions')->truncate();"
```

### Paquetes no se actualizan

**Verificar symlinks**:
```bash
ls -la packages/aichadigital/
# Deben mostrar -> /Users/abkrim/development/...
```

---

## 📚 Documentación Adicional

- [README.md](README.md) - Documentación general
- [DEVELOPMENT_COMMANDS.md](docs/DEVELOPMENT_COMMANDS.md) - Comandos útiles
- [Paquetes en GitHub](https://github.com/AichaDigital)

---

## 🤝 Contribución

Este es un proyecto privado de staging. Para contribuir a los paquetes públicos:

- [larabill](https://github.com/AichaDigital/larabill)
- [lararoi](https://github.com/AichaDigital/lararoi)
- [lara-verifactu](https://github.com/AichaDigital/lara-verifactu)

---

## 📄 Licencia

MIT License - Ver [LICENSE](LICENSE) para más detalles.

---

**Última actualización**: 28 de noviembre de 2025  
**Versión**: 1.0.0-staging  
**Objetivo v1.0 estable**: 15 de diciembre de 2025
