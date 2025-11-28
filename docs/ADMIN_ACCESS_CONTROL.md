# 🔐 Control de Acceso al Panel de Administración

## Configuración de Seguridad

### Variables de Entorno

El acceso al panel de Filament se controla mediante dos variables de entorno en `.env`:

```env
# Emails específicos permitidos (separados por comas)
ADMIN_EMAILS=admin@tuempresa.com,manager@tuempresa.com,ceo@company.com

# Dominios completos permitidos (con o sin @)
ADMIN_DOMAINS=@tuempresa.com,@company.com
# O sin @:
ADMIN_DOMAINS=tuempresa.com,company.com
```

---

## 📋 Reglas de Acceso

### En Local Development
✅ **Todos los usuarios tienen acceso**
- Facilita desarrollo y testing
- No requiere configuración adicional
- Detectado automáticamente por `APP_ENV=local`

### En Producción/Staging
🔒 **Solo usuarios autorizados**
- Debe cumplir al menos una de estas condiciones:
  1. Email exacto en `ADMIN_EMAILS`
  2. Dominio en `ADMIN_DOMAINS`

---

## 🎯 Ejemplos de Uso

### Caso 1: Emails Específicos

Permitir solo a ciertos usuarios:

```env
ADMIN_EMAILS=admin@example.com,manager@example.com
ADMIN_DOMAINS=
```

**Resultado**:
- ✅ `admin@example.com` → Acceso permitido
- ✅ `manager@example.com` → Acceso permitido
- ❌ `empleado@example.com` → Acceso denegado

---

### Caso 2: Por Dominio

Permitir a todos los usuarios de tu empresa:

```env
ADMIN_EMAILS=
ADMIN_DOMAINS=@tuempresa.com
```

**Resultado**:
- ✅ `admin@tuempresa.com` → Acceso permitido
- ✅ `manager@tuempresa.com` → Acceso permitido
- ✅ `empleado@tuempresa.com` → Acceso permitido
- ❌ `externo@gmail.com` → Acceso denegado

---

### Caso 3: Combinación (Recomendado)

Permitir dominio corporativo + emails específicos externos:

```env
ADMIN_EMAILS=consultor@external.com,freelancer@gmail.com
ADMIN_DOMAINS=@tuempresa.com
```

**Resultado**:
- ✅ `admin@tuempresa.com` → Acceso permitido (dominio)
- ✅ `consultor@external.com` → Acceso permitido (email específico)
- ✅ `freelancer@gmail.com` → Acceso permitido (email específico)
- ❌ `hacker@malicious.com` → Acceso denegado

---

### Caso 4: Múltiples Dominios

Si tienes varias empresas:

```env
ADMIN_EMAILS=
ADMIN_DOMAINS=@empresa1.com,@empresa2.com,@holding.com
```

**Resultado**:
- ✅ `admin@empresa1.com` → Acceso permitido
- ✅ `manager@empresa2.com` → Acceso permitido
- ✅ `ceo@holding.com` → Acceso permitido
- ❌ `user@otradomain.com` → Acceso denegado

---

## 🛡️ Seguridad

### Configuración Vacía = Acceso Denegado

Si **no configuras ninguna variable**:

```env
ADMIN_EMAILS=
ADMIN_DOMAINS=
```

**Resultado**: ❌ **Nadie tiene acceso** (excepto en local development)

### Whitespace Handling

El sistema tolera espacios:

```env
ADMIN_EMAILS=admin@example.com , manager@company.com , ceo@holding.com
```

Se procesará correctamente eliminando espacios automáticamente.

### Con o Sin @ en Dominios

Ambas sintaxis son válidas:

```env
# Con @
ADMIN_DOMAINS=@example.com,@company.com

# Sin @
ADMIN_DOMAINS=example.com,company.com
```

El sistema normaliza automáticamente.

---

## 🧪 Testing

### Tests Automatizados

El proyecto incluye tests completos para verificar:
- ✅ Acceso con email exacto
- ✅ Denegación con email no autorizado
- ✅ Acceso por dominio
- ✅ Denegación por dominio no autorizado
- ✅ Combinación email + dominio
- ✅ Configuración vacía = denegado
- ✅ Manejo de espacios en blanco
- ✅ Acceso en local development

### Ejecutar Tests

```bash
php artisan test --filter=AdminAccessTest
```

---

## 📖 Implementación Técnica

### Ubicación del Código

**Modelo**: `app/Models/User.php`

```php
public function canAccessPanel(Panel $panel): bool
{
    // En local: acceso para todos
    if (App::environment('local')) {
        return true;
    }

    // En producción: verificar autorización
    return $this->isAllowedAdminUser();
}
```

### Configuración

**Config**: `config/app.php`

```php
'admin_emails' => env('ADMIN_EMAILS', ''),
'admin_domains' => env('ADMIN_DOMAINS', ''),
```

---

## 🚨 Troubleshooting

### Problema: No puedo acceder al panel en producción

**Causa**: Variables no configuradas o usuario no autorizado.

**Solución**:
```bash
# 1. Verificar .env
cat .env | grep ADMIN_

# 2. Limpiar cache de config
php artisan config:clear
php artisan config:cache

# 3. Verificar email del usuario
php artisan tinker --execute="User::where('email', 'tu@email.com')->first()"
```

### Problema: En local tampoco puedo acceder

**Causa**: `APP_ENV` no es `local`.

**Solución**:
```env
# .env
APP_ENV=local
```

Luego:
```bash
php artisan config:clear
```

### Problema: Configuré ADMIN_EMAILS pero no funciona

**Causa**: Cache de configuración.

**Solución**:
```bash
php artisan config:clear
php artisan config:cache
```

---

## 🔄 Migración desde Otros Sistemas

### Desde WHMCS

Si migras usuarios desde WHMCS, asegúrate de:

1. **Validar emails**: Todos los emails deben ser válidos
2. **Asignar roles**: Usa `ADMIN_EMAILS` para admins de WHMCS
3. **Testing**: Verifica acceso antes de ir a producción

### Best Practices

✅ **En producción**:
- Usa `ADMIN_DOMAINS` para tu empresa
- Añade `ADMIN_EMAILS` para externos/consultores
- Limpia cache después de cambios: `php artisan config:cache`

✅ **En staging**:
- Usa misma configuración que producción
- Verifica acceso antes de deploy

✅ **En local**:
- No requiere configuración
- Todos tienen acceso automáticamente

---

## 📚 Referencias

- **Filament Docs**: https://filamentphp.com/docs
- **Tests**: `tests/Feature/AdminAccessTest.php`
- **Código**: `app/Models/User.php`
- **Config**: `config/app.php`

---

**Última actualización**: 28 de noviembre de 2025  
**Versión Larafactu**: 1.0-dev  
**Coverage de Tests**: 100% ✅

