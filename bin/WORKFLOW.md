# 📜 Scripts de Utilidad - Larafactu

Este directorio contiene scripts para facilitar el desarrollo y mantenimiento del proyecto.

---

## 🚀 Scripts Principales

### 🔍 `check-packages-sync.sh` - Verificar Sincronización

**Propósito**: Verifica que los paquetes locales estén sincronizados con GitHub.

**Uso**:
```bash
./bin/check-packages-sync.sh
```

**Verifica**:
- ✅ Working tree limpio (sin cambios sin commit)
- ✅ Commits pusheados a GitHub (main == origin/main)
- ✅ No hay divergencia con origin

**Salida de ejemplo**:
```
✓ larabill (main) - En sync con origin/main
✓ lara-verifactu (main) - En sync con origin/main
⚠ laratickets (main) - Commits pendientes de push:
  abc1234 feat: add new feature
```

---

### 🔄 `composer-update-vcs.sh` - Actualizar desde GitHub

**Propósito**: Actualizar paquetes desde GitHub (VCS) de forma segura.

**Uso**:
```bash
# Actualizar todos los paquetes
./bin/composer-update-vcs.sh

# Actualizar paquetes específicos
./bin/composer-update-vcs.sh larabill
./bin/composer-update-vcs.sh larabill verifactu tickets
```

**Paquetes soportados**:
- `larabill` → aichadigital/larabill
- `verifactu` → aichadigital/lara-verifactu
- `tickets` → aichadigital/laratickets
- `roi` → aichadigital/lararoi

**Flujo interno**:
1. Ejecuta `check-packages-sync.sh` (verifica sync)
2. Backup de `composer.json`
3. Cambia temporalmente a repositorios VCS (GitHub)
4. Ejecuta `composer update`
5. Restaura `composer.json` a PATH (symlinks)

---

### 🧪 `reset-for-testing.sh` - Reset para Testing

**Propósito**: Resetea el proyecto a un estado limpio.

**Uso**:
```bash
./bin/reset-for-testing.sh
```

**Ejecuta**:
- Limpia base de datos
- Elimina migraciones publicadas
- Limpia caches de Composer y Laravel

---

---

## 🔧 Workflow Recomendado

### **Desarrollo Local (Modo PATH - Symlinks)**

```bash
# 1. Trabajar en paquetes
cd /Users/abkrim/development/packages/aichadigital/larabill
# ... hacer cambios ...
git add -A
git commit -m "feat: nueva funcionalidad"
git push origin main

# 2. Cambios se reflejan INMEDIATAMENTE en Larafactu (symlinks)
# NO necesitas composer update
```

### **Actualizar desde GitHub (Modo VCS)**

```bash
# 1. Verificar que todo esté pusheado
./bin/check-packages-sync.sh

# 2. Si todo OK, actualizar
./bin/composer-update-vcs.sh larabill

# O actualizar todos
./bin/composer-update-vcs.sh
```

---

## 📝 PATH vs VCS: ¿Cuál usar?

### **Modo PATH (Desarrollo - ACTUAL)**

```json
"repositories": [
    {
        "type": "path",
        "url": "./packages/aichadigital/larabill",
        "options": { "symlink": true }
    }
]
```

**Ventajas**:
- ✅ Cambios instantáneos (symlinks)
- ✅ No necesitas `composer update` después de cada cambio

**Desventajas**:
- ❌ `composer update` NO funciona (no hay nada que actualizar)

### **Modo VCS (Actualización - TEMPORAL)**

```json
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/AichaDigital/larabill"
    }
]
```

**Ventajas**:
- ✅ `composer update` descarga desde GitHub
- ✅ Obtiene últimas versiones/tags

**Desventajas**:
- ❌ Cambios locales NO se reflejan (copia en vendor/)

---

## 📊 Tabla de Situaciones

| Situación | Modo | Comando |
|-----------|------|---------|
| Desarrollando localmente | PATH | (Cambios automáticos) |
| Después de push a GitHub | VCS | `./bin/composer-update-vcs.sh` |
| Testing de instalación limpia | Web Wizard | Ver `docs/PRODUCTION_WEB_INSTALL.md` |
| CI/CD en GitHub Actions | VCS | (Automático en workflow) |

---

## 🚨 Errores Comunes

### Error: "Paquetes no están en sync"

```
⚠ larabill (main) - Commits pendientes de push
```

**Solución**:
```bash
cd /Users/abkrim/development/packages/aichadigital/larabill
git push origin main
```

### Error: "composer update no descarga nada"

Estás en modo PATH (symlinks). Usa:
```bash
./bin/composer-update-vcs.sh
```

---

## 📚 Ver También

- [QUICK_START.md](../docs/en-desarrollo/QUICK_START.md) - Comandos diarios
- [INSTALACION_PAQUETES.md](../docs/en-desarrollo/INSTALACION_PAQUETES.md) - Guía completa

---

**Última actualización**: 2025-11-23  
**Versiones**: Larabill v0.4.2 | Lara-Verifactu v0.2.1-alpha

