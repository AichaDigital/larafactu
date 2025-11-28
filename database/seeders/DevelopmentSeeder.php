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

        // Seed Fiscal Settings (if CompanyFiscalConfig exists)
        if (class_exists(\AichaDigital\Larabill\Models\CompanyFiscalConfig::class)) {
            $fiscalConfig = \AichaDigital\Larabill\Models\CompanyFiscalConfig::firstOrCreate(
                ['id' => 1],
                [
                    'company_name' => 'Larafactu Development S.L.',
                    'company_vat' => 'ESB12345678',
                    'company_address' => 'Calle Test, 123',
                    'company_city' => 'Madrid',
                    'company_postal_code' => '28001',
                    'company_country' => 'ES',
                    'is_roi_operator' => true,
                    'default_tax_rate_id' => null, // Set manually if needed
                    'invoice_prefix' => 'FAC',
                    'invoice_footer' => 'Gracias por su confianza',
                ]
            );

            $this->command->info('✅ Fiscal settings configured');
        }

        $this->command->newLine();
        $this->command->info('🎉 Development data seeded successfully!');
        $this->command->newLine();
        $this->command->line('📧 Admin: admin@example.com / password');
        $this->command->line('📧 Test:  test@example.com / password');
    }
}
