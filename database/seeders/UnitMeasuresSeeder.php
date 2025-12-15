<?php

declare(strict_types=1);

namespace Database\Seeders;

use AichaDigital\Larabill\Enums\UnitMeasureCategory;
use AichaDigital\Larabill\Models\UnitMeasure;
use Illuminate\Database\Seeder;

/**
 * Seeder for standard unit measures.
 *
 * Creates common unit measures used in invoicing:
 * - Count: units, items
 * - Time: hours, days
 * - Weight: kg, g
 * - Volume: liters
 * - Length: meters
 * - Area: square meters
 */
class UnitMeasuresSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = $this->getStandardUnits();

        foreach ($units as $unit) {
            UnitMeasure::updateOrCreate(
                ['code' => $unit['code']],
                $unit
            );
        }

        $this->command->info('Standard unit measures seeded successfully.');
        $this->command->line('  - Count: Unidad, Paquete');
        $this->command->line('  - Time: Hora, Día, Mes');
        $this->command->line('  - Weight: Kilogramo, Gramo');
        $this->command->line('  - Volume: Litro');
        $this->command->line('  - Length: Metro');
        $this->command->line('  - Area: Metro cuadrado');
    }

    /**
     * Get standard unit measures.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getStandardUnits(): array
    {
        return [
            // COUNT units
            [
                'code' => 'unit',
                'symbol' => 'ud.',
                'name' => 'Unidad / Unit',
                'category' => UnitMeasureCategory::COUNT,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'code' => 'pack',
                'symbol' => 'paq.',
                'name' => 'Paquete / Package',
                'category' => UnitMeasureCategory::COUNT,
                'is_active' => true,
                'sort_order' => 2,
            ],

            // TIME units
            [
                'code' => 'hour',
                'symbol' => 'h',
                'name' => 'Hora / Hour',
                'category' => UnitMeasureCategory::TIME,
                'is_active' => true,
                'sort_order' => 10,
            ],
            [
                'code' => 'day',
                'symbol' => 'd',
                'name' => 'Día / Day',
                'category' => UnitMeasureCategory::TIME,
                'is_active' => true,
                'sort_order' => 11,
            ],
            [
                'code' => 'month',
                'symbol' => 'mes',
                'name' => 'Mes / Month',
                'category' => UnitMeasureCategory::TIME,
                'is_active' => true,
                'sort_order' => 12,
            ],

            // WEIGHT units
            [
                'code' => 'kg',
                'symbol' => 'kg',
                'name' => 'Kilogramo / Kilogram',
                'category' => UnitMeasureCategory::WEIGHT,
                'is_active' => true,
                'sort_order' => 20,
            ],
            [
                'code' => 'g',
                'symbol' => 'g',
                'name' => 'Gramo / Gram',
                'category' => UnitMeasureCategory::WEIGHT,
                'is_active' => true,
                'sort_order' => 21,
            ],

            // VOLUME units
            [
                'code' => 'liter',
                'symbol' => 'L',
                'name' => 'Litro / Liter',
                'category' => UnitMeasureCategory::VOLUME,
                'is_active' => true,
                'sort_order' => 30,
            ],
            [
                'code' => 'ml',
                'symbol' => 'ml',
                'name' => 'Mililitro / Milliliter',
                'category' => UnitMeasureCategory::VOLUME,
                'is_active' => true,
                'sort_order' => 31,
            ],

            // LENGTH units
            [
                'code' => 'meter',
                'symbol' => 'm',
                'name' => 'Metro / Meter',
                'category' => UnitMeasureCategory::LENGTH,
                'is_active' => true,
                'sort_order' => 40,
            ],
            [
                'code' => 'cm',
                'symbol' => 'cm',
                'name' => 'Centímetro / Centimeter',
                'category' => UnitMeasureCategory::LENGTH,
                'is_active' => true,
                'sort_order' => 41,
            ],

            // AREA units
            [
                'code' => 'sqm',
                'symbol' => 'm²',
                'name' => 'Metro cuadrado / Square meter',
                'category' => UnitMeasureCategory::AREA,
                'is_active' => true,
                'sort_order' => 50,
            ],
        ];
    }
}
