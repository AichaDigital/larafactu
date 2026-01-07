<?php

declare(strict_types=1);

use AichaDigital\Larabill\Enums\InvoiceSerieType;
use AichaDigital\Larabill\Models\InvoiceSeriesControl;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Admin Invoice Series Tests - TDD
|--------------------------------------------------------------------------
|
| Tests for the admin invoice series control management.
| InvoiceSeriesControl manages correlative numbering for invoices.
|
*/

beforeEach(function () {
    // ADR-004: Create admin user using staff() factory state
    $this->admin = User::factory()->staff()->create();

    // Create regular user
    $this->regularUser = User::factory()->create(['email' => 'user@example.com']);

    // Create an active invoice series
    $this->activeSeries = InvoiceSeriesControl::create([
        'prefix' => 'F',
        'serie' => InvoiceSerieType::INVOICE->value,
        'fiscal_year' => now()->year,
        'fiscal_year_start' => now()->startOfYear()->format('Y-m-d'),
        'fiscal_year_end' => now()->endOfYear()->format('Y-m-d'),
        'last_number' => 0,
        'start_number' => 1,
        'reset_annually' => true,
        'number_format' => '{{prefix}}{{year}}-{{number}}',
        'is_active' => true,
        'description' => 'Serie de facturas estandar',
        'user_id' => null, // Global series
    ]);
});

describe('Invoice Series List Access', function () {

    it('allows admin to access invoice series list', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.invoice-series'));

        $response->assertOk();
        $response->assertSee('Series de Facturacion');
    });

    it('denies non-admin access to invoice series list', function () {
        $response = $this->actingAs($this->regularUser)
            ->get(route('admin.invoice-series'));

        $response->assertForbidden();
    });

    it('redirects guest to login', function () {
        $response = $this->get(route('admin.invoice-series'));

        $response->assertRedirect(route('login'));
    });

    it('displays existing invoice series', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.invoice-series'));

        $response->assertOk();
        $response->assertSee('Serie de facturas estandar');
        $response->assertSee('F');
    });

    it('shows active badge for active series', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.invoice-series'));

        $response->assertOk();
        $response->assertSee('Activa');
    });

    it('shows series type badge', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.invoice-series'));

        $response->assertOk();
        $response->assertSee('Factura');
    });

});

describe('Invoice Series Create Access', function () {

    it('allows admin to access create form', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.invoice-series.create'));

        $response->assertOk();
        $response->assertSee('Nueva Serie de Facturacion');
    });

    it('denies non-admin access to create form', function () {
        $response = $this->actingAs($this->regularUser)
            ->get(route('admin.invoice-series.create'));

        $response->assertForbidden();
    });

});

describe('Invoice Series Create Form', function () {

    it('displays required form fields', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.invoice-series.create'));

        $response->assertOk();
        $response->assertSee('Prefijo');
        $response->assertSee('Tipo de Serie');
        $response->assertSee('Ano Fiscal');
    });

    it('displays numbering options', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.invoice-series.create'));

        $response->assertOk();
        $response->assertSee('Numero inicial');
        $response->assertSee('Formato de numero');
    });

    it('displays reset annually option', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.invoice-series.create'));

        $response->assertOk();
        $response->assertSee('Reiniciar anualmente');
    });

});

describe('Invoice Series Create Validation', function () {

    it('requires prefix', function () {
        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\InvoiceSeriesCreate::class)
            ->set('prefix', '')
            ->call('create')
            ->assertHasErrors(['prefix' => 'required']);
    });

    it('requires serie type', function () {
        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\InvoiceSeriesCreate::class)
            ->set('serie', '')
            ->call('create')
            ->assertHasErrors(['serie' => 'required']);
    });

    it('requires fiscal_year', function () {
        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\InvoiceSeriesCreate::class)
            ->set('fiscal_year', '')
            ->call('create')
            ->assertHasErrors(['fiscal_year' => 'required']);
    });

    it('validates fiscal_year is numeric', function () {
        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\InvoiceSeriesCreate::class)
            ->set('fiscal_year', 'not-a-year')
            ->call('create')
            ->assertHasErrors(['fiscal_year']);
    });

    it('requires start_number', function () {
        // Nullable int property requires explicit null to trigger required validation
        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\InvoiceSeriesCreate::class)
            ->set('start_number', null)
            ->call('create')
            ->assertHasErrors(['start_number']);
    });

    it('validates start_number is positive', function () {
        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\InvoiceSeriesCreate::class)
            ->set('start_number', 0)
            ->call('create')
            ->assertHasErrors(['start_number']);
    });

    it('requires number_format', function () {
        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\InvoiceSeriesCreate::class)
            ->set('number_format', '')
            ->call('create')
            ->assertHasErrors(['number_format' => 'required']);
    });

    it('validates unique prefix per fiscal year', function () {
        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\InvoiceSeriesCreate::class)
            ->set('prefix', 'F') // Same as existing series
            ->set('serie', InvoiceSerieType::INVOICE->value)
            ->set('fiscal_year', now()->year)
            ->set('start_number', 1)
            ->set('number_format', '{{prefix}}{{year}}-{{number}}')
            ->call('create')
            ->assertHasErrors(['prefix']);
    });

});

describe('Invoice Series Create Success', function () {

    it('creates new invoice series', function () {
        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\InvoiceSeriesCreate::class)
            ->set('prefix', 'R')
            ->set('serie', InvoiceSerieType::RECTIFICATIVE->value)
            ->set('fiscal_year', now()->year)
            ->set('start_number', 1)
            ->set('number_format', '{{prefix}}{{year}}-{{number}}')
            ->set('description', 'Serie de rectificativas')
            ->call('create')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.invoice-series'));

        $this->assertDatabaseHas('invoice_series_control', [
            'prefix' => 'R',
            'serie' => InvoiceSerieType::RECTIFICATIVE->value,
            'is_active' => true,
        ]);
    });

    it('creates series with custom start number', function () {
        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\InvoiceSeriesCreate::class)
            ->set('prefix', 'S')
            ->set('serie', InvoiceSerieType::SIMPLIFIED->value)
            ->set('fiscal_year', now()->year)
            ->set('start_number', 1000)
            ->set('number_format', '{{prefix}}-{{number}}')
            ->call('create')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('invoice_series_control', [
            'prefix' => 'S',
            'start_number' => 1000,
            'last_number' => 999, // last_number = start_number - 1
        ]);
    });

    it('creates series for different fiscal year', function () {
        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\InvoiceSeriesCreate::class)
            ->set('prefix', 'F') // Same prefix but different year
            ->set('serie', InvoiceSerieType::INVOICE->value)
            ->set('fiscal_year', now()->year + 1)
            ->set('start_number', 1)
            ->set('number_format', '{{prefix}}{{year}}-{{number}}')
            ->call('create')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('invoice_series_control', [
            'prefix' => 'F',
            'fiscal_year' => now()->year + 1,
        ]);
    });

    it('sets flash message on success', function () {
        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\InvoiceSeriesCreate::class)
            ->set('prefix', 'P')
            ->set('serie', InvoiceSerieType::PROFORMA->value)
            ->set('fiscal_year', now()->year)
            ->set('start_number', 1)
            ->set('number_format', 'PRO-{{number}}')
            ->call('create')
            ->assertSessionHas('success');
    });

});

describe('Invoice Series Show', function () {

    it('allows admin to view series details', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.invoice-series.show', $this->activeSeries));

        $response->assertOk();
        $response->assertSee('Serie de facturas estandar');
        $response->assertSee('F');
    });

    it('denies non-admin access to view series', function () {
        $response = $this->actingAs($this->regularUser)
            ->get(route('admin.invoice-series.show', $this->activeSeries));

        $response->assertForbidden();
    });

    it('returns 404 for non-existent series', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.invoice-series.show', 99999));

        $response->assertNotFound();
    });

    it('displays numbering information', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.invoice-series.show', $this->activeSeries));

        $response->assertOk();
        $response->assertSee('Numero inicial');
        $response->assertSee('Ultimo numero');
    });

    it('displays fiscal year information', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.invoice-series.show', $this->activeSeries));

        $response->assertOk();
        $response->assertSee('Ano Fiscal');
    });

});

describe('Invoice Series Edit Access', function () {

    it('allows admin to access edit form', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.invoice-series.edit', $this->activeSeries));

        $response->assertOk();
        $response->assertSee('Editar Serie de Facturacion');
    });

    it('denies non-admin access to edit form', function () {
        $response = $this->actingAs($this->regularUser)
            ->get(route('admin.invoice-series.edit', $this->activeSeries));

        $response->assertForbidden();
    });

    it('returns 404 for non-existent series', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.invoice-series.edit', 99999));

        $response->assertNotFound();
    });

});

describe('Invoice Series Edit Form', function () {

    it('pre-fills form with existing data', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.invoice-series.edit', $this->activeSeries));

        $response->assertOk();
        $response->assertSee('F');
        $response->assertSee('Serie de facturas estandar');
    });

    it('displays all editable fields', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.invoice-series.edit', $this->activeSeries));

        $response->assertOk();
        $response->assertSee('Prefijo');
        $response->assertSee('Descripcion');
    });

});

describe('Invoice Series Edit Validation', function () {

    it('requires prefix on update', function () {
        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\InvoiceSeriesEdit::class, ['invoiceSeries' => $this->activeSeries])
            ->set('prefix', '')
            ->call('update')
            ->assertHasErrors(['prefix' => 'required']);
    });

    it('requires number_format on update', function () {
        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\InvoiceSeriesEdit::class, ['invoiceSeries' => $this->activeSeries])
            ->set('number_format', '')
            ->call('update')
            ->assertHasErrors(['number_format' => 'required']);
    });

});

describe('Invoice Series Edit Success', function () {

    it('updates invoice series', function () {
        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\InvoiceSeriesEdit::class, ['invoiceSeries' => $this->activeSeries])
            ->set('description', 'Descripcion actualizada')
            ->call('update')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.invoice-series'));

        $this->assertDatabaseHas('invoice_series_control', [
            'id' => $this->activeSeries->id,
            'description' => 'Descripcion actualizada',
        ]);
    });

    it('can toggle is_active', function () {
        expect($this->activeSeries->is_active)->toBeTrue();

        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\InvoiceSeriesEdit::class, ['invoiceSeries' => $this->activeSeries])
            ->set('is_active', false)
            ->call('update')
            ->assertHasNoErrors();

        $this->activeSeries->refresh();
        expect($this->activeSeries->is_active)->toBeFalse();
    });

    it('can toggle reset_annually', function () {
        expect($this->activeSeries->reset_annually)->toBeTrue();

        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\InvoiceSeriesEdit::class, ['invoiceSeries' => $this->activeSeries])
            ->set('reset_annually', false)
            ->call('update')
            ->assertHasNoErrors();

        $this->activeSeries->refresh();
        expect($this->activeSeries->reset_annually)->toBeFalse();
    });

    it('sets flash message on update', function () {
        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\InvoiceSeriesEdit::class, ['invoiceSeries' => $this->activeSeries])
            ->call('update')
            ->assertSessionHas('success');
    });

});

describe('Invoice Series Types', function () {

    it('can create proforma series', function () {
        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\InvoiceSeriesCreate::class)
            ->set('prefix', 'PRO')
            ->set('serie', InvoiceSerieType::PROFORMA->value)
            ->set('fiscal_year', now()->year)
            ->set('start_number', 1)
            ->set('number_format', 'PRO-{{number}}')
            ->call('create')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('invoice_series_control', [
            'prefix' => 'PRO',
            'serie' => InvoiceSerieType::PROFORMA->value,
        ]);
    });

    it('can create simplified invoice series', function () {
        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\InvoiceSeriesCreate::class)
            ->set('prefix', 'T')
            ->set('serie', InvoiceSerieType::SIMPLIFIED->value)
            ->set('fiscal_year', now()->year)
            ->set('start_number', 1)
            ->set('number_format', 'T{{year}}-{{number}}')
            ->call('create')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('invoice_series_control', [
            'prefix' => 'T',
            'serie' => InvoiceSerieType::SIMPLIFIED->value,
        ]);
    });

});
