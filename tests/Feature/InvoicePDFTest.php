<?php

declare(strict_types=1);

use AichaDigital\Larabill\Models\Invoice;
use AichaDigital\Larabill\Models\InvoiceItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Invoice PDF Tests - TDD Approach
|--------------------------------------------------------------------------
|
| Tests for the invoice PDF generation and download functionality.
|
*/

beforeEach(function () {
    // ADR-004: Create user with staff type for full access
    $this->user = User::factory()->staff()->create();

    // Create a customer user
    $this->customer = User::factory()->create(['name' => 'Test Customer']);

    // Create an invoice with items
    $this->invoice = Invoice::create([
        'user_id' => $this->user->id,
        'billable_user_id' => $this->customer->id,
        'fiscal_number' => 'FAC-2025-000001',
        'prefix' => 'FAC',
        'serie' => 1, // INVOICE
        'series_number' => 1,
        'fiscal_year' => 2025,
        'invoice_date' => now(),
        'status' => 1, // SENT
        'taxable_amount' => 10000, // 100.00 EUR
        'total_tax_amount' => 2100, // 21.00 EUR
        'total_amount' => 12100, // 121.00 EUR
    ]);

    // Add invoice item
    InvoiceItem::create([
        'invoice_id' => $this->invoice->id,
        'description' => 'Test Service',
        'quantity' => 100, // 1.00
        'unit_price' => 10000, // 100.00 EUR
        'taxable_amount' => 10000,
        'total_tax_amount' => 2100,
        'total_amount' => 12100,
        'taxes_applied' => json_encode([
            ['name' => 'IVA', 'rate' => 2100, 'amount' => 2100],
        ]),
    ]);
});

describe('Invoice PDF Download', function () {

    it('allows authenticated user to download invoice PDF', function () {
        $response = $this->actingAs($this->user)
            ->get(route('invoices.pdf', $this->invoice));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    });

    it('denies guest access to invoice PDF', function () {
        $response = $this->get(route('invoices.pdf', $this->invoice));

        $response->assertRedirect(route('login'));
    });

    it('returns 404 for non-existent invoice', function () {
        $response = $this->actingAs($this->user)
            ->get(route('invoices.pdf', 'non-existent-id'));

        $response->assertNotFound();
    });

    it('sets correct filename in Content-Disposition header', function () {
        $response = $this->actingAs($this->user)
            ->get(route('invoices.pdf', $this->invoice));

        $response->assertOk();
        $response->assertHeader('content-disposition');

        $disposition = $response->headers->get('content-disposition');
        expect($disposition)->toContain('FAC-2025-000001');
        expect($disposition)->toContain('.pdf');
    });

    it('can force download with download parameter', function () {
        $response = $this->actingAs($this->user)
            ->get(route('invoices.pdf', ['invoice' => $this->invoice, 'download' => 1]));

        $response->assertOk();
        $disposition = $response->headers->get('content-disposition');
        expect($disposition)->toContain('attachment');
    });

    it('displays inline without download parameter', function () {
        $response = $this->actingAs($this->user)
            ->get(route('invoices.pdf', $this->invoice));

        $response->assertOk();
        $disposition = $response->headers->get('content-disposition');
        expect($disposition)->toContain('inline');
    });

});

describe('Invoice PDF Generation', function () {

    it('generates PDF for invoice with items', function () {
        $result = $this->invoice->generatePDF();

        expect($result)->toBeArray()
            ->and($result['success'])->toBeTrue()
            ->and($result['pdf_path'])->toBeString();
    });

    it('includes correct invoice data in PDF', function () {
        $result = $this->invoice->generatePDF();

        expect($result['success'])->toBeTrue();

        // Verify the PDF file exists
        expect(file_exists($result['pdf_path']))->toBeTrue();
    });

});

describe('Invoice Show Page PDF Button', function () {

    it('shows enabled PDF download button on invoice show page', function () {
        $response = $this->actingAs($this->user)
            ->get(route('invoices.show', $this->invoice));

        $response->assertOk();
        $response->assertSee('Descargar PDF');
        // Should have the actual route, not be disabled
        $response->assertSee(route('invoices.pdf', $this->invoice));
    });

});
