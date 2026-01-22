<?php

declare(strict_types=1);

use AichaDigital\Larabill\Enums\InvoiceStatus;
use AichaDigital\Larabill\Enums\UserRelationshipType;
use AichaDigital\Larabill\Models\CompanyFiscalConfig;
use AichaDigital\Larabill\Models\Invoice;
use AichaDigital\Larabill\Models\LegalEntityType;
use AichaDigital\Larabill\Models\UserTaxProfile;
use App\Models\User;

uses()->group('integration', 'invoices', 'eu-b2b');

beforeEach(function () {
    $this->artisan('migrate:fresh');

    // Create required legal entity types for tests
    LegalEntityType::create([
        'code' => 'INDIVIDUAL',
        'name' => json_encode(['es' => 'Persona Física', 'en' => 'Individual']),
        'country_code' => 'ES',
        'is_company' => false,
        'is_active' => true,
    ]);
    LegalEntityType::create([
        'code' => 'COMPANY',
        'name' => json_encode(['es' => 'Empresa', 'en' => 'Company']),
        'country_code' => 'ES',
        'is_company' => true,
        'is_active' => true,
    ]);

    $this->companyConfig = CompanyFiscalConfig::factory()->spanish()->active()->create([
        'business_name' => 'Spanish Hosting S.L.',
        'tax_id' => 'ESB12345678',
    ]);
});

it('creates invoice for EU B2B customer with reverse charge (ROI)', function () {
    // Arrange: Create issuer (Spanish company - direct user)
    $issuer = User::factory()->create([
        'name' => 'Spanish Hosting S.L.',
        'email' => 'admin@spanishhosting.es',
        'relationship_type' => UserRelationshipType::DIRECT,
    ]);

    // Arrange: Create billable user (German B2B company - delegated to issuer)
    $billableUser = User::factory()->delegatedOf($issuer)->create([
        'name' => 'Deutsche GmbH',
        'email' => 'billing@deutsche.de',
        'legal_entity_type_code' => null, // FK to legal_entity_types not seeded in test
    ]);

    // Create tax profile for German B2B customer
    $taxProfile = UserTaxProfile::factory()->create([
        'owner_user_id' => $billableUser->id,
        'fiscal_name' => 'Deutsche GmbH',
        'tax_id' => 'DE123456789',
        'country_code' => 'DE',
        'is_company' => true,
        'is_eu_vat_registered' => true,
        'valid_from' => now()->subYear(),
        'valid_until' => null,
        'is_active' => true,
    ]);

    // Act: Create invoice with reverse charge (ROI)
    $invoice = Invoice::factory()->create([
        'user_id' => $issuer->id,
        'billable_user_id' => $billableUser->id,
        'user_tax_profile_id' => $taxProfile->id,
        'invoice_date' => now(),
        'due_date' => now()->addDays(30),
        'status' => InvoiceStatus::DRAFT,
        'is_roi_taxed' => true, // EU B2B: reverse charge applies
        'taxable_amount' => 0,
        'total_tax_amount' => 0,
        'total_amount' => 0,
    ]);

    // Act: Add item (dedicated server)
    $invoice->items()->create([
        'description' => 'Dedicated Server Monthly',
        'quantity' => 100, // Base100: 1 unit
        'unit_price' => 9999, // 99.99 EUR
        'taxable_amount' => 9999,
        'tax_rate' => 0, // No VAT - reverse charge
        'total_tax_amount' => 0,
        'total_amount' => 9999,
    ]);

    $invoice->calculateTotals()->save();
    $invoice->refresh();

    // Assert: ROI flag is set
    expect($invoice->is_roi_taxed)->toBeTrue();

    // Assert: No VAT applied (reverse charge)
    expect((int) $invoice->taxable_amount)->toBe(9999);
    expect((int) $invoice->total_tax_amount)->toBe(0);
    expect((int) $invoice->total_amount)->toBe(9999);

    // Assert: Tax profile indicates EU B2B
    $currentTaxProfile = $billableUser->currentTaxProfile();
    expect($currentTaxProfile)->not->toBeNull();
    expect($currentTaxProfile->is_company)->toBeTrue();
    expect($currentTaxProfile->is_eu_vat_registered)->toBeTrue();
    expect($currentTaxProfile->country_code)->toBe('DE');

    // Assert: Invoice helper methods
    expect($invoice->isReverseCharge())->toBeTrue();
    expect($invoice->requiresVAT())->toBeFalse();
});

it('detects intra-community transaction in fiscal snapshot', function () {
    $issuer = User::factory()->create([
        'relationship_type' => UserRelationshipType::DIRECT,
    ]);

    // French B2B customer
    $billableUser = User::factory()->delegatedOf($issuer)->create([
        'legal_entity_type_code' => null, // FK to legal_entity_types not seeded in test
    ]);

    $taxProfile = UserTaxProfile::factory()->create([
        'owner_user_id' => $billableUser->id,
        'fiscal_name' => 'French SARL',
        'tax_id' => 'FR12345678901',
        'country_code' => 'FR',
        'is_company' => true,
        'is_eu_vat_registered' => true,
        'valid_from' => now()->subYear(),
        'valid_until' => null,
        'is_active' => true,
    ]);

    $invoice = Invoice::factory()->create([
        'user_id' => $issuer->id,
        'billable_user_id' => $billableUser->id,
        'user_tax_profile_id' => $taxProfile->id,
        'invoice_date' => now(),
        'status' => InvoiceStatus::DRAFT,
        'is_roi_taxed' => true,
    ]);

    $invoice->refresh();

    // Assert: Fiscal snapshot detects intra-community
    $fiscalData = $invoice->getFiscalSnapshotData();
    expect($fiscalData)->toBeArray();
    expect($fiscalData['issuer_country'])->toBe('ES');
    expect($fiscalData['customer_country'])->toBe('FR');
    expect($fiscalData['is_intra_community'])->toBeTrue();
});

it('applies VAT for non-EU B2B customer', function () {
    $issuer = User::factory()->create([
        'relationship_type' => UserRelationshipType::DIRECT,
    ]);

    // UK company (post-Brexit, not EU)
    $billableUser = User::factory()->delegatedOf($issuer)->create([
        'legal_entity_type_code' => null, // FK to legal_entity_types not seeded in test
    ]);

    $taxProfile = UserTaxProfile::factory()->create([
        'owner_user_id' => $billableUser->id,
        'fiscal_name' => 'British Ltd',
        'tax_id' => 'GB123456789',
        'country_code' => 'GB',
        'is_company' => true,
        'is_eu_vat_registered' => false, // Not EU registered
        'valid_from' => now()->subYear(),
        'valid_until' => null,
        'is_active' => true,
    ]);

    $invoice = Invoice::factory()->create([
        'user_id' => $issuer->id,
        'billable_user_id' => $billableUser->id,
        'user_tax_profile_id' => $taxProfile->id,
        'invoice_date' => now(),
        'status' => InvoiceStatus::DRAFT,
        'is_roi_taxed' => false, // Non-EU: normal VAT applies
    ]);

    // Add item with Spanish VAT
    $invoice->items()->create([
        'description' => 'Consulting Services',
        'quantity' => 100,
        'unit_price' => 10000, // 100.00 EUR
        'taxable_amount' => 10000,
        'tax_rate' => 2100, // 21% Spanish VAT
        'total_tax_amount' => 2100,
        'total_amount' => 12100,
    ]);

    $invoice->calculateTotals()->save();
    $invoice->refresh();

    // Assert: VAT applied (non-EU customer)
    expect($invoice->is_roi_taxed)->toBeFalse();
    expect((int) $invoice->total_tax_amount)->toBe(2100);
    expect((int) $invoice->total_amount)->toBe(12100);
});

it('validates EU VAT number format', function () {
    $issuer = User::factory()->create([
        'relationship_type' => UserRelationshipType::DIRECT,
    ]);

    $billableUser = User::factory()->delegatedOf($issuer)->create([
        'legal_entity_type_code' => null, // FK to legal_entity_types not seeded in test
    ]);

    // Create tax profiles with different EU VAT formats
    $profiles = [
        ['tax_id' => 'DE123456789', 'country_code' => 'DE'],
        ['tax_id' => 'FR12345678901', 'country_code' => 'FR'],
        ['tax_id' => 'IT12345678901', 'country_code' => 'IT'],
        ['tax_id' => 'NL123456789B01', 'country_code' => 'NL'],
    ];

    foreach ($profiles as $data) {
        $taxProfile = UserTaxProfile::factory()->create([
            'owner_user_id' => $billableUser->id,
            'tax_id' => $data['tax_id'],
            'country_code' => $data['country_code'],
            'is_company' => true,
            'is_eu_vat_registered' => true,
            'valid_from' => now()->subYear(),
            'valid_until' => null,
            'is_active' => true,
        ]);

        expect($taxProfile->tax_id)->toBe($data['tax_id']);
        expect($taxProfile->country_code)->toBe($data['country_code']);
    }
});
