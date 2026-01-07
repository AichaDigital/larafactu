<?php

declare(strict_types=1);

use App\Enums\UserType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * ADR-004: User Type Authorization Tests
 *
 * Tests for the new user_type-based authorization system.
 */
describe('User Type Enum', function () {
    it('has correct values for each type', function () {
        expect(UserType::STAFF->value)->toBe(0)
            ->and(UserType::CUSTOMER->value)->toBe(1)
            ->and(UserType::DELEGATE->value)->toBe(2);
    });

    it('provides labels in Spanish', function () {
        expect(UserType::STAFF->label())->toBe('Empleado')
            ->and(UserType::CUSTOMER->label())->toBe('Cliente')
            ->and(UserType::DELEGATE->label())->toBe('Delegado');
    });

    it('identifies staff correctly', function () {
        expect(UserType::STAFF->isStaff())->toBeTrue()
            ->and(UserType::CUSTOMER->isStaff())->toBeFalse()
            ->and(UserType::DELEGATE->isStaff())->toBeFalse();
    });

    it('identifies customer correctly', function () {
        expect(UserType::CUSTOMER->isCustomer())->toBeTrue()
            ->and(UserType::STAFF->isCustomer())->toBeFalse()
            ->and(UserType::DELEGATE->isCustomer())->toBeFalse();
    });

    it('identifies delegate correctly', function () {
        expect(UserType::DELEGATE->isDelegate())->toBeTrue()
            ->and(UserType::STAFF->isDelegate())->toBeFalse()
            ->and(UserType::CUSTOMER->isDelegate())->toBeFalse();
    });

    it('only allows staff to access admin', function () {
        expect(UserType::STAFF->canAccessAdmin())->toBeTrue()
            ->and(UserType::CUSTOMER->canAccessAdmin())->toBeFalse()
            ->and(UserType::DELEGATE->canAccessAdmin())->toBeFalse();
    });

    it('only allows customer to create delegates', function () {
        expect(UserType::CUSTOMER->canCreateDelegates())->toBeTrue()
            ->and(UserType::STAFF->canCreateDelegates())->toBeFalse()
            ->and(UserType::DELEGATE->canCreateDelegates())->toBeFalse();
    });
});

describe('User Model User Type', function () {
    it('defaults to CUSTOMER type', function () {
        $user = User::factory()->create();

        expect($user->user_type)->toBe(UserType::CUSTOMER)
            ->and($user->isCustomer())->toBeTrue()
            ->and($user->isStaff())->toBeFalse()
            ->and($user->isDelegate())->toBeFalse();
    });

    it('can create staff user', function () {
        $user = User::factory()->staff()->create();

        expect($user->user_type)->toBe(UserType::STAFF)
            ->and($user->isStaff())->toBeTrue()
            ->and($user->isCustomer())->toBeFalse()
            ->and($user->canAccessAdmin())->toBeTrue();
    });

    it('can create delegate user', function () {
        $user = User::factory()->delegate()->create();

        expect($user->user_type)->toBe(UserType::DELEGATE)
            ->and($user->isDelegate())->toBeTrue()
            ->and($user->isCustomer())->toBeFalse();
    });

    it('can create superadmin user', function () {
        $user = User::factory()->superadmin()->create();

        expect($user->isSuperadmin())->toBeTrue()
            ->and($user->isStaff())->toBeTrue()
            ->and($user->canAccessAdmin())->toBeTrue();
    });
});

describe('User Account Status', function () {
    it('defaults to active account', function () {
        $user = User::factory()->create();

        expect($user->is_active)->toBeTrue()
            ->and($user->suspended_at)->toBeNull()
            ->and($user->isAccountActive())->toBeTrue();
    });

    it('can create inactive user', function () {
        $user = User::factory()->inactive()->create();

        expect($user->is_active)->toBeFalse()
            ->and($user->isAccountActive())->toBeFalse();
    });

    it('can create suspended user', function () {
        $user = User::factory()->suspended()->create();

        expect($user->is_active)->toBeFalse()
            ->and($user->suspended_at)->not->toBeNull()
            ->and($user->isAccountActive())->toBeFalse();
    });

    it('can suspend active user', function () {
        $user = User::factory()->create();
        expect($user->isAccountActive())->toBeTrue();

        $user->suspend();

        expect($user->fresh()->is_active)->toBeFalse()
            ->and($user->fresh()->suspended_at)->not->toBeNull()
            ->and($user->fresh()->isAccountActive())->toBeFalse();
    });

    it('can reactivate suspended user', function () {
        $user = User::factory()->suspended()->create();
        expect($user->isAccountActive())->toBeFalse();

        $user->reactivate();

        expect($user->fresh()->is_active)->toBeTrue()
            ->and($user->fresh()->suspended_at)->toBeNull()
            ->and($user->fresh()->isAccountActive())->toBeTrue();
    });
});

describe('Admin Access Control', function () {
    it('allows staff to access admin via canAccessAdmin()', function () {
        $user = User::factory()->staff()->create();

        expect($user->canAccessAdmin())->toBeTrue();
    });

    it('denies customers admin access via canAccessAdmin()', function () {
        $user = User::factory()->customer()->create();

        expect($user->canAccessAdmin())->toBeFalse();
    });

    it('denies delegates admin access via canAccessAdmin()', function () {
        $user = User::factory()->delegate()->create();

        expect($user->canAccessAdmin())->toBeFalse();
    });

    it('allows superadmin to access admin regardless of type', function () {
        $user = User::factory()->superadmin()->create();

        expect($user->canAccessAdmin())->toBeTrue();
    });

    it('legacy isAdmin() still works for staff', function () {
        $user = User::factory()->staff()->create();

        expect($user->isAdmin())->toBeTrue();
    });

    it('legacy isAdmin() still works for superadmin', function () {
        $user = User::factory()->superadmin()->create();

        expect($user->isAdmin())->toBeTrue();
    });
});
