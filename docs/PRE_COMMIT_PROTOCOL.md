# 📋 Protocolo de Pre-Commit

## ⚠️ Regla de Oro

**NUNCA hacer push directo sin ejecutar el protocolo completo**

---

## 🔄 Workflow Obligatorio (Pre-Push)

### Paso 1: Formatear Código (Pint)

```bash
vendor/bin/pint
```

**✅ Debe decir**: `PASS ... X files`  
**❌ Si dice `FAIL`**: Revisa los archivos y vuelve a ejecutar

---

### Paso 2: Ejecutar Tests Localmente

```bash
php artisan test
```

**✅ Debe decir**: `Tests: X passed`  
**❌ Si falla**: Arregla los tests antes de continuar

---

### Paso 3: Verificar Estado Git

```bash
git status
```

Asegúrate de que solo los archivos deseados están staged.

---

### Paso 4: Commit con Mensaje Descriptivo

```bash
git add .
git commit -m "tipo: descripción corta

- Cambio 1
- Cambio 2
- Cambio 3"
```

**Tipos válidos**: `feat`, `fix`, `docs`, `style`, `refactor`, `test`, `chore`

---

### Paso 5: Push a Main

```bash
git push origin main
```

---

### Paso 6: Verificar CI en GitHub (3-5 minutos)

```bash
# Ver último workflow
gh run list --limit 1

# Ver detalles si falla
gh run view --log-failed
```

**✅ Debe decir**: Status: ✅ completed  
**❌ Si falla**: 
1. Ver logs con `gh run view --log-failed`
2. Arreglar localmente
3. Volver al Paso 1

---

## 🚀 Script Automatizado (Recomendado)

Crear archivo `scripts/pre-push.sh`:

```bash
#!/bin/bash

echo "🚀 Larafactu - Pre-Push Checklist"
echo "=================================="
echo ""

# Step 1: Pint
echo "📝 Step 1: Running Pint..."
vendor/bin/pint
if [ $? -ne 0 ]; then
    echo "❌ Pint failed. Fix style issues and try again."
    exit 1
fi
echo "✅ Pint passed"
echo ""

# Step 2: Tests
echo "🧪 Step 2: Running tests..."
php artisan test
if [ $? -ne 0 ]; then
    echo "❌ Tests failed. Fix tests and try again."
    exit 1
fi
echo "✅ Tests passed"
echo ""

# Step 3: Git status
echo "📊 Step 3: Git status..."
git status
echo ""

# Ask for confirmation
read -p "👉 Do you want to continue with commit? (y/n) " -n 1 -r
echo ""
if [[ ! $REPLY =~ ^[Yy]$ ]]
then
    echo "❌ Aborted by user"
    exit 1
fi

echo "✅ Ready to commit and push!"
echo ""
echo "Next steps:"
echo "  1. git add ."
echo "  2. git commit -m \"your message\""
echo "  3. git push origin main"
echo "  4. gh run list --limit 1 (wait 3-5 min)"
```

**Uso**:
```bash
chmod +x scripts/pre-push.sh
./scripts/pre-push.sh
```

---

## 🎯 Quick Commands

```bash
# Verificar último CI
gh run list --limit 1

# Ver logs si falla
gh run view --log-failed

# Ver logs completos
gh run view --log

# Re-run workflow fallido
gh run rerun <run-id>

# Ver tests localmente con coverage
php artisan test --coverage
```

---

## 📚 Referencia Rápida

| Comando | Propósito |
|---------|-----------|
| `vendor/bin/pint` | Formatear código |
| `php artisan test` | Ejecutar tests |
| `gh run list` | Ver workflows CI |
| `gh run view --log-failed` | Ver errores CI |

---

## ⚠️ Errores Comunes

### Error: "Pint failed"
**Solución**: Ejecuta `vendor/bin/pint` y commitea los cambios

### Error: "Tests failed locally but pass in CI"
**Causa**: Diferencias de entorno (SQLite vs MySQL)  
**Solución**: Usa misma DB que CI o ajusta tests

### Error: "CI fails but tests pass locally"
**Causa**: Archivos no commiteados o dependencias desactualizadas  
**Solución**: 
```bash
git status  # Verificar archivos no tracked
composer install  # Actualizar dependencias
```

---

## 🎓 Mejores Prácticas

1. **Commits pequeños y frecuentes** > Commits grandes
2. **Mensajes descriptivos** > Mensajes vagos
3. **Tests antes de push** > Fix en producción
4. **Revisar CI después de push** > Asumir que funciona
5. **Branch para features grandes** > Todo en main

---

## 🔒 Protección de Rama Main

- ✅ PR requerido para no-admins
- ✅ Admins pueden push directo (pero DEBEN seguir protocolo)
- ✅ CI ejecuta tests en cada push
- ✅ Pint verifica estilo de código

---

**Última actualización**: 28 de noviembre de 2025  
**Versión**: 1.0.0

