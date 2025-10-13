# Larafactu - Staging Project for Package Testing

> **Proyecto de staging para probar los paquetes larabill y lara-verifactu en diferentes configuraciones de modelos**

## 🎯 Propósito

Este proyecto sirve como **entorno de prueba** para validar la compatibilidad y funcionalidad de los paquetes:

- **`aichadigital/larabill`** - Sistema de facturación y billing agnóstico
- **`aichadigital/lara-verifactu`** - Integración con AEAT Verifactu para España

---

## 🌳 BRANCH: `model/uuid-binary`

### ⚙️ Configuración de esta Branch

Esta branch prueba el paquete larabill con **UUID v7 ordenado almacenado como binary(16)**.

#### User Model Configuration:
- **Tipo de ID**: UUID v7 (ordered)
- **Storage**: `binary(16)` en base de datos
- **Paquete**: `dyrynda/laravel-model-uuid` v8.2.0
- **Eficiencia**: 55% menos espacio que UUID string (16 bytes vs 36 bytes)

#### Implementación:

```php
// app/Models/User.php
use Dyrynda\Database\Support\{BindsOnUuid, GeneratesUuid};
use Dyrynda\Database\Support\Casts\EfficientUuid;
use Filament\Models\Contracts\FilamentUser;

class User extends Authenticatable implements FilamentUser
{
    use BindsOnUuid, GeneratesUuid, HasFactory, Notifiable;

    public $incrementing = false;
    protected $keyType = 'string';

    public function uuidVersion(): string { return 'uuid7'; }
    public function uuidColumn(): string { return 'id'; }

    protected function casts(): array
    {
        return [
            'id' => EfficientUuid::class,
            // ...
        ];
    }

    // CLAVE: Retorna valor RAW binary para Laravel Auth
    public function getAuthIdentifier(): mixed
    {
        return $this->getRawOriginal($this->getAuthIdentifierName());
    }
}
```

#### Migración:

```php
Schema::create('users', function (Blueprint $table) {
    $table->binary('id', 16)->primary();
    // ...
});
```

---

## 🌳 Estrategia de Branches

Este proyecto utiliza una **estrategia de branches por configuración de modelo** para probar la compatibilidad del paquete con diferentes tipos de ID de usuario:

### Branches Disponibles:

| Branch | Descripción | User ID Type | Estado |
|--------|-------------|--------------|--------|
| `main` | Base limpia con Filament | N/A | ✅ Ready |
| `model/uuid-binary` | **← ESTÁS AQUÍ** | UUID v7 (16 bytes) | 🚧 En desarrollo |
| `model/autoincrement` | Auto-increment tradicional | bigIncrements | 📋 Pendiente |
| `model/uuid-string` | UUID v7 como string | UUID v7 (36 chars) | 📋 Pendiente |
| `model/ulid-binary` | ULID como binary(16) | ULID (16 bytes) | 📋 Pendiente |

### ¿Por qué esta estrategia?

1. **Testing Completo**: Verificar que larabill funciona con cualquier tipo de user_id
2. **Comparación Fácil**: `git diff model/uuid-binary model/autoincrement`
3. **Un Solo Repositorio**: Todo el historial centralizado
4. **Documentación por Branch**: Cada configuración documentada en su contexto
5. **Evita Duplicación**: Los paquetes (symlinks) se comparten entre branches

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
   - UserTaxProfileResource (opcional)
   - FiscalSettingsResource (opcional)
✅ Seeders con datos de prueba específicos
```

## 📦 Paquetes Bajo Prueba

### Larabill v0.1.0 (Development)

**Características clave:**
- ✅ Agnóstico al tipo de user_id (UUID, ULID, Int)
- ✅ UUID binary para facturas (eficiencia del 55%)
- ✅ **Base-100 para montos (lara100)**: €12.34 → 1234
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

**⚠️ IMPORTANTE - Lara100 (Base-100):**

El paquete usa `aichadigital/lara100` para manejar montos en **base 100** (sin decimales):
- €12.34 se almacena como `1234` (integer)
- €0.99 se almacena como `99` (integer)
- €100.00 se almacena como `10000` (integer)

**Beneficios:**
- ✅ Sin errores de redondeo de punto flotante
- ✅ Cálculos precisos de impuestos
- ✅ Performance mejorada (integer vs decimal/float)

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

## 🚀 Stack Tecnológico (Branch: model/uuid-binary)

- **Laravel**: 12.33.0
- **PHP**: 8.4.13
- **Filament**: 4.1.7
- **MySQL**: Latest
- **User ID**: UUID v7 binary(16) con `dyrynda/laravel-model-uuid`
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

### Verifactu
```bash
php artisan verifactu:test-connection    # Probar conexión AEAT
php artisan verifactu:register {id}      # Registrar factura
php artisan verifactu:status             # Ver estado del sistema
php artisan verifactu:verify-blockchain  # Verificar integridad
php artisan verifactu:retry-failed       # Reintentar envíos fallidos
```

## 📚 Documentación Adicional

- **`STAGING_SETUP.md`** - Configuración detallada del entorno staging y UUID binary
- **`.cursor/rules/larafactu.mdc`** - Reglas específicas del proyecto para Cursor AI
- **Paquete Larabill**: `./packages/aichadigital/larabill/README.md`
- **Paquete Lara-Verifactu**: `./packages/aichadigital/lara-verifactu/README.md`

## 🐛 Troubleshooting

### Cambiar entre branches

```bash
# Al cambiar de branch, siempre ejecutar:
git checkout model/nombre-branch
composer install                    # Por si hay dependencias diferentes
php artisan migrate:fresh --seed   # Recrear DB para la nueva configuración
php artisan optimize:clear          # Limpiar cachés
```

### Problemas con Herd (Testing Simultáneo)

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
