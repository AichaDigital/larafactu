# Larafactu - Project Context for Claude Code

> **Read this file first** to understand the project structure and conventions.

## 🎯 Project Identity

**Larafactu** is a staging/testing environment for billing packages. It's NOT a production app - it's a real-world test bed for package development.

### Critical Information

| Item | Value |
|------|-------|
| **Deadline** | December 15, 2025 (v1.0 stable) |
| **Target** | Hosting companies, Spain/EU, WHMCS migration |
| **PHP** | ^8.3 (Laravel 12 standard) |
| **Laravel** | 12.x |
| **Filament** | 4.x |
| **Database** | MySQL (local: Laravel Herd) |

### Packages Under Development

All packages use **path repositories with symlinks** for local development:

```
packages/aichadigital/
├── larabill/        # Core billing (dev-main)
├── lararoi/         # EU VAT/ROI (dev-main)
├── lara-verifactu/  # Spain AEAT (dev-main)
└── laratickets/     # Support tickets (dev-main)
```

**Source location**: `/Users/abkrim/development/packages/aichadigital/`

## 🔧 Technical Conventions

### UUID Strategy - UUID v7 String (Opinionated Choice)

**IMPORTANT**: Larafactu uses **UUID v7 STRING** (char 36) for compatibility with FilamentPHP v4.

The packages (larabill, etc.) are **agnostic** - they support int, uuid string, ulid.
Larafactu **chooses** `uuid` via `LARABILL_USER_ID_TYPE=uuid` in `.env`.

> **Note**: UUID binary was removed due to incompatibility with FilamentPHP v4 + Livewire.
> See `docs/ADR_002_UUID_V7_CONSOLIDATION.md` for details.

```php
// Model with UUID (agnostic trait)
use AichaDigital\Larabill\Concerns\HasUuid;

class User extends Authenticatable
{
    use HasUuid;
    // Trait auto-configures $incrementing = false, $keyType = 'string'
    // Uses Laravel 12 native Str::orderedUuid() for UUID v7
}

// Migration
$table->uuid('id')->primary();
$table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
```

**UUID Models**: User, Invoice, Ticket
**Integer Models**: TaxRate, CompanyFiscalConfig, CustomerFiscalData, UnitMeasure

### Monetary Values - Base 100

**NEVER use float/decimal for money**. Always integers in base 100:

- €12.34 → `1234`
- 21.5% IVA → `2150`
- €0.99 → `99`

Package: `aichadigital/lara100` (v1.0 stable)

### Fiscal Architecture (ADR-001)

```
CompanyFiscalConfig    → Company fiscal settings (temporal validity)
CustomerFiscalData     → Customer fiscal data (historical)
Invoice                → Invoice (immutable once issued)
```

Temporal validity uses `valid_from` and `valid_until` (null = current).

## 📁 Key Files Reference

| File | Purpose |
|------|---------|
| `docs/WORKFLOW.md` | Complete multi-package development workflow |
| `docs/ADR_001_REFACTORING_FISCAL_SETTINGS.md` | Fiscal architecture decisions |
| `docs/PRODUCTION_INSTALL.md` | Production deployment guide |
| `.cursor/rules/larafactu.mdc` | Cursor IDE rules (also useful context) |

## 🔄 Development Workflow

### Editing Code

1. Edit in `packages/aichadigital/` (symlinks to source)
2. Changes reflect immediately
3. Test with `php artisan test`

### Committing Changes

**IMPORTANT**: Commits are SEPARATE per repository:

```bash
# 1. Commit in PACKAGE first (if changed)
cd packages/aichadigital/larabill
git add -A && git commit -m "feat: ..." && git push

# 2. Then commit in APP
cd /Users/abkrim/SitesLR12/larafactu
git add -A && git commit -m "feat: ..." && git push
```

### After Package Commits

```bash
composer update aichadigital/* --no-interaction
```

## ⚠️ Known Issues & Gotchas

### Migration Order

Larabill migrations have dependencies. If you see foreign key errors:
- Check migration timestamps (earlier = runs first)
- `users` must exist before `invoices`

### Filament Compatibility

Use correct types for Filament 4:
```php
protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';
```

### Path vs VCS Repositories

Local uses `path` repos. Production needs conversion:
```bash
php scripts/post-deploy.php  # Converts to VCS
```

## 📝 Documentation Rules

### Allowed in docs/

- ✅ `ADR_*.md` - Architectural decisions (permanent)
- ✅ `PRODUCTION_*.md` - Production guides
- ✅ `DEVELOPMENT_*.md` - Development guides
- ✅ `WORKFLOW.md` - This is key

### NOT Allowed (don't commit)

- ❌ `HOTFIX_*.md` - Apply and delete
- ❌ `BUG_*.md` - Resolve and delete
- ❌ `RESUMEN_*.md` - Session summaries
- ❌ `SESION_*.md` - Session notes

## 🚀 Quick Commands

```bash
# Development
php artisan test                    # Run tests
php artisan test --filter=Invoice   # Filter tests
vendor/bin/pint --dirty             # Format code
php artisan dev:seed --fresh        # Fresh DB + dev data

# Pre-push
./scripts/pre-push.sh               # Pint + tests

# Database
php artisan migrate:fresh --seed    # Reset everything
```

## 🌐 Language Convention

- **Code**: English (variables, methods, comments, docblocks)
- **Git commits**: English
- **User docs**: Spanish
- **Communication**: Spanish

## 📞 References

- **Linear**: https://linear.app/aichadigital/
- **GitHub Larafactu**: https://github.com/AichaDigital/larafactu
- **GitHub Larabill**: https://github.com/AichaDigital/larabill

---

**Remember**: This is a staging project. Pragmatism > perfection. Laravel conventions are the guide. Avoid over-engineering.

