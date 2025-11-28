#!/bin/bash

################################################################################
# Larafactu - Deploy/Update Script
#
# Handles production updates safely without git conflicts
# For use in DirectAdmin or any production environment
################################################################################

set -e  # Exit on error

echo "🚀 Larafactu - Deploy/Update Script"
echo "===================================="
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
APP_DIR=$(pwd)
BACKUP_DIR="${APP_DIR}/../larafactu-backups"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

################################################################################
# Functions
################################################################################

print_success() {
    echo -e "${GREEN}✓${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

################################################################################
# Pre-Update Checks
################################################################################

echo "📋 Pre-update checks..."

# Check if we're in a git repo
if [ ! -d ".git" ]; then
    print_error "Not a git repository. Please clone the repo first."
    exit 1
fi

print_success "Git repository detected"

################################################################################
# Backup Current State
################################################################################

echo ""
echo "💾 Creating backup..."

# Create backup directory
mkdir -p "${BACKUP_DIR}"

# Backup .env
if [ -f ".env" ]; then
    cp .env "${BACKUP_DIR}/.env.${TIMESTAMP}"
    print_success ".env backed up"
fi

# Backup composer.json (if modified)
if [ -f "composer.json" ]; then
    cp composer.json "${BACKUP_DIR}/composer.json.${TIMESTAMP}"
    print_success "composer.json backed up"
fi

# Backup database (optional but recommended)
if [ -f ".env" ]; then
    source .env 2>/dev/null || true
    if [ ! -z "$DB_DATABASE" ]; then
        read -p "👉 Backup database? (y/n) " -n 1 -r
        echo ""
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            mysqldump -u"${DB_USERNAME}" -p"${DB_PASSWORD}" "${DB_DATABASE}" > "${BACKUP_DIR}/db_${TIMESTAMP}.sql"
            print_success "Database backed up to ${BACKUP_DIR}/db_${TIMESTAMP}.sql"
        fi
    fi
fi

################################################################################
# Enter Maintenance Mode
################################################################################

echo ""
echo "🔒 Entering maintenance mode..."
php artisan down --render="errors::503" --retry=60
print_success "Application in maintenance mode"

################################################################################
# Git Update Strategy
################################################################################

echo ""
echo "📥 Updating code from repository..."

# Stash local changes (like modified composer.json)
print_warning "Stashing local changes..."
git stash push -m "Auto-stash before update ${TIMESTAMP}"

# Fetch latest changes
git fetch origin main

# Reset to latest (DESTRUCTIVE - but we have backups)
print_warning "Resetting to latest version..."
git reset --hard origin/main

# If there were stashed changes, we DON'T pop them
# User can manually review later if needed

print_success "Code updated to latest version"

################################################################################
# Post-Deploy Script (Convert repositories)
################################################################################

echo ""
echo "🔧 Running post-deploy script..."

if [ -f "scripts/post-deploy.php" ]; then
    php scripts/post-deploy.php
    print_success "Repositories converted for production"
else
    print_warning "post-deploy.php not found, skipping"
fi

################################################################################
# Update Dependencies
################################################################################

echo ""
echo "📦 Updating dependencies..."

# Composer update
composer install --no-dev --optimize-autoloader --no-interaction
print_success "Composer dependencies updated"

# NPM update (if needed)
if [ -f "package.json" ]; then
    read -p "👉 Update frontend assets? (y/n) " -n 1 -r
    echo ""
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        npm install
        npm run build
        print_success "Frontend assets updated"
    fi
fi

################################################################################
# Restore .env if needed
################################################################################

echo ""
echo "⚙️  Checking configuration..."

if [ ! -f ".env" ]; then
    print_warning ".env not found, restoring from backup"
    cp "${BACKUP_DIR}/.env.${TIMESTAMP}" .env
    print_success ".env restored"
else
    print_success ".env exists"
fi

################################################################################
# Run Migrations
################################################################################

echo ""
echo "🗄️  Running database migrations..."

read -p "👉 Run migrations? (y/n) " -n 1 -r
echo ""
if [[ $REPLY =~ ^[Yy]$ ]]; then
    php artisan migrate --force
    print_success "Migrations completed"
else
    print_warning "Migrations skipped"
fi

################################################################################
# Clear & Cache
################################################################################

echo ""
echo "🧹 Clearing and caching..."

php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

print_success "Caches cleared"

php artisan config:cache
php artisan route:cache
php artisan view:cache

print_success "Caches rebuilt"

################################################################################
# Exit Maintenance Mode
################################################################################

echo ""
echo "🔓 Exiting maintenance mode..."
php artisan up
print_success "Application is now LIVE"

################################################################################
# Summary
################################################################################

echo ""
echo "=========================================="
echo "✅ Update completed successfully!"
echo "=========================================="
echo ""
echo "📊 Summary:"
echo "  - Backup created: ${BACKUP_DIR}"
echo "  - Code updated to latest version"
echo "  - Dependencies updated"
echo "  - Caches rebuilt"
echo ""
echo "🔍 Check logs if issues:"
echo "  tail -f storage/logs/laravel.log"
echo ""
echo "↩️  Rollback if needed:"
echo "  php artisan down"
echo "  git reset --hard <previous-commit>"
echo "  cp ${BACKUP_DIR}/.env.${TIMESTAMP} .env"
echo "  composer install --no-dev"
echo "  php artisan up"
echo ""

