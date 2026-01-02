<?php

declare(strict_types=1);

namespace App\Policies;

use AichaDigital\Larabill\Models\UserTaxProfile;
use App\Models\User;

/**
 * Authorization policy for UserTaxProfile model (customers).
 *
 * @see ADR-004 for authorization architecture
 */
class UserTaxProfilePolicy
{
    /**
     * Determine whether the user can view any tax profiles.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view customer list (filtered by their access)
        return true;
    }

    /**
     * Determine whether the user can view the tax profile.
     */
    public function view(User $user, UserTaxProfile $profile): bool
    {
        // Admin can view any profile
        if ($user->isAdmin()) {
            return true;
        }

        // User can view profiles they own
        if ($this->isOwner($user, $profile)) {
            return true;
        }

        // User can view their current profile
        return $user->current_tax_profile_id === $profile->id;
    }

    /**
     * Determine whether the user can create tax profiles.
     */
    public function create(User $user): bool
    {
        // Admin can create profiles
        if ($user->isAdmin()) {
            return true;
        }

        // Customers can create their own profiles
        return true;
    }

    /**
     * Determine whether the user can update the tax profile.
     */
    public function update(User $user, UserTaxProfile $profile): bool
    {
        // Admin can update any profile
        if ($user->isAdmin()) {
            return true;
        }

        // Only owner can update their profile
        return $this->isOwner($user, $profile);
    }

    /**
     * Determine whether the user can delete the tax profile.
     */
    public function delete(User $user, UserTaxProfile $profile): bool
    {
        // Only admin can delete profiles
        if (! $user->isAdmin()) {
            return false;
        }

        // Cannot delete profile that is in use
        if ($profile->linkedUsers()->exists()) {
            return false;
        }

        // Cannot delete profile with invoices
        if ($profile->invoices()->exists()) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can restore the tax profile.
     */
    public function restore(User $user, UserTaxProfile $profile): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the tax profile.
     */
    public function forceDelete(User $user, UserTaxProfile $profile): bool
    {
        // Force delete not allowed (fiscal integrity)
        return false;
    }

    /**
     * Check if user owns the tax profile.
     */
    protected function isOwner(User $user, UserTaxProfile $profile): bool
    {
        return $profile->owner_user_id === $user->id;
    }
}
