<?php

declare(strict_types=1);

use App\Enums\AccessLevel;
use App\Models\User;
use App\Models\UserCustomerAccess;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| UserCustomerAccess Tests - TDD Approach
|--------------------------------------------------------------------------
|
| These tests define the expected behavior for the UserCustomerAccess domain.
| The model manages delegate access to customer accounts.
|
| @see ADR-004 Authorization System
| @see ADR-006 Consolidated State
|
*/

beforeEach(function () {
    // Create test users
    $this->customer = User::factory()->create(['name' => 'Customer User']);
    $this->delegate = User::factory()->create(['name' => 'Delegate User']);
    $this->admin = User::factory()->create(['name' => 'Admin User']);
});

describe('UserCustomerAccess Model', function () {

    it('can create access for a delegate to a customer', function () {
        $access = UserCustomerAccess::create([
            'user_id' => $this->delegate->id,
            'customer_user_id' => $this->customer->id,
            'access_level' => AccessLevel::READ,
            'can_view_invoices' => true,
            'can_view_services' => true,
            'can_manage_tickets' => false,
            'can_manage_delegates' => false,
            'granted_by' => $this->admin->id,
            'granted_at' => now(),
        ]);

        expect($access)->toBeInstanceOf(UserCustomerAccess::class)
            ->and($access->user_id)->toBe($this->delegate->id)
            ->and($access->customer_user_id)->toBe($this->customer->id)
            ->and($access->access_level)->toBe(AccessLevel::READ);
    });

    it('casts access_level to AccessLevel enum', function () {
        $access = UserCustomerAccess::create([
            'user_id' => $this->delegate->id,
            'customer_user_id' => $this->customer->id,
            'access_level' => AccessLevel::WRITE,
            'granted_by' => $this->admin->id,
            'granted_at' => now(),
        ]);

        expect($access->access_level)->toBeInstanceOf(AccessLevel::class)
            ->and($access->access_level)->toBe(AccessLevel::WRITE);
    });

    it('belongs to a delegate user', function () {
        $access = UserCustomerAccess::create([
            'user_id' => $this->delegate->id,
            'customer_user_id' => $this->customer->id,
            'access_level' => AccessLevel::READ,
            'granted_by' => $this->admin->id,
            'granted_at' => now(),
        ]);

        expect($access->user)->toBeInstanceOf(User::class)
            ->and($access->user->id)->toBe($this->delegate->id);
    });

    it('belongs to a customer user', function () {
        $access = UserCustomerAccess::create([
            'user_id' => $this->delegate->id,
            'customer_user_id' => $this->customer->id,
            'access_level' => AccessLevel::READ,
            'granted_by' => $this->admin->id,
            'granted_at' => now(),
        ]);

        expect($access->customer)->toBeInstanceOf(User::class)
            ->and($access->customer->id)->toBe($this->customer->id);
    });

    it('belongs to a grantor user', function () {
        $access = UserCustomerAccess::create([
            'user_id' => $this->delegate->id,
            'customer_user_id' => $this->customer->id,
            'access_level' => AccessLevel::READ,
            'granted_by' => $this->admin->id,
            'granted_at' => now(),
        ]);

        expect($access->grantor)->toBeInstanceOf(User::class)
            ->and($access->grantor->id)->toBe($this->admin->id);
    });

    it('has default values for granular permissions', function () {
        $access = UserCustomerAccess::create([
            'user_id' => $this->delegate->id,
            'customer_user_id' => $this->customer->id,
            'access_level' => AccessLevel::READ,
            'granted_by' => $this->admin->id,
            'granted_at' => now(),
        ]);

        expect($access->can_view_invoices)->toBeFalse()
            ->and($access->can_view_services)->toBeFalse()
            ->and($access->can_manage_tickets)->toBeFalse()
            ->and($access->can_manage_delegates)->toBeFalse();
    });

});

describe('UserCustomerAccess Expiration', function () {

    it('can set an expiration date', function () {
        $expiresAt = Carbon::now()->addMonth();

        $access = UserCustomerAccess::create([
            'user_id' => $this->delegate->id,
            'customer_user_id' => $this->customer->id,
            'access_level' => AccessLevel::READ,
            'granted_by' => $this->admin->id,
            'granted_at' => now(),
            'expires_at' => $expiresAt,
        ]);

        expect($access->expires_at)->toBeInstanceOf(Carbon::class)
            ->and($access->expires_at->toDateString())->toBe($expiresAt->toDateString());
    });

    it('detects expired access', function () {
        $access = UserCustomerAccess::create([
            'user_id' => $this->delegate->id,
            'customer_user_id' => $this->customer->id,
            'access_level' => AccessLevel::READ,
            'granted_by' => $this->admin->id,
            'granted_at' => now(),
            'expires_at' => Carbon::now()->subDay(),
        ]);

        expect($access->isExpired())->toBeTrue();
    });

    it('detects non-expired access', function () {
        $access = UserCustomerAccess::create([
            'user_id' => $this->delegate->id,
            'customer_user_id' => $this->customer->id,
            'access_level' => AccessLevel::READ,
            'granted_by' => $this->admin->id,
            'granted_at' => now(),
            'expires_at' => Carbon::now()->addMonth(),
        ]);

        expect($access->isExpired())->toBeFalse();
    });

    it('treats null expiration as never expires', function () {
        $access = UserCustomerAccess::create([
            'user_id' => $this->delegate->id,
            'customer_user_id' => $this->customer->id,
            'access_level' => AccessLevel::READ,
            'granted_by' => $this->admin->id,
            'granted_at' => now(),
            'expires_at' => null,
        ]);

        expect($access->isExpired())->toBeFalse();
    });

    it('has scope to filter active (non-expired) access', function () {
        // Create active access
        UserCustomerAccess::create([
            'user_id' => $this->delegate->id,
            'customer_user_id' => $this->customer->id,
            'access_level' => AccessLevel::READ,
            'granted_by' => $this->admin->id,
            'granted_at' => now(),
            'expires_at' => Carbon::now()->addMonth(),
        ]);

        // Create expired access
        $expiredDelegate = User::factory()->create();
        UserCustomerAccess::create([
            'user_id' => $expiredDelegate->id,
            'customer_user_id' => $this->customer->id,
            'access_level' => AccessLevel::READ,
            'granted_by' => $this->admin->id,
            'granted_at' => now(),
            'expires_at' => Carbon::now()->subDay(),
        ]);

        $activeAccess = UserCustomerAccess::active()->get();

        expect($activeAccess)->toHaveCount(1)
            ->and($activeAccess->first()->user_id)->toBe($this->delegate->id);
    });

});

describe('UserCustomerAccess Permissions', function () {

    it('can check read permission via access level', function () {
        $access = UserCustomerAccess::create([
            'user_id' => $this->delegate->id,
            'customer_user_id' => $this->customer->id,
            'access_level' => AccessLevel::READ,
            'granted_by' => $this->admin->id,
            'granted_at' => now(),
        ]);

        expect($access->canRead())->toBeTrue()
            ->and($access->canWrite())->toBeFalse()
            ->and($access->canDelete())->toBeFalse();
    });

    it('can check write permission via access level', function () {
        $access = UserCustomerAccess::create([
            'user_id' => $this->delegate->id,
            'customer_user_id' => $this->customer->id,
            'access_level' => AccessLevel::WRITE,
            'granted_by' => $this->admin->id,
            'granted_at' => now(),
        ]);

        expect($access->canRead())->toBeTrue()
            ->and($access->canWrite())->toBeTrue()
            ->and($access->canDelete())->toBeFalse();
    });

    it('can check full permission via access level', function () {
        $access = UserCustomerAccess::create([
            'user_id' => $this->delegate->id,
            'customer_user_id' => $this->customer->id,
            'access_level' => AccessLevel::FULL,
            'granted_by' => $this->admin->id,
            'granted_at' => now(),
        ]);

        expect($access->canRead())->toBeTrue()
            ->and($access->canWrite())->toBeTrue()
            ->and($access->canDelete())->toBeTrue();
    });

    it('returns false for all permissions with NONE access level', function () {
        $access = UserCustomerAccess::create([
            'user_id' => $this->delegate->id,
            'customer_user_id' => $this->customer->id,
            'access_level' => AccessLevel::NONE,
            'granted_by' => $this->admin->id,
            'granted_at' => now(),
        ]);

        expect($access->canRead())->toBeFalse()
            ->and($access->canWrite())->toBeFalse()
            ->and($access->canDelete())->toBeFalse();
    });

    it('combines access level with granular invoice permission', function () {
        $access = UserCustomerAccess::create([
            'user_id' => $this->delegate->id,
            'customer_user_id' => $this->customer->id,
            'access_level' => AccessLevel::READ,
            'can_view_invoices' => true,
            'granted_by' => $this->admin->id,
            'granted_at' => now(),
        ]);

        expect($access->canViewInvoices())->toBeTrue();

        $accessNoInvoices = UserCustomerAccess::create([
            'user_id' => User::factory()->create()->id,
            'customer_user_id' => $this->customer->id,
            'access_level' => AccessLevel::READ,
            'can_view_invoices' => false,
            'granted_by' => $this->admin->id,
            'granted_at' => now(),
        ]);

        expect($accessNoInvoices->canViewInvoices())->toBeFalse();
    });

    it('denies granular permissions when access is expired', function () {
        $access = UserCustomerAccess::create([
            'user_id' => $this->delegate->id,
            'customer_user_id' => $this->customer->id,
            'access_level' => AccessLevel::FULL,
            'can_view_invoices' => true,
            'can_view_services' => true,
            'can_manage_tickets' => true,
            'can_manage_delegates' => true,
            'granted_by' => $this->admin->id,
            'granted_at' => now(),
            'expires_at' => Carbon::now()->subDay(),
        ]);

        expect($access->canViewInvoices())->toBeFalse()
            ->and($access->canViewServices())->toBeFalse()
            ->and($access->canManageTickets())->toBeFalse()
            ->and($access->canManageDelegates())->toBeFalse();
    });

});

describe('UserCustomerAccess Uniqueness', function () {

    it('enforces unique constraint on user_id and customer_user_id', function () {
        UserCustomerAccess::create([
            'user_id' => $this->delegate->id,
            'customer_user_id' => $this->customer->id,
            'access_level' => AccessLevel::READ,
            'granted_by' => $this->admin->id,
            'granted_at' => now(),
        ]);

        expect(fn () => UserCustomerAccess::create([
            'user_id' => $this->delegate->id,
            'customer_user_id' => $this->customer->id,
            'access_level' => AccessLevel::WRITE,
            'granted_by' => $this->admin->id,
            'granted_at' => now(),
        ]))->toThrow(Illuminate\Database\QueryException::class);
    });

    it('allows same delegate to have access to different customers', function () {
        $customer2 = User::factory()->create(['name' => 'Customer 2']);

        $access1 = UserCustomerAccess::create([
            'user_id' => $this->delegate->id,
            'customer_user_id' => $this->customer->id,
            'access_level' => AccessLevel::READ,
            'granted_by' => $this->admin->id,
            'granted_at' => now(),
        ]);

        $access2 = UserCustomerAccess::create([
            'user_id' => $this->delegate->id,
            'customer_user_id' => $customer2->id,
            'access_level' => AccessLevel::WRITE,
            'granted_by' => $this->admin->id,
            'granted_at' => now(),
        ]);

        expect($access1->exists)->toBeTrue()
            ->and($access2->exists)->toBeTrue();
    });

});

describe('User has delegate access', function () {

    it('can get all customers a delegate has access to', function () {
        $customer2 = User::factory()->create(['name' => 'Customer 2']);

        UserCustomerAccess::create([
            'user_id' => $this->delegate->id,
            'customer_user_id' => $this->customer->id,
            'access_level' => AccessLevel::READ,
            'granted_by' => $this->admin->id,
            'granted_at' => now(),
        ]);

        UserCustomerAccess::create([
            'user_id' => $this->delegate->id,
            'customer_user_id' => $customer2->id,
            'access_level' => AccessLevel::WRITE,
            'granted_by' => $this->admin->id,
            'granted_at' => now(),
        ]);

        $accessibleCustomers = $this->delegate->accessibleCustomers;

        expect($accessibleCustomers)->toHaveCount(2)
            ->and($accessibleCustomers->pluck('id')->toArray())->toContain($this->customer->id, $customer2->id);
    });

    it('can get all delegates for a customer', function () {
        $delegate2 = User::factory()->create(['name' => 'Delegate 2']);

        UserCustomerAccess::create([
            'user_id' => $this->delegate->id,
            'customer_user_id' => $this->customer->id,
            'access_level' => AccessLevel::READ,
            'granted_by' => $this->admin->id,
            'granted_at' => now(),
        ]);

        UserCustomerAccess::create([
            'user_id' => $delegate2->id,
            'customer_user_id' => $this->customer->id,
            'access_level' => AccessLevel::WRITE,
            'granted_by' => $this->admin->id,
            'granted_at' => now(),
        ]);

        $delegates = $this->customer->delegates;

        expect($delegates)->toHaveCount(2)
            ->and($delegates->pluck('id')->toArray())->toContain($this->delegate->id, $delegate2->id);
    });

    it('can check if user has access to a specific customer', function () {
        UserCustomerAccess::create([
            'user_id' => $this->delegate->id,
            'customer_user_id' => $this->customer->id,
            'access_level' => AccessLevel::READ,
            'granted_by' => $this->admin->id,
            'granted_at' => now(),
        ]);

        $otherCustomer = User::factory()->create();

        expect($this->delegate->hasAccessTo($this->customer))->toBeTrue()
            ->and($this->delegate->hasAccessTo($otherCustomer))->toBeFalse();
    });

    it('returns false for expired access when checking hasAccessTo', function () {
        UserCustomerAccess::create([
            'user_id' => $this->delegate->id,
            'customer_user_id' => $this->customer->id,
            'access_level' => AccessLevel::READ,
            'granted_by' => $this->admin->id,
            'granted_at' => now(),
            'expires_at' => Carbon::now()->subDay(),
        ]);

        expect($this->delegate->hasAccessTo($this->customer))->toBeFalse();
    });

    it('can get access level for a specific customer', function () {
        UserCustomerAccess::create([
            'user_id' => $this->delegate->id,
            'customer_user_id' => $this->customer->id,
            'access_level' => AccessLevel::WRITE,
            'granted_by' => $this->admin->id,
            'granted_at' => now(),
        ]);

        expect($this->delegate->getAccessLevelFor($this->customer))->toBe(AccessLevel::WRITE);
    });

    it('returns NONE access level for customers without access', function () {
        $otherCustomer = User::factory()->create();

        expect($this->delegate->getAccessLevelFor($otherCustomer))->toBe(AccessLevel::NONE);
    });

});

describe('UserCustomerAccess revocation', function () {

    it('can revoke access by deleting the record', function () {
        $access = UserCustomerAccess::create([
            'user_id' => $this->delegate->id,
            'customer_user_id' => $this->customer->id,
            'access_level' => AccessLevel::READ,
            'granted_by' => $this->admin->id,
            'granted_at' => now(),
        ]);

        $access->delete();

        expect(UserCustomerAccess::where('user_id', $this->delegate->id)
            ->where('customer_user_id', $this->customer->id)
            ->exists())->toBeFalse();
    });

    it('can revoke all access for a delegate', function () {
        $customer2 = User::factory()->create();

        UserCustomerAccess::create([
            'user_id' => $this->delegate->id,
            'customer_user_id' => $this->customer->id,
            'access_level' => AccessLevel::READ,
            'granted_by' => $this->admin->id,
            'granted_at' => now(),
        ]);

        UserCustomerAccess::create([
            'user_id' => $this->delegate->id,
            'customer_user_id' => $customer2->id,
            'access_level' => AccessLevel::READ,
            'granted_by' => $this->admin->id,
            'granted_at' => now(),
        ]);

        UserCustomerAccess::where('user_id', $this->delegate->id)->delete();

        expect(UserCustomerAccess::where('user_id', $this->delegate->id)->count())->toBe(0);
    });

});
