<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserType;
use App\Models\User;

/**
 * ADR-004: User Authorization Policy
 *
 * Defines who can perform what actions on User models.
 *
 * Permission Matrix:
 * | Action     | Superadmin | Staff | Customer | Delegate |
 * |------------|------------|-------|----------|----------|
 * | viewAny    | ✓          | ✓     | ✗        | ✗        |
 * | view       | ✓          | ✓     | own/del  | own      |
 * | create     | ✓          | ✓     | delegate | ✗        |
 * | update     | ✓          | ✓*    | own/del  | own      |
 * | suspend    | ✓          | ✓     | ✗        | ✗        |
 * | delete     | ✓          | ✓*    | ✗        | ✗        |
 *
 * * Staff cannot modify superadmins unless they are superadmin
 */
class UserPolicy
{
    /**
     * Perform pre-authorization checks.
     *
     * Superadmins bypass all authorization checks.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperadmin()) {
            return true;
        }

        // Account must be active to perform any action
        if (! $user->isAccountActive()) {
            return false;
        }

        return null; // Fall through to specific policy methods
    }

    /**
     * Determine whether the user can view any models.
     *
     * Staff can view all users for administration.
     */
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    /**
     * Determine whether the user can view the model.
     *
     * - Staff can view anyone
     * - Customers can view their own profile and their delegates
     * - Delegates can view their own profile
     */
    public function view(User $user, User $model): bool
    {
        // Staff can view anyone
        if ($user->isStaff()) {
            return true;
        }

        // Users can view themselves
        if ($user->id === $model->id) {
            return true;
        }

        // Customers can view their delegates
        if ($user->isCustomer()) {
            return $model->parent_user_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     *
     * - Staff can create any user type
     * - Customers can create delegates (their own)
     */
    public function create(User $user): bool
    {
        return $user->isStaff() || $user->isCustomer();
    }

    /**
     * Determine whether the user can create a specific user type.
     *
     * @param  UserType  $userType  The type of user being created
     */
    public function createType(User $user, UserType $userType): bool
    {
        // Staff can create any type except superadmin
        if ($user->isStaff()) {
            return true;
        }

        // Customers can only create delegates
        if ($user->isCustomer()) {
            return $userType === UserType::DELEGATE;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * - Staff can update anyone (except superadmin, unless they are superadmin)
     * - Customers can update themselves and their delegates
     * - Delegates can update only themselves
     */
    public function update(User $user, User $model): bool
    {
        // Staff cannot modify superadmins
        if ($user->isStaff() && $model->isSuperadmin()) {
            return false;
        }

        // Staff can update anyone else
        if ($user->isStaff()) {
            return true;
        }

        // Users can update themselves
        if ($user->id === $model->id) {
            return true;
        }

        // Customers can update their delegates
        if ($user->isCustomer()) {
            return $model->parent_user_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can suspend the model.
     *
     * Only staff can suspend accounts.
     */
    public function suspend(User $user, User $model): bool
    {
        // Cannot suspend yourself
        if ($user->id === $model->id) {
            return false;
        }

        // Cannot suspend superadmins
        if ($model->isSuperadmin()) {
            return false;
        }

        return $user->isStaff();
    }

    /**
     * Determine whether the user can reactivate a suspended user.
     *
     * Only staff can reactivate accounts.
     */
    public function reactivate(User $user, User $model): bool
    {
        return $user->isStaff();
    }

    /**
     * Determine whether the user can delete the model.
     *
     * Only staff can delete users.
     */
    public function delete(User $user, User $model): bool
    {
        // Cannot delete yourself
        if ($user->id === $model->id) {
            return false;
        }

        // Cannot delete superadmins
        if ($model->isSuperadmin()) {
            return false;
        }

        return $user->isStaff();
    }

    /**
     * Determine whether the user can restore the model.
     *
     * Only staff can restore deleted users.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->isStaff();
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * Only superadmin can force delete (handled by before()).
     */
    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can impersonate the model.
     *
     * Staff can impersonate customers and delegates (not other staff).
     */
    public function impersonate(User $user, User $model): bool
    {
        // Cannot impersonate yourself
        if ($user->id === $model->id) {
            return false;
        }

        // Cannot impersonate superadmins
        if ($model->isSuperadmin()) {
            return false;
        }

        // Cannot impersonate staff (unless superadmin - handled by before())
        if ($model->isStaff()) {
            return false;
        }

        // Account must be active to impersonate
        if (! $model->isAccountActive()) {
            return false;
        }

        return $user->isStaff();
    }

    /**
     * Determine whether the user can manage the model's delegates.
     *
     * - Customers manage their own delegates
     * - Staff can manage anyone's delegates
     */
    public function manageDelegates(User $user, User $model): bool
    {
        // Staff can manage anyone's delegates
        if ($user->isStaff()) {
            return true;
        }

        // Customers can manage their own delegates
        if ($user->isCustomer() && $user->id === $model->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can promote to superadmin.
     *
     * Only superadmins can promote others (handled by before()).
     */
    public function promoteSuperadmin(User $user, User $model): bool
    {
        return false;
    }
}
