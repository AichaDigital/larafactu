<?php

declare(strict_types=1);

namespace App\Providers;

use AichaDigital\Larabill\Models\Article;
use AichaDigital\Larabill\Models\Invoice;
use AichaDigital\Larabill\Models\UserTaxProfile;
use App\Models\User;
use App\Policies\ArticlePolicy;
use App\Policies\InvoicePolicy;
use App\Policies\UserTaxProfilePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

/**
 * Authorization service provider.
 *
 * Registers policies and gates for the application.
 *
 * @see ADR-004 for authorization architecture
 * @see ADR-005 for application-level authorization
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Invoice::class => InvoicePolicy::class,
        UserTaxProfile::class => UserTaxProfilePolicy::class,
        Article::class => ArticlePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
        $this->registerGates();
    }

    /**
     * Register application gates.
     */
    protected function registerGates(): void
    {
        // Admin access gate
        Gate::define('access-admin', function (User $user): bool {
            return $user->isAdmin();
        });

        // Impersonation gate (only admins can impersonate)
        Gate::define('impersonate', function (User $user, User $target): bool {
            // Cannot impersonate yourself
            if ($user->id === $target->id) {
                return false;
            }

            // Only admins can impersonate
            if (! $user->isAdmin()) {
                return false;
            }

            // Cannot impersonate other admins (security)
            if ($target->isAdmin()) {
                return false;
            }

            return true;
        });

        // Manage users gate
        Gate::define('manage-users', function (User $user): bool {
            return $user->isAdmin();
        });

        // View reports gate
        Gate::define('view-reports', function (User $user): bool {
            return $user->isAdmin();
        });

        // Manage settings gate
        Gate::define('manage-settings', function (User $user): bool {
            return $user->isAdmin();
        });
    }
}
