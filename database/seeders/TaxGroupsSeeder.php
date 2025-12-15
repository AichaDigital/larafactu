<?php

declare(strict_types=1);

namespace Database\Seeders;

use AichaDigital\Larabill\Models\TaxGroup;
use AichaDigital\Larabill\Models\TaxRate;
use Illuminate\Database\Seeder;

/**
 * Seeder for Spanish tax groups.
 *
 * Creates tax groups for Spanish VAT and links them to the corresponding rates.
 * Each group represents a common taxation scenario.
 *
 * IMPORTANT: Run TaxRatesSeeder first to ensure rates exist.
 */
class TaxGroupsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $groups = $this->getSpanishTaxGroups();

        foreach ($groups as $groupData) {
            $rateName = $groupData['rate_name'];
            unset($groupData['rate_name']);

            $group = TaxGroup::updateOrCreate(
                ['name' => $groupData['name']],
                $groupData
            );

            // Link to corresponding tax rate
            $rate = TaxRate::where('name', $rateName)
                ->where('region', 'ES')
                ->first();

            if ($rate) {
                // Sync to avoid duplicates (detach all, then attach)
                $group->taxRates()->syncWithoutDetaching([
                    $rate->id => ['priority' => 1],
                ]);
            }
        }

        $this->command->info('Spanish tax groups seeded successfully.');
        $this->command->line('  - IVA General (21%)');
        $this->command->line('  - IVA Reducido (10%)');
        $this->command->line('  - IVA Superreducido (4%)');
        $this->command->line('  - Exento de IVA (0%)');
    }

    /**
     * Get Spanish tax groups.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getSpanishTaxGroups(): array
    {
        return [
            // General VAT Group - 21%
            [
                'name' => 'IVA General',
                'description' => 'Grupo de IVA general al 21% para la mayoría de bienes y servicios / General VAT group at 21% for most goods and services',
                'rate_name' => 'IVA General 21%',
            ],

            // Reduced VAT Group - 10%
            [
                'name' => 'IVA Reducido',
                'description' => 'Grupo de IVA reducido al 10% para alimentos, transporte, hostelería / Reduced VAT group at 10% for food, transport, hospitality',
                'rate_name' => 'IVA Reducido 10%',
            ],

            // Super-reduced VAT Group - 4%
            [
                'name' => 'IVA Superreducido',
                'description' => 'Grupo de IVA superreducido al 4% para productos de primera necesidad / Super-reduced VAT group at 4% for essential products',
                'rate_name' => 'IVA Superreducido 4%',
            ],

            // Exempt Group - 0%
            [
                'name' => 'Exento de IVA',
                'description' => 'Grupo para operaciones exentas de IVA (médicos, educación, seguros) / Group for VAT-exempt operations (medical, education, insurance)',
                'rate_name' => 'Exento de IVA',
            ],
        ];
    }
}
