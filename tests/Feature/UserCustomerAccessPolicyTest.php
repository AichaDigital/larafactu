<?php

declare(strict_types=1);

use App\Enums\AccessLevel;
use App\Models\User;
use App\Models\UserCustomerAccess;
use App\Policies\UserCustomerAccessPolicy;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| UserCustomerAccessPolicy Tests - TDD Approach
|--------------------------------------------------------------------------
|
| These tests define authorization rules for managing delegate access.
|
| @see ADR-004 Authorization System
| @see ADR-006 Consolidated State
|
*/

beforeEach(function () {
    $this->policy = new UserCustomerAccessPolicy;

    // Create test users
    $this->customer = User::factory()->create(['name' => 'Customer']);
    $this->delegate = User::factory()->create(['name' => 'Delegate']);
    $this->otherUser = User::factory()->create(['name' => 'Other User']);

    // ADR-004: Admin user using staff() factory state
    $this->admin = User::factory()->staff()->create();
});

describe('UserCustomerAccessPolicy viewAny', function () {

    it('allows admin to view all access records', function () {
        expect($this->policy->viewAny($this->admin))->toBeTrue();
    });

    it('allows customer to view their delegates', function () {
        // Customer viewing who has access to their account
        expect($this->policy->viewAny($this->customer))->toBeTrue();
    });

    it('allows delegate to view their access records', function () {
        // Delegate viewing what customers they have access to
        expect($this->policy->viewAny($this->delegate))->toBeTrue();
    });

});

describe('UserCustomerAccessPolicy view', function () {

    beforeEach(function () {
        $this->access = UserCustomerAccess::create([
            'user_id' => $this->delegate->id,
            'customer_user_id' => $this->customer->id,
            'access_level' => AccessLevel::READ,
            'granted_by' => $this->admin->id,
            'granted_at' => now(),
        ]);
    });

    it('allows admin to view any access record', function () {
        expect($this->policy->view($this->admin, $this->access))->toBeTrue();
    });

    it('allows customer to view access to their account', function () {
        expect($this->policy->view($this->customer, $this->access))->toBeTrue();
    });

    it('allows delegate to view their own access record', function () {
        expect($this->policy->view($this->delegate, $this->access))->toBeTrue();
    });

    it('denies other users from viewing access record', function () {
        expect($this->policy->view($this->otherUser, $this->access))->toBeFalse();
    });

});

describe('UserCustomerAccessPolicy create', function () {

    it('allows admin to create access for any customer', function () {
        expect($this->policy->create($this->admin, $this->customer))->toBeTrue();
    });

    it('allows customer to create delegates for their own account', function () {
        expect($this->policy->create($this->customer, $this->customer))->toBeTrue();
    });

    it('denies customer from creating delegates for other accounts', function () {
        $otherCustomer = User::factory()->create();
        expect($this->policy->create($this->customer, $otherCustomer))->toBeFalse();
    });

    it('allows delegate with can_manage_delegates to create sub-delegates', function () {
        UserCustomerAccess::create([
            'user_id' => $this->delegate->id,
            'customer_user_id' => $this->customer->id,
            'access_level' => AccessLevel::FULL,
            'can_manage_delegates' => true,
            'granted_by' => $this->customer->id,
            'granted_at' => now(),
        ]);

        expect($this->policy->create($this->delegate, $this->customer))->toBeTrue();
    });

    it('denies delegate without can_manage_delegates from creating sub-delegates', function () {
        UserCustomerAccess::create([
            'user_id' => $this->delegate->id,
            'customer_user_id' => $this->customer->id,
            'access_level' => AccessLevel::FULL,
            'can_manage_delegates' => false,
            'granted_by' => $this->customer->id,
            'granted_at' => now(),
        ]);

        expect($this->policy->create($this->delegate, $this->customer))->toBeFalse();
    });

    it('denies delegate with expired access from creating sub-delegates', function () {
        UserCustomerAccess::create([
            'user_id' => $this->delegate->id,
            'customer_user_id' => $this->customer->id,
            'access_level' => AccessLevel::FULL,
            'can_manage_delegates' => true,
            'granted_by' => $this->customer->id,
            'granted_at' => now(),
            'expires_at' => Carbon::now()->subDay(),
        ]);

        expect($this->policy->create($this->delegate, $this->customer))->toBeFalse();
    });

});

describe('UserCustomerAccessPolicy update', function () {

    beforeEach(function () {
        $this->access = UserCustomerAccess::create([
            'user_id' => $this->delegate->id,
            'customer_user_id' => $this->customer->id,
            'access_level' => AccessLevel::READ,
            'granted_by' => $this->admin->id,
            'granted_at' => now(),
        ]);
    });

    it('allows admin to update any access record', function () {
        expect($this->policy->update($this->admin, $this->access))->toBeTrue();
    });

    it('allows customer to update access to their account', function () {
        expect($this->policy->update($this->customer, $this->access))->toBeTrue();
    });

    it('denies delegate from updating their own access record', function () {
        // Delegates cannot modify their own permissions
        expect($this->policy->update($this->delegate, $this->access))->toBeFalse();
    });

    it('allows delegate with can_manage_delegates to update other delegates access', function () {
        // Create a manager delegate
        UserCustomerAccess::create([
            'user_id' => $this->otherUser->id,
            'customer_user_id' => $this->customer->id,
            'access_level' => AccessLevel::FULL,
            'can_manage_delegates' => true,
            'granted_by' => $this->customer->id,
            'granted_at' => now(),
        ]);

        expect($this->policy->update($this->otherUser, $this->access))->toBeTrue();
    });

    it('denies other users from updating access record', function () {
        $randomUser = User::factory()->create();
        expect($this->policy->update($randomUser, $this->access))->toBeFalse();
    });

});

describe('UserCustomerAccessPolicy delete', function () {

    beforeEach(function () {
        $this->access = UserCustomerAccess::create([
            'user_id' => $this->delegate->id,
            'customer_user_id' => $this->customer->id,
            'access_level' => AccessLevel::READ,
            'granted_by' => $this->admin->id,
            'granted_at' => now(),
        ]);
    });

    it('allows admin to delete any access record', function () {
        expect($this->policy->delete($this->admin, $this->access))->toBeTrue();
    });

    it('allows customer to revoke access to their account', function () {
        expect($this->policy->delete($this->customer, $this->access))->toBeTrue();
    });

    it('allows delegate to revoke their own access (resign)', function () {
        // Delegates can remove themselves
        expect($this->policy->delete($this->delegate, $this->access))->toBeTrue();
    });

    it('allows delegate with can_manage_delegates to revoke other delegates', function () {
        // Create a manager delegate
        UserCustomerAccess::create([
            'user_id' => $this->otherUser->id,
            'customer_user_id' => $this->customer->id,
            'access_level' => AccessLevel::FULL,
            'can_manage_delegates' => true,
            'granted_by' => $this->customer->id,
            'granted_at' => now(),
        ]);

        expect($this->policy->delete($this->otherUser, $this->access))->toBeTrue();
    });

    it('denies other users from deleting access record', function () {
        $randomUser = User::factory()->create();
        expect($this->policy->delete($randomUser, $this->access))->toBeFalse();
    });

});

describe('UserCustomerAccessPolicy acting on behalf', function () {

    it('allows delegate to act on behalf of customer for invoices', function () {
        UserCustomerAccess::create([
            'user_id' => $this->delegate->id,
            'customer_user_id' => $this->customer->id,
            'access_level' => AccessLevel::READ,
            'can_view_invoices' => true,
            'granted_by' => $this->customer->id,
            'granted_at' => now(),
        ]);

        expect($this->policy->viewInvoicesFor($this->delegate, $this->customer))->toBeTrue();
    });

    it('denies delegate without invoice permission from viewing invoices', function () {
        UserCustomerAccess::create([
            'user_id' => $this->delegate->id,
            'customer_user_id' => $this->customer->id,
            'access_level' => AccessLevel::FULL,
            'can_view_invoices' => false,
            'granted_by' => $this->customer->id,
            'granted_at' => now(),
        ]);

        expect($this->policy->viewInvoicesFor($this->delegate, $this->customer))->toBeFalse();
    });

    it('allows delegate to manage tickets for customer', function () {
        UserCustomerAccess::create([
            'user_id' => $this->delegate->id,
            'customer_user_id' => $this->customer->id,
            'access_level' => AccessLevel::WRITE,
            'can_manage_tickets' => true,
            'granted_by' => $this->customer->id,
            'granted_at' => now(),
        ]);

        expect($this->policy->manageTicketsFor($this->delegate, $this->customer))->toBeTrue();
    });

    it('denies expired delegate from acting on behalf', function () {
        UserCustomerAccess::create([
            'user_id' => $this->delegate->id,
            'customer_user_id' => $this->customer->id,
            'access_level' => AccessLevel::FULL,
            'can_view_invoices' => true,
            'can_manage_tickets' => true,
            'granted_by' => $this->customer->id,
            'granted_at' => now(),
            'expires_at' => Carbon::now()->subDay(),
        ]);

        expect($this->policy->viewInvoicesFor($this->delegate, $this->customer))->toBeFalse()
            ->and($this->policy->manageTicketsFor($this->delegate, $this->customer))->toBeFalse();
    });

    it('allows customer to always view their own invoices', function () {
        expect($this->policy->viewInvoicesFor($this->customer, $this->customer))->toBeTrue();
    });

    it('allows admin to view any customer invoices', function () {
        expect($this->policy->viewInvoicesFor($this->admin, $this->customer))->toBeTrue();
    });

});
