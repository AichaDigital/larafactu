<?php

declare(strict_types=1);

use App\Models\User;
use App\Services\ImpersonationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Impersonation Tests - TDD Approach
|--------------------------------------------------------------------------
|
| Tests for the impersonation system that allows admins to view the app
| as if they were a specific customer.
|
| @see ADR-004 Authorization System
| @see ADR-006 Consolidated State
|
*/

beforeEach(function () {
    // ADR-004: Admin user using staff() factory state
    $this->admin = User::factory()->staff()->create();

    // Regular users (customers by default)
    $this->customer = User::factory()->customer()->create(['name' => 'Customer']);
    $this->otherAdmin = User::factory()->staff()->create();
});

describe('Impersonation Gate', function () {

    it('allows admin to impersonate non-admin user', function () {
        $this->actingAs($this->admin);

        expect(Gate::allows('impersonate', $this->customer))->toBeTrue();
    });

    it('denies non-admin from impersonating', function () {
        $this->actingAs($this->customer);

        $otherUser = User::factory()->create();
        expect(Gate::allows('impersonate', $otherUser))->toBeFalse();
    });

    it('denies admin from impersonating themselves', function () {
        $this->actingAs($this->admin);

        expect(Gate::allows('impersonate', $this->admin))->toBeFalse();
    });

    it('denies admin from impersonating other admin', function () {
        $this->actingAs($this->admin);

        expect(Gate::allows('impersonate', $this->otherAdmin))->toBeFalse();
    });

});

describe('ImpersonationService', function () {

    it('can start impersonation session', function () {
        $this->actingAs($this->admin);

        $service = app(ImpersonationService::class);
        $result = $service->start($this->customer);

        expect($result)->toBeTrue()
            ->and(session('impersonating_user_id'))->toBe($this->customer->id)
            ->and(session('original_user_id'))->toBe($this->admin->id);
    });

    it('denies impersonation for non-admin', function () {
        $this->actingAs($this->customer);

        $service = app(ImpersonationService::class);
        $otherUser = User::factory()->create();
        $result = $service->start($otherUser);

        expect($result)->toBeFalse()
            ->and(session('impersonating_user_id'))->toBeNull();
    });

    it('can stop impersonation session', function () {
        $this->actingAs($this->admin);

        $service = app(ImpersonationService::class);
        $service->start($this->customer);

        expect(session('impersonating_user_id'))->toBe($this->customer->id);

        $service->stop();

        expect(session('impersonating_user_id'))->toBeNull()
            ->and(session('original_user_id'))->toBeNull();
    });

    it('checks if currently impersonating', function () {
        $this->actingAs($this->admin);

        $service = app(ImpersonationService::class);

        expect($service->isImpersonating())->toBeFalse();

        $service->start($this->customer);

        expect($service->isImpersonating())->toBeTrue();
    });

    it('returns impersonated user', function () {
        $this->actingAs($this->admin);

        $service = app(ImpersonationService::class);
        $service->start($this->customer);

        $impersonated = $service->getImpersonatedUser();

        expect($impersonated)->toBeInstanceOf(User::class)
            ->and($impersonated->id)->toBe($this->customer->id);
    });

    it('returns original user during impersonation', function () {
        $this->actingAs($this->admin);

        $service = app(ImpersonationService::class);
        $service->start($this->customer);

        $original = $service->getOriginalUser();

        expect($original)->toBeInstanceOf(User::class)
            ->and($original->id)->toBe($this->admin->id);
    });

    it('returns null for impersonated user when not impersonating', function () {
        $this->actingAs($this->admin);

        $service = app(ImpersonationService::class);

        expect($service->getImpersonatedUser())->toBeNull();
    });

});

describe('Impersonation Routes', function () {

    it('can start impersonation via route', function () {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.impersonate.start', $this->customer));

        $response->assertRedirect();
        expect(session('impersonating_user_id'))->toBe($this->customer->id);
    });

    it('denies impersonation for non-admin via route', function () {
        $response = $this->actingAs($this->customer)
            ->post(route('admin.impersonate.start', User::factory()->create()));

        $response->assertForbidden();
    });

    it('can stop impersonation via route', function () {
        $this->actingAs($this->admin);
        app(ImpersonationService::class)->start($this->customer);

        $response = $this->post(route('admin.impersonate.stop'));

        $response->assertRedirect();
        expect(session('impersonating_user_id'))->toBeNull();
    });

    it('redirects to dashboard after starting impersonation', function () {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.impersonate.start', $this->customer));

        $response->assertRedirect(route('dashboard'));
    });

    it('redirects to admin dashboard after stopping impersonation', function () {
        $this->actingAs($this->admin);
        app(ImpersonationService::class)->start($this->customer);

        $response = $this->post(route('admin.impersonate.stop'));

        $response->assertRedirect(route('admin.dashboard'));
    });

});

describe('Impersonation Middleware', function () {

    it('switches auth user when impersonating', function () {
        $this->actingAs($this->admin);
        app(ImpersonationService::class)->start($this->customer);

        $response = $this->get(route('dashboard'));

        $response->assertOk();
        // The view should see the impersonated user
        expect(auth()->id())->toBe($this->admin->id); // Auth stays as admin
        expect(session('impersonating_user_id'))->toBe($this->customer->id);
    });

    it('blocks admin routes when impersonating', function () {
        $this->actingAs($this->admin);
        app(ImpersonationService::class)->start($this->customer);

        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('dashboard'));
    });

});

describe('Impersonation in Panel Admin', function () {

    it('shows impersonate button for non-admin users', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.users'));

        $response->assertOk();
        $response->assertSee('Impersonar');
    });

});
