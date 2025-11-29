<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DevelopmentSeeder extends Seeder
{
    /**
     * Seed development data (users and fiscal settings).
     * Only run in local/testing environments.
     */
    public function run(): void
    {
        // Safety check: only run in local/testing
        if (! app()->environment(['local', 'testing'])) {
            $this->command->error('❌ DevelopmentSeeder can only run in local/testing environments.');

            return;
        }

        $this->command->info('🌱 Seeding development data...');

        // Create Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Abdelkarim Mateos',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $this->command->info("✅ Admin user: {$admin->email} (password: password)");

        // Create Test User
        $testUser = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $this->command->info("✅ Test user: {$testUser->email} (password: password)");

        // TODO ADR-001: Seed Company Fiscal Config y Customer Fiscal Data
        // Deshabilitado temporalmente hasta resolver carga de migraciones de Larabill
        /*
        // Seed Company Fiscal Config (ADR-001)
        if (class_exists(\AichaDigital\Larabill\Models\CompanyFiscalConfig::class)) {
            $companyConfig = \AichaDigital\Larabill\Models\CompanyFiscalConfig::firstOrCreate(
                ['tax_id' => 'ESB12345678'],
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
                    'valid_from' => now()->startOfYear(),
                    'valid_until' => null,
                    'is_active' => true,
                    'notes' => 'Configuración inicial de desarrollo',
                ]
            );

            $this->command->info('✅ Company fiscal config created');
        }

        // Seed Customer Fiscal Data for Admin (ADR-001)
        if (class_exists(\AichaDigital\Larabill\Models\CustomerFiscalData::class)) {
            $adminFiscal = \AichaDigital\Larabill\Models\CustomerFiscalData::firstOrCreate(
                ['user_id' => $admin->id, 'is_active' => true],
                [
                    'fiscal_name' => $admin->name,
                    'tax_id' => '12345678A',
                    'legal_entity_type' => 'Particular',
                    'address' => 'Calle Admin, 1',
                    'city' => 'Madrid',
                    'state' => 'Madrid',
                    'zip_code' => '28001',
                    'country_code' => 'ES',
                    'is_company' => false,
                    'is_eu_vat_registered' => false,
                    'is_exempt_vat' => false,
                    'valid_from' => now()->startOfYear(),
                    'valid_until' => null,
                    'notes' => 'Datos fiscales iniciales',
                ]
            );

            $this->command->info('✅ Admin fiscal data created');

            // Seed Customer Fiscal Data for Test User (B2B)
            $testFiscal = \AichaDigital\Larabill\Models\CustomerFiscalData::firstOrCreate(
                ['user_id' => $testUser->id, 'is_active' => true],
                [
                    'fiscal_name' => 'Test Company S.L.',
                    'tax_id' => 'ESB87654321',
                    'legal_entity_type' => 'SL',
                    'address' => 'Calle Test, 999',
                    'city' => 'Barcelona',
                    'state' => 'Barcelona',
                    'zip_code' => '08001',
                    'country_code' => 'ES',
                    'is_company' => true,
                    'is_eu_vat_registered' => false,
                    'is_exempt_vat' => false,
                    'valid_from' => now()->startOfYear(),
                    'valid_until' => null,
                    'notes' => 'Empresa de pruebas B2B',
                ]
            );

            $this->command->info('✅ Test user fiscal data created (B2B)');
        }
        */

        $this->command->newLine();
        $this->command->info('🎉 Development data seeded successfully!');
        $this->command->newLine();
        $this->command->line('📧 Admin: admin@example.com / password');
        $this->command->line('📧 Test:  test@example.com / password');
    }
}
