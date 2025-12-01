#!/bin/bash
# bin/fresh-install.sh
# Reset duro del proyecto preservando configuración local y estado de IDEs

set -e

echo "🔄 Fresh install preservando estado local..."

# Verificar que estamos en el directorio correcto
if [[ ! -f "artisan" ]]; then
    echo "❌ Error: Ejecuta este script desde la raíz del proyecto Laravel"
    exit 1
fi

# Archivos/directorios a preservar (no se tocan)
PRESERVE=(
    ".cursor"       # Estado de Cursor, chats, etc.
    ".claude"       # Estado de Claude
    ".vscode"       # Configuración VS Code
    ".env"          # Variables de entorno
    ".env.local"    # Variables locales adicionales
    ".mcp.json"     # Configuración MCP
)

# Construir la lista de exclusiones para git clean
EXCLUDES=""
for item in "${PRESERVE[@]}"; do
    EXCLUDES="$EXCLUDES --exclude=$item"
done

echo "📋 Preservando: ${PRESERVE[*]}"

# Fetch último código
echo "📥 Obteniendo últimos cambios..."
git fetch origin

# Reset duro al origen
echo "🔨 Reset a origin/main..."
git reset --hard origin/main

# Limpiar archivos no trackeados EXCEPTO los preservados
echo "🧹 Limpiando archivos generados..."
git clean -fdx $EXCLUDES

echo ""
echo "✅ Reset completado!"
echo ""
echo "📋 Siguiente paso:"
echo "   php artisan larafactu:install"
echo ""
