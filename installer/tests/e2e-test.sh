#!/bin/bash
#
# Larafactu Installer - E2E Test Script
#
# This script tests the installer flow automatically in Docker.
# It verifies both UUID and Integer ID type scenarios.
#
# Usage:
#   ./tests/e2e-test.sh [--uuid-only|--integer-only]
#
# Requirements:
#   - Docker and docker-compose installed
#   - curl installed
#   - jq installed (for JSON parsing)
#

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
INSTALLER_DIR="$(dirname "$SCRIPT_DIR")"
BASE_URL="http://localhost:8889"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Test counters
TESTS_PASSED=0
TESTS_FAILED=0

log_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

log_test() {
    echo -e "${GREEN}[TEST]${NC} $1"
}

assert_contains() {
    local haystack="$1"
    local needle="$2"
    local message="$3"

    if echo "$haystack" | grep -q "$needle"; then
        log_test "PASS: $message"
        ((TESTS_PASSED++))
    else
        log_error "FAIL: $message"
        log_error "Expected to find: $needle"
        ((TESTS_FAILED++))
    fi
}

assert_http_code() {
    local actual="$1"
    local expected="$2"
    local message="$3"

    if [ "$actual" = "$expected" ]; then
        log_test "PASS: $message (HTTP $actual)"
        ((TESTS_PASSED++))
    else
        log_error "FAIL: $message"
        log_error "Expected HTTP $expected, got HTTP $actual"
        ((TESTS_FAILED++))
    fi
}

# Cleanup function
cleanup() {
    log_info "Cleaning up..."
    cd "$INSTALLER_DIR"
    ./test.sh --down 2>/dev/null || true
}

# Start Docker environment
start_docker() {
    log_info "Starting Docker environment..."
    cd "$INSTALLER_DIR"
    ./test.sh

    # Wait for services
    log_info "Waiting for services to be ready..."
    sleep 5

    # Verify MySQL is ready
    local max_attempts=30
    local attempt=0
    while ! docker exec docker-mysql-1 mysqladmin ping -h localhost -u root -proot --silent 2>/dev/null; do
        ((attempt++))
        if [ $attempt -ge $max_attempts ]; then
            log_error "MySQL failed to start"
            exit 1
        fi
        sleep 2
    done
    log_info "MySQL is ready"
}

# Clean installer state
clean_state() {
    log_info "Cleaning installer state..."
    rm -f "$INSTALLER_DIR/.done"
    rm -f "$INSTALLER_DIR/storage/install_state.json"
    rm -f "$INSTALLER_DIR/../.env" 2>/dev/null || true

    # Reset database
    docker exec docker-mysql-1 mysql -u root -proot -e "DROP DATABASE IF EXISTS larafactu_test; CREATE DATABASE larafactu_test;" 2>/dev/null
    log_info "State cleaned"
}

# Get access token
get_token() {
    if [ -f "$INSTALLER_DIR/storage/.token" ]; then
        cat "$INSTALLER_DIR/storage/.token" | grep -o '"token":"[^"]*"' | cut -d'"' -f4
    else
        log_error "Token file not found"
        exit 1
    fi
}

# Run installer flow
run_installer_test() {
    local id_type="$1"
    local cookie_file="/tmp/installer_cookies_$$.txt"

    log_info "=========================================="
    log_info "Testing installation with ID type: $id_type"
    log_info "=========================================="

    # Get token
    local token=$(get_token)
    log_info "Using token: ${token:0:8}..."

    # Step 0: Access with token
    log_info "Step 0: Authenticating..."
    local response=$(curl -s -c "$cookie_file" -b "$cookie_file" -X POST \
        -d "token=$token" \
        -w "\n%{http_code}" \
        "$BASE_URL/")
    local http_code=$(echo "$response" | tail -1)
    assert_http_code "$http_code" "200" "Token authentication"

    # Step 1: Welcome (execute)
    log_info "Step 1: Welcome..."
    response=$(curl -s -c "$cookie_file" -b "$cookie_file" -X POST \
        -H "Content-Type: application/json" \
        -d '{"action":"execute_step","step":"welcome","data":{"language":"es"}}' \
        -w "\n%{http_code}" \
        "$BASE_URL/api.php")
    http_code=$(echo "$response" | tail -1)
    assert_http_code "$http_code" "200" "Welcome step"
    assert_contains "$response" '"success":true' "Welcome returns success"

    # Step 2: Requirements (execute)
    log_info "Step 2: Requirements..."
    response=$(curl -s -c "$cookie_file" -b "$cookie_file" -X POST \
        -H "Content-Type: application/json" \
        -d '{"action":"execute_step","step":"requirements","data":{}}' \
        -w "\n%{http_code}" \
        "$BASE_URL/api.php")
    http_code=$(echo "$response" | tail -1)
    assert_http_code "$http_code" "200" "Requirements step"

    # Step 3: Dependencies (execute)
    log_info "Step 3: Dependencies..."
    response=$(curl -s -c "$cookie_file" -b "$cookie_file" -X POST \
        -H "Content-Type: application/json" \
        -d '{"action":"execute_step","step":"dependencies","data":{}}' \
        -w "\n%{http_code}" \
        "$BASE_URL/api.php")
    http_code=$(echo "$response" | tail -1)
    assert_http_code "$http_code" "200" "Dependencies step"

    # Step 4: App Key (execute)
    log_info "Step 4: App Key..."
    response=$(curl -s -c "$cookie_file" -b "$cookie_file" -X POST \
        -H "Content-Type: application/json" \
        -d '{"action":"execute_step","step":"appkey","data":{}}' \
        -w "\n%{http_code}" \
        "$BASE_URL/api.php")
    http_code=$(echo "$response" | tail -1)
    assert_http_code "$http_code" "200" "App Key step"

    # Step 5: Database (execute with id_type)
    log_info "Step 5: Database with id_type=$id_type..."
    response=$(curl -s -c "$cookie_file" -b "$cookie_file" -X POST \
        -H "Content-Type: application/json" \
        -d "{\"action\":\"execute_step\",\"step\":\"database\",\"data\":{\"db_type\":\"docker\",\"id_type\":\"$id_type\"}}" \
        -w "\n%{http_code}" \
        "$BASE_URL/api.php")
    http_code=$(echo "$response" | tail -1)
    assert_http_code "$http_code" "200" "Database step"
    assert_contains "$response" '"success":true' "Database returns success"

    # Step 6: Migrations (execute)
    log_info "Step 6: Migrations..."
    response=$(curl -s -c "$cookie_file" -b "$cookie_file" -X POST \
        -H "Content-Type: application/json" \
        -d '{"action":"execute_step","step":"migrations","data":{"fresh":true}}' \
        -w "\n%{http_code}" \
        "$BASE_URL/api.php")
    http_code=$(echo "$response" | tail -1)
    assert_http_code "$http_code" "200" "Migrations step"

    # Verify essential seeders ran
    log_info "Verifying essential data was seeded..."
    local legal_count=$(docker exec docker-mysql-1 mysql -u larafactu -plarafactu larafactu_test -N -e "SELECT COUNT(*) FROM legal_entity_types" 2>/dev/null)
    if [ "$legal_count" -gt 0 ]; then
        log_test "PASS: LegalEntityTypes seeded ($legal_count records)"
        ((TESTS_PASSED++))
    else
        log_error "FAIL: LegalEntityTypes not seeded"
        ((TESTS_FAILED++))
    fi

    local tax_count=$(docker exec docker-mysql-1 mysql -u larafactu -plarafactu larafactu_test -N -e "SELECT COUNT(*) FROM tax_rates" 2>/dev/null)
    if [ "$tax_count" -gt 0 ]; then
        log_test "PASS: TaxRates seeded ($tax_count records)"
        ((TESTS_PASSED++))
    else
        log_error "FAIL: TaxRates not seeded"
        ((TESTS_FAILED++))
    fi

    # Step 7: Company (execute)
    log_info "Step 7: Company..."
    response=$(curl -s -c "$cookie_file" -b "$cookie_file" -X POST \
        -H "Content-Type: application/json" \
        -d '{"action":"execute_step","step":"company","data":{"business_name":"Test Company SL","tax_id":"B12345678","legal_entity_type":"LIMITED_COMPANY","address":"Test Street 123","zip_code":"28001","city":"Madrid","state":"Madrid","country_code":"ES","currency":"EUR"}}' \
        -w "\n%{http_code}" \
        "$BASE_URL/api.php")
    http_code=$(echo "$response" | tail -1)
    assert_http_code "$http_code" "200" "Company step"

    # Step 8: Verifactu (execute - disabled mode)
    log_info "Step 8: Verifactu..."
    response=$(curl -s -c "$cookie_file" -b "$cookie_file" -X POST \
        -H "Content-Type: application/json" \
        -d '{"action":"execute_step","step":"verifactu","data":{"mode":"disabled"}}' \
        -w "\n%{http_code}" \
        "$BASE_URL/api.php")
    http_code=$(echo "$response" | tail -1)
    assert_http_code "$http_code" "200" "Verifactu step"

    # Step 9: Admin (execute)
    log_info "Step 9: Admin..."
    response=$(curl -s -c "$cookie_file" -b "$cookie_file" -X POST \
        -H "Content-Type: application/json" \
        -d '{"action":"execute_step","step":"admin","data":{"name":"Admin Test","email":"admin@test.com","password":"Test1234!","password_confirm":"Test1234!"}}' \
        -w "\n%{http_code}" \
        "$BASE_URL/api.php")
    http_code=$(echo "$response" | tail -1)
    assert_http_code "$http_code" "200" "Admin step"

    # Verify user ID type
    log_info "Verifying user ID type..."
    local user_id=$(docker exec docker-mysql-1 mysql -u larafactu -plarafactu larafactu_test -N -e "SELECT id FROM users LIMIT 1" 2>/dev/null)

    if [ "$id_type" = "uuid" ]; then
        # UUID should contain dashes
        if echo "$user_id" | grep -q "-"; then
            log_test "PASS: User ID is UUID format ($user_id)"
            ((TESTS_PASSED++))
        else
            log_error "FAIL: Expected UUID format, got: $user_id"
            ((TESTS_FAILED++))
        fi
    else
        # Integer should be numeric
        if [[ "$user_id" =~ ^[0-9]+$ ]]; then
            log_test "PASS: User ID is Integer format ($user_id)"
            ((TESTS_PASSED++))
        else
            log_error "FAIL: Expected Integer format, got: $user_id"
            ((TESTS_FAILED++))
        fi
    fi

    # Step 10: Finalize
    log_info "Step 10: Finalize..."
    response=$(curl -s -c "$cookie_file" -b "$cookie_file" -X POST \
        -H "Content-Type: application/json" \
        -d '{"action":"finalize","data":{}}' \
        -w "\n%{http_code}" \
        "$BASE_URL/api.php")
    http_code=$(echo "$response" | tail -1)
    assert_http_code "$http_code" "200" "Finalize step"

    # Cleanup
    rm -f "$cookie_file"

    log_info "=========================================="
    log_info "Completed $id_type scenario"
    log_info "=========================================="
}

# Print summary
print_summary() {
    echo ""
    echo "=========================================="
    echo "TEST SUMMARY"
    echo "=========================================="
    echo -e "Passed: ${GREEN}$TESTS_PASSED${NC}"
    echo -e "Failed: ${RED}$TESTS_FAILED${NC}"
    echo "=========================================="

    if [ $TESTS_FAILED -gt 0 ]; then
        exit 1
    fi
}

# Main execution
main() {
    local run_uuid=true
    local run_integer=true

    # Parse arguments
    case "${1:-}" in
        --uuid-only)
            run_integer=false
            ;;
        --integer-only)
            run_uuid=false
            ;;
    esac

    # Setup trap for cleanup
    trap cleanup EXIT

    # Start Docker
    start_docker

    # Run UUID scenario
    if [ "$run_uuid" = true ]; then
        clean_state
        run_installer_test "uuid"
    fi

    # Run Integer scenario
    if [ "$run_integer" = true ]; then
        clean_state
        run_installer_test "integer"
    fi

    # Print results
    print_summary
}

# Run main
main "$@"
