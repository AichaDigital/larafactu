# Larafactu - Proyecto Staging

> Proyecto staging para probar los paquetes **larabill** y **lara-verifactu** en desarrollo local

## 🌍 Normativa de Lenguaje (IMPORTANTE)

### Código en Inglés
**TODO el código debe estar en inglés**:
- ✅ Nombres de variables, funciones, clases
- ✅ Comentarios de código (inline y docblocks)
- ✅ Bloques PHPDoc y anotaciones
- ✅ Mensajes de error y excepciones
- ✅ Nombres de tests

### Chat en Español
**Solo la comunicación con el usuario**:
- ✅ Respuestas en chat
- ✅ Documentación de usuario (archivos .md)

## 📋 Resumen

Este proyecto sirve como entorno de staging para validar la integración de los paquetes:
- `aichadigital/larabill` - Sistema de facturación y billing
- `aichadigital/lara-verifactu` - Integración con AEAT Verifactu

## 🎯 Configuración Actual

### UUID Binary (varbinary(16)) con UUID v7

El proyecto usa **UUID v7 ordered** almacenado como **binary(16)** para máxima eficiencia:

- **Formato**: `varbinary(16)` (16 bytes)
- **Ventajas**:
  - 55% menos espacio vs UUID string (36 bytes)
  - Índices más pequeños y rápidos
  - UUID v7 ordenado temporalmente (mejor para B-tree indexes)
  - Compatible con los paquetes larabill y lara-verifactu

### Modelo User - Solución con `dyrynda/laravel-model-uuid`

**Paquete usado**: `dyrynda/laravel-model-uuid` v8.2.0

```php
use Dyrynda\Database\Support\BindsOnUuid;
use Dyrynda\Database\Support\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;
use Filament\Models\Contracts\FilamentUser;

class User extends Authenticatable implements FilamentUser
{
    use BindsOnUuid, GeneratesUuid, HasFactory, Notifiable;
    
    public $incrementing = false;
    protected $keyType = 'string';
    
    // UUID v7 (ordered) para mejor performance
    public function uuidVersion(): string { return 'uuid7'; }
    public function uuidColumn(): string { return 'id'; }
    
    protected function casts(): array
    {
        return [
            'id' => EfficientUuid::class, // Conversión binary <-> string
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    
    // CLAVE: Retorna valor RAW binary para Laravel Auth
    public function getAuthIdentifier(): mixed
    {
        return $this->getRawOriginal($this->getAuthIdentifierName());
    }
    
    public function canAccessPanel(Panel $panel): bool
    {
        return App::environment('local');
    }
}
```

⚠️ **IMPORTANTE**: El método `getAuthIdentifier()` es **crucial**. Sin él, Laravel Auth no puede recuperar usuarios desde la sesión porque el valor del ID se convierte a string, pero la base de datos espera binary.

### Estructura de Base de Datos

Todas las tablas están creadas y funcionando:

#### Core Laravel
- ✅ `users` (id binary(16))
- ✅ `cache`, `jobs`, `sessions`

#### Larabill (Facturación)
- ✅ `invoices` - Facturas con UUID binary
- ✅ `invoice_items` - Items de facturas
- ✅ `user_tax_infos` - Perfiles fiscales de usuarios
- ✅ `tax_rates` - Tasas de impuestos
- ✅ `vat_verifications` - Verificaciones de CIF/VAT
- ✅ `company_fiscal_configs` - Configuración fiscal
- ✅ `invoice_templates` - Plantillas de facturas
- ✅ `company_template_settings` - Configuración de plantillas

#### Lara-Verifactu (AEAT)
- ✅ `verifactu_invoices` - Facturas Verifactu
- ✅ `verifactu_registries` - Registros de envíos AEAT
- ✅ `verifactu_invoice_breakdowns` - Desglose de facturas

## 👤 Usuarios de Prueba

### Usuario Principal (Persistente)
Credenciales de acceso local:
```
Email: test@example.com
Password: password
UUID: 52C0442D179E42E89C8A150495C0FC28
```
Este usuario se crea automáticamente con el seeder usando `firstOrCreate()`.

### Usuario Admin (Adicional)
```
Email: admin@larafactu.test
Password: password
UUID: 4BD914F14E9741B0B0AE3BD8102AE7DA
```

### Acceso a Filament Admin

**URL**: https://larafactu.test/admin/login

Puedes usar cualquiera de los dos usuarios. El modelo User tiene configurado `canAccessPanel()` para permitir acceso a **todos los usuarios** en modo local. 

⚠️ **Importante**: En producción, deberás implementar la lógica de roles/permisos (Spatie Permission, políticas, etc.).

## 📦 Instalación de Paquetes

Los paquetes están instalados como **symlinks locales** desde `./packages/`:

```json
{
  "repositories": [
    {
      "type": "path",
      "url": "./packages/aichadigital/larabill",
      "options": { "symlink": true }
    },
    {
      "type": "path",
      "url": "./packages/aichadigital/lara-verifactu",
      "options": { "symlink": true }
    }
  ]
}
```

**Ventajas del symlink**:
- Cambios en los paquetes se reflejan inmediatamente
- No necesitas `composer update` constantemente
- Perfecto para desarrollo iterativo

## 🚀 Comandos Artisan Disponibles

### Larabill
```bash
# Ver todos los comandos de larabill
php artisan list
```

### Verifactu
```bash
# Probar conexión con AEAT
php artisan verifactu:test-connection

# Registrar factura en AEAT
php artisan verifactu:register

# Ver estado del sistema
php artisan verifactu:status

# Verificar blockchain
php artisan verifactu:verify-blockchain

# Reintentar envíos fallidos
php artisan verifactu:retry-failed
```

## 🛠️ Comandos de Desarrollo

```bash
# Refrescar migraciones y seed
php artisan migrate:fresh --seed

# Formatear código
vendor/bin/pint

# Ejecutar tests
php artisan test

# Ver schema de base de datos
php artisan db:show
php artisan db:table users
```

## 📚 Backend Filament

El proyecto tiene **Filament 4.1** instalado para pruebas de backend.

Acceso: https://larafactu.test/admin (con Laravel Herd)

## 🔧 Stack Tecnológico

- **Laravel**: 12.33.0
- **PHP**: 8.4.13
- **Filament**: 4.1.7
- **Livewire**: 3.6.4
- **Tailwind**: 4.1.14
- **Pest**: 4.1.2

## 📝 Notas Importantes

1. **UUID Binary**: El campo `id` de users es `binary(16)`. El modelo se encarga automáticamente de convertir entre string UUID y binary.

2. **Paquetes en Desarrollo**: Los paquetes están en `./packages/` y se cargan mediante symlinks. Cualquier cambio en los paquetes se refleja automáticamente.

3. **Laravel Herd**: El proyecto está configurado para usar Laravel Herd con HTTPS activado.

4. **Usuario Persistente**: El usuario de prueba `test@example.com` se preserva entre migraciones usando `firstOrCreate()`.

## 🎯 Próximos Pasos

1. Probar la creación de facturas con larabill
2. Integrar facturas con verifactu
3. Validar el flujo completo de facturación → envío AEAT
4. Crear recursos de Filament para gestión visual
5. Probar con diferentes esquemas de ID en otros modelos

## 🐛 Troubleshooting

### Re-ejecutar migraciones
```bash
php artisan migrate:fresh --seed
```

### Limpiar caches
```bash
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Reinstalar paquetes
```bash
composer update aichadigital/larabill aichadigital/lara-verifactu --no-interaction
```

---

**Fecha de creación**: 12 de octubre de 2025  
**Versión**: 1.0.0

