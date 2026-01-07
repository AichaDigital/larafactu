#!/bin/bash
#
# Larafactu Installer - Test Runner
#
# This script runs the installer tests in a clean Docker environment.
# It's designed to be run OUTSIDE of the main CI pipeline.
#
# Usage:
#   ./test.sh              # Run all tests
#   ./test.sh --unit       # Run only unit tests
#   ./test.sh --integration # Run only integration tests
#

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

echo "🧪 Larafactu Installer - Test Suite"
echo "===================================="
echo ""

# Parse arguments
TEST_TYPE="all"
if [ "$1" == "--unit" ]; then
    TEST_TYPE="unit"
elif [ "$1" == "--integration" ]; then
    TEST_TYPE="integration"
fi

# Clean up previous containers
echo "🧹 Cleaning up previous containers..."
docker-compose -f docker/docker-compose.yml down -v 2>/dev/null || true

# Start fresh containers
echo "🚀 Starting Docker containers..."
docker-compose -f docker/docker-compose.yml up -d

# Wait for MySQL to be ready
echo "⏳ Waiting for MySQL to be ready..."
sleep 10

# Check MySQL is healthy
MAX_TRIES=30
TRIES=0
until docker-compose -f docker/docker-compose.yml exec -T mysql mysqladmin ping -h localhost -u root -proot --silent 2>/dev/null; do
    TRIES=$((TRIES + 1))
    if [ $TRIES -gt $MAX_TRIES ]; then
        echo "❌ MySQL failed to start"
        docker-compose -f docker/docker-compose.yml logs mysql
        exit 1
    fi
    echo "   Waiting for MySQL... ($TRIES/$MAX_TRIES)"
    sleep 2
done
echo "✅ MySQL is ready"

# Run tests based on type
echo ""
echo "🧪 Running tests..."
echo ""

case $TEST_TYPE in
    "unit")
        docker-compose -f docker/docker-compose.yml exec -T php-fpm \
            php vendor/bin/phpunit --testsuite=Unit
        ;;
    "integration")
        docker-compose -f docker/docker-compose.yml exec -T php-fpm \
            php vendor/bin/phpunit --testsuite=Integration
        ;;
    *)
        docker-compose -f docker/docker-compose.yml exec -T php-fpm \
            php vendor/bin/phpunit
        ;;
esac

TEST_RESULT=$?

# Cleanup
echo ""
echo "🧹 Cleaning up..."
docker-compose -f docker/docker-compose.yml down -v

# Report result
echo ""
if [ $TEST_RESULT -eq 0 ]; then
    echo "✅ All tests passed!"
else
    echo "❌ Some tests failed"
    exit $TEST_RESULT
fi

