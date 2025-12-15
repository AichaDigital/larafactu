<?php

declare(strict_types=1);

namespace Database\Seeders;

use AichaDigital\Larabill\Enums\TaxType;
use AichaDigital\Larabill\Models\TaxRate;
use Illuminate\Database\Seeder;

/**
 * Seeder for Spanish VAT (IVA) tax rates.
 *
 * Creates the standard Spanish VAT rates:
 * - General: 21%
 * - Reduced: 10%
 * - Super-reduced: 4%
 * - Exempt: 0%
 *
 * Rate is stored as base-100 integer (21% = 2100).
 */
class TaxRatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rates = $this->getSpanishVatRates();

        foreach ($rates as $rate) {
            TaxRate::updateOrCreate(
                [
                    'name' => $rate['name'],
                    'region' => $rate['region'],
                ],
                $rate
            );
        }

        $this->command->info('Spanish VAT (IVA) rates seeded successfully.');
        $this->command->line('  - IVA General 21%');
        $this->command->line('  - IVA Reducido 10%');
        $this->command->line('  - IVA Superreducido 4%');
        $this->command->line('  - Exento 0%');
    }

    /**
     * Get Spanish VAT rates.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getSpanishVatRates(): array
    {
        return [
            // IVA General - 21%
            [
                'name' => 'IVA General 21%',
                'rate' => 2100, // 21.00% in base-100
                'region' => 'ES',
                'type' => TaxType::VAT,
                'special_conditions' => [
                    'es' => 'Tipo general aplicable a la mayoría de bienes y servicios',
                    'en' => 'General rate applicable to most goods and services',
                ],
            ],

            // IVA Reducido - 10%
            [
                'name' => 'IVA Reducido 10%',
                'rate' => 1000, // 10.00% in base-100
                'region' => 'ES',
                'type' => TaxType::VAT,
                'special_conditions' => [
                    'es' => 'Aplicable a alimentos, transporte, hostelería, vivienda nueva, servicios culturales',
                    'en' => 'Applicable to food, transport, hospitality, new housing, cultural services',
                ],
            ],

            // IVA Superreducido - 4%
            [
                'name' => 'IVA Superreducido 4%',
                'rate' => 400, // 4.00% in base-100
                'region' => 'ES',
                'type' => TaxType::VAT,
                'special_conditions' => [
                    'es' => 'Aplicable a productos de primera necesidad: pan, leche, huevos, frutas, verduras, libros, prensa, medicamentos',
                    'en' => 'Applicable to essential products: bread, milk, eggs, fruits, vegetables, books, press, medicines',
                ],
            ],

            // Exento - 0%
            [
                'name' => 'Exento de IVA',
                'rate' => 0,
                'region' => 'ES',
                'type' => TaxType::VAT,
                'special_conditions' => [
                    'es' => 'Operaciones exentas: servicios médicos, educación, seguros, operaciones financieras, exportaciones',
                    'en' => 'Exempt operations: medical services, education, insurance, financial operations, exports',
                ],
            ],
        ];
    }
}
