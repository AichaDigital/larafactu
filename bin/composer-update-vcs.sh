#!/usr/bin/env bash

###############################################################################
# Safe Composer Update - Actualiza paquetes desde GitHub (VCS) de forma segura
#
# Usage:
#   ./bin/composer-update-vcs.sh                    # Actualizar todos
#   ./bin/composer-update-vcs.sh larabill           # Actualizar uno
#   ./bin/composer-update-vcs.sh larabill verifactu # Actualizar varios
#
# Este script:
# 1. Verifica que los paquetes estén en sync con GitHub
# 2. Cambia temporalmente a repositorios VCS
# 3. Ejecuta composer update
# 4. Restaura repositorios PATH (symlinks)
###############################################################################

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Directories
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(cd "$SCRIPT_DIR/.." && pwd)"

echo -e "${BLUE}╔════════════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║  Safe Composer Update (VCS Mode)                              ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════════════════════╝${NC}"
echo ""

# Step 1: Check sync
echo -e "${YELLOW}→ Verificando sincronización de paquetes...${NC}"
if ! "$SCRIPT_DIR/check-packages-sync.sh"; then
    echo ""
    echo -e "${RED}✗ Los paquetes no están en sync con GitHub${NC}"
    echo -e "${RED}✗ Pushea los cambios primero${NC}"
    exit 1
fi

echo ""
echo -e "${GREEN}✓ Paquetes en sync${NC}"
echo ""

# Step 2: Backup composer.json
echo -e "${YELLOW}→ Backing up composer.json...${NC}"
cd "$PROJECT_DIR"
cp composer.json composer.json.backup

# Step 3: Switch to VCS
echo -e "${YELLOW}→ Cambiando a repositorios VCS (GitHub)...${NC}"

cat > modify-composer-vcs.php << 'EOFPHP'
<?php
$composer = json_decode(file_get_contents('composer.json'), true);

$composer['repositories'] = [
    ['type' => 'vcs', 'url' => 'https://github.com/AichaDigital/larabill'],
    ['type' => 'vcs', 'url' => 'https://github.com/AichaDigital/lara-verifactu'],
    ['type' => 'vcs', 'url' => 'https://github.com/AichaDigital/laratickets'],
    ['type' => 'vcs', 'url' => 'https://github.com/AichaDigital/lararoi'],
];

file_put_contents('composer.json', json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo "✓ Switched to VCS repositories\n";
EOFPHP

php modify-composer-vcs.php
rm modify-composer-vcs.php

# Step 4: Composer update
echo ""
if [ $# -eq 0 ]; then
    echo -e "${YELLOW}→ Ejecutando: composer update (todos los paquetes)${NC}"
    composer update --no-interaction --prefer-dist
else
    PACKAGES=""
    for arg in "$@"; do
        case $arg in
            larabill)
                PACKAGES="$PACKAGES aichadigital/larabill"
                ;;
            verifactu|lara-verifactu)
                PACKAGES="$PACKAGES aichadigital/lara-verifactu"
                ;;
            tickets|laratickets)
                PACKAGES="$PACKAGES aichadigital/laratickets"
                ;;
            roi|lararoi)
                PACKAGES="$PACKAGES aichadigital/lararoi"
                ;;
            *)
                echo -e "${RED}✗ Paquete desconocido: $arg${NC}"
                echo -e "Opciones: larabill, verifactu, tickets, roi"
                mv composer.json.backup composer.json
                exit 1
                ;;
        esac
    done

    echo -e "${YELLOW}→ Ejecutando: composer update$PACKAGES${NC}"
    composer update $PACKAGES --no-interaction --prefer-dist
fi

# Step 5: Restore PATH repositories
echo ""
echo -e "${YELLOW}→ Restaurando repositorios PATH (symlinks)...${NC}"
mv composer.json.backup composer.json

echo ""
echo -e "${BLUE}════════════════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}✓ Actualización completada${NC}"
echo -e "${GREEN}✓ composer.json restaurado a modo PATH (symlinks)${NC}"
echo ""
echo -e "Versiones actualizadas:"
composer show aichadigital/larabill aichadigital/lara-verifactu aichadigital/laratickets 2>/dev/null | grep -E "^(name|versions)" || true

