# TODO - Larafactu Project Status

**Last Update**: 2026-01-06
**Current Phase**: Post-refactor Filament → DaisyUI, system adjustments
**Deadline**: ~15 February 2026
**Context**: Larafactu v1.0 - Staging Pre-Production

---

## 🔥 OPERATIONAL STATUS - IMMEDIATE (2026-01-06)

### Recently Completed Work

#### 1. Theme and Preferences System (COMPLETED)

**Problem**: Guest users without cookies redirected to login, preferences not applied correctly

**Solution Implemented**:

- Modified `ApplyUserPreferences` middleware to return `null` when no explicit preferences exist
- Updated `guest.blade.php` with listener for system theme changes (prefers-color-scheme)
- Updated `app.blade.php` with same logic
- Current behavior:
  - Guest without preferences → detects system theme (dark/light)
  - Guest with cookie/session → respects saved preference
  - Authenticated → respects DB preferences (user_preferences table)

**Modified Files**:

- `app/Http/Middleware/ApplyUserPreferences.php`
- `resources/views/components/layouts/guest.blade.php`
- `resources/views/components/layouts/app.blade.php`

#### 2. Laravel Boost - Performance and Security (CRITICAL - COMPLETED)

**Critical Problem**: Pages with 30+ second timeouts, 502 errors, multiple `php artisan boost:mcp` processes blocking requests

**Root Cause**:

- Browser logs watcher generating massive logs (>1MB)
- Multiple boost:mcp processes running simultaneously
- Boost enabled without explicit configuration

**Solution Implemented**:

- Published `config/boost.php` with:
  - `BOOST_ENABLED=true` (development only)
  - `BOOST_BROWSER_LOGS_WATCHER=false` (CRITICAL for performance)
- Added environment variables to `.env` and `.env.example`
- Complete security documentation in README.md (ES + EN)
- Technical documentation in BookStack wiki

**Performance Improved**: 30+ seconds → 0.83 seconds

**Created/Modified Files**:

- `config/boost.php` (published and configured)
- `.env.example`
- `README.md` (security section)

**Documentation**:

- BookStack Wiki: <https://wiki.castris.com/books/laravel/page/laravel-boost-problemas-de-rendimiento-y-seguridad>

#### 3. Critical Config File Error (COMPLETED)

**Problem**: "Target class [env] does not exist" when accessing any page

**Root Cause**: Using `app()->isProduction()` in `config/boost.php`

**Explanation**: Config files load BEFORE the application initializes. CANNOT use helpers like `app()`, `view()`, `config()`, etc. ONLY `env()`.

**Solution**:

```php
// ❌ INCORRECT - Causes fatal error
'enabled' => env('BOOST_ENABLED', ! app()->isProduction()),

// ✅ CORRECT - Use only env()
'enabled' => env('BOOST_ENABLED', env('APP_ENV') !== 'production'),
```

**Modified Files**:

- `config/boost.php`
- Complete cache cleanup: bootstrap/cache/, storage/framework/cache, storage/framework/views
- Herd services restart

**Lesson Learned**: ALWAYS use only `env()` in config files. Documented in wiki.

### Current Issues

#### 1. Failing Browser Test (HIGH PRIORITY)

**Test Failing**: `Tests\Browser\AuthPagesTest > Login Page → it loads the login page successfully`

**Error**: Timeout 5000ms waiting for text "Iniciar sesion" on login page

**Details**:

```
Timeout 5000ms exceeded.
at tests/Browser/AuthPagesTest.php:24
$this->visit(AUTH_BASE_URL.'/login')
    ->waitForText('Iniciar sesion', PAGE_LOAD_TIMEOUT)
    ->assertSee('Iniciar sesion');
```

**Test Status**:

- ✅ 243 tests passing
- ❌ 1 test failing (browser test)
- ⏸️ 19 pending tests
- Total assertions: 557

**Possible Causes**:

1. Timeout too short (5000ms)
2. Login page slow to load (possible relation with themes/preferences)
3. Text "Iniciar sesion" changed or not visible
4. Problem with browser testing setup (Playwright/Dusk)

**Related Files**:

- `tests/Browser/AuthPagesTest.php:24`
- `resources/views/livewire/auth/login.blade.php` (probable location of text)

**Next Steps**:

1. Verify text "Iniciar sesion" exists in login view
2. Increase PAGE_LOAD_TIMEOUT if needed
3. Review browser testing configuration
4. Run test individually for more details: `php vendor/bin/pest tests/Browser/AuthPagesTest.php --filter="Login Page"`

#### 2. Git Status - Uncommitted Files

**Modified**:

- `.env.example` (Boost configuration)
- `README.md` (security warnings)
- `app/Http/Middleware/ApplyUserPreferences.php` (theme logic)
- `database/seeders/DevelopmentSeeder.php`
- `docs/themes/daisyui-themes.md`
- `resources/views/components/layouts/app.blade.php` (themes)
- `resources/views/components/layouts/guest.blade.php` (themes)
- `resources/views/welcome.blade.php`

**Added**:

- `docs/logo/favicon_io.zip`

**Deleted**:

- `storage/framework/cache/data/.gitignore`

**Untracked**:

- `database/seeders/RealisticServicesSeeder.php`

**Next Steps**:

1. Review changes with `git diff`
2. Prepare coherent commit with:
   - Fix: Theme and preferences system for guest/authenticated users
   - Fix: Laravel Boost configuration (performance + security)
   - Fix: Config file error (app() helper)
   - Docs: README update with security warnings
3. Run linters before commit: `composer pint`
4. Run complete tests: `composer test` (resolve browser test first)

### Current Development Phase

**Context**:

We are in post-refactor phase after:

- Abandoning Filament (ADR-005)
- Adopting DaisyUI 5 + TailwindCSS 4
- Implementing customizable theme system
- Resolving critical performance issues (Laravel Boost)

**Next Priority Tasks**:

1. **URGENT**: Resolve failing browser test
2. **HIGH**: Commit completed changes (theme system + boost fixes)
3. **MEDIUM**: Review and update ADR-004 (authorization should be in app, not packages)
4. **MEDIUM**: Continue UI migration from Filament to DaisyUI
5. **LOW**: Remove Filament dependencies from composer.json

### Important Technical Notes

#### Laravel Boost - Reminders

- NEVER enable in production (exposes credentials)
- NEVER enable browser_logs_watcher except for active frontend debugging
- If performance issues, check processes: `ps aux | grep boost`
- Kill orphaned processes: `pkill -f "boost:mcp"`

#### Config Files - Critical Rule

**NEVER use helpers in config/\*.php files**:

- ❌ `app()`, `view()`, `config()`, `route()`, etc.
- ✅ ONLY `env()`

Reason: Config loads before application initializes.

#### Tests

- Package tests: Run IN package directory
- larafactu tests: Run IN larafactu directory
- Always use SQLite for tests (configured in phpunit.xml)
- Browser tests require running server

#### Enums in Database

- FORBIDDEN to use ENUM type in MySQL
- Use PHP Enum + unsignedTinyInteger
- Model cast: `protected $casts = ['field' => MyEnum::class];`

### Useful Commands

```bash
# Lint
composer pint

# Static analysis
composer phpstan

# Tests (without browser)
php vendor/bin/pest --exclude-group=browser

# Complete tests
composer test

# Individual test
php vendor/bin/pest tests/Browser/AuthPagesTest.php --filter="Login Page"

# Coverage
composer test-coverage

# Complete quality (lint + phpstan + tests)
composer quality

# Laravel Boost - Check processes
ps aux | grep boost

# Laravel Boost - Kill processes
pkill -f "boost:mcp"

# Complete cache clear
php artisan cache:clear
php artisan config:clear
php artisan view:clear
rm -rf bootstrap/cache/*.php
```

### Environment for Cursor

**Configured Environment Variables**:

```env
BOOST_ENABLED=true
BOOST_BROWSER_LOGS_WATCHER=false
```

**Relevant Packages**:

- larabill (billing core)
- lara-verifactu (AEAT)
- laratickets (tickets)
- lararoi (VAT/ROI EU)

**Current Stack**:

- Laravel 12
- Livewire 3
- TailwindCSS 4
- DaisyUI 5
- Alpine.js
- Pest (testing)

---

## 📊 STRATEGIC STATUS - ADRs ROADMAP

| ADR | Title | Status | Progress |
|-----|-------|--------|----------|
| [ADR-001](./ADR_001_REFACTORING_FISCAL_SETTINGS.md) | Fiscal Settings Refactoring | ✅ COMPLETED | 95% |
| [ADR-002](./ADR_002_UUID_V7_CONSOLIDATION.md) | UUID v7 String | ✅ COMPLETED | 95% |
| [ADR-003](./ADR_003_USER_CUSTOMER_UNIFICATION.md) | Users/Customers Unification | ✅ COMPLETED | 100% |
| [ADR-004](./adr/) | Authorization System | 🔄 IN REVIEW | - |
| [ADR-005](./adr/) | Filament Deprecation | ✅ COMPLETED | - |

---

## 🎯 Immediate Priorities

### 🔴 Critical - This Week

- [x] **ADR-003**: Phase 1 - UserTaxProfile replaces CustomerFiscalData ✅ 2025-12-16
- [x] **ADR-003**: Phase 2 - Remove customers table, unify in users ✅ 2025-12-16
- [x] **Tests**: Complete suite passing (13/13) ✅ 2025-12-16
- [x] **ADR-001**: Implement automatic fiscal snapshot in Invoice ✅ 2025-12-16
- [ ] **Browser Test**: Fix failing AuthPagesTest (Iniciar sesion timeout) 🆕
- [ ] **Commit**: Theme system + Boost fixes 🆕

### 🟡 High - Next 2 Weeks

- [x] **ADR-001**: Fiscal change management (close previous config) ✅ 2025-12-18 - Implemented in model boot()
- [x] **Tests**: Create tests for fiscal temporality ✅ 2025-12-16
- [x] **ADR-001**: FiscalIntegrityChecker to detect duplicate configs ✅ 2025-12-18
- [x] **Docs**: Update ADR-003 with Phase 2 completed ✅ 2025-12-19
- [ ] **ADR-005**: Continue Filament → DaisyUI migration 🆕
- [ ] **ADR-004**: Review authorization (should be in app, not packages) 🆕

### 🟢 Medium - Next Month

- [x] **ADR-001**: Implement proforma management with fiscal change ✅ 2025-12-18
- [x] **Filament**: UserResource with delegate management ✅ 2025-12-18
  - [x] Form with relationship_type, parent_user_id
  - [x] DelegatedUsersRelationManager
  - [x] TaxProfilesRelationManager
  - [x] Complete ES/EN translations
- [ ] **Docs**: Update API documentation
- [ ] **Seeding**: Create production seeders
- [ ] **Cleanup**: Remove Filament dependencies from composer.json 🆕

---

## 📋 ADR-001: Fiscal Settings Refactoring

### ✅ Completed

- [x] Create `CompanyFiscalConfig` model
- [x] Create `company_fiscal_configs` migration
- [x] Implement temporality (`valid_from`, `valid_until`)
- [x] Create factory and seeder
- [x] Basic CompanyFiscalConfig tests
- [x] **Implement automatic fiscal snapshot in Invoice** ✅ 2025-12-16
  - [x] Capture current `CompanyFiscalConfig` at `invoice_date`
  - [x] Capture current `UserTaxProfile` at `invoice_date`
  - [x] Save immutable snapshots (encrypted)
  - [x] Methods: `generateIssuerSnapshot()`, `generateBillableUserSnapshot()`, `generateFiscalContextSnapshot()`
  - [x] Helpers: `hasEncryptedSnapshots()`, `hasFiscalSnapshots()`, `regenerateEncryptedSnapshots()`
  - [x] Auto-generation in `boot::creating`
- [x] Fiscal change management (in model boot)
  - [x] When creating new config, close previous (`valid_until = today - 1`)
  - [x] `closeActiveConfig()` method in CompanyFiscalConfig
  - [x] `closeActiveForUser()` method in UserTaxProfile

### ✅ Proforma Fiscal Change Handling (2025-12-18)

- [x] FiscalChangeDetector service
  - [x] Detect critical changes (tax_id, country_code, is_eu_vat_registered)
  - [x] Detect warning changes (business_name, address, fiscal_name...)
  - [x] Compare CompanyFiscalConfig at proforma creation vs conversion
  - [x] Compare UserTaxProfile at proforma creation vs conversion
- [x] FiscalConfigChangedException
  - [x] Thrown when critical fiscal changes block conversion
  - [x] Methods: getChanges(), hasCriticalChanges(), getCriticalFields()
- [x] InvoiceService.convertProformaToInvoice integration
  - [x] Options: force, on_changes ('throw'|'warn'|'ignore')
  - [x] Returns Invoice|array{invoice, warnings}
- [x] Tests: 15 new tests covering all fiscal change scenarios

### ⏳ Pending

- [ ] Document CompanyFiscalConfig API
- [x] Edge case tests (multiple active configs - validation) ✅ 2025-12-18
  - [x] FiscalIntegrityChecker service (32 tests)
  - [x] FiscalIntegrityBanner widget in larabill-filament
  - [x] FiscalIntegrityAlert notification
- [ ] Filament Resource for historical config management

---

## 📋 ADR-002: UUID v7 String

### ✅ Completed

- [x] Migrate from UUID binary(16) to UUID string char(36)
- [x] Update `users` table migration
- [x] Update `sessions` table migration (FK constraint)
- [x] Remove `dyrynda/laravel-model-uuid` dependency
- [x] Update models to use `Str::orderedUuid()`
- [x] Validate compatibility with FilamentPHP v4
- [x] User model with UUID v7
- [x] Invoice model with UUID v7

### ⏳ Pending (lower priority)

- [ ] Document UUID strategy in README
- [ ] UUID v7 performance tests
- [ ] Validate chronological ordering in queries

---

## 📋 ADR-003: Users/Customers Unification

### ✅ Phase 1 Completed (2025-12-16)

- [x] Create `UserTaxProfile` model in larabill
- [x] Create `user_tax_profiles` migration
- [x] Remove `CustomerFiscalData` model
- [x] Remove `CustomerFiscalDataFactory` factory
- [x] Update `Invoice` model to use `user_tax_profile_id`
- [x] Update `InvoiceService` to use `UserTaxProfile`
- [x] Update `VatVerification` relation → `userTaxProfiles()`
- [x] Update `ModelMappingService` → `user_tax_profile`
- [x] Fix `LegalEntityTypesSeeder` (JSON translatable)
- [x] Invoice tests passing

### ✅ Phase 2 Completed (2025-12-16)

- [x] Create `add_billing_fields_to_users_table` migration
  - [x] `parent_user_id` (UUID, self-reference)
  - [x] `relationship_type` (unsignedTinyInteger → enum)
  - [x] `display_name` (nullable)
  - [x] `legal_entity_type_code` (FK)
- [x] Update User model with relations:
  - [x] `parentUser()` - Parent user (if delegated)
  - [x] `delegatedUsers()` - Delegated users (children)
  - [x] `legalEntityType()` - Legal entity type
  - [x] `taxProfiles()` - Fiscal history
  - [x] `currentTaxProfile()` - Active fiscal profile
- [x] Helper methods: `isDirect()`, `isDelegated()`, `billableName()`, `hasDelegatedUsers()`
- [x] Update UserFactory with states `delegatedOf()`, `direct()`, `withDisplayName()`
- [x] Modify Invoice migration: `customer_id` → `billable_user_id`
- [x] Update Invoice model: `customer()` → `billableUser()`
- [x] Refactor InvoiceService without Customer
- [x] Update InvoiceFactory
- [x] Update InvoiceResource (Filament)
- [x] Remove Customer model, factory, resource, migration
- [x] Update LegalEntityType: `customers()` → `users()`
- [x] Update ModelMappingService: remove 'customer'
- [x] Update LarabillFilamentPlugin: remove CustomerResource
- [x] Update SpanishB2CInvoiceTest tests
- [x] Validate with `larafactu:install --local --fresh`
- [x] Tests passing (11/11)

### Removed Files (Phase 2)

```
packages/aichadigital/larabill/src/Models/Customer.php
packages/aichadigital/larabill/src/Database/Factories/CustomerFactory.php
packages/aichadigital/larabill/database/migrations/2025_01_25_000004_create_customers_table.php
packages/aichadigital/larabill-filament/src/Resources/CustomerResource.php
packages/aichadigital/larabill-filament/src/Resources/CustomerResource/
```

---

## 🧪 Testing

### ✅ Passing Tests (243/244)

- [x] AdminAccessTest (8/8)
- [x] SpanishB2CInvoiceTest (5/5) - Updated for ADR-001 + ADR-003
  - [x] Create invoice for spanish B2C customer
  - [x] Calculates correct VAT for multiple items
  - [x] Validates Spanish DNI format
  - [x] **Generates encrypted fiscal snapshots on creation (ADR-001)** ✅ 2025-12-16
  - [x] **Creates fiscal snapshots with temporal validity (ADR-001)** ✅ 2025-12-16
- [ ] AuthPagesTest (0/1) - **FAILING** 🆕 2026-01-06
  - [ ] Login page timeout waiting for "Iniciar sesion" text

### 🚧 Pending Tests

- [x] CompanyFiscalConfig temporality - Covered in existing tests
- [x] Invoice fiscal snapshot ✅ 2025-12-16
- [x] FiscalIntegrityChecker (32 tests) ✅ 2025-12-18
- [x] FiscalChangeDetector (15 tests) ✅ 2025-12-18
- [ ] User relations (parent/delegated) - additional tests
- [ ] UserTaxProfile historical - edge case tests
- [ ] Edge cases:
  - [ ] Fiscal change during billing period
  - [x] Multiple active configs (validation) ✅ 2025-12-18
  - [x] Proformas with fiscal change ✅ 2025-12-18

---

## 📦 Packages (larabill)

### Current Status

- **Version**: dev-main
- **Tests**: Passing in larafactu (243/244) - 1 browser test failing 🆕
- **ADR-003**: Phase 1 + Phase 2 completed
- **ADR-001**: Automatic fiscal snapshot implemented

### Next Steps

1. [ ] Create tag `v0.6.0-alpha` (ADR-003 completed)
2. [ ] Update CHANGELOG.md
3. [ ] Document breaking changes (Customer → User)
4. [ ] Prepare v1.0.0 for February 2026

---

## 🎯 Roadmap v1.0.0 (15 Feb 2026)

### December 2025

- [x] ADR-003: CustomerFiscalData → UserTaxProfile ✅
- [x] ADR-003: Remove customers table ✅
- [x] ADR-001: Fiscal snapshot in Invoice ✅ 2025-12-16
- [x] Additional integration tests (13/13) ✅ 2025-12-16

### January 2026

- [x] ADR-001: Complete fiscal change management ✅ 2025-12-18
- [x] ADR-005: Filament deprecation decision ✅ 2026-01-06 🆕
- [x] Theme and preferences system ✅ 2026-01-06 🆕
- [x] Laravel Boost performance fix ✅ 2026-01-06 🆕
- [ ] Filament Resources updated (UserResource delegates) - **IN PROGRESS** 🆕
- [ ] Complete documentation
- [ ] Production seeders

### February 2026

- [ ] Exhaustive testing
- [ ] Final code review
- [ ] Production deployment
- [ ] **v1.0.0 Release**

---

## 📝 Notes

### Technical Decisions

- **UUID v7 string**: Chosen for FilamentPHP v4 compatibility
- **UserTaxProfile**: Clearer name than CustomerFiscalData
- **Temporality**: `valid_from`/`valid_until` for fiscal immutability
- **JSON translatable**: Spatie for legal entity types
- **billable_user_id**: Replaces customer_id in invoices (ADR-003)
- **UserRelationshipType**: DIRECT (0) / DELEGATED (1) enum
- **DaisyUI 5**: Replaces Filament for UI (ADR-005) 🆕
- **Laravel Boost**: Development only, browser_logs_watcher disabled 🆕

### Final Architecture (ADR-003)

```
┌─────────────────────────────────────────────────────────────────┐
│  users                                                          │
│  - id (UUID v7 string)                                          │
│  - parent_user_id (nullable) → FK self-reference                │
│  - relationship_type (PHP Enum → unsignedTinyInteger)           │
│  - display_name, legal_entity_type_code                         │
│                                                                 │
│  parent_user_id = NULL   → Direct customer of Company           │
│  parent_user_id = X      → Customer of User X (delegated)       │
└─────────────────────────────────────────────────────────────────┘
                        │
                        │ 1:N
                        ▼
┌─────────────────────────────────────────────────────────────────┐
│  user_tax_profiles (fiscal history)                             │
│  - user_id → FK users.id                                        │
│  - fiscal_name, tax_id, address, country_code...                │
│  - valid_from / valid_until (temporality)                       │
└─────────────────────────────────────────────────────────────────┘
                        │
                        │ N:1
                        ▼
┌─────────────────────────────────────────────────────────────────┐
│  invoices                                                       │
│  - user_id → FK users.id (owner/issuer)                         │
│  - billable_user_id → FK users.id (user being billed)           │
│  - user_tax_profile_id → FK user_tax_profiles.id (snapshot)     │
└─────────────────────────────────────────────────────────────────┘
```

### Identified Risks

1. ~~**Customer → users migration**~~: ✅ Completed (no legacy data)
2. ~~**Fiscal snapshot**~~: ✅ Implemented - immutable and encrypted (AES-256-CBC)
3. **Tests**: Temporal edge case coverage critical
4. **Performance**: Validate with 100k+ invoices
5. **Browser Tests**: Current timeout issue with Playwright/Dusk 🆕

### References

- [Laravel 12 Documentation](https://laravel.com/docs/12.x)
- [FilamentPHP v4](https://filamentphp.com/docs/4.x) - Being deprecated
- [DaisyUI v5](https://daisyui.com) - New UI framework 🆕
- [Spatie Translatable](https://github.com/spatie/laravel-translatable)
- ADRs: `docs/adr/`
- Schema Requirements: `packages/aichadigital/larabill/SCHEMA_REQUIREMENTS.md`
- DaisyUI Themes: `docs/themes/daisyui-themes.md` 🆕
- Nexus Template: `docs/themes/nexus/` 🆕
- BookStack Wiki: <https://wiki.castris.com> 🆕

---

**Maintained by**: @abkrim
**Last Review**: 2026-01-06
**Next Review**: 2026-01-13
