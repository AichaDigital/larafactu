#!/bin/bash
# Test complete package installation flow

set -e

echo "🧪 Testing Package Installation Flow"
echo "===================================="
echo ""

# Colores
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# 1. Reset
echo "${YELLOW}Step 1: Resetting to clean state...${NC}"
./bin/reset-for-testing.sh
echo ""

# 2. Actualizar paquetes
echo "${YELLOW}Step 2: Updating packages from local repos...${NC}"
composer update aichadigital/larabill aichadigital/lara-verifactu aichadigital/laratickets --no-interaction
echo "${GREEN}✓ Packages updated${NC}"
echo ""

# 3. Instalar Larabill
echo "${YELLOW}Step 3: Installing Larabill...${NC}"
php artisan larabill:install --user-id-type=uuid_binary --no-migrate --force
if [ $? -eq 0 ]; then
    echo "${GREEN}✓ Larabill install command completed${NC}"
else
    echo "${RED}✗ Larabill install failed${NC}"
    exit 1
fi
echo ""

# 4. Instalar Lara-Verifactu
echo "${YELLOW}Step 4: Installing Lara-Verifactu...${NC}"
php artisan verifactu:install --no-migrate --force
if [ $? -eq 0 ]; then
    echo "${GREEN}✓ Lara-Verifactu install command completed${NC}"
else
    echo "${RED}✗ Lara-Verifactu install failed${NC}"
    exit 1
fi
echo ""

# 5. Instalar Laratickets
echo "${YELLOW}Step 5: Installing Laratickets...${NC}"
php artisan laratickets:install --no-migrate --force
if [ $? -eq 0 ]; then
    echo "${GREEN}✓ Laratickets install command completed${NC}"
else
    echo "${RED}✗ Laratickets install failed${NC}"
    exit 1
fi
echo ""

# 6. Verificar migraciones publicadas
echo "${YELLOW}Step 6: Verifying published migrations...${NC}"
MIGRATION_COUNT=$(ls -1 database/migrations/*.php | wc -l | tr -d ' ')
echo "  Total migrations: ${MIGRATION_COUNT}"

if [ "$MIGRATION_COUNT" -gt 30 ]; then
    echo "${GREEN}✓ All migrations published${NC}"
else
    echo "${RED}✗ Expected ~38 migrations, found ${MIGRATION_COUNT}${NC}"
    exit 1
fi
echo ""

# 7. Ejecutar migraciones
echo "${YELLOW}Step 7: Running migrations...${NC}"
php artisan migrate --no-interaction
if [ $? -eq 0 ]; then
    echo "${GREEN}✓ All migrations executed successfully${NC}"
else
    echo "${RED}✗ Migration failed${NC}"
    exit 1
fi
echo ""

# 8. Verificar tablas creadas
echo "${YELLOW}Step 8: Verifying database tables...${NC}"
TABLE_COUNT=$(mysql -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'larafactu';" larafactu -s -N)
echo "  Total tables: ${TABLE_COUNT}"

if [ "$TABLE_COUNT" -ge 40 ]; then
    echo "${GREEN}✓ All tables created (expected ~42)${NC}"
else
    echo "${RED}✗ Expected ~42 tables, found ${TABLE_COUNT}${NC}"
    exit 1
fi
echo ""

# 9. Resumen
echo "================================================"
echo "${GREEN}✅ ALL TESTS PASSED${NC}"
echo "================================================"
echo ""
echo "Summary:"
echo "  - Larabill: ✓ Installed"
echo "  - Lara-Verifactu: ✓ Installed"
echo "  - Laratickets: ✓ Installed"
echo "  - Migrations: ${MIGRATION_COUNT} published"
echo "  - Database: ${TABLE_COUNT} tables created"
echo ""
echo "Next: Test with seeders and business logic"

