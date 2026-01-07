<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * ADR-004: UserPolicy Authorization Tests
 *
 * Tests for user model authorization policies.
 */
describe('Superadmin Authorization', function () {
    it('superadmin can do everything', function () {
        $superadmin = User::factory()->superadmin()->create();
        $otherSuperadmin = User::factory()->superadmin()->create();
        $staff = User::factory()->staff()->create();
        $customer = User::factory()->customer()->create();

        expect($superadmin->can('viewAny', User::class))->toBeTrue()
            ->and($superadmin->can('view', $staff))->toBeTrue()
            ->and($superadmin->can('update', $otherSuperadmin))->toBeTrue()
            ->and($superadmin->can('delete', $staff))->toBeTrue()
            ->and($superadmin->can('impersonate', $customer))->toBeTrue();
    });
});

describe('Staff Authorization', function () {
    it('staff can view all users', function () {
        $staff = User::factory()->staff()->create();
        $customer = User::factory()->customer()->create();

        expect($staff->can('viewAny', User::class))->toBeTrue()
            ->and($staff->can('view', $customer))->toBeTrue();
    });

    it('staff can create users', function () {
        $staff = User::factory()->staff()->create();

        expect($staff->can('create', User::class))->toBeTrue();
    });

    it('staff can update non-superadmin users', function () {
        $staff = User::factory()->staff()->create();
        $customer = User::factory()->customer()->create();

        expect($staff->can('update', $customer))->toBeTrue();
    });

    it('staff cannot update superadmin', function () {
        $staff = User::factory()->staff()->create();
        $superadmin = User::factory()->superadmin()->create();

        expect($staff->can('update', $superadmin))->toBeFalse();
    });

    it('staff can suspend customers and delegates', function () {
        $staff = User::factory()->staff()->create();
        $customer = User::factory()->customer()->create();

        expect($staff->can('suspend', $customer))->toBeTrue();
    });

    it('staff cannot suspend themselves', function () {
        $staff = User::factory()->staff()->create();

        expect($staff->can('suspend', $staff))->toBeFalse();
    });

    it('staff cannot suspend superadmin', function () {
        $staff = User::factory()->staff()->create();
        $superadmin = User::factory()->superadmin()->create();

        expect($staff->can('suspend', $superadmin))->toBeFalse();
    });

    it('staff can delete non-superadmin users', function () {
        $staff = User::factory()->staff()->create();
        $customer = User::factory()->customer()->create();

        expect($staff->can('delete', $customer))->toBeTrue();
    });

    it('staff cannot delete superadmin', function () {
        $staff = User::factory()->staff()->create();
        $superadmin = User::factory()->superadmin()->create();

        expect($staff->can('delete', $superadmin))->toBeFalse();
    });

    it('staff cannot delete themselves', function () {
        $staff = User::factory()->staff()->create();

        expect($staff->can('delete', $staff))->toBeFalse();
    });

    it('staff can impersonate customers', function () {
        $staff = User::factory()->staff()->create();
        $customer = User::factory()->customer()->create();

        expect($staff->can('impersonate', $customer))->toBeTrue();
    });

    it('staff cannot impersonate other staff', function () {
        $staff = User::factory()->staff()->create();
        $otherStaff = User::factory()->staff()->create();

        expect($staff->can('impersonate', $otherStaff))->toBeFalse();
    });

    it('staff cannot impersonate themselves', function () {
        $staff = User::factory()->staff()->create();

        expect($staff->can('impersonate', $staff))->toBeFalse();
    });
});

describe('Customer Authorization', function () {
    it('customer cannot view all users', function () {
        $customer = User::factory()->customer()->create();

        expect($customer->can('viewAny', User::class))->toBeFalse();
    });

    it('customer can view themselves', function () {
        $customer = User::factory()->customer()->create();

        expect($customer->can('view', $customer))->toBeTrue();
    });

    it('customer can view their delegates', function () {
        $customer = User::factory()->customer()->create();
        $delegate = User::factory()->delegate()->create([
            'parent_user_id' => $customer->id,
        ]);

        expect($customer->can('view', $delegate))->toBeTrue();
    });

    it('customer cannot view other customers', function () {
        $customer = User::factory()->customer()->create();
        $otherCustomer = User::factory()->customer()->create();

        expect($customer->can('view', $otherCustomer))->toBeFalse();
    });

    it('customer can create (delegates)', function () {
        $customer = User::factory()->customer()->create();

        expect($customer->can('create', User::class))->toBeTrue();
    });

    it('customer can update themselves', function () {
        $customer = User::factory()->customer()->create();

        expect($customer->can('update', $customer))->toBeTrue();
    });

    it('customer can update their delegates', function () {
        $customer = User::factory()->customer()->create();
        $delegate = User::factory()->delegate()->create([
            'parent_user_id' => $customer->id,
        ]);

        expect($customer->can('update', $delegate))->toBeTrue();
    });

    it('customer cannot update other customers', function () {
        $customer = User::factory()->customer()->create();
        $otherCustomer = User::factory()->customer()->create();

        expect($customer->can('update', $otherCustomer))->toBeFalse();
    });

    it('customer cannot suspend anyone', function () {
        $customer = User::factory()->customer()->create();
        $delegate = User::factory()->delegate()->create([
            'parent_user_id' => $customer->id,
        ]);

        expect($customer->can('suspend', $delegate))->toBeFalse();
    });

    it('customer cannot delete anyone', function () {
        $customer = User::factory()->customer()->create();
        $delegate = User::factory()->delegate()->create([
            'parent_user_id' => $customer->id,
        ]);

        expect($customer->can('delete', $delegate))->toBeFalse();
    });

    it('customer can manage their own delegates', function () {
        $customer = User::factory()->customer()->create();

        expect($customer->can('manageDelegates', $customer))->toBeTrue();
    });

    it('customer cannot manage other customers delegates', function () {
        $customer = User::factory()->customer()->create();
        $otherCustomer = User::factory()->customer()->create();

        expect($customer->can('manageDelegates', $otherCustomer))->toBeFalse();
    });
});

describe('Delegate Authorization', function () {
    it('delegate can view themselves', function () {
        $delegate = User::factory()->delegate()->create();

        expect($delegate->can('view', $delegate))->toBeTrue();
    });

    it('delegate cannot view other users', function () {
        $delegate = User::factory()->delegate()->create();
        $customer = User::factory()->customer()->create();

        expect($delegate->can('view', $customer))->toBeFalse();
    });

    it('delegate cannot create users', function () {
        $delegate = User::factory()->delegate()->create();

        // Delegates cannot create - the create method only allows staff and customers
        expect($delegate->can('create', User::class))->toBeFalse();
    });

    it('delegate can update only themselves', function () {
        $delegate = User::factory()->delegate()->create();
        $otherDelegate = User::factory()->delegate()->create();

        expect($delegate->can('update', $delegate))->toBeTrue()
            ->and($delegate->can('update', $otherDelegate))->toBeFalse();
    });
});

describe('Inactive User Authorization', function () {
    it('inactive user cannot perform any action', function () {
        $inactiveStaff = User::factory()->staff()->inactive()->create();
        $customer = User::factory()->customer()->create();

        expect($inactiveStaff->can('viewAny', User::class))->toBeFalse()
            ->and($inactiveStaff->can('view', $customer))->toBeFalse()
            ->and($inactiveStaff->can('update', $customer))->toBeFalse();
    });

    it('suspended user cannot perform any action', function () {
        $suspendedStaff = User::factory()->staff()->suspended()->create();
        $customer = User::factory()->customer()->create();

        expect($suspendedStaff->can('viewAny', User::class))->toBeFalse()
            ->and($suspendedStaff->can('view', $customer))->toBeFalse();
    });
});

describe('Impersonation of Inactive Users', function () {
    it('staff cannot impersonate inactive users', function () {
        $staff = User::factory()->staff()->create();
        $inactiveCustomer = User::factory()->customer()->inactive()->create();

        expect($staff->can('impersonate', $inactiveCustomer))->toBeFalse();
    });

    it('staff cannot impersonate suspended users', function () {
        $staff = User::factory()->staff()->create();
        $suspendedCustomer = User::factory()->customer()->suspended()->create();

        expect($staff->can('impersonate', $suspendedCustomer))->toBeFalse();
    });
});
