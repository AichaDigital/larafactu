#!/usr/bin/env bash

###############################################################################
# Check Packages Sync - Verifica que los paquetes locales estén en sync con GitHub
#
# Usage: ./bin/check-packages-sync.sh
#
# Verifica:
# - Commits pendientes de push en cada paquete
# - Estado de los branches (main vs origin/main)
# - Working tree limpio
###############################################################################

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Package paths
PACKAGES_DIR="/Users/abkrim/development/packages/aichadigital"
PACKAGES=("larabill" "lara-verifactu" "laratickets" "lararoi")

echo -e "${BLUE}╔════════════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║  Verificando sincronización de paquetes con GitHub            ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════════════════════╝${NC}"
echo ""

ALL_SYNCED=true

for package in "${PACKAGES[@]}"; do
    PACKAGE_PATH="$PACKAGES_DIR/$package"

    if [ ! -d "$PACKAGE_PATH" ]; then
        echo -e "${RED}✗ $package${NC} - Directorio no encontrado: $PACKAGE_PATH"
        ALL_SYNCED=false
        continue
    fi

    cd "$PACKAGE_PATH"

    # Check if git repo
    if [ ! -d ".git" ]; then
        echo -e "${RED}✗ $package${NC} - No es un repositorio Git"
        ALL_SYNCED=false
        continue
    fi

    # Get current branch
    CURRENT_BRANCH=$(git branch --show-current)

    # Check working tree
    if ! git diff-index --quiet HEAD --; then
        echo -e "${YELLOW}⚠ $package${NC} (${CURRENT_BRANCH}) - Working tree tiene cambios sin commit"
        ALL_SYNCED=false
        git status --short
        echo ""
        continue
    fi

    # Fetch latest from origin
    git fetch origin --quiet

    # Check commits ahead/behind
    LOCAL=$(git rev-parse @)
    REMOTE=$(git rev-parse @{u} 2>/dev/null || echo "no-remote")
    BASE=$(git merge-base @ @{u} 2>/dev/null || echo "no-base")

    if [ "$REMOTE" = "no-remote" ]; then
        echo -e "${YELLOW}⚠ $package${NC} (${CURRENT_BRANCH}) - No tiene remote tracking branch"
        ALL_SYNCED=false
    elif [ "$LOCAL" = "$REMOTE" ]; then
        echo -e "${GREEN}✓ $package${NC} (${CURRENT_BRANCH}) - En sync con origin/${CURRENT_BRANCH}"
    elif [ "$LOCAL" = "$BASE" ]; then
        echo -e "${YELLOW}⚠ $package${NC} (${CURRENT_BRANCH}) - Detrás de origin/${CURRENT_BRANCH} (necesitas pull)"
        ALL_SYNCED=false
        git log --oneline HEAD..@{u} | head -3
        echo ""
    elif [ "$REMOTE" = "$BASE" ]; then
        echo -e "${YELLOW}⚠ $package${NC} (${CURRENT_BRANCH}) - Commits pendientes de push:"
        ALL_SYNCED=false
        git log --oneline @{u}..HEAD
        echo ""
    else
        echo -e "${RED}✗ $package${NC} (${CURRENT_BRANCH}) - Diverged de origin/${CURRENT_BRANCH}"
        ALL_SYNCED=false
    fi
done

echo ""
echo -e "${BLUE}════════════════════════════════════════════════════════════════${NC}"

if [ "$ALL_SYNCED" = true ]; then
    echo -e "${GREEN}✓ Todos los paquetes están en sync con GitHub${NC}"
    echo -e "${GREEN}✓ Seguro ejecutar 'composer update' desde VCS${NC}"
    exit 0
else
    echo -e "${YELLOW}⚠ Algunos paquetes tienen cambios pendientes${NC}"
    echo -e "${YELLOW}⚠ Asegúrate de pushear los cambios antes de 'composer update'${NC}"
    echo ""
    echo -e "Comandos sugeridos:"
    echo -e "  ${BLUE}cd /Users/abkrim/development/packages/aichadigital/<package>${NC}"
    echo -e "  ${BLUE}git add -A && git commit -m 'descripción'${NC}"
    echo -e "  ${BLUE}git push origin main${NC}"
    exit 1
fi

