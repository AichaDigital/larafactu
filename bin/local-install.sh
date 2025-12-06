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
#   - Base de datos MySQL o SQLite

set -e

# Colores
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
CYAN='\033[0;36m'
BOLD='\033[1m'
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

# ═══════════════════════════════════════════════════════════
# PASO 0: Parsear argumentos
# ═══════════════════════════════════════════════════════════
PACKAGES_PATH=""
SKIP_NPM=false
DB_TYPE=""
DB_DATABASE=""
DB_USERNAME=""
DB_PASSWORD=""
DB_SOCKET=""

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
        --sqlite)
            DB_TYPE="sqlite"
            shift
            ;;
        --mysql)
            DB_TYPE="mysql"
            shift
            ;;
        --db-database=*)
            DB_DATABASE="${arg#*=}"
            shift
            ;;
        --db-username=*)
            DB_USERNAME="${arg#*=}"
            shift
            ;;
        --db-password=*)
            DB_PASSWORD="${arg#*=}"
            shift
            ;;
        --db-socket=*)
            DB_SOCKET="${arg#*=}"
            shift
            ;;
        --help)
            echo "Uso: ./bin/local-install.sh [opciones]"
            echo ""
            echo "Opciones generales:"
            echo "  --packages-path=/ruta   Ruta a los paquetes de desarrollo"
            echo "  --skip-npm              No ejecutar npm install/build"
            echo "  --help                  Mostrar esta ayuda"
            echo ""
            echo "Opciones de base de datos:"
            echo "  --sqlite                Usar SQLite (crea database/database.sqlite)"
            echo "  --mysql                 Usar MySQL"
            echo "  --db-database=nombre    Nombre de la base de datos MySQL"
            echo "  --db-username=user      Usuario MySQL (default: root)"
            echo "  --db-password=pass      Password MySQL"
            echo "  --db-socket=/path       Socket MySQL (opcional)"
            echo ""
            echo "Ejemplos:"
            echo "  ./bin/local-install.sh --sqlite"
            echo "  ./bin/local-install.sh --mysql --db-database=larafactu --db-username=root"
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
# PASO 3: Configurar .env base
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
# PASO 4: Configurar base de datos
# ═══════════════════════════════════════════════════════════
echo -e "${CYAN}🗄️  Configurando base de datos...${NC}"
echo ""

# Si no se especificó tipo de BD, preguntar
if [[ -z "$DB_TYPE" ]]; then
    echo -e "   ${BOLD}¿Qué base de datos quieres usar?${NC}"
    echo ""
    echo "   1) SQLite  - Archivo local, sin configuración adicional"
    echo "   2) MySQL   - Requiere servidor MySQL corriendo"
    echo ""
    read -p "   Selecciona [1/2] (default: 1): " db_choice

    case $db_choice in
        2|mysql|MySQL)
            DB_TYPE="mysql"
            ;;
        *)
            DB_TYPE="sqlite"
            ;;
    esac
fi

echo ""
echo -e "   Tipo seleccionado: ${GREEN}${DB_TYPE}${NC}"
echo ""

# Función para actualizar .env
update_env() {
    local key=$1
    local value=$2
    local file=".env"

    if grep -q "^${key}=" "$file"; then
        # Escapar caracteres especiales en el valor para sed
        local escaped_value=$(printf '%s\n' "$value" | sed -e 's/[\/&]/\\&/g')
        sed -i.bak "s|^${key}=.*|${key}=${escaped_value}|" "$file"
        rm -f "${file}.bak"
    else
        echo "${key}=${value}" >> "$file"
    fi
}

if [[ "$DB_TYPE" == "sqlite" ]]; then
    # ─────────────────────────────────────────────────────────
    # SQLite: Crear archivo y configurar .env
    # ─────────────────────────────────────────────────────────
    SQLITE_PATH="database/database.sqlite"

    if [[ ! -f "$SQLITE_PATH" ]]; then
        touch "$SQLITE_PATH"
        echo -e "   ✓ Archivo SQLite creado: ${SQLITE_PATH}"
    else
        echo -e "   ✓ Archivo SQLite ya existe: ${SQLITE_PATH}"
    fi

    # Actualizar .env para SQLite
    update_env "DB_CONNECTION" "sqlite"
    update_env "DB_DATABASE" "database/database.sqlite"
    # Comentar o limpiar las variables MySQL
    update_env "DB_HOST" ""
    update_env "DB_PORT" ""
    update_env "DB_USERNAME" ""
    update_env "DB_PASSWORD" ""

    echo -e "   ✓ .env configurado para SQLite"

else
    # ─────────────────────────────────────────────────────────
    # MySQL: Pedir datos de conexión
    # ─────────────────────────────────────────────────────────
    echo -e "   ${YELLOW}⚠️  IMPORTANTE: La base de datos debe existir previamente${NC}"
    echo -e "   ${YELLOW}   Créala con: mysql -e \"CREATE DATABASE larafactu\"${NC}"
    echo ""

    # Nombre de base de datos
    if [[ -z "$DB_DATABASE" ]]; then
        read -p "   Nombre de la base de datos [larafactu]: " DB_DATABASE
        DB_DATABASE=${DB_DATABASE:-larafactu}
    fi

    # Usuario
    if [[ -z "$DB_USERNAME" ]]; then
        read -p "   Usuario MySQL [root]: " DB_USERNAME
        DB_USERNAME=${DB_USERNAME:-root}
    fi

    # Password
    if [[ -z "$DB_PASSWORD" ]]; then
        read -s -p "   Password MySQL (vacío si no tiene): " DB_PASSWORD
        echo ""
    fi

    # Socket (opcional)
    if [[ -z "$DB_SOCKET" ]]; then
        echo ""
        echo -e "   ${YELLOW}Nota:${NC} Si usas Herd/Valet con socket, indícalo aquí."
        read -p "   Socket MySQL (vacío para TCP estándar): " DB_SOCKET
    fi

    # Actualizar .env
    update_env "DB_CONNECTION" "mysql"
    update_env "DB_DATABASE" "$DB_DATABASE"
    update_env "DB_USERNAME" "$DB_USERNAME"
    update_env "DB_PASSWORD" "$DB_PASSWORD"

    if [[ -n "$DB_SOCKET" ]]; then
        update_env "DB_SOCKET" "$DB_SOCKET"
        # Si usa socket, el host debe ser localhost
        update_env "DB_HOST" "localhost"
        echo -e "   ✓ Configurado con socket: ${DB_SOCKET}"
    else
        update_env "DB_HOST" "127.0.0.1"
        update_env "DB_PORT" "3306"
    fi

    echo -e "   ✓ .env configurado para MySQL"
    echo -e "      Database: ${GREEN}${DB_DATABASE}${NC}"
    echo -e "      User: ${GREEN}${DB_USERNAME}${NC}"
fi

echo ""

# ═══════════════════════════════════════════════════════════
# PASO 5: Crear symlinks
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
        if [[ "$CURRENT_TARGET" == "$SOURCE_PATH" || "$(cd "$(dirname "$LINK_PATH")" && cd "$CURRENT_TARGET" 2>/dev/null && pwd)" == "$SOURCE_PATH" ]]; then
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
# PASO 6: Modificar composer.json
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

# Marcar composer.json para que git ignore los cambios locales
if git rev-parse --git-dir > /dev/null 2>&1; then
    git update-index --skip-worktree composer.json 2>/dev/null || true
    echo -e "   ✓ composer.json protegido (git skip-worktree)"
fi
echo ""

# ═══════════════════════════════════════════════════════════
# PASO 7: Ejecutar composer install
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
# PASO 8: Generar APP_KEY
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
# PASO 9: Ejecutar instalador Laravel (migraciones, seeders)
# ═══════════════════════════════════════════════════════════
echo -e "${CYAN}🗄️  Ejecutando instalador Laravel...${NC}"
echo ""

php artisan larafactu:install --local --fresh --skip-composer --no-interaction

echo ""

# ═══════════════════════════════════════════════════════════
# PASO 10: Compilar assets (opcional)
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
echo -e "║     ${GREEN}✅ INSTALACIÓN COMPLETADA ✅${NC}         ║"
echo "╚══════════════════════════════════════════╝"
echo ""
echo -e "   📍 Admin: ${CYAN}http://larafactu.test/admin${NC}"
echo -e "   👤 Usuario: ${YELLOW}admin@example.com${NC}"
echo -e "   🔑 Password: ${YELLOW}password${NC}"
echo ""
echo -e "   🗄️  Base de datos: ${GREEN}${DB_TYPE}${NC}"
if [[ "$DB_TYPE" == "mysql" ]]; then
    echo -e "      └─ ${DB_DATABASE}"
fi
echo ""
echo -e "   ${GREEN}✓ composer.json protegido con git skip-worktree${NC}"
echo -e "     (Para restaurar: git update-index --no-skip-worktree composer.json)"
echo ""
