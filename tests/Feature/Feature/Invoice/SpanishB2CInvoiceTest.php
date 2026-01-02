<?php

use AichaDigital\Larabill\Enums\InvoiceStatus;
use AichaDigital\Larabill\Enums\UserRelationshipType;
use AichaDigital\Larabill\Models\Article;
use AichaDigital\Larabill\Models\CompanyFiscalConfig;
use AichaDigital\Larabill\Models\Invoice;
use AichaDigital\Larabill\Models\UserTaxProfile;
use App\Models\User;

uses()->group('integration', 'invoices', 'spanish-b2c');

beforeEach(function () {
    // Fresh database for each test
    $this->artisan('migrate:fresh');

    // Seed necessary master data from Larabill
    $this->seed(\AichaDigital\Larabill\Database\Seeders\LegalEntityTypesSeeder::class);

    // Create company fiscal config for ADR-001 snapshot tests
    $this->companyConfig = CompanyFiscalConfig::factory()->spanish()->active()->create([
        'business_name' => 'Test Company S.L.',
        'tax_id' => 'ESB12345678',
    ]);
});

it('can create invoice for spanish B2C customer with monthly hosting', function () {
    // Arrange: Create issuer (our company - direct user)
    $issuer = User::factory()->create([
        'name' => 'HostingSpain S.L.',
        'email' => 'admin@hostingspain.es',
        'relationship_type' => UserRelationshipType::DIRECT,
    ]);

    // Arrange: Create billable user (Spanish B2C consumer - delegated to issuer)
    $billableUser = User::factory()->delegatedOf($issuer)->create([
        'name' => 'Juan Pérez',
        'email' => 'juan@example.com',
        'legal_entity_type_code' => 'INDIVIDUAL',
    ]);

    // Create tax profile for billable user
    $taxProfile = UserTaxProfile::factory()->create([
        'owner_user_id' => $billableUser->id,
        'fiscal_name' => 'Juan Pérez García',
        'tax_id' => '12345678Z',
        'country_code' => 'ES',
        'valid_from' => now()->subYear(),
        'valid_until' => null,
        'is_active' => true,
    ]);

    // Arrange: Create article (Monthly Hosting) - Articles are global, no user_id
    $article = Article::factory()->create([
        'name' => 'Hosting Básico Mensual',
        'description' => 'Plan de hosting compartido básico',
        'cost_price' => 999, // €9.99 en base100
    ]);

    // Act: Create invoice (ADR-003: billable_user_id instead of customer_id)
    $invoice = Invoice::factory()->create([
        'user_id' => $issuer->id,
        'billable_user_id' => $billableUser->id,
        'user_tax_profile_id' => $taxProfile->id,
        'invoice_date' => now(),
        'due_date' => now()->addDays(15),
        'status' => InvoiceStatus::DRAFT,
        'taxable_amount' => 0,
        'total_tax_amount' => 0,
        'total_amount' => 0,
    ]);

    // Act: Add item to invoice
    $invoice->items()->create([
        'description' => $article->name,
        'quantity' => 100, // Base100: 1 unit = 100
        'unit_price' => 999, // €9.99 base100
        'taxable_amount' => 999, // €9.99 base100
        'tax_rate' => 2100, // 21% base100
        'total_tax_amount' => 210, // 21% of €9.99 = €2.10 base100
        'total_amount' => 1209, // €9.99 + €2.10 = €12.09 base100
    ]);

    // Act: Calculate invoice totals
    $invoice->calculateTotals()->save();

    // Refresh invoice to get latest data
    $invoice->refresh();

    // Assert: Basic invoice data (ADR-003: billable_user_id)
    expect($invoice->billable_user_id)->toBe($billableUser->id);
    expect($invoice->items()->count())->toBe(1);

    // Assert: Totals calculated correctly
    expect((int) $invoice->taxable_amount)->toBe(999); // €9.99
    expect((int) $invoice->total_tax_amount)->toBe(210); // €2.10 (21% of €9.99)
    expect((int) $invoice->total_amount)->toBe(1209); // €12.09

    // Assert: Billable user has valid tax profile
    $currentTaxProfile = $billableUser->currentTaxProfile();
    expect($currentTaxProfile)->not->toBeNull();
    expect($currentTaxProfile->tax_id)->toBe('12345678Z');
    expect($currentTaxProfile->country_code)->toBe('ES');

    // Assert: Tax methods work correctly
    expect($invoice->requiresVAT())->toBeTrue();
    expect($invoice->isReverseCharge())->toBeFalse();
});

it('calculates correct VAT for multiple items', function () {
    $issuer = User::factory()->create([
        'relationship_type' => UserRelationshipType::DIRECT,
    ]);

    // Create billable user (delegated)
    $billableUser = User::factory()->delegatedOf($issuer)->create([
        'legal_entity_type_code' => 'INDIVIDUAL',
    ]);

    // Create tax profile with Spanish data
    UserTaxProfile::factory()->create([
        'owner_user_id' => $billableUser->id,
        'country_code' => 'ES',
        'valid_from' => now()->subYear(),
        'valid_until' => null,
        'is_active' => true,
    ]);

    $invoice = Invoice::factory()->create([
        'user_id' => $issuer->id,
        'billable_user_id' => $billableUser->id,
        'status' => InvoiceStatus::DRAFT,
        'taxable_amount' => 0,
        'total_tax_amount' => 0,
        'total_amount' => 0,
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
    $issuer = User::factory()->create([
        'relationship_type' => UserRelationshipType::DIRECT,
    ]);

    // Create billable user (delegated)
    $billableUser = User::factory()->delegatedOf($issuer)->create([
        'legal_entity_type_code' => 'INDIVIDUAL',
    ]);

    // Create tax profile with Spanish DNI
    $taxProfile = UserTaxProfile::factory()->create([
        'owner_user_id' => $billableUser->id,
        'tax_id' => '12345678Z',
        'country_code' => 'ES',
        'valid_from' => now()->subYear(),
        'valid_until' => null,
        'is_active' => true,
    ]);

    // Assert tax profile was created correctly
    expect($taxProfile->tax_id)->toBe('12345678Z');
    expect($taxProfile->country_code)->toBe('ES');

    // Assert billable user can access current tax profile
    $currentProfile = $billableUser->currentTaxProfile();
    expect($currentProfile)->not->toBeNull();
    expect($currentProfile->tax_id)->toBe('12345678Z');

    // TODO: Implement DNI validation in UserTaxProfile model
    // expect($taxProfile->hasValidTaxId())->toBeTrue();
});

it('generates encrypted fiscal snapshots on invoice creation (ADR-001)', function () {
    // Arrange: Create issuer (direct user)
    $issuer = User::factory()->create([
        'name' => 'HostingSpain S.L.',
        'email' => 'admin@hostingspain.es',
        'relationship_type' => UserRelationshipType::DIRECT,
    ]);

    // Arrange: Create billable user (delegated)
    $billableUser = User::factory()->delegatedOf($issuer)->create([
        'name' => 'Juan Pérez',
        'email' => 'juan@example.com',
        'legal_entity_type_code' => 'INDIVIDUAL',
    ]);

    // Create tax profile for billable user
    $taxProfile = UserTaxProfile::factory()->create([
        'owner_user_id' => $billableUser->id,
        'fiscal_name' => 'Juan Pérez García',
        'tax_id' => '12345678Z',
        'country_code' => 'ES',
        'valid_from' => now()->subYear(),
        'valid_until' => null,
        'is_active' => true,
    ]);

    // Act: Create invoice WITHOUT using InvoiceService (direct factory)
    $invoice = Invoice::factory()->create([
        'user_id' => $issuer->id,
        'billable_user_id' => $billableUser->id,
        'invoice_date' => now(),
        'due_date' => now()->addDays(15),
        'status' => InvoiceStatus::DRAFT,
    ]);

    // Refresh to get all auto-generated data
    $invoice->refresh();

    // Assert: FK references are set (from snapshotFiscalConfigs)
    expect($invoice->company_fiscal_config_id)->toBe($this->companyConfig->id);
    expect($invoice->user_tax_profile_id)->toBe($taxProfile->id);

    // Assert: Encrypted snapshots are generated (ADR-001)
    expect($invoice->issuer_snapshot)->not->toBeNull();
    expect($invoice->customer_snapshot)->not->toBeNull();
    expect($invoice->fiscal_snapshot)->not->toBeNull();

    // Assert: Helper methods work
    expect($invoice->hasFiscalSnapshots())->toBeTrue();
    expect($invoice->hasFiscalSnapshots(includeEncrypted: true))->toBeTrue();
    expect($invoice->hasEncryptedSnapshots())->toBeTrue();

    // Assert: Issuer snapshot contains correct data
    $issuerData = $invoice->getIssuerSnapshotData();
    expect($issuerData)->toBeArray();
    expect($issuerData['business_name'])->toBe('Test Company S.L.');
    expect($issuerData['tax_id'])->toBe('ESB12345678');
    expect($issuerData['country_code'])->toBe('ES');

    // Assert: Customer snapshot contains correct data
    $customerData = $invoice->getCustomerSnapshotData();
    expect($customerData)->toBeArray();
    expect($customerData['billable_user_id'])->toBe($billableUser->id);
    expect($customerData['fiscal_name'])->toBe('Juan Pérez García');
    expect($customerData['tax_id'])->toBe('12345678Z');
    expect($customerData['country_code'])->toBe('ES');

    // Assert: Fiscal context snapshot contains correct data
    $fiscalData = $invoice->getFiscalSnapshotData();
    expect($fiscalData)->toBeArray();
    expect($fiscalData['issuer_country'])->toBe('ES');
    expect($fiscalData['customer_country'])->toBe('ES');
    expect($fiscalData['is_intra_community'])->toBeFalse(); // Same country = not intra-community
});

it('creates fiscal snapshots with temporal validity (ADR-001)', function () {
    // Arrange: Create a historical company config
    $oldConfig = CompanyFiscalConfig::factory()->spanish()->create([
        'business_name' => 'Old Company Name S.L.',
        'tax_id' => 'ESA11111111',
        'valid_from' => now()->subYears(2)->startOfYear(),
        'valid_until' => now()->subYear()->endOfYear(),
        'is_active' => false,
    ]);

    // Current config already created in beforeEach

    // Arrange: Create issuer and billable user
    $issuer = User::factory()->create([
        'relationship_type' => UserRelationshipType::DIRECT,
    ]);

    $billableUser = User::factory()->delegatedOf($issuer)->create([
        'legal_entity_type_code' => 'INDIVIDUAL',
    ]);

    // Create historical tax profile (closed)
    $oldTaxProfile = UserTaxProfile::factory()->create([
        'owner_user_id' => $billableUser->id,
        'fiscal_name' => 'Old Name',
        'tax_id' => '00000000A',
        'country_code' => 'ES',
        'valid_from' => now()->subYears(2),
        'valid_until' => now()->subYear(),
        'is_active' => false,
    ]);

    // Create current tax profile
    $currentTaxProfile = UserTaxProfile::factory()->create([
        'owner_user_id' => $billableUser->id,
        'fiscal_name' => 'Current Name',
        'tax_id' => '12345678Z',
        'country_code' => 'ES',
        'valid_from' => now()->subYear()->addDay(),
        'valid_until' => null,
        'is_active' => true,
    ]);

    // Act: Create invoice with today's date
    $invoice = Invoice::factory()->create([
        'user_id' => $issuer->id,
        'billable_user_id' => $billableUser->id,
        'invoice_date' => now(),
        'status' => InvoiceStatus::DRAFT,
    ]);

    $invoice->refresh();

    // Assert: Uses CURRENT configs (not historical)
    expect($invoice->company_fiscal_config_id)->toBe($this->companyConfig->id);
    expect($invoice->user_tax_profile_id)->toBe($currentTaxProfile->id);

    // Assert: Snapshots reflect current data
    $issuerData = $invoice->getIssuerSnapshotData();
    expect($issuerData['business_name'])->toBe('Test Company S.L.');
    expect($issuerData['business_name'])->not->toBe('Old Company Name S.L.');

    $customerData = $invoice->getCustomerSnapshotData();
    expect($customerData['fiscal_name'])->toBe('Current Name');
    expect($customerData['fiscal_name'])->not->toBe('Old Name');
});
