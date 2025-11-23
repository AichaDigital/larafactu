# 📚 Documentación en Desarrollo - Larafactu

> **Propósito**: Documentar el proceso de integración y testing de paquetes Larabill, LaraROI, Lara-Verifactu y Laratickets en el proyecto de staging Larafactu.

---

## 📋 **Índice de Documentos**

### 🔧 **Instalación y Configuración**

1. **[INSTALACION_PAQUETES.md](./INSTALACION_PAQUETES.md)**
   - Guía completa de instalación
   - Modo desarrollo local (symlinks)
   - Modo producción (GitHub/VCS)
   - Troubleshooting común

### 🧪 **Testing e Integración**

2. **[INTEGRACION_PAQUETES.md](./INTEGRACION_PAQUETES.md)**
   - Problemas detectados durante integración
   - Soluciones aplicadas localmente
   - Tabla de tracking de issues (8 problemas identificados)

3. **[RESUMEN_INTEGRACION.md](./RESUMEN_INTEGRACION.md)**
   - Resumen ejecutivo del proceso
   - Estado actual del proyecto
   - Próximos pasos

### 📝 **Historial de Sesiones**

4. **[SESION_2025_11_23_LIMPIEZA.md](./SESION_2025_11_23_LIMPIEZA.md)** ⭐ NUEVO
   - Consolidación de fixes en paquetes (v0.4.2, v0.2.1-alpha)
   - Limpieza de .gitignore (vendor-published files)
   - GitHub CI/CD pasando al 100%
   - Lecciones sobre separación de responsabilidades

5. **[CAMBIOS_PENDIENTES_PAQUETES.md](./CAMBIOS_PENDIENTES_PAQUETES.md)** ✅ RESUELTO
   - ~~Cambios aplicados en Larafactu~~ → Ya aplicados en paquetes
   - ~~Lista de mejoras críticas en Larabill~~ → Incluidas en v0.4.2
   - ~~Issues de Lara-Verifactu~~ → Resueltas en v0.2.1-alpha

---

## 🎯 **Proceso de Desarrollo**

### **Fase 1: Setup Inicial** ✅ COMPLETADO
- [x] Configuración de repositorios `path` en `composer.json`
- [x] Creación de symlinks locales
- [x] Instalación de paquetes en modo desarrollo

### **Fase 2: Integración de Migraciones** ✅ COMPLETADO
- [x] Identificación de problemas de FK y orden
- [x] Corrección de tipos incompatibles (UUID binary)
- [x] Ajuste de índices MySQL (longitud máxima)
- [x] Documentación de todos los problemas

### **Fase 3: Comandos de Instalación** ✅ COMPLETADO
- [x] Implementación de `larabill:install`
- [x] Implementación de `verifactu:install`
- [x] Implementación de `laratickets:install`
- [x] Registro manual de comandos (workaround Spatie skeleton)

### **Fase 4: Testing End-to-End** 🔄 EN PROGRESO
- [ ] Seeders para datos de prueba
- [ ] Validación de lógica de negocio
- [ ] Tests de integración con Pest
- [ ] Validación de relaciones entre paquetes

### **Fase 5: Documentación** ✅ COMPLETADO
- [x] Guía de instalación completa
- [x] Documentación de problemas conocidos
- [x] Troubleshooting y soluciones
- [x] Estructura de este README

### **Fase 6: Migración WHMCS** ⏳ PENDIENTE (v2.0)
- [ ] Análisis de schema WHMCS
- [ ] Mapeo de datos WHMCS → Larabill
- [ ] Script de migración
- [ ] Validación de integridad

---

## 🏗️ **Arquitectura del Proyecto**

### **Estructura de Directorios**

```
larafactu/                          # Aplicación Laravel (staging)
├── packages/                       # Symlinks a paquetes source
│   └── aichadigital/
│       ├── larabill -> /Users/abkrim/development/packages/aichadigital/larabill
│       ├── lara-verifactu -> ...
│       └── laratickets -> ...
├── database/
│   ├── migrations/                 # Migraciones publicadas (ignoradas en Git)
│   └── dumps/                      # Dumps SQL para testing reproducible
│       ├── 00_laravel_core_base.sql
│       └── 01_all_packages_integrated.sql
├── docs/
│   └── en-desarrollo/              # Esta documentación
└── composer.json                   # Configurado con "type": "path"
```

### **Paquetes Integrados**

| Paquete | Versión | Estado | Propósito |
|---------|---------|--------|-----------|
| **larabill** | dev-main | ✅ Funcional | Core billing (invoices, customers, articles) |
| **lararoi** | dev-main | ✅ Funcional | Lógica EU VAT/ROI (intra-community) |
| **lara-verifactu** | dev-main | ✅ Funcional | Integración AEAT España (Verifactu) |
| **laratickets** | dev-main | ✅ Funcional | Sistema de tickets de soporte |
| **lara100** | v1.0 | ✅ Estable | Manejo de valores monetarios (base 100) |

---

## 🔑 **Convenciones Clave**

### **UUID Strategy**
- **UUID v7 Binary (16 bytes)**: `users`, `invoices`, `tickets` (expuestos públicamente)
- **Integer IDs**: Tablas de configuración interna (`tax_rates`, `fiscal_settings`, etc.)

### **Monetary Values**
- **Siempre base 100** (lara100): `€12.34` → `1234` (integer)
- **Nunca float/decimal** para dinero

### **Database**
- **Producción**: MySQL 8.0+
- **Testing**: SQLite in-memory (phpunit.xml)

### **Testing Coverage**
- **Paquetes**: 80-95%
- **Staging app**: 60-70%

### **PHPStan**
- **Level 6** (pragmático, AI-friendly)
- Evitar abuso de baseline

---

## 🐛 **Problemas Conocidos**

### **1. Larabill no usa Spatie skeleton**
**Impacto**: Comandos no se descubren automáticamente con `hasCommand()`  
**Solución**: Registro manual en `boot()` del ServiceProvider  
**Estado**: ✅ Resuelto (workaround funcional)

### **2. Migraciones con orden incorrecto**
**Impacto**: Errores de FK al ejecutar `php artisan migrate`  
**Solución**: Comando `larabill:install` publica en orden correcto  
**Estado**: ✅ Resuelto

### **3. Composer cache con symlinks**
**Impacto**: Cambios en paquetes no se reflejan  
**Solución**: `composer dump-autoload && php artisan optimize:clear`  
**Estado**: ⚠️ Workaround disponible

---

## 🚀 **Quick Start**

### **Para Desarrolladores de Paquetes**

```bash
# 1. Clonar Larafactu
cd /Users/abkrim/SitesLR12
git clone https://github.com/aichadigital/larafactu.git
cd larafactu

# 2. Crear symlinks a paquetes source
mkdir -p packages/aichadigital
ln -s /Users/abkrim/development/packages/aichadigital/larabill packages/aichadigital/larabill
ln -s /Users/abkrim/development/packages/aichadigital/lara-verifactu packages/aichadigital/lara-verifactu
ln -s /Users/abkrim/development/packages/aichadigital/laratickets packages/aichadigital/laratickets

# 3. Instalar dependencias
composer install

# 4. Configurar .env
cp .env.example .env
php artisan key:generate

# 5. Instalar paquetes
php artisan larabill:install
php artisan verifactu:install
php artisan laratickets:install

# 6. Verificar
php artisan db:show --json | jq -r '.tables[].name' | wc -l
# Debe mostrar: 42 tablas
```

### **Para Usuarios Finales (Producción)**

```bash
# 1. Crear proyecto Laravel
composer create-project laravel/laravel mi-proyecto
cd mi-proyecto

# 2. Instalar paquetes
composer require aichadigital/larabill
composer require aichadigital/lara-verifactu
composer require aichadigital/laratickets

# 3. Ejecutar instaladores
php artisan larabill:install
php artisan verifactu:install
php artisan laratickets:install
```

---

## 📞 **Contacto y Soporte**

- **Desarrollador**: @abkrim
- **Email**: [pendiente]
- **GitHub Issues**: [Repositorio de cada paquete]
- **Documentación**: Esta carpeta (`docs/en-desarrollo/`)

---

## 📅 **Cronología del Proyecto**

- **2025-11-20**: Inicio de integración sistemática
- **2025-11-21**: Resolución de problemas de FK y autoloading
- **2025-11-21**: Implementación de comandos `install`
- **2025-11-21**: Documentación completa del proceso
- **2025-12-15** (objetivo): Versión 1.0 estable para migración WHMCS

---

**¡Documentación viva! Se actualiza conforme avanza el desarrollo.**

