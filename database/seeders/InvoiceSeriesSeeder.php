<?php

declare(strict_types=1);

namespace Database\Seeders;

use AichaDigital\Larabill\Enums\InvoiceSerieType;
use AichaDigital\Larabill\Models\InvoiceSeriesControl;
use Illuminate\Database\Seeder;

/**
 * InvoiceSeriesSeeder - Creates invoice series for fiscal years 2024 and 2025.
 *
 * Series format: LF{{YY}}-{{number}}
 * Example: LF24-00001, LF25-00001
 *
 * Only creates INVOICE type series (not proforma, rectificative, etc.)
 * Proformas use timestamp-based numbers and don't need series control.
 *
 * @see LOCAL_SEEDING_STRATEGY.md for series configuration
 */
class InvoiceSeriesSeeder extends Seeder
{
    /**
     * Series configuration.
     */
    private const SERIES_CONFIG = [
        'prefix' => 'LF',
        'number_format' => 'LF{{YY}}-{{number}}',
        'start_number' => 1,
        'reset_annually' => true,
    ];

    /**
     * Fiscal years to create series for.
     */
    private const FISCAL_YEARS = [2024, 2025];

    /**
     * Seed invoice series for development.
     */
    public function run(): void
    {
        $this->command->info('Creating invoice series for fiscal years...');

        foreach (self::FISCAL_YEARS as $year) {
            $this->createSeriesForYear($year);
        }

        $this->command->info('  Invoice series created for years: '.implode(', ', self::FISCAL_YEARS));
    }

    /**
     * Create invoice series for a specific year.
     */
    private function createSeriesForYear(int $year): void
    {
        $shortYear = substr((string) $year, -2);

        InvoiceSeriesControl::firstOrCreate(
            [
                'prefix' => self::SERIES_CONFIG['prefix'],
                'serie' => InvoiceSerieType::INVOICE->value,
                'fiscal_year' => $year,
                'user_id' => null, // Global series
            ],
            [
                'fiscal_year_start' => "{$year}-01-01",
                'fiscal_year_end' => "{$year}-12-31",
                'start_number' => self::SERIES_CONFIG['start_number'],
                'last_number' => 0,
                'number_format' => self::SERIES_CONFIG['number_format'],
                'reset_annually' => self::SERIES_CONFIG['reset_annually'],
                'is_active' => true,
                'description' => "Serie principal de facturas {$year} (LF{$shortYear})",
            ]
        );

        $this->command->line("    - {$year}: LF{$shortYear}-XXXXX");
    }
}
