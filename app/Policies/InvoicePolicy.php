<?php

declare(strict_types=1);

namespace App\Policies;

use AichaDigital\Larabill\Models\Invoice;
use App\Models\User;

/**
 * Authorization policy for Invoice model.
 *
 * @see ADR-004 for authorization architecture
 */
class InvoicePolicy
{
    /**
     * Determine whether the user can view any invoices.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view invoice list (filtered by their access)
        return true;
    }

    /**
     * Determine whether the user can view the invoice.
     */
    public function view(User $user, Invoice $invoice): bool
    {
        // Admin can view any invoice
        if ($user->isAdmin()) {
            return true;
        }

        // User can view their own invoices
        return $this->isOwner($user, $invoice);
    }

    /**
     * Determine whether the user can create invoices.
     */
    public function create(User $user): bool
    {
        // Only admin can create invoices
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the invoice.
     */
    public function update(User $user, Invoice $invoice): bool
    {
        // Only admin can update invoices
        if (! $user->isAdmin()) {
            return false;
        }

        // Can only update draft invoices
        return $invoice->isDraft();
    }

    /**
     * Determine whether the user can delete the invoice.
     */
    public function delete(User $user, Invoice $invoice): bool
    {
        // Only admin can delete invoices
        if (! $user->isAdmin()) {
            return false;
        }

        // Can only delete draft invoices
        return $invoice->isDraft();
    }

    /**
     * Determine whether the user can restore the invoice.
     */
    public function restore(User $user, Invoice $invoice): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the invoice.
     */
    public function forceDelete(User $user, Invoice $invoice): bool
    {
        // Force delete not allowed for invoices (fiscal integrity)
        return false;
    }

    /**
     * Determine whether the user can download the invoice PDF.
     */
    public function download(User $user, Invoice $invoice): bool
    {
        return $this->view($user, $invoice);
    }

    /**
     * Determine whether the user can send the invoice by email.
     */
    public function send(User $user, Invoice $invoice): bool
    {
        // Only admin can send invoices
        if (! $user->isAdmin()) {
            return false;
        }

        // Can only send confirmed invoices
        return ! $invoice->isDraft();
    }

    /**
     * Check if user owns the invoice (is the billable user or has access to it).
     */
    protected function isOwner(User $user, Invoice $invoice): bool
    {
        // Check if user is the billable user
        if ($invoice->billable_user_id === $user->id) {
            return true;
        }

        // Check if user's current tax profile matches the invoice's tax profile
        if ($user->current_tax_profile_id && $invoice->user_tax_profile_id) {
            return $user->current_tax_profile_id === $invoice->user_tax_profile_id;
        }

        return false;
    }
}
