#!/bin/bash
#
# Larafactu Installer - Docker Testing Environment
#
# This script starts the Docker environment for testing the web installer.
# It does NOT run automated tests - it sets up the environment for manual testing.
#
# Usage:
#   ./test.sh         # Start Docker and open installer
#   ./test.sh --down  # Stop and clean up Docker containers
#

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

echo "Larafactu Installer - Docker Testing Environment"
echo "================================================="
echo ""

# Check for --down flag
if [ "$1" == "--down" ]; then
    echo "Stopping Docker containers..."
    docker-compose -f docker/docker-compose.yml down -v 2>/dev/null || true
    echo "Done. Containers stopped and volumes removed."
    exit 0
fi

# Clean up previous containers
echo "Cleaning up previous containers..."
docker-compose -f docker/docker-compose.yml down -v 2>/dev/null || true

# Start fresh containers
echo "Starting Docker containers..."
docker-compose -f docker/docker-compose.yml up -d

# Wait for MySQL to be ready
echo "Waiting for MySQL to be ready..."
sleep 5

# Check MySQL is healthy
MAX_TRIES=30
TRIES=0
until docker-compose -f docker/docker-compose.yml exec -T mysql mysqladmin ping -h localhost -u root -proot --silent 2>/dev/null; do
    TRIES=$((TRIES + 1))
    if [ $TRIES -gt $MAX_TRIES ]; then
        echo "ERROR: MySQL failed to start"
        docker-compose -f docker/docker-compose.yml logs mysql
        exit 1
    fi
    echo "   Waiting for MySQL... ($TRIES/$MAX_TRIES)"
    sleep 2
done
echo "MySQL is ready"

echo ""
echo "================================================="
echo "Docker environment is ready!"
echo ""
echo "Open the installer at:"
echo ""
echo "    http://localhost:8889"
echo ""
echo "Database credentials (predefined for Docker):"
echo "    Host:     mysql"
echo "    Port:     3306"
echo "    Database: larafactu_test"
echo "    Username: larafactu"
echo "    Password: larafactu"
echo ""
echo "To stop the environment:"
echo "    ./test.sh --down"
echo ""
echo "Or manually:"
echo "    docker-compose -f docker/docker-compose.yml down -v"
echo "================================================="
