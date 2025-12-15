<?php

declare(strict_types=1);

namespace Database\Seeders;

use AichaDigital\Larabill\Models\CompanyFiscalConfig;
use AichaDigital\Larabill\Models\UserTaxProfile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * DevelopmentSeeder - Seeds development/testing data.
 *
 * Creates:
 * - Catalog data (legal entity types)
 * - Admin and test users with fiscal profiles
 * - Company fiscal config (issuer)
 * - 100 additional users via UsersSeeder (optional)
 *
 * Only runs in local/testing environments.
 *
 * @see ADR-003 for User/Customer unification
 */
class DevelopmentSeeder extends Seeder
{
    /**
     * Seed development data.
     */
    public function run(): void
    {
        // Safety check: only run in local/testing
        if (! app()->environment(['local', 'testing'])) {
            $this->command->error('DevelopmentSeeder can only run in local/testing environments.');

            return;
        }

        $this->command->info('Seeding development data...');

        // 1. Seed catalog data first
        $this->call(LegalEntityTypesSeeder::class);

        // 2. Seed tax rates and groups
        $this->call(TaxRatesSeeder::class);
        $this->call(TaxGroupsSeeder::class);

        // 3. Seed unit measures
        $this->call(UnitMeasuresSeeder::class);

        // 4. Create core users (admin + test)
        $admin = $this->createAdminUser();
        $testUser = $this->createTestUser();

        // 5. Create Company Fiscal Config (issuer - ADR-001)
        $this->createCompanyFiscalConfig();

        // 6. Create fiscal profiles for core users (ADR-003: UserTaxProfile)
        $this->createAdminTaxProfile($admin);
        $this->createTestUserTaxProfile($testUser);

        // 7. Create additional users (100 users with fiscal profiles)
        $this->call(UsersSeeder::class);

        // Summary
        $this->command->newLine();
        $this->command->info('Development data seeded successfully!');
        $this->command->newLine();
        $this->command->line('Admin: admin@example.com / password');
        $this->command->line('Test:  test@example.com / password');
        $this->command->line('Additional users: 100 (password: password)');
    }

    /**
     * Create admin user.
     */
    private function createAdminUser(): User
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Abdelkarim Mateos',
                'password' => Hash::make('password'),
                'email_verified_at' => Carbon::create(2025, 1, 1),
            ]
        );

        $this->command->info("Admin user: {$admin->email}");

        return $admin;
    }

    /**
     * Create test user.
     */
    private function createTestUser(): User
    {
        $testUser = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'email_verified_at' => Carbon::create(2025, 1, 1),
            ]
        );

        $this->command->info("Test user: {$testUser->email}");

        return $testUser;
    }

    /**
     * Create company fiscal config (issuer).
     */
    private function createCompanyFiscalConfig(): void
    {
        if (! class_exists(CompanyFiscalConfig::class)) {
            $this->command->warn('CompanyFiscalConfig model not found, skipping...');

            return;
        }

        CompanyFiscalConfig::firstOrCreate(
            ['tax_id' => 'B12345678'],
            [
                'business_name' => 'Aicha Digital S.L.',
                'legal_entity_type' => 'SL',
                'address' => 'Calle de la Innovación, 123',
                'city' => 'Madrid',
                'state' => 'Madrid',
                'zip_code' => '28001',
                'country_code' => 'ES',
                'is_oss' => false,
                'is_roi' => true,
                'currency' => 'EUR',
                'fiscal_year_start' => '01-01',
                'valid_from' => Carbon::create(2025, 1, 1),
                'valid_until' => null,
                'is_active' => true,
                'notes' => 'Configuración inicial de desarrollo',
            ]
        );

        $this->command->info('Company fiscal config created');
    }

    /**
     * Create tax profile for admin user.
     */
    private function createAdminTaxProfile(User $admin): void
    {
        if (! class_exists(UserTaxProfile::class)) {
            $this->command->warn('UserTaxProfile model not found, skipping...');

            return;
        }

        // Check if profile already exists
        if (UserTaxProfile::where('user_id', $admin->id)->exists()) {
            $this->command->line('Admin tax profile already exists');

            return;
        }

        UserTaxProfile::create([
            'user_id' => $admin->id,
            'fiscal_name' => $admin->name,
            'tax_id' => '12345678Z',
            'legal_entity_type_code' => 'SELF_EMPLOYED',
            'address' => 'Calle Admin, 1',
            'city' => 'Madrid',
            'state' => 'Madrid',
            'zip_code' => '28001',
            'country_code' => 'ES',
            'is_company' => false,
            'is_eu_vat_registered' => false,
            'is_exempt_vat' => false,
            'valid_from' => Carbon::create(2025, 1, 1),
            'valid_until' => null,
            'is_active' => true,
            'notes' => 'Admin - Autónomo',
        ]);

        $this->command->info('Admin tax profile created (SELF_EMPLOYED)');
    }

    /**
     * Create tax profile for test user (B2B company).
     */
    private function createTestUserTaxProfile(User $testUser): void
    {
        if (! class_exists(UserTaxProfile::class)) {
            $this->command->warn('UserTaxProfile model not found, skipping...');

            return;
        }

        // Check if profile already exists
        if (UserTaxProfile::where('user_id', $testUser->id)->exists()) {
            $this->command->line('Test user tax profile already exists');

            return;
        }

        UserTaxProfile::create([
            'user_id' => $testUser->id,
            'fiscal_name' => 'Test Company S.L.',
            'tax_id' => 'B87654321',
            'legal_entity_type_code' => 'LIMITED_COMPANY',
            'address' => 'Calle Test, 999',
            'city' => 'Barcelona',
            'state' => 'Barcelona',
            'zip_code' => '08001',
            'country_code' => 'ES',
            'is_company' => true,
            'is_eu_vat_registered' => false,
            'is_exempt_vat' => false,
            'valid_from' => Carbon::create(2025, 1, 1),
            'valid_until' => null,
            'is_active' => true,
            'notes' => 'Test B2B - S.L.',
        ]);

        $this->command->info('Test user tax profile created (LIMITED_COMPANY)');
    }
}
