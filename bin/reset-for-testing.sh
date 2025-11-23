#!/bin/bash
# Reset Larafactu to fresh state for testing package installation

set -e

echo "🔄 Resetting Larafactu to clean state..."
echo ""

# 1. Limpiar base de datos
echo "📦 Cleaning database..."
php artisan db:wipe --no-interaction
mysql larafactu < database/dumps/00_laravel_core_base.sql
echo "✓ Database reset to CORE Laravel state"
echo ""

# 2. Limpiar migraciones publicadas (excepto CORE)
echo "🗑️  Removing published package migrations..."
rm -f database/migrations/2024_*.php
rm -f database/migrations/2025_*.php
echo "✓ Package migrations removed"
echo ""

# 3. Limpiar configuraciones publicadas (opcional)
echo "🗑️  Removing published configs..."
rm -f config/larabill.php
rm -f config/verifactu.php
rm -f config/laratickets.php
echo "✓ Package configs removed"
echo ""

# 4. Estado actual
echo "📊 Current state:"
echo "  - Database: Laravel CORE (9 tables)"
echo "  - Migrations: 3 CORE files only"
echo "  - Configs: Laravel defaults only"
echo ""

echo "✅ Ready for fresh package installation!"
echo ""
echo "Next steps:"
echo "  1. php artisan larabill:install"
echo "  2. php artisan verifactu:install"
echo "  3. php artisan laratickets:install"

