<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * User types for authorization system.
 *
 * @see ADR-004 for authorization architecture
 */
enum UserType: int
{
    case STAFF = 0;      // Employee of the company
    case CUSTOMER = 1;   // Direct customer
    case DELEGATE = 2;   // Delegate of a customer

    public function label(): string
    {
        return match ($this) {
            self::STAFF => 'Empleado',
            self::CUSTOMER => 'Cliente',
            self::DELEGATE => 'Delegado',
        };
    }

    public function isStaff(): bool
    {
        return $this === self::STAFF;
    }

    public function isCustomer(): bool
    {
        return $this === self::CUSTOMER;
    }

    public function isDelegate(): bool
    {
        return $this === self::DELEGATE;
    }

    /**
     * Check if user type can access admin panel.
     */
    public function canAccessAdmin(): bool
    {
        return $this === self::STAFF;
    }

    /**
     * Check if user type can create delegates.
     */
    public function canCreateDelegates(): bool
    {
        return $this === self::CUSTOMER;
    }
}
