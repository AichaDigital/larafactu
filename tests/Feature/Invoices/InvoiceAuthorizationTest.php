<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Invoice authorization defense-in-depth', function () {
    it('redirects guest to login', function () {
        $response = $this->get(route('invoices.create'));

        $response->assertRedirect(route('login'));
    });

    it('forbids non-admin from accessing create page', function () {
        $customer = User::factory()->customer()->create();

        $this->actingAs($customer);

        $response = $this->get(route('invoices.create'));

        $response->assertForbidden();
    });

    it('allows admin to access create page', function () {
        $admin = User::factory()->staff()->create();

        $this->actingAs($admin);

        $response = $this->get(route('invoices.create'));

        $response->assertOk();
    });

    it('forbids non-admin from saving invoice via Livewire', function () {
        $customer = User::factory()->customer()->create();

        $this->actingAs($customer);

        Livewire\Livewire::test(\App\Livewire\Invoices\InvoiceCreate::class)
            ->call('save')
            ->assertForbidden();
    });
});
