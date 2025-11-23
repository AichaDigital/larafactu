# 🧪 Testing de Instalación de Paquetes

Scripts para probar la instalación limpia de paquetes Larafactu.

## 📋 Pre-requisitos

- Base de datos `larafactu` creada
- Dump CORE disponible: `database/dumps/00_laravel_core_base.sql`
- Paquetes en modo desarrollo con symlinks configurados

## 🚀 Uso Rápido

### **Opción 1: Test Automático Completo**

```bash
./bin/test-install.sh
```

Este script hace TODO automáticamente:
1. Reset a estado limpio
2. Actualiza paquetes desde repos locales
3. Ejecuta `larabill:install`
4. Ejecuta `verifactu:install`
5. Ejecuta `laratickets:install`
6. Ejecuta todas las migraciones
7. Verifica que todo funcionó correctamente

**Salida esperada:**
```
✅ ALL TESTS PASSED
Summary:
  - Larabill: ✓ Installed
  - Lara-Verifactu: ✓ Installed
  - Laratickets: ✓ Installed
  - Migrations: 38 published
  - Database: 42 tables created
```

---

### **Opción 2: Proceso Manual (Paso a Paso)**

#### **1. Reset a Estado Limpio**

```bash
./bin/reset-for-testing.sh
```

Esto:
- Limpia la base de datos
- Restaura base CORE Laravel (9 tablas)
- Elimina migraciones publicadas
- Elimina configuraciones publicadas

#### **2. Actualizar Paquetes**

```bash
composer update aichadigital/larabill aichadigital/lara-verifactu aichadigital/laratickets
```

#### **3. Instalar Paquetes (Interactivo)**

```bash
# Larabill (detecta UUID automáticamente)
php artisan larabill:install

# Lara-Verifactu
php artisan verifactu:install

# Laratickets
php artisan laratickets:install
```

**Opciones disponibles:**
- `--user-id-type=uuid_binary` - Especificar tipo de ID manualmente
- `--force` - Sobrescribir archivos existentes
- `--no-migrate` - No ejecutar migraciones automáticamente
- `--seed` - (Solo Laratickets) Seed datos de ejemplo

---

## 🔍 Verificación Manual

### **Ver migraciones publicadas:**

```bash
ls -1 database/migrations/*.php | wc -l
# Esperado: ~38 migraciones (3 CORE + 35 paquetes)
```

### **Ver tablas en base de datos:**

```bash
mysql -e "SHOW TABLES;" larafactu
# Esperado: 42 tablas
```

### **Ver estado de migraciones:**

```bash
php artisan migrate:status
```

---

## 🐛 Troubleshooting

### **Error: "Table already exists"**

Ejecuta reset:
```bash
./bin/reset-for-testing.sh
```

### **Error: "Migration not found"**

Los paquetes no están actualizados. Ejecuta:
```bash
composer update aichadigital/larabill aichadigital/lara-verifactu aichadigital/laratickets
```

### **Error: "Foreign key constraint fails"**

Orden de migraciones incorrecto. Debería estar resuelto en comandos `install`. Si persiste, reportar en issues.

### **Ver comandos disponibles:**

```bash
php artisan list | grep -E "larabill|verifactu|laratickets"
```

---

## 📊 Estado Esperado Después de Instalación

### **Base de Datos:**

```
42 tablas totales:
├── 9 Laravel CORE (users, cache, jobs, etc.)
├── 23 Larabill (invoices, customers, articles, etc.)
├── 3 Lara-Verifactu (verifactu_invoices, registries, breakdowns)
└── 8 Laratickets (tickets, departments, evaluations, etc.)
```

### **Migraciones:**

```
~38 archivos en database/migrations/:
├── 3 CORE (0001_01_01_*)
├── 24 Larabill
├── 3 Lara-Verifactu
└── 8 Laratickets
```

### **Configuraciones:**

```
config/
├── larabill.php (publicado)
├── verifactu.php (publicado)
└── laratickets.php (publicado)
```

---

## 📝 Notas Importantes

1. **UUID Binary**: Los comandos detectan automáticamente el tipo de `users.id`
2. **Orden Correcto**: Las migraciones se publican en el orden correcto para evitar errores de FK
3. **Stubs Incluidos**: `unit_measures` y `tax_categories` se publican automáticamente
4. **Idempotente**: Puedes ejecutar los comandos múltiples veces (usa `--force` para sobrescribir)

---

## 🎯 Siguientes Pasos Después de Instalación

1. **Configurar .env**:
   ```env
   LARABILL_USER_ID_TYPE=uuid_binary
   VERIFACTU_ENABLED=true
   VERIFACTU_ENVIRONMENT=sandbox
   ```

2. **Seed datos de prueba**:
   ```bash
   php artisan laratickets:install --seed
   ```

3. **Validar integración**:
   ```bash
   php artisan test --filter=Integration
   ```

---

**Última actualización**: 2025-11-21  
**Branch de paquetes**: `improvements/larafactu-join`

