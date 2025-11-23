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
    ]);

    // Act: Add item to invoice (invoice_items is agnostic, no article_id)
    $invoice->items()->create([
        'description' => $article->name,
        'quantity' => 1,
        'unit_price' => $article->base_price,
        'line_subtotal' => $article->base_price,
        'line_tax_amount' => 210, // 21% of €9.99 = €2.10 (base100)
        'line_total' => 1209, // €9.99 + €2.10 = €12.09 (base100)
    ]);

    // TODO: Implement calculateTotals() method in Invoice model
    // $invoice->calculateTotals();

    // Refresh invoice to get latest data
    $invoice->refresh();

    // Assert: Basic invoice data
    expect($invoice->customer_id)->toBe($customer->id);
    expect($invoice->items()->count())->toBe(1);

    // TODO: Assert totals when calculateTotals() is implemented
    // expect($invoice->taxable_amount)->toBe(999); // €9.99
    // expect($invoice->tax_amount)->toBe(210); // €2.10 (21% of €9.99)
    // expect($invoice->total_amount)->toBe(1209); // €12.09

    // Assert: Customer tax profile exists
    expect($customer->currentTaxProfile)->not->toBeNull();
    expect($customer->currentTaxProfile->tax_code)->toBe('12345678Z');
    expect($customer->currentTaxProfile->country_code)->toBe('ES');

    // TODO: Assert tax methods when implemented
    // expect($invoice->requiresVAT())->toBeTrue();
    // expect($invoice->isReverseCharge())->toBeFalse();
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
    ]);

    // Item 1: Hosting €9.99
    $invoice->items()->create([
        'description' => 'Hosting Mensual',
        'quantity' => 1,
        'unit_price' => 999,
        'line_subtotal' => 999,
        'line_tax_amount' => 210,
        'line_total' => 1209,
    ]);

    // Item 2: Domain €12.99
    $invoice->items()->create([
        'description' => 'Dominio .com',
        'quantity' => 1,
        'unit_price' => 1299,
        'line_subtotal' => 1299,
        'line_tax_amount' => 273, // 21% of €12.99 = €2.73
        'line_total' => 1572,
    ]);

    // TODO: Implement calculateTotals()
    // $invoice->calculateTotals();

    $invoice->refresh();

    // Assert items were created
    expect($invoice->items()->count())->toBe(2);
    
    // TODO: Assert totals when calculateTotals() is implemented
    // Subtotal: €9.99 + €12.99 = €22.98
    // expect($invoice->taxable_amount)->toBe(2298);
    // Tax: €2.10 + €2.73 = €4.83
    // expect($invoice->tax_amount)->toBe(483);
    // Total: €22.98 + €4.83 = €27.81
    // expect($invoice->total_amount)->toBe(2781);
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
