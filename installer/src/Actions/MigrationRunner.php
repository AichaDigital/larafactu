<?php

declare(strict_types=1);

namespace Installer\Actions;

/**
 * Runs database migrations.
 */
class MigrationRunner
{
    private CommandRunner $commandRunner;

    private array $migrationLog = [];

    public function __construct(?CommandRunner $commandRunner = null)
    {
        $this->commandRunner = $commandRunner ?? new CommandRunner;
    }

    /**
     * Run all pending migrations
     */
    public function migrate(): ActionResult
    {
        $this->migrationLog = [];

        // Run migrations
        $result = $this->commandRunner->artisan('migrate', '--force');

        if (! $result->isSuccess()) {
            return ActionResult::failure(
                __('migrations.error', ['error' => $result->getError()]),
                $result->getError()
            );
        }

        $this->migrationLog = $result->get('output', []);

        return ActionResult::success(
            __('migrations.success'),
            [
                'output' => $this->migrationLog,
                'type' => 'migrate',
            ]
        );
    }

    /**
     * Run fresh migrations (drop all tables and re-migrate)
     */
    public function fresh(): ActionResult
    {
        $this->migrationLog = [];

        // Run fresh migrations
        $result = $this->commandRunner->artisan('migrate:fresh', '--force');

        if (! $result->isSuccess()) {
            return ActionResult::failure(
                __('migrations.error', ['error' => $result->getError()]),
                $result->getError()
            );
        }

        $this->migrationLog = $result->get('output', []);

        return ActionResult::success(
            __('migrations.success'),
            [
                'output' => $this->migrationLog,
                'type' => 'fresh',
            ]
        );
    }

    /**
     * Get migration status
     */
    public function status(): ActionResult
    {
        $result = $this->commandRunner->artisan('migrate:status');

        if (! $result->isSuccess()) {
            return ActionResult::failure(
                'No se pudo obtener el estado de las migraciones',
                $result->getError()
            );
        }

        return ActionResult::success(
            'Estado de migraciones obtenido',
            [
                'output' => $result->get('output', []),
            ]
        );
    }

    /**
     * Run database seeders
     */
    public function seed(): ActionResult
    {
        $result = $this->commandRunner->artisan('db:seed', '--force');

        if (! $result->isSuccess()) {
            return ActionResult::failure(
                'Error al ejecutar seeders',
                $result->getError()
            );
        }

        return ActionResult::success(
            'Seeders ejecutados correctamente',
            [
                'output' => $result->get('output', []),
            ]
        );
    }

    /**
     * Run essential seeders that are always required for the application to work.
     *
     * These seeders populate lookup tables required for the application:
     * - LegalEntityTypesSeeder: Company form legal entity dropdown
     * - TaxRatesSeeder: Spanish VAT rates for invoicing
     * - TaxGroupsSeeder: Tax groupings (depends on TaxRates)
     * - UnitMeasuresSeeder: Unit measures for invoice lines
     *
     * Order matters: TaxGroupsSeeder depends on TaxRatesSeeder.
     */
    public function seedEssentials(): ActionResult
    {
        $essentialSeeders = [
            'Database\\Seeders\\LegalEntityTypesSeeder',
            'Database\\Seeders\\TaxRatesSeeder',
            'Database\\Seeders\\TaxGroupsSeeder',
            'Database\\Seeders\\UnitMeasuresSeeder',
        ];

        $outputs = [];
        $errors = [];

        foreach ($essentialSeeders as $seeder) {
            $result = $this->commandRunner->artisan('db:seed', '--force', '--class='.$seeder);

            if ($result->isSuccess()) {
                $outputs[] = "Seeded: {$seeder}";
            } else {
                $errors[] = "Failed: {$seeder} - ".$result->getError();
            }
        }

        if (! empty($errors)) {
            return ActionResult::failure(
                'Algunos seeders esenciales fallaron',
                implode('; ', $errors),
                ['output' => array_merge($outputs, $errors)]
            );
        }

        return ActionResult::success(
            'Seeders esenciales ejecutados correctamente',
            ['output' => $outputs]
        );
    }

    /**
     * Get migration log
     */
    public function getMigrationLog(): array
    {
        return $this->migrationLog;
    }
}
