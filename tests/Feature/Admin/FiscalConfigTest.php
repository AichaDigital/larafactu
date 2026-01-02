<?php

declare(strict_types=1);

use AichaDigital\Larabill\Models\CompanyFiscalConfig;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Admin Fiscal Configuration Tests - TDD
|--------------------------------------------------------------------------
|
| Tests for the admin fiscal configuration management.
| CompanyFiscalConfig uses temporal validity (valid_from, valid_until).
|
*/

beforeEach(function () {
    // Create admin user
    $this->admin = User::factory()->create(['email' => 'admin@testdomain.com']);
    config(['app.admin_domains' => 'testdomain.com']);

    // Create regular user
    $this->regularUser = User::factory()->create(['email' => 'user@example.com']);

    // Create an active fiscal config
    $this->activeConfig = CompanyFiscalConfig::create([
        'business_name' => 'Test Company S.L.',
        'tax_id' => 'B12345678',
        'legal_entity_type' => 'SL',
        'address' => 'Calle Test 123',
        'city' => 'Madrid',
        'state' => 'Madrid',
        'zip_code' => '28001',
        'country_code' => 'ES',
        'is_oss' => false,
        'is_roi' => false,
        'currency' => 'EUR',
        'fiscal_year_start' => '01-01',
        'valid_from' => now()->subYear(),
        'valid_until' => null,
        'is_active' => true,
    ]);
});

describe('Fiscal Config List Access', function () {

    it('allows admin to access fiscal config list', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.fiscal-configs'));

        $response->assertOk();
        $response->assertSee('Configuracion Fiscal');
    });

    it('denies non-admin access to fiscal config list', function () {
        $response = $this->actingAs($this->regularUser)
            ->get(route('admin.fiscal-configs'));

        $response->assertForbidden();
    });

    it('redirects guest to login', function () {
        $response = $this->get(route('admin.fiscal-configs'));

        $response->assertRedirect(route('login'));
    });

    it('displays existing fiscal configs', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.fiscal-configs'));

        $response->assertOk();
        $response->assertSee('Test Company S.L.');
        $response->assertSee('B12345678');
    });

    it('shows active badge for active config', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.fiscal-configs'));

        $response->assertOk();
        $response->assertSee('Activa');
    });

});

describe('Fiscal Config Create Access', function () {

    it('allows admin to access create form', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.fiscal-configs.create'));

        $response->assertOk();
        $response->assertSee('Nueva Configuracion Fiscal');
    });

    it('denies non-admin access to create form', function () {
        $response = $this->actingAs($this->regularUser)
            ->get(route('admin.fiscal-configs.create'));

        $response->assertForbidden();
    });

});

describe('Fiscal Config Create Form', function () {

    it('displays required form fields', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.fiscal-configs.create'));

        $response->assertOk();
        $response->assertSee('Razon Social');
        $response->assertSee('NIF/CIF');
        $response->assertSee('Tipo de Entidad');
    });

    it('displays address fields', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.fiscal-configs.create'));

        $response->assertOk();
        $response->assertSee('Direccion');
        $response->assertSee('Ciudad');
        $response->assertSee('Codigo Postal');
    });

    it('displays fiscal options', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.fiscal-configs.create'));

        $response->assertOk();
        $response->assertSee('Regimen OSS');
        $response->assertSee('Operador ROI');
    });

    it('displays validity date field', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.fiscal-configs.create'));

        $response->assertOk();
        $response->assertSee('Vigente desde');
    });

});

describe('Fiscal Config Create Validation', function () {

    it('requires business_name', function () {
        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\FiscalConfigCreate::class)
            ->set('business_name', '')
            ->call('create')
            ->assertHasErrors(['business_name' => 'required']);
    });

    it('requires tax_id', function () {
        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\FiscalConfigCreate::class)
            ->set('tax_id', '')
            ->call('create')
            ->assertHasErrors(['tax_id' => 'required']);
    });

    it('requires legal_entity_type', function () {
        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\FiscalConfigCreate::class)
            ->set('legal_entity_type', '')
            ->call('create')
            ->assertHasErrors(['legal_entity_type' => 'required']);
    });

    it('requires address', function () {
        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\FiscalConfigCreate::class)
            ->set('address', '')
            ->call('create')
            ->assertHasErrors(['address' => 'required']);
    });

    it('requires city', function () {
        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\FiscalConfigCreate::class)
            ->set('city', '')
            ->call('create')
            ->assertHasErrors(['city' => 'required']);
    });

    it('requires zip_code', function () {
        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\FiscalConfigCreate::class)
            ->set('zip_code', '')
            ->call('create')
            ->assertHasErrors(['zip_code' => 'required']);
    });

    it('requires country_code', function () {
        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\FiscalConfigCreate::class)
            ->set('country_code', '')
            ->call('create')
            ->assertHasErrors(['country_code' => 'required']);
    });

    it('requires valid_from date', function () {
        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\FiscalConfigCreate::class)
            ->set('valid_from', '')
            ->call('create')
            ->assertHasErrors(['valid_from' => 'required']);
    });

    it('requires valid_from to be a valid date', function () {
        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\FiscalConfigCreate::class)
            ->set('valid_from', 'not-a-date')
            ->call('create')
            ->assertHasErrors(['valid_from' => 'date']);
    });

    it('validates country_code is valid', function () {
        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\FiscalConfigCreate::class)
            ->set('country_code', 'INVALID')
            ->call('create')
            ->assertHasErrors(['country_code']);
    });

    it('validates currency is valid', function () {
        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\FiscalConfigCreate::class)
            ->set('currency', 'INVALID')
            ->call('create')
            ->assertHasErrors(['currency']);
    });

});

describe('Fiscal Config Create Success', function () {

    it('creates new fiscal config', function () {
        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\FiscalConfigCreate::class)
            ->set('business_name', 'Nueva Empresa S.A.')
            ->set('tax_id', 'A87654321')
            ->set('legal_entity_type', 'SA')
            ->set('address', 'Avenida Nueva 456')
            ->set('city', 'Barcelona')
            ->set('state', 'Barcelona')
            ->set('zip_code', '08001')
            ->set('country_code', 'ES')
            ->set('currency', 'EUR')
            ->set('fiscal_year_start', '01-01')
            ->set('valid_from', now()->format('Y-m-d'))
            ->call('create')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.fiscal-configs'));

        $this->assertDatabaseHas('company_fiscal_configs', [
            'business_name' => 'Nueva Empresa S.A.',
            'tax_id' => 'A87654321',
            'is_active' => true,
        ]);
    });

    it('closes previous active config when creating new one', function () {
        $oldConfig = $this->activeConfig;

        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\FiscalConfigCreate::class)
            ->set('business_name', 'Nueva Empresa S.A.')
            ->set('tax_id', 'A87654321')
            ->set('legal_entity_type', 'SA')
            ->set('address', 'Avenida Nueva 456')
            ->set('city', 'Barcelona')
            ->set('zip_code', '08001')
            ->set('country_code', 'ES')
            ->set('currency', 'EUR')
            ->set('valid_from', now()->format('Y-m-d'))
            ->call('create')
            ->assertHasNoErrors();

        $oldConfig->refresh();
        expect($oldConfig->is_active)->toBeFalse();
        expect($oldConfig->valid_until)->not->toBeNull();
    });

    it('creates config with OSS enabled', function () {
        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\FiscalConfigCreate::class)
            ->set('business_name', 'Empresa OSS')
            ->set('tax_id', 'B11111111')
            ->set('legal_entity_type', 'SL')
            ->set('address', 'Calle OSS 1')
            ->set('city', 'Valencia')
            ->set('zip_code', '46001')
            ->set('country_code', 'ES')
            ->set('currency', 'EUR')
            ->set('is_oss', true)
            ->set('valid_from', now()->format('Y-m-d'))
            ->call('create')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('company_fiscal_configs', [
            'tax_id' => 'B11111111',
            'is_oss' => true,
        ]);
    });

    it('creates config with ROI enabled', function () {
        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\FiscalConfigCreate::class)
            ->set('business_name', 'Empresa ROI')
            ->set('tax_id', 'B22222222')
            ->set('legal_entity_type', 'SL')
            ->set('address', 'Calle ROI 2')
            ->set('city', 'Sevilla')
            ->set('zip_code', '41001')
            ->set('country_code', 'ES')
            ->set('currency', 'EUR')
            ->set('is_roi', true)
            ->set('valid_from', now()->format('Y-m-d'))
            ->call('create')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('company_fiscal_configs', [
            'tax_id' => 'B22222222',
            'is_roi' => true,
        ]);
    });

    it('sets flash message on success', function () {
        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\FiscalConfigCreate::class)
            ->set('business_name', 'Flash Test S.L.')
            ->set('tax_id', 'B33333333')
            ->set('legal_entity_type', 'SL')
            ->set('address', 'Calle Flash 3')
            ->set('city', 'Bilbao')
            ->set('zip_code', '48001')
            ->set('country_code', 'ES')
            ->set('currency', 'EUR')
            ->set('valid_from', now()->format('Y-m-d'))
            ->call('create')
            ->assertSessionHas('success');
    });

});

describe('Fiscal Config Show', function () {

    it('allows admin to view config details', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.fiscal-configs.show', $this->activeConfig));

        $response->assertOk();
        $response->assertSee('Test Company S.L.');
        $response->assertSee('B12345678');
    });

    it('denies non-admin access to view config', function () {
        $response = $this->actingAs($this->regularUser)
            ->get(route('admin.fiscal-configs.show', $this->activeConfig));

        $response->assertForbidden();
    });

    it('returns 404 for non-existent config', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.fiscal-configs.show', 99999));

        $response->assertNotFound();
    });

    it('displays full address', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.fiscal-configs.show', $this->activeConfig));

        $response->assertOk();
        $response->assertSee('Calle Test 123');
        $response->assertSee('Madrid');
        $response->assertSee('28001');
    });

    it('displays validity information', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.fiscal-configs.show', $this->activeConfig));

        $response->assertOk();
        $response->assertSee('Vigente desde');
    });

});

describe('Fiscal Config Edit Access', function () {

    it('allows admin to access edit form for active config', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.fiscal-configs.edit', $this->activeConfig));

        $response->assertOk();
        $response->assertSee('Editar Configuracion Fiscal');
    });

    it('denies non-admin access to edit form', function () {
        $response = $this->actingAs($this->regularUser)
            ->get(route('admin.fiscal-configs.edit', $this->activeConfig));

        $response->assertForbidden();
    });

    it('returns 404 for non-existent config', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.fiscal-configs.edit', 99999));

        $response->assertNotFound();
    });

});

describe('Fiscal Config Edit Form', function () {

    it('pre-fills form with existing data', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.fiscal-configs.edit', $this->activeConfig));

        $response->assertOk();
        $response->assertSee('Test Company S.L.');
        $response->assertSee('B12345678');
    });

    it('displays all editable fields', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.fiscal-configs.edit', $this->activeConfig));

        $response->assertOk();
        $response->assertSee('Razon Social');
        $response->assertSee('NIF/CIF');
        $response->assertSee('Direccion');
    });

});

describe('Fiscal Config Edit Validation', function () {

    it('requires business_name on update', function () {
        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\FiscalConfigEdit::class, ['fiscalConfig' => $this->activeConfig])
            ->set('business_name', '')
            ->call('update')
            ->assertHasErrors(['business_name' => 'required']);
    });

    it('requires tax_id on update', function () {
        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\FiscalConfigEdit::class, ['fiscalConfig' => $this->activeConfig])
            ->set('tax_id', '')
            ->call('update')
            ->assertHasErrors(['tax_id' => 'required']);
    });

});

describe('Fiscal Config Edit Success', function () {

    it('updates fiscal config', function () {
        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\FiscalConfigEdit::class, ['fiscalConfig' => $this->activeConfig])
            ->set('business_name', 'Updated Company Name')
            ->set('address', 'Nueva Direccion 789')
            ->call('update')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.fiscal-configs'));

        $this->assertDatabaseHas('company_fiscal_configs', [
            'id' => $this->activeConfig->id,
            'business_name' => 'Updated Company Name',
            'address' => 'Nueva Direccion 789',
        ]);
    });

    it('can toggle OSS setting', function () {
        expect($this->activeConfig->is_oss)->toBeFalse();

        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\FiscalConfigEdit::class, ['fiscalConfig' => $this->activeConfig])
            ->set('is_oss', true)
            ->call('update')
            ->assertHasNoErrors();

        $this->activeConfig->refresh();
        expect($this->activeConfig->is_oss)->toBeTrue();
    });

    it('can toggle ROI setting', function () {
        expect($this->activeConfig->is_roi)->toBeFalse();

        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\FiscalConfigEdit::class, ['fiscalConfig' => $this->activeConfig])
            ->set('is_roi', true)
            ->call('update')
            ->assertHasNoErrors();

        $this->activeConfig->refresh();
        expect($this->activeConfig->is_roi)->toBeTrue();
    });

    it('sets flash message on update', function () {
        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\FiscalConfigEdit::class, ['fiscalConfig' => $this->activeConfig])
            ->call('update')
            ->assertSessionHas('success');
    });

});

describe('Fiscal Config Historical', function () {

    it('can view historical (closed) configs', function () {
        // Create a historical config
        $historicalConfig = CompanyFiscalConfig::create([
            'business_name' => 'Old Company',
            'tax_id' => 'B99999999',
            'legal_entity_type' => 'SL',
            'address' => 'Old Address',
            'city' => 'Old City',
            'zip_code' => '00000',
            'country_code' => 'ES',
            'currency' => 'EUR',
            'valid_from' => now()->subYears(2),
            'valid_until' => now()->subYear(),
            'is_active' => false,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.fiscal-configs.show', $historicalConfig));

        $response->assertOk();
        $response->assertSee('Old Company');
    });

    it('list shows both active and historical configs', function () {
        // Create a historical config
        CompanyFiscalConfig::create([
            'business_name' => 'Historical Company',
            'tax_id' => 'B88888888',
            'legal_entity_type' => 'SL',
            'address' => 'Historical Address',
            'city' => 'Historical City',
            'zip_code' => '11111',
            'country_code' => 'ES',
            'currency' => 'EUR',
            'valid_from' => now()->subYears(2),
            'valid_until' => now()->subYear(),
            'is_active' => false,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.fiscal-configs'));

        $response->assertOk();
        $response->assertSee('Test Company S.L.'); // Active
        $response->assertSee('Historical Company'); // Historical
    });

});
