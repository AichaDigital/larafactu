<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\UserCustomerAccess;

/**
 * Policy for managing delegate access to customer accounts.
 *
 * Authorization rules:
 * - Admin: Full access to all records
 * - Customer: Can manage delegates for their own account
 * - Delegate with can_manage_delegates: Can manage other delegates for that customer
 * - Delegate: Can view own access and resign (delete own access)
 *
 * @see ADR-004 Authorization System
 * @see ADR-006 Consolidated State
 */
class UserCustomerAccessPolicy
{
    /**
     * Determine if user can view any access records.
     * Everyone can view the list (filtered by their permissions in controller).
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine if user can view a specific access record.
     */
    public function view(User $user, UserCustomerAccess $access): bool
    {
        // Admin can view all
        if ($user->isAdmin()) {
            return true;
        }

        // Customer can view access to their account
        if ($access->customer_user_id === $user->id) {
            return true;
        }

        // Delegate can view their own access record
        if ($access->user_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine if user can create access for a customer.
     *
     * @param  User  $user  The user attempting to create
     * @param  User  $customer  The customer account to grant access to
     */
    public function create(User $user, User $customer): bool
    {
        // Admin can create for anyone
        if ($user->isAdmin()) {
            return true;
        }

        // Customer can create delegates for their own account
        if ($user->id === $customer->id) {
            return true;
        }

        // Delegate with can_manage_delegates can create sub-delegates
        $delegateAccess = $user->getCustomerAccess($customer);
        if ($delegateAccess && $delegateAccess->canManageDelegates()) {
            return true;
        }

        return false;
    }

    /**
     * Determine if user can update an access record.
     */
    public function update(User $user, UserCustomerAccess $access): bool
    {
        // Admin can update all
        if ($user->isAdmin()) {
            return true;
        }

        // Customer can update access to their account
        if ($access->customer_user_id === $user->id) {
            return true;
        }

        // Delegate cannot update their own access
        if ($access->user_id === $user->id) {
            return false;
        }

        // Delegate with can_manage_delegates can update other delegates
        $delegateAccess = $user->getCustomerAccess($access->customer);
        if ($delegateAccess && $delegateAccess->canManageDelegates()) {
            return true;
        }

        return false;
    }

    /**
     * Determine if user can delete an access record.
     */
    public function delete(User $user, UserCustomerAccess $access): bool
    {
        // Admin can delete all
        if ($user->isAdmin()) {
            return true;
        }

        // Customer can revoke access to their account
        if ($access->customer_user_id === $user->id) {
            return true;
        }

        // Delegate can resign (delete their own access)
        if ($access->user_id === $user->id) {
            return true;
        }

        // Delegate with can_manage_delegates can revoke other delegates
        $delegateAccess = $user->getCustomerAccess($access->customer);
        if ($delegateAccess && $delegateAccess->canManageDelegates()) {
            return true;
        }

        return false;
    }

    // ========================================
    // ACTING ON BEHALF METHODS
    // ========================================

    /**
     * Determine if user can view invoices for a customer.
     */
    public function viewInvoicesFor(User $user, User $customer): bool
    {
        // Customer can always view own invoices
        if ($user->id === $customer->id) {
            return true;
        }

        // Admin can view any invoices
        if ($user->isAdmin()) {
            return true;
        }

        // Delegate needs active access with can_view_invoices
        $access = $user->getCustomerAccess($customer);

        return $access !== null && $access->canViewInvoices();
    }

    /**
     * Determine if user can view services for a customer.
     */
    public function viewServicesFor(User $user, User $customer): bool
    {
        // Customer can always view own services
        if ($user->id === $customer->id) {
            return true;
        }

        // Admin can view any services
        if ($user->isAdmin()) {
            return true;
        }

        // Delegate needs active access with can_view_services
        $access = $user->getCustomerAccess($customer);

        return $access !== null && $access->canViewServices();
    }

    /**
     * Determine if user can manage tickets for a customer.
     */
    public function manageTicketsFor(User $user, User $customer): bool
    {
        // Customer can always manage own tickets
        if ($user->id === $customer->id) {
            return true;
        }

        // Admin can manage any tickets
        if ($user->isAdmin()) {
            return true;
        }

        // Delegate needs active access with can_manage_tickets
        $access = $user->getCustomerAccess($customer);

        return $access !== null && $access->canManageTickets();
    }

    /**
     * Determine if user can manage delegates for a customer.
     */
    public function manageDelegatesFor(User $user, User $customer): bool
    {
        // Customer can always manage own delegates
        if ($user->id === $customer->id) {
            return true;
        }

        // Admin can manage any delegates
        if ($user->isAdmin()) {
            return true;
        }

        // Delegate needs active access with can_manage_delegates
        $access = $user->getCustomerAccess($customer);

        return $access !== null && $access->canManageDelegates();
    }
}
