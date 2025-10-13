# Larafactu - Staging Project for Package Testing

> **Proyecto de staging para probar los paquetes larabill y lara-verifactu en diferentes configuraciones de modelos**

## 🎯 Propósito

Este proyecto sirve como **entorno de prueba** para validar la compatibilidad y funcionalidad de los paquetes:

- **`aichadigital/larabill`** - Sistema de facturación y billing agnóstico
- **`aichadigital/lara-verifactu`** - Integración con AEAT Verifactu para España

## 🌳 Estrategia de Branches

Este proyecto utiliza una **estrategia de branches por configuración de modelo** para probar la compatibilidad del paquete con diferentes tipos de ID de usuario:

### Branches Disponibles:

| Branch | Descripción | User ID Type | Estado |
|--------|-------------|--------------|--------|
| `main` | Base limpia con Filament | N/A | ✅ Ready |
| `model/uuid-binary` | UUID v7 ordenado como binary(16) | UUID v7 (16 bytes) | 🚧 En desarrollo |
| `model/autoincrement` | Auto-increment tradicional | bigIncrements | 📋 Pendiente |
| `model/uuid-string` | UUID v7 como string | UUID v7 (36 chars) | 📋 Pendiente |
| `model/ulid-binary` | ULID como binary(16) | ULID (16 bytes) | 📋 Pendiente |

### ¿Por qué esta estrategia?

1. **Testing Completo**: Verificar que larabill funciona con cualquier tipo de user_id
2. **Comparación Fácil**: `git diff model/uuid-binary model/autoincrement`
3. **Un Solo Repositorio**: Todo el historial centralizado
4. **Documentación por Branch**: Cada configuración documentada en su contexto

### Workflow de Testing:

```bash
# Probar configuración UUID Binary
git checkout model/uuid-binary
composer install
php artisan migrate:fresh --seed
# → Acceder a https://larafactu.test/admin

# Probar configuración Auto-increment
git checkout model/autoincrement
php artisan migrate:fresh --seed
# → Acceder a https://larafactu.test/admin

# Volver a la base limpia
git checkout main
```

## 🏗️ Estructura del Proyecto

### Branch `main` (Base Limpia):
```
✅ Laravel 12
✅ Filament 4.1 (Admin Panel básico)
✅ User model con FilamentUser (login funcional)
✅ Paquetes instalados (symlinks):
   - aichadigital/larabill
   - aichadigital/lara-verifactu
✅ Migraciones de paquetes publicadas
❌ SIN recursos de Filament (vacío)
```

### Branches `model/*` (Configuraciones Específicas):
```
✅ Todo lo de main
✅ User model configurado para tipo específico de ID
✅ Recursos de Filament para testing:
   - UserResource
   - InvoiceResource
   - UserTaxProfileResource
   - FiscalSettingsResource
✅ Seeders con datos de prueba
```

## 📦 Paquetes Bajo Prueba

### Larabill v0.1.0 (Development)

**Características clave:**
- ✅ Agnóstico al tipo de user_id (UUID, ULID, Int)
- ✅ UUID binary para facturas (eficiencia del 55%)
- ✅ Base-100 para montos (lara100): €12.34 → 1234
- ✅ Verificación de CIF/VAT
- ✅ Cálculo de impuestos (IVA, IGIC, IPSI, EU)
- ✅ Inmutabilidad de facturas
- ✅ Generación de PDF

**Instalación:**
```json
{
    "repositories": [
        {
            "type": "path",
            "url": "./packages/aichadigital/larabill",
            "options": { "symlink": true }
        }
    ],
    "require": {
        "aichadigital/larabill": "@dev"
    }
}
```

### Lara-Verifactu (Development)

**Características clave:**
- ✅ Integración con AEAT Verifactu
- ✅ Firma electrónica de facturas
- ✅ Blockchain de facturas
- ✅ Envío a la AEAT

**Instalación:**
```json
{
    "repositories": [
        {
            "type": "path",
            "url": "./packages/aichadigital/lara-verifactu",
            "options": { "symlink": true }
        }
    ],
    "require": {
        "aichadigital/lara-verifactu": "@dev"
    }
}
```

## 🚀 Stack Tecnológico

- **Laravel**: 12.33.0
- **PHP**: 8.4.13
- **Filament**: 4.1.7
- **MySQL**: Latest
- **Entorno Local**: Laravel Herd
- **URL**: https://larafactu.test/ (HTTPS activo)

## 👤 Usuarios de Prueba

Disponibles en todas las branches:

```
Email: test@example.com
Password: password
```

Este usuario se crea automáticamente con el seeder usando `firstOrCreate()` para persistir entre migraciones.

## 🛠️ Comandos Útiles

### General
```bash
php artisan migrate:fresh --seed  # Recrear DB con datos
php artisan optimize:clear        # Limpiar cachés
vendor/bin/pint                   # Formatear código
php artisan test                  # Ejecutar tests
```

### Verifactu (cuando esté en branch específica)
```bash
php artisan verifactu:test-connection    # Probar conexión AEAT
php artisan verifactu:register {id}      # Registrar factura
php artisan verifactu:status             # Ver estado del sistema
php artisan verifactu:verify-blockchain  # Verificar integridad
```

## 📚 Documentación Adicional

- **`STAGING_SETUP.md`** - Configuración detallada del entorno staging
- **`.cursor/rules/larafactu.mdc`** - Reglas específicas del proyecto para Cursor AI

## 🐛 Troubleshooting

### Cambiar entre branches

```bash
# Al cambiar de branch, siempre ejecutar:
git checkout model/nombre-branch
composer install                    # Por si hay dependencias diferentes
php artisan migrate:fresh --seed   # Recrear DB para la nueva configuración
php artisan optimize:clear          # Limpiar cachés
```

### Problemas con Herd

Si necesitas múltiples entornos simultáneos, puedes crear symlinks:

```bash
# Crear copia para testing paralelo
ln -s ~/SitesLR12/larafactu ~/SitesLR12/larafactu-int
cd ~/SitesLR12/larafactu-int
git checkout model/autoincrement

# Herd creará automáticamente:
# - larafactu.test → model/uuid-binary
# - larafactu-int.test → model/autoincrement
```

## 🌍 Normativa de Lenguaje

- **Código**: Todo en inglés (variables, clases, comentarios, docblocks)
- **Chat/Docs**: En español (documentación de usuario, comunicación)

Ver `.cursor/rules/larafactu.mdc` para más detalles.

## 📝 License

The MIT License (MIT). Ver [License File](LICENSE.md).
