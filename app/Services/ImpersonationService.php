<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Session;

/**
 * Service for managing user impersonation.
 *
 * Allows admins to view the application as if they were a specific customer.
 * The original admin session is preserved to allow returning to admin view.
 *
 * @see ADR-004 Authorization System
 * @see ADR-006 Consolidated State
 */
class ImpersonationService
{
    private const SESSION_IMPERSONATING_KEY = 'impersonating_user_id';

    private const SESSION_ORIGINAL_KEY = 'original_user_id';

    /**
     * Start impersonating a user.
     *
     * @param  User  $target  The user to impersonate
     * @return bool True if impersonation started successfully
     */
    public function start(User $target): bool
    {
        $currentUser = auth()->user();

        if (! $currentUser) {
            return false;
        }

        // Check if allowed to impersonate
        if (! Gate::allows('impersonate', $target)) {
            return false;
        }

        // Don't allow nested impersonation
        if ($this->isImpersonating()) {
            return false;
        }

        // Store original user and target in session
        Session::put(self::SESSION_ORIGINAL_KEY, $currentUser->id);
        Session::put(self::SESSION_IMPERSONATING_KEY, $target->id);

        return true;
    }

    /**
     * Stop impersonating and return to original user.
     */
    public function stop(): void
    {
        Session::forget(self::SESSION_IMPERSONATING_KEY);
        Session::forget(self::SESSION_ORIGINAL_KEY);
    }

    /**
     * Check if currently impersonating a user.
     */
    public function isImpersonating(): bool
    {
        return Session::has(self::SESSION_IMPERSONATING_KEY);
    }

    /**
     * Get the user being impersonated.
     */
    public function getImpersonatedUser(): ?User
    {
        $userId = Session::get(self::SESSION_IMPERSONATING_KEY);

        if (! $userId) {
            return null;
        }

        return User::find($userId);
    }

    /**
     * Get the original admin user who started impersonation.
     */
    public function getOriginalUser(): ?User
    {
        $userId = Session::get(self::SESSION_ORIGINAL_KEY);

        if (! $userId) {
            return null;
        }

        return User::find($userId);
    }

    /**
     * Get the ID of the user being impersonated.
     */
    public function getImpersonatedUserId(): ?string
    {
        return Session::get(self::SESSION_IMPERSONATING_KEY);
    }

    /**
     * Get the ID of the original admin user.
     */
    public function getOriginalUserId(): ?string
    {
        return Session::get(self::SESSION_ORIGINAL_KEY);
    }
}
