<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerGates();
    }

    /**
     * Register authorization gates.
     *
     * ADR-004: Global authorization gates for common checks.
     */
    protected function registerGates(): void
    {
        // Gate: Can access admin panel
        Gate::define('access-admin', function (User $user): bool {
            return $user->canAccessAdmin();
        });

        // Gate: Can impersonate users (staff only)
        Gate::define('impersonate-users', function (User $user): bool {
            if (! $user->isAccountActive()) {
                return false;
            }

            return $user->isSuperadmin() || $user->isStaff();
        });

        // Gate: Can manage own delegates (customers only)
        Gate::define('manage-delegates', function (User $user): bool {
            if (! $user->isAccountActive()) {
                return false;
            }

            return $user->isSuperadmin() || $user->isStaff() || $user->isCustomer();
        });

        // Gate: Can view invoices (all active users)
        Gate::define('view-invoices', function (User $user): bool {
            return $user->isAccountActive();
        });

        // Gate: Can manage company settings (staff/superadmin only)
        Gate::define('manage-company-settings', function (User $user): bool {
            if (! $user->isAccountActive()) {
                return false;
            }

            return $user->isSuperadmin() || $user->isStaff();
        });
    }
}
