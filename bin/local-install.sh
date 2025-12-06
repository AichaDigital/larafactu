#!/bin/bash
# bin/local-install.sh
# Instalador bootstrap para desarrollo local desde cero
#
# Este script resuelve el problema "huevo-gallina":
# - php artisan requiere composer install
# - composer install requiere symlinks configurados
# - symlinks se configuraban en php artisan
#
# USO:
#   ./bin/local-install.sh [--packages-path=/ruta/a/paquetes]
#
# REQUISITOS:
#   - PHP 8.4+
#   - Composer 2.x
#   - Node.js 20+ (opcional, para assets)
#   - Base de datos MySQL configurada

set -e

# Colores
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Banner
echo ""
echo "╔══════════════════════════════════════════╗"
echo -e "║       🚀 ${CYAN}LARAFACTU LOCAL INSTALLER${NC} 🚀     ║"
echo "║                                          ║"
echo "║  Bootstrap desde cero para desarrollo   ║"
echo "╚══════════════════════════════════════════╝"
echo ""

# Verificar que estamos en el directorio correcto
if [[ ! -f "artisan" ]]; then
    echo -e "${RED}❌ Error: Ejecuta este script desde la raíz del proyecto Laravel${NC}"
    echo "   cd /ruta/a/larafactu && ./bin/local-install.sh"
    exit 1
fi

# Paquetes a configurar
PACKAGES=("larabill" "lara-verifactu" "laratickets" "lararoi")
COMPOSER_PACKAGES=("aichadigital/larabill" "aichadigital/lara-verifactu" "aichadigital/laratickets" "aichadigital/lararoi")

# ═══════════════════════════════════════════════════════════
# PASO 0: Parsear argumentos
# ═══════════════════════════════════════════════════════════
PACKAGES_PATH=""
SKIP_NPM=false

for arg in "$@"; do
    case $arg in
        --packages-path=*)
            PACKAGES_PATH="${arg#*=}"
            shift
            ;;
        --skip-npm)
            SKIP_NPM=true
            shift
            ;;
        --help)
            echo "Uso: ./bin/local-install.sh [opciones]"
            echo ""
            echo "Opciones:"
            echo "  --packages-path=/ruta   Ruta a los paquetes de desarrollo"
            echo "  --skip-npm              No ejecutar npm install/build"
            echo "  --help                  Mostrar esta ayuda"
            exit 0
            ;;
    esac
done

# ═══════════════════════════════════════════════════════════
# PASO 1: Verificar requisitos
# ═══════════════════════════════════════════════════════════
echo -e "${CYAN}📋 Verificando requisitos...${NC}"

# PHP
if ! command -v php &> /dev/null; then
    echo -e "${RED}❌ PHP no encontrado${NC}"
    exit 1
fi
PHP_VERSION=$(php -r "echo PHP_VERSION;")
echo -e "   ✓ PHP ${PHP_VERSION}"

# Composer
if ! command -v composer &> /dev/null; then
    echo -e "${RED}❌ Composer no encontrado${NC}"
    exit 1
fi
COMPOSER_VERSION=$(composer --version 2>/dev/null | head -n1)
echo -e "   ✓ ${COMPOSER_VERSION}"

# Node (opcional)
if command -v node &> /dev/null; then
    NODE_VERSION=$(node --version)
    echo -e "   ✓ Node ${NODE_VERSION}"
else
    echo -e "   ⚠️  Node no encontrado (assets no se compilarán)"
    SKIP_NPM=true
fi

echo ""

# ═══════════════════════════════════════════════════════════
# PASO 2: Detectar ruta de paquetes
# ═══════════════════════════════════════════════════════════
echo -e "${CYAN}📦 Detectando paquetes de desarrollo...${NC}"

# Rutas a probar (en orden de prioridad)
POSSIBLE_PATHS=(
    "$PACKAGES_PATH"
    "../../development/packages/aichadigital"
    "../development/packages/aichadigital"
    "$HOME/development/packages/aichadigital"
    "$HOME/Sites/packages/aichadigital"
    "$HOME/Code/packages/aichadigital"
)

FOUND_PATH=""
for path in "${POSSIBLE_PATHS[@]}"; do
    if [[ -n "$path" && -d "$path" ]]; then
        # Verificar que tiene al menos larabill
        if [[ -d "$path/larabill" ]]; then
            FOUND_PATH=$(cd "$path" && pwd)
            break
        fi
    fi
done

if [[ -z "$FOUND_PATH" ]]; then
    echo -e "${RED}❌ No se encontraron los paquetes de desarrollo${NC}"
    echo ""
    echo "   Rutas probadas:"
    for path in "${POSSIBLE_PATHS[@]}"; do
        [[ -n "$path" ]] && echo "     - $path"
    done
    echo ""
    echo "   Usa: ./bin/local-install.sh --packages-path=/ruta/a/paquetes"
    exit 1
fi

echo -e "   ${GREEN}✓ Encontrados en: ${FOUND_PATH}${NC}"

# Verificar cada paquete
for pkg in "${PACKAGES[@]}"; do
    if [[ -d "$FOUND_PATH/$pkg" ]]; then
        echo -e "      ✓ $pkg"
    else
        echo -e "      ${YELLOW}⚠️  $pkg no encontrado${NC}"
    fi
done
echo ""

# ═══════════════════════════════════════════════════════════
# PASO 3: Configurar .env
# ═══════════════════════════════════════════════════════════
echo -e "${CYAN}🔧 Configurando entorno...${NC}"

if [[ ! -f ".env" ]]; then
    if [[ -f ".env.example" ]]; then
        cp .env.example .env
        echo -e "   ✓ .env creado desde .env.example"
    else
        echo -e "${RED}❌ No existe .env.example${NC}"
        exit 1
    fi
else
    echo -e "   ✓ .env ya existe"
fi
echo ""

# ═══════════════════════════════════════════════════════════
# PASO 4: Crear symlinks
# ═══════════════════════════════════════════════════════════
echo -e "${CYAN}🔗 Creando symlinks a paquetes...${NC}"

LOCAL_PACKAGES_DIR="packages/aichadigital"

# Crear directorio si no existe
mkdir -p "$LOCAL_PACKAGES_DIR"

for pkg in "${PACKAGES[@]}"; do
    SOURCE_PATH="$FOUND_PATH/$pkg"
    LINK_PATH="$LOCAL_PACKAGES_DIR/$pkg"

    if [[ ! -d "$SOURCE_PATH" ]]; then
        echo -e "   ${YELLOW}⚠️  Saltando $pkg (no existe)${NC}"
        continue
    fi

    # Si ya es symlink correcto
    if [[ -L "$LINK_PATH" ]]; then
        CURRENT_TARGET=$(readlink "$LINK_PATH")
        if [[ "$CURRENT_TARGET" == "$SOURCE_PATH" || "$(cd "$(dirname "$LINK_PATH")" && cd "$CURRENT_TARGET" && pwd)" == "$SOURCE_PATH" ]]; then
            echo -e "   ✓ $pkg (ya enlazado)"
            continue
        fi
        # Symlink incorrecto, eliminar
        rm "$LINK_PATH"
    fi

    # Si existe pero no es symlink
    if [[ -e "$LINK_PATH" ]]; then
        echo -e "   ${YELLOW}⚠️  $pkg existe pero no es symlink, saltando${NC}"
        continue
    fi

    # Crear symlink
    ln -s "$SOURCE_PATH" "$LINK_PATH"
    echo -e "   ${GREEN}✓ $pkg enlazado${NC}"
done
echo ""

# ═══════════════════════════════════════════════════════════
# PASO 5: Modificar composer.json
# ═══════════════════════════════════════════════════════════
echo -e "${CYAN}📝 Configurando composer.json para desarrollo local...${NC}"

# Backup del original si no existe
if [[ ! -f "composer.json.original" ]]; then
    cp composer.json composer.json.original
    echo -e "   ✓ Backup creado: composer.json.original"
fi

# Usar PHP para modificar composer.json (más confiable que sed/jq)
php << 'PHPSCRIPT'
<?php
$composerPath = 'composer.json';
$composer = json_decode(file_get_contents($composerPath), true);

$packages = ['larabill', 'lara-verifactu', 'laratickets', 'lararoi'];

$newRepositories = [];
foreach ($packages as $package) {
    $newRepositories[] = [
        'type' => 'path',
        'url' => './packages/aichadigital/' . $package,
        'options' => ['symlink' => true],
    ];
}

$composer['repositories'] = $newRepositories;

file_put_contents(
    $composerPath,
    json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n"
);

echo "   ✓ composer.json actualizado con paths locales\n";
PHPSCRIPT

echo -e "   ${YELLOW}⚠️  IMPORTANTE: No commitear composer.json modificado${NC}"
echo ""

# ═══════════════════════════════════════════════════════════
# PASO 6: Ejecutar composer install
# ═══════════════════════════════════════════════════════════
echo -e "${CYAN}📦 Instalando dependencias (composer install)...${NC}"
echo "   Esto puede tardar unos minutos..."
echo ""

if composer install --no-interaction; then
    echo ""
    echo -e "   ${GREEN}✓ Dependencias instaladas${NC}"
else
    echo ""
    echo -e "${RED}❌ Error en composer install${NC}"
    echo "   Revisa los errores arriba y vuelve a ejecutar el script"
    exit 1
fi
echo ""

# ═══════════════════════════════════════════════════════════
# PASO 7: Generar APP_KEY
# ═══════════════════════════════════════════════════════════
echo -e "${CYAN}🔑 Generando APP_KEY...${NC}"

if grep -q "APP_KEY=base64:" .env; then
    echo -e "   ✓ APP_KEY ya existe"
else
    php artisan key:generate --force
    echo -e "   ✓ APP_KEY generado"
fi
echo ""

# ═══════════════════════════════════════════════════════════
# PASO 8: Ejecutar instalador Laravel (migraciones, seeders)
# ═══════════════════════════════════════════════════════════
echo -e "${CYAN}🗄️  Ejecutando instalador Laravel...${NC}"
echo ""

php artisan larafactu:install --local --fresh --skip-composer --no-interaction

echo ""

# ═══════════════════════════════════════════════════════════
# PASO 9: Compilar assets (opcional)
# ═══════════════════════════════════════════════════════════
if [[ "$SKIP_NPM" == false ]]; then
    echo -e "${CYAN}🎨 Compilando assets frontend...${NC}"

    if npm install && npm run build; then
        echo -e "   ${GREEN}✓ Assets compilados${NC}"
    else
        echo -e "   ${YELLOW}⚠️  Error compilando assets (no crítico)${NC}"
    fi
    echo ""
fi

# ═══════════════════════════════════════════════════════════
# ÉXITO
# ═══════════════════════════════════════════════════════════
echo ""
echo "╔══════════════════════════════════════════╗"
echo "║     ${GREEN}✅ INSTALACIÓN COMPLETADA ✅${NC}         ║"
echo "╚══════════════════════════════════════════╝"
echo ""
echo -e "   📍 Admin: ${CYAN}http://larafactu.test/admin${NC}"
echo -e "   👤 Usuario: ${YELLOW}admin@example.com${NC}"
echo -e "   🔑 Password: ${YELLOW}password${NC}"
echo ""
echo -e "   ${YELLOW}⚠️  RECUERDA: No commitear composer.json modificado${NC}"
echo ""
