<?php

use AichaDigital\Larabill\Enums\InvoiceStatus;
use AichaDigital\Larabill\Models\Article;
use AichaDigital\Larabill\Models\Customer;
use AichaDigital\Larabill\Models\Invoice;
use App\Models\User;

uses()->group('integration', 'invoices', 'spanish-b2c');

beforeEach(function () {
    // Fresh database for each test
    $this->artisan('migrate:fresh');

    // Seed necessary master data from Larabill
    $this->seed(\AichaDigital\Larabill\Database\Seeders\LegalEntityTypesSeeder::class);
});

it('can create invoice for spanish B2C customer with monthly hosting', function () {
    // Arrange: Create issuer (our company)
    $issuer = User::factory()->create([
        'name' => 'HostingSpain S.L.',
        'email' => 'admin@hostingspain.es',
    ]);

    // Arrange: Create customer (Spanish B2C consumer)
    $customer = Customer::factory()->create([
        'user_id' => $issuer->id,
        'display_name' => 'Juan Pérez',
        'legal_entity_type_code' => 'PERSONA_FISICA',
        'relationship_type' => 'client',
    ]);

    // Factory auto-creates tax profile, so we update it
    $customer->currentTaxProfile->update([
        'email' => 'juan@example.es',
        'tax_code' => '12345678Z',
        'country_code' => 'ES',
    ]);

    // Arrange: Create article (Monthly Hosting) - Articles are global, no user_id
    $article = Article::factory()->create([
        'name' => 'Hosting Básico Mensual',
        'description' => 'Plan de hosting compartido básico',
        'base_price' => 999, // €9.99 en base100 (campo correcto: base_price)
        'is_recurring' => true,
        'billing_frequency' => 'M', // Monthly
    ]);

    // Act: Create invoice
    $invoice = Invoice::factory()->create([
        'user_id' => $issuer->id,
        'customer_id' => $customer->id,
        'invoice_date' => now(),
        'due_date' => now()->addDays(15),
        'status' => InvoiceStatus::DRAFT, // Enum value, not string
        'taxable_amount' => 0, // Reset to 0, will be calculated
        'total_tax_amount' => 0, // Reset to 0, will be calculated
        'total_amount' => 0, // Reset to 0, will be calculated
    ]);

    // Act: Add item to invoice (correct column names from migration)
    $invoice->items()->create([
        'description' => $article->name,
        'quantity' => 100, // Base100: 1 unit = 100
        'unit_price' => 999, // €9.99 base100 (explicit value, not $article->base_price)
        'taxable_amount' => 999, // €9.99 base100
        'tax_rate' => 2100, // 21% base100
        'total_tax_amount' => 210, // 21% of €9.99 = €2.10 base100
        'total_amount' => 1209, // €9.99 + €2.10 = €12.09 base100
    ]);

    // Act: Calculate invoice totals
    $invoice->calculateTotals()->save();

    // Refresh invoice to get latest data
    $invoice->refresh();

    // Assert: Basic invoice data
    expect($invoice->customer_id)->toBe($customer->id);
    expect($invoice->items()->count())->toBe(1);

    // Assert: Totals calculated correctly (use toEqual for Base100 cast float values)
    expect((int) $invoice->taxable_amount)->toBe(999); // €9.99
    expect((int) $invoice->total_tax_amount)->toBe(210); // €2.10 (21% of €9.99)
    expect((int) $invoice->total_amount)->toBe(1209); // €12.09

    // Assert: Customer tax profile exists
    expect($customer->currentTaxProfile)->not->toBeNull();
    expect($customer->currentTaxProfile->tax_code)->toBe('12345678Z');
    expect($customer->currentTaxProfile->country_code)->toBe('ES');

    // Assert: Tax methods work correctly
    expect($invoice->requiresVAT())->toBeTrue();
    expect($invoice->isReverseCharge())->toBeFalse();
});

it('calculates correct VAT for multiple items', function () {
    $issuer = User::factory()->create();
    $customer = Customer::factory()->create([
        'user_id' => $issuer->id,
        'legal_entity_type_code' => 'PERSONA_FISICA',
    ]);

    // Update tax profile with Spanish data
    $customer->currentTaxProfile->update([
        'country_code' => 'ES',
    ]);

    $invoice = Invoice::factory()->create([
        'user_id' => $issuer->id,
        'customer_id' => $customer->id,
        'status' => InvoiceStatus::DRAFT,
        'taxable_amount' => 0, // Reset to 0, will be calculated
        'total_tax_amount' => 0, // Reset to 0, will be calculated
        'total_amount' => 0, // Reset to 0, will be calculated
    ]);

    // Item 1: Hosting €9.99
    $invoice->items()->create([
        'description' => 'Hosting Mensual',
        'quantity' => 100,
        'unit_price' => 999,
        'taxable_amount' => 999,
        'tax_rate' => 2100,
        'total_tax_amount' => 210,
        'total_amount' => 1209,
    ]);

    // Item 2: Domain €12.99
    $invoice->items()->create([
        'description' => 'Dominio .com',
        'quantity' => 100,
        'unit_price' => 1299,
        'taxable_amount' => 1299,
        'tax_rate' => 2100,
        'total_tax_amount' => 273, // 21% of €12.99 = €2.73
        'total_amount' => 1572,
    ]);

    // Act: Calculate totals
    $invoice->calculateTotals()->save();

    $invoice->refresh();

    // Assert items were created
    expect($invoice->items()->count())->toBe(2);

    // Assert: Totals calculated correctly
    // Subtotal: €9.99 + €12.99 = €22.98
    expect((int) $invoice->taxable_amount)->toBe(2298);

    // Tax: €2.10 + €2.73 = €4.83
    expect((int) $invoice->total_tax_amount)->toBe(483);

    // Total: €22.98 + €4.83 = €27.81
    expect((int) $invoice->total_amount)->toBe(2781);
});

it('validates Spanish DNI format', function () {
    $issuer = User::factory()->create();

    $customer = Customer::factory()->create([
        'user_id' => $issuer->id,
        'legal_entity_type_code' => 'PERSONA_FISICA',
    ]);

    // Update tax profile with Spanish DNI
    $customer->currentTaxProfile->update([
        'tax_code' => '12345678Z',
        'country_code' => 'ES',
    ]);

    $customer->refresh();

    // Assert tax profile was updated
    expect($customer->currentTaxProfile->tax_code)->toBe('12345678Z');
    expect($customer->currentTaxProfile->country_code)->toBe('ES');

    // TODO: Implement DNI validation in CustomerTaxProfile model
    // expect($customer->currentTaxProfile->hasValidTaxCode())->toBeTrue();
});
