# 🚀 Comandos de Desarrollo - Larafactu

## Usuarios de Desarrollo

Después de cada `migrate:fresh` o `migrate:fresh --seed`, se crean automáticamente:

### 👤 Admin
- **Email**: `admin@example.com`
- **Password**: `password`
- **Nombre**: Abdelkarim Mateos

### 👤 Test User
- **Email**: `test@example.com`
- **Password**: `password`
- **Nombre**: Test User

## Comandos Útiles

### Seed de Desarrollo (Solo Local/Testing)

```bash
# Seed solo usuarios y fiscal settings (sin wipe)
php artisan dev:seed

# Wipe completo + migrations + seed
php artisan dev:seed --fresh

# Alternativa: migrate:fresh con seed automático
php artisan migrate:fresh --seed
```

### Características

- ✅ **Protección**: Solo funciona en `local` y `testing`
- ✅ **Idempotente**: `firstOrCreate()` - no duplica usuarios
- ✅ **Automático**: `DatabaseSeeder` llama a `DevelopmentSeeder` en local
- ✅ **Fiscal Settings**: Crea configuración básica si existe el modelo

## Tests

```bash
# Tests completos
php artisan test

# Tests de Invoice
php artisan test --filter=Invoice

# Con coverage
composer test-coverage
```

## Limpieza

```bash
# Limpiar caché
php artisan config:clear
php artisan cache:clear

# Formateo código
composer pint
```

