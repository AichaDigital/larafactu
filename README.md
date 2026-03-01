# 🚀 Larafactu

**Modern Billing & Invoicing Platform for Hosting Companies with Spanish Tax Compliance (Verifactu)**

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat&logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.4+-777BB4?style=flat&logo=php)](https://php.net)
![TailwindCSS](https://img.shields.io/badge/tailwindcss-%2338B2AC.svg?style=flat&logo=tailwind-css&logoColor=white)
![DaisyUI](https://img.shields.io/badge/daisyui-5A0EF8?style=flat&logo=daisyui&logoColor=white)
[![Pest](https://img.shields.io/badge/Tested-Pest-22C55E?style=flat)](https://pestphp.com)
[![License](https://img.shields.io/badge/License-AGPL--3.0-blue.svg)](LICENSE)

---

## 🌍 Multi-Language

- [🇪🇸 Español](#español)
- [🇬🇧 English](#english)

---

# Español

## 📋 Sobre Larafactu

**Larafactu** es una plataforma completa de facturación y billing diseñada específicamente para **empresas de hosting en España** con cumplimiento fiscal automático (Verifactu AEAT).

### ✨ Características Principales

- 🧾 **Facturación Completa** - Facturas, presupuestos, notas de crédito
- 🇪🇺 **ROI/OSS** - Operador intracomunitario (B2B reverse charge)
- 🏛️ **Verifactu AEAT** - Integración nativa con el sistema español
- 💰 **Base 100** - Cálculos monetarios precisos sin errores de float
- 🎫 **Soporte Integrado** - Sistema de tickets incorporado
- 📊 **Panel Admin** - Livewire + DaisyUI para gestion completa
- 🔐 **UUID v7** - Seguridad contra ataques de descubrimiento

### 🎯 Mercado Objetivo

| Aspecto | Descripción |
|---------|-------------|
| **Industria** | Hosting (dominios, VPS, servidores dedicados) |
| **Región** | España + UE (operadores intracomunitarios) |
| **Fiscal** | Verifactu, IVA 21%, IGIC, IPSI, ROI |
| **Migración** | Compatible con WHMCS |

### 📦 Arquitectura Modular

Larafactu está construido sobre paquetes Laravel independientes y reutilizables:

```
aichadigital/larabill        → Core de facturación y billing
aichadigital/lararoi         → Lógica fiscal ROI/OSS
aichadigital/lara-verifactu  → Integración AEAT Verifactu
aichadigital/laratickets     → Sistema de tickets
aichadigital/lara100         → Valores monetarios base 100
```

## 🚀 Instalación Rápida

### Requisitos

- PHP 8.4+
- MySQL 8.0+ / PostgreSQL 15+
- Composer 2.x
- Node.js 20+

### 🏠 Desarrollo Local

```bash
# 1. Clonar repositorio
git clone https://github.com/AichaDigital/larafactu.git
cd larafactu

# 2. Copiar .env y configurar base de datos
cp .env.example .env

# 3. Instalar dependencias
composer install
npm install && npm run build

# 4. Generar clave y migrar
php artisan key:generate
php artisan migrate --seed

# 5. Activar git hooks (pre-commit con Pint)
git config core.hooksPath bin/hooks
```

### 🎉 ¡Listo!

- **Frontend**: http://larafactu.test
- **Admin**: http://larafactu.test/admin
- **Credenciales**: `admin@example.com` / `password`

---

### 🌐 Producción / Pre-producción

**Pasos detallados para instalación en servidor:**

#### Paso 1: Clonar y Preparar

```bash
# Clonar repositorio
git clone https://github.com/AichaDigital/larafactu.git
cd larafactu

# Convertir repositorios de paquetes (path → GitHub)
php scripts/post-deploy.php
```

#### Paso 2: Instalar Dependencias

```bash
# Composer (sin dev dependencies)
composer install --no-dev --optimize-autoloader --no-interaction

# Si pide GitHub token (rate limit), crearlo en:
# https://github.com/settings/tokens (read:packages)
```

#### Paso 3: Configurar Entorno

```bash
# Crear .env desde example
cp .env.example .env

# Editar con tus datos reales
nano .env  # o vim, vi, etc.
```

**⚠️ DIFERENCIAS: Local vs Producción**

| Variable | 🏠 Local | 🚀 Producción | Nota |
|----------|---------|---------------|------|
| `APP_ENV` | `local` | `production` | ⚠️ Afecta acceso admin |
| `APP_DEBUG` | `true` | `false` | ⚠️ NUNCA true en vivo |
| `APP_URL` | `.test` | `.com` real | URLs absolutas |
| `LOG_LEVEL` | `debug` | `error` | Menos ruido |
| `MAIL_MAILER` | `log` | `smtp` | Email real |
| `ADMIN_EMAILS` | *(todos)* | **REQUERIDO** | Sin esto: 403 |
| `ADMIN_DOMAINS` | *(todos)* | **REQUERIDO** | Sin esto: 403 |

**Variables CRÍTICAS en `.env`:**

```env
APP_NAME=Larafactu
APP_ENV=production           # ⚠️ Cambia comportamiento de admin access
APP_DEBUG=false              # ⚠️ NUNCA true en producción
APP_URL=https://tudominio.com

# Admin Panel Access Control (IMPORTANTE)
# Sin esto, NADIE podrá acceder al panel (excepto en local)
ADMIN_EMAILS=admin@tuempresa.com,manager@tuempresa.com
ADMIN_DOMAINS=@tuempresa.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=larafactu_db
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_password

# Redis (si usas socket Unix)
REDIS_CLIENT=phpredis
REDIS_HOST=/path/to/redis.sock
REDIS_PORT=0

# Mail, Queue, Cache según tu setup...
```

#### Paso 4: Generate Key

```bash
php artisan key:generate --force
```

#### Paso 5: Migrar Tablas Base de Laravel

```bash
# IMPORTANTE: Migrar PRIMERO las tablas base de Laravel
# (users, password_resets, sessions, etc.)
php artisan migrate --force
```

**Tablas creadas en este paso:**
- `users` (requerida por larabill:install)
- `password_reset_tokens`
- `sessions`
- `cache`, `jobs` (si usas queue/cache en DB)

#### Paso 6: Instalar Larabill (CRÍTICO)

```bash
# Publica migraciones y configuraciones del paquete
php artisan larabill:install --no-interaction
```

**Qué hace `larabill:install`:**
- ✅ Publica 30+ migraciones adicionales de facturación
- ✅ Publica configuraciones del paquete
- ✅ Verifica que la tabla `users` exista (paso anterior)
- ✅ En producción: **NO** intenta migrar automáticamente (solo publica)

**Output esperado:**
```
🚀 Installing Larabill...
✓ User ID type: uuid_binary
📝 Publishing configurations...
📄 Publishing migrations in correct order...
✓ Published 30 migrations
✓ Migrations published successfully

📋 Next step:
   Run migrations: php artisan migrate --force
```

#### Paso 7: Migrar Tablas de Larabill

```bash
# Crear todas las tablas de facturación
php artisan migrate --force
```

**Tablas creadas en este paso:**
- `invoices`, `invoice_items`, `fiscal_settings`
- `customers`, `tax_rates`, `tax_categories`
- `articles`, `commissions`, `vat_verifications`
- Y 20+ tablas más para el sistema completo

#### Paso 8: Optimizar

```bash
# Limpiar cache
php artisan config:clear
php artisan cache:clear

# Cachear para producción
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### Paso 9 (Opcional): Compilar Assets Frontend

```bash
npm install
npm run build
```

---

### 🔄 Script de Deploy Automático

Para **actualizaciones futuras**, usa el script:

```bash
./scripts/deploy.sh
```

**El script automáticamente:**
1. 💾 Hace backup (.env, composer.json, BD)
2. 🔒 Entra en modo mantenimiento
3. 📥 Pull del último código (git reset hard)
4. 🔧 Convierte repositories (post-deploy.php)
5. 📦 Actualiza dependencias (composer install)
6. 🗄️ Corre migraciones (con confirmación)
7. 🧹 Limpia y cachea
8. 🔓 Sale de modo mantenimiento

Más detalles: [docs/UPDATE_MANAGEMENT.md](docs/UPDATE_MANAGEMENT.md)

## ⚠️ Laravel Boost - Configuración Crítica

**IMPORTANTE PARA DESARROLLO Y PRODUCCIÓN**

Laravel Boost es una herramienta MCP para desarrollo asistido por IA, pero **puede causar problemas graves** si no se configura correctamente.

### Problemas Conocidos

- **Rendimiento degradado**: Páginas que tardan 30+ segundos, errores 502
- **Seguridad en producción**: Expone credenciales, tokens, estructura de BD
- **Procesos múltiples**: Acumulación de instancias `php artisan boost:mcp`

### Configuración Obligatoria

**1. Variables de entorno (.env)**

```env
# CRITICAL: MUST be false in production
BOOST_ENABLED=true
BOOST_BROWSER_LOGS_WATCHER=false  # Desactivado (causa problemas de rendimiento)
```

**2. Producción**

```env
APP_ENV=production
BOOST_ENABLED=false  # OBLIGATORIO en producción
```

**NUNCA** dejes Laravel Boost activo en producción. Expone información sensible.

### Diagnóstico Rápido

Si experimentas lentitud o errores 502:

```bash
# Detener procesos de Boost
pkill -f "boost:mcp"

# Limpiar cache
php artisan optimize:clear

# Reiniciar servicios
herd restart  # o tu servidor web
```

📖 **Documentación completa**: [Laravel Boost - Problemas de Rendimiento y Seguridad](https://wiki.castris.com/books/laravel/page/laravel-boost-problemas-de-rendimiento-y-seguridad)

---

## 📚 Documentación

### Instalación
- [docs/PRODUCTION_WEB_INSTALL.md](docs/PRODUCTION_WEB_INSTALL.md) - **Guía de instalación web (producción)** ⭐
- [docs/DEVELOPMENT_WIZARD_TESTING.md](docs/DEVELOPMENT_WIZARD_TESTING.md) - Testing del wizard de instalación (desarrollo)

### Configuración
- [STAGING_SETUP.md](STAGING_SETUP.md) - Configuración completa de staging/pre-producción
- [docs/DEVELOPMENT_COMMANDS.md](docs/DEVELOPMENT_COMMANDS.md) - Comandos útiles de desarrollo

### Wizard de Instalación
- [docs/wizard/TODO_WIZARD.md](docs/wizard/TODO_WIZARD.md) - Roadmap del wizard
- [docs/wizard/ADR-WZ-001_ARCHITECTURE.md](docs/wizard/ADR-WZ-001_ARCHITECTURE.md) - Arquitectura del wizard (sin framework)

## 🧪 Testing

```bash
# Tests completos
php artisan test

# Con cobertura
composer test-coverage

# Solo Invoice tests
php artisan test --filter=Invoice
```

**Cobertura objetivo**:
- Paquetes: 80-95%
- App staging: 60-70%

## 🛠️ Stack Tecnológico

| Componente | Tecnología | Versión |
|------------|-----------|---------|
| **Framework** | Laravel | 12.x |
| **PHP** | PHP | 8.4+ |
| **Admin Panel** | Livewire + DaisyUI | 3.x + 5.x |
| **Testing** | Pest | 3.x |
| **Frontend** | TailwindCSS + Alpine.js | 4.x + 3.x |
| **Database** | MySQL / PostgreSQL | 8.0+ / 15+ |

## 📋 Roadmap

### ✅ v1.0 (15 dic 2025)

- [x] Sistema de facturación completo
- [x] Integración Verifactu AEAT
- [x] ROI/OSS para intracomunitario
- [x] Panel admin Livewire + DaisyUI
- [x] UUID v7 nativo
- [ ] Portal de clientes
- [ ] Herramienta migración WHMCS
- [ ] Pasarelas de pago

### 🚧 v2.0 (Q1 2026)

- [ ] Multi-tenant SaaS
- [ ] Más jurisdicciones fiscales
- [ ] API pública
- [ ] Integraciones (Stripe, PayPal, etc.)

## 🤝 Contribuir

Este proyecto es **staging/pre-producción** para validar paquetes. Para contribuir:

1. **Reporta issues** en los paquetes individuales
2. **Pull requests** en [GitHub](https://github.com/AichaDigital)
3. **Documentación** siempre bienvenida

### Paquetes Principales

- [larabill](https://github.com/AichaDigital/larabill) - Sistema de facturación
- [lararoi](https://github.com/AichaDigital/lararoi) - Lógica fiscal ROI
- [lara-verifactu](https://github.com/AichaDigital/lara-verifactu) - AEAT Verifactu

## 📄 Licencia

AGPL-3.0 License - Consulta [LICENSE](LICENSE) para más detalles.

---

## 🙏 Créditos

Desarrollado con ❤️ por [Aicha Digital](https://aichadigital.com)

---

# English

## 📋 About Larafactu

**Larafactu** is a complete billing and invoicing platform designed specifically for **hosting companies in Spain** with automatic tax compliance (Verifactu AEAT).

### ✨ Key Features

- 🧾 **Complete Invoicing** - Invoices, quotes, credit notes
- 🇪🇺 **ROI/OSS** - Intra-community operator (B2B reverse charge)
- 🏛️ **Verifactu AEAT** - Native integration with Spanish tax system
- 💰 **Base 100** - Precise monetary calculations without float errors
- 🎫 **Integrated Support** - Built-in ticket system
- 📊 **Admin Panel** - Livewire + DaisyUI for complete management
- 🔐 **UUID v7** - Security against discovery attacks

### 🎯 Target Market

| Aspect | Description |
|--------|-------------|
| **Industry** | Hosting (domains, VPS, dedicated servers) |
| **Region** | Spain + EU (intra-community operators) |
| **Tax** | Verifactu, VAT 21%, IGIC, IPSI, ROI |
| **Migration** | WHMCS compatible |

### 📦 Modular Architecture

Larafactu is built on independent and reusable Laravel packages:

```
aichadigital/larabill        → Billing & invoicing core
aichadigital/lararoi         → ROI/OSS tax logic
aichadigital/lara-verifactu  → AEAT Verifactu integration
aichadigital/laratickets     → Ticket system
aichadigital/lara100         → Base-100 monetary values
```

## 🚀 Quick Installation

### Requirements

- PHP 8.4+
- MySQL 8.0+ / PostgreSQL 15+
- Composer 2.x
- Node.js 20+

### Steps

```bash
# 1. Clone repository
git clone https://github.com/yourorg/larafactu.git
cd larafactu

# 2. Install dependencies
composer install
npm install && npm run build

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Configure database
# Edit .env with your DB credentials

# 5. Migrate and seed
php artisan migrate:fresh --seed

# 6. Serve (development)
php artisan serve
```

### 🎉 Done!

- **Frontend**: http://localhost:8000
- **Admin**: http://localhost:8000/admin
- **Credentials**: `admin@example.com` / `password`

## ⚠️ Laravel Boost - Critical Configuration

**IMPORTANT FOR DEVELOPMENT AND PRODUCTION**

Laravel Boost is an MCP tool for AI-assisted development, but **can cause serious issues** if not configured correctly.

### Known Issues

- **Performance degradation**: Pages taking 30+ seconds, 502 errors
- **Production security**: Exposes credentials, tokens, DB structure
- **Multiple processes**: Accumulation of `php artisan boost:mcp` instances

### Required Configuration

**1. Environment variables (.env)**

```env
# CRITICAL: MUST be false in production
BOOST_ENABLED=true
BOOST_BROWSER_LOGS_WATCHER=false  # Disabled (causes performance issues)
```

**2. Production**

```env
APP_ENV=production
BOOST_ENABLED=false  # MANDATORY in production
```

**NEVER** leave Laravel Boost active in production. It exposes sensitive information.

### Quick Diagnosis

If experiencing slowness or 502 errors:

```bash
# Stop Boost processes
pkill -f "boost:mcp"

# Clear cache
php artisan optimize:clear

# Restart services
herd restart  # or your web server
```

📖 **Full documentation**: [Laravel Boost - Performance and Security Issues](https://wiki.castris.com/books/laravel/page/laravel-boost-problemas-de-rendimiento-y-seguridad)

---

## 📚 Documentation

### Installation
- [docs/PRODUCTION_WEB_INSTALL.md](docs/PRODUCTION_WEB_INSTALL.md) - **Web installation guide (production)** ⭐
- [docs/DEVELOPMENT_WIZARD_TESTING.md](docs/DEVELOPMENT_WIZARD_TESTING.md) - Installation wizard testing (development)

### Configuration
- [STAGING_SETUP.md](STAGING_SETUP.md) - Complete staging/pre-production setup
- [docs/DEVELOPMENT_COMMANDS.md](docs/DEVELOPMENT_COMMANDS.md) - Useful development commands

### Installation Wizard
- [docs/wizard/TODO_WIZARD.md](docs/wizard/TODO_WIZARD.md) - Wizard roadmap
- [docs/wizard/ADR-WZ-001_ARCHITECTURE.md](docs/wizard/ADR-WZ-001_ARCHITECTURE.md) - Wizard architecture (framework-less)

## 🧪 Testing

```bash
# Full test suite
php artisan test

# With coverage
composer test-coverage

# Invoice tests only
php artisan test --filter=Invoice
```

**Coverage targets**:
- Packages: 80-95%
- Staging app: 60-70%

## 🛠️ Tech Stack

| Component | Technology | Version |
|-----------|-----------|---------|
| **Framework** | Laravel | 12.x |
| **PHP** | PHP | 8.4+ |
| **Admin Panel** | Livewire + DaisyUI | 3.x + 5.x |
| **Testing** | Pest | 3.x |
| **Frontend** | TailwindCSS + Alpine.js | 4.x + 3.x |
| **Database** | MySQL / PostgreSQL | 8.0+ / 15+ |

## 📋 Roadmap

### ✅ v1.0 (Dec 15, 2025)

- [x] Complete invoicing system
- [x] Verifactu AEAT integration
- [x] ROI/OSS for intra-community
- [x] Livewire + DaisyUI admin panel
- [x] Native UUID v7
- [ ] Customer portal
- [ ] WHMCS migration tool
- [ ] Payment gateways

### 🚧 v2.0 (Q1 2026)

- [ ] Multi-tenant SaaS
- [ ] More tax jurisdictions
- [ ] Public API
- [ ] Integrations (Stripe, PayPal, etc.)

## 🤝 Contributing

This project is **staging/pre-production** for package validation. To contribute:

1. **Report issues** in individual packages
2. **Pull requests** on [GitHub](https://github.com/AichaDigital)
3. **Documentation** always welcome

### Main Packages

- [larabill](https://github.com/AichaDigital/larabill) - Billing system
- [lararoi](https://github.com/AichaDigital/lararoi) - ROI tax logic
- [lara-verifactu](https://github.com/AichaDigital/lara-verifactu) - AEAT Verifactu

## 📄 License

AGPL-3.0 License - See [LICENSE](LICENSE) for details.

---

## 🙏 Credits

Built with ❤️ by [Aicha Digital](https://aichadigital.com)

---

**Last updated**: November 28, 2025  
**Version**: 1.0.0-staging  
**Target v1.0 stable**: December 15, 2025
