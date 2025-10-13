<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use AichaDigital\Larabill\Models\{
    Invoice,
    InvoiceItem,
    FiscalSettings
};
use Illuminate\Support\Facades\DB;

class CompleteDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test user (preserved)
        $testUser = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Local Test User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Spanish users (10 users - majority)
        $spanishUsers = $this->createSpanishUsers(10);

        // EU Intra-community users with ROI (2 users)
        $euRoiUsers = $this->createEuRoiUsers(2);

        // EU Intra-community users without ROI (2 users)
        $euNonRoiUsers = $this->createEuNonRoiUsers(2);

        // Non-EU users (1 user)
        $nonEuUsers = $this->createNonEuUsers(1);

        // Merge all users
        $allUsers = collect([$testUser])
            ->merge($spanishUsers)
            ->merge($euRoiUsers)
            ->merge($euNonRoiUsers)
            ->merge($nonEuUsers);

        // Create invoices for each user (2-8 invoices per user)
        $allUsers->each(function ($user) {
            $this->createInvoicesForUser($user, rand(2, 8));
        });

        $this->command->info('Demo seeding completed successfully!');
        $this->command->info('Created: ' . $allUsers->count() . ' users');
        $this->command->info('Created: ' . Invoice::count() . ' invoices');
        $this->command->info('Created: ' . InvoiceItem::count() . ' invoice items');
    }

    /**
     * Create Spanish users
     */
    private function createSpanishUsers(int $count): array
    {
        $users = [];
        $spanishCities = ['Madrid', 'Barcelona', 'Valencia', 'Sevilla', 'Zaragoza', 'Málaga'];

        for ($i = 0; $i < $count; $i++) {
            $user = User::create([
                'name' => fake()->company() . ' S.L.',
                'email' => fake()->unique()->companyEmail(),
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]);

            // Create tax profile (using direct DB insert due to table name mismatch in package)
            DB::table('user_tax_infos')->insert([
                'user_id' => $user->getRawOriginal('id'), // Get raw binary UUID
                'is_current' => true,
                'tax_id' => 'ES' . fake()->numerify('B########'),
                'company_name' => $user->name,
                'address' => fake()->streetAddress(),
                'city' => fake()->randomElement($spanishCities),
                'postal_code' => fake()->numerify('#####'),
                'country' => 'ES',
                'phone' => fake()->numerify('+34 9## ### ###'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create fiscal settings
            FiscalSettings::create([
                'user_id' => $user->getRawOriginal('id'), // Get raw binary UUID
                'fiscal_year' => now()->year,
                'is_oss' => fake()->boolean(20), // 20% chance OSS
                'is_roi' => false,
                'current_eu_sales_amount' => 0,
                'eu_sales_threshold' => 1000000, // €10,000 in base-100
                'apply_destination_iva' => false,
            ]);

            $users[] = $user;
        }

        return $users;
    }

    /**
     * Create EU Intra-community users with ROI
     */
    private function createEuRoiUsers(int $count): array
    {
        $users = [];
        $euCountries = [
            'DE' => ['suffix' => 'GmbH', 'cities' => ['Berlin', 'Munich', 'Hamburg'], 'prefix' => 'DE'],
            'FR' => ['suffix' => 'SAS', 'cities' => ['Paris', 'Lyon', 'Marseille'], 'prefix' => 'FR'],
        ];

        $countryKeys = array_keys($euCountries);

        for ($i = 0; $i < $count; $i++) {
            $countryCode = $countryKeys[$i % count($countryKeys)];
            $countryData = $euCountries[$countryCode];

            $user = User::create([
                'name' => fake()->company() . ' ' . $countryData['suffix'],
                'email' => fake()->unique()->companyEmail(),
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]);

            // Create tax profile (using direct DB insert due to table name mismatch in package)
            DB::table('user_tax_infos')->insert([
                'user_id' => $user->getRawOriginal('id'), // Get raw binary UUID
                'is_current' => true,
                'tax_id' => $countryData['prefix'] . fake()->numerify('###########'),
                'company_name' => $user->name,
                'address' => fake()->streetAddress(),
                'city' => fake()->randomElement($countryData['cities']),
                'postal_code' => fake()->postcode(),
                'country' => $countryCode,
                'phone' => fake()->phoneNumber(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create fiscal settings (ROI)
            FiscalSettings::create([
                'user_id' => $user->getRawOriginal('id'), // Get raw binary UUID
                'fiscal_year' => now()->year,
                'is_oss' => false,
                'is_roi' => true, // ROI enabled
                'current_eu_sales_amount' => 0,
                'eu_sales_threshold' => 1000000, // €10,000 in base-100
                'apply_destination_iva' => false,
            ]);

            $users[] = $user;
        }

        return $users;
    }

    /**
     * Create EU Intra-community users without ROI
     */
    private function createEuNonRoiUsers(int $count): array
    {
        $users = [];
        $euCountries = [
            'IT' => ['suffix' => 'S.r.l.', 'cities' => ['Rome', 'Milan', 'Naples'], 'prefix' => 'IT'],
            'PT' => ['suffix' => 'Lda.', 'cities' => ['Lisbon', 'Porto', 'Braga'], 'prefix' => 'PT'],
        ];

        $countryKeys = array_keys($euCountries);

        for ($i = 0; $i < $count; $i++) {
            $countryCode = $countryKeys[$i % count($countryKeys)];
            $countryData = $euCountries[$countryCode];

            $user = User::create([
                'name' => fake()->company() . ' ' . $countryData['suffix'],
                'email' => fake()->unique()->companyEmail(),
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]);

            // Create tax profile (using direct DB insert due to table name mismatch in package)
            DB::table('user_tax_infos')->insert([
                'user_id' => $user->getRawOriginal('id'), // Get raw binary UUID
                'is_current' => true,
                'tax_id' => $countryData['prefix'] . fake()->numerify('###########'),
                'company_name' => $user->name,
                'address' => fake()->streetAddress(),
                'city' => fake()->randomElement($countryData['cities']),
                'postal_code' => fake()->postcode(),
                'country' => $countryCode,
                'phone' => fake()->phoneNumber(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create fiscal settings (Non-ROI)
            FiscalSettings::create([
                'user_id' => $user->getRawOriginal('id'), // Get raw binary UUID
                'fiscal_year' => now()->year,
                'is_oss' => true, // OSS/MOSS enabled
                'is_roi' => false,
                'current_eu_sales_amount' => 0,
                'eu_sales_threshold' => 1000000, // €10,000 in base-100
                'apply_destination_iva' => false,
            ]);

            $users[] = $user;
        }

        return $users;
    }

    /**
     * Create Non-EU users
     */
    private function createNonEuUsers(int $count): array
    {
        $users = [];
        $nonEuCountries = [
            'US' => ['suffix' => 'Inc.', 'cities' => ['New York', 'Los Angeles', 'Chicago']],
            'GB' => ['suffix' => 'Ltd.', 'cities' => ['London', 'Manchester', 'Birmingham']],
        ];

        $countryKeys = array_keys($nonEuCountries);

        for ($i = 0; $i < $count; $i++) {
            $countryCode = $countryKeys[$i % count($countryKeys)];
            $countryData = $nonEuCountries[$countryCode];

            $user = User::create([
                'name' => fake()->company() . ' ' . $countryData['suffix'],
                'email' => fake()->unique()->companyEmail(),
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]);

            // Create tax profile (using direct DB insert due to table name mismatch in package)
            DB::table('user_tax_infos')->insert([
                'user_id' => $user->getRawOriginal('id'), // Get raw binary UUID
                'is_current' => true,
                'tax_id' => fake()->numerify('##########'),
                'company_name' => $user->name,
                'address' => fake()->streetAddress(),
                'city' => fake()->randomElement($countryData['cities']),
                'postal_code' => fake()->postcode(),
                'country' => $countryCode,
                'phone' => fake()->phoneNumber(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create fiscal settings
            FiscalSettings::create([
                'user_id' => $user->getRawOriginal('id'), // Get raw binary UUID
                'fiscal_year' => now()->year,
                'is_oss' => false,
                'is_roi' => false,
                'current_eu_sales_amount' => 0,
                'eu_sales_threshold' => 1000000, // €10,000 in base-100
                'apply_destination_iva' => false,
            ]);

            $users[] = $user;
        }

        return $users;
    }

    /**
     * Create invoices for a user
     */
    private function createInvoicesForUser(User $user, int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            $isPaid = fake()->boolean(40);
            $isImmutable = fake()->boolean(70);

            $invoice = Invoice::create([
                'number' => 'FAC-' . now()->year . '-' . str_pad(Invoice::count() + 1, 6, '0', STR_PAD_LEFT),
                'type' => fake()->randomElement(['invoice', 'proforma']),
                'status' => fake()->randomElement(['draft', 'sent', 'paid', 'overdue']),
                'user_id' => $user->getRawOriginal('id'), // Get raw binary UUID
                'is_immutable' => $isImmutable,
                'immutable_at' => $isImmutable ? now()->subDays(rand(1, 30)) : null,
                'subtotal' => 0, // Will be calculated from items
                'tax_amount' => 0, // Will be calculated from items
                'total' => 0, // Will be calculated from items
                'due_date' => now()->addDays(30),
                'paid_at' => $isPaid ? now()->subDays(rand(1, 15)) : null,
            ]);

            // Create invoice items (1-3 items per invoice)
            $this->createInvoiceItems($invoice, rand(1, 3));

            // Update invoice totals
            $this->updateInvoiceTotals($invoice);
        }
    }

    /**
     * Create invoice items
     */
    private function createInvoiceItems(Invoice $invoice, int $count): void
    {
        $products = [
            'Professional Services',
            'Consulting',
            'Software Development',
            'Web Design',
            'Digital Marketing',
            'SEO Optimization',
            'Content Creation',
            'Technical Support',
        ];

        for ($i = 0; $i < $count; $i++) {
            $quantity = fake()->randomFloat(2, 1, 10);
            $unitPrice = fake()->randomFloat(2, 50, 500);
            $taxRate = fake()->randomElement([21.00, 10.00, 4.00, 0.00]);

            $subtotal = $quantity * $unitPrice;
            $taxAmount = round($subtotal * ($taxRate / 100), 2);
            $total = $subtotal + $taxAmount;

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => fake()->randomElement($products),
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $subtotal,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'total' => $total,
            ]);
        }
    }

    /**
     * Update invoice totals from items
     */
    private function updateInvoiceTotals(Invoice $invoice): void
    {
        $items = $invoice->items;

        $invoice->update([
            'subtotal' => $items->sum('subtotal'),
            'tax_amount' => $items->sum('tax_amount'),
            'total' => $items->sum('total'),
        ]);
    }
}
