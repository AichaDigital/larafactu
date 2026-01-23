<?php

declare(strict_types=1);

namespace Installer\Steps;

use Installer\Actions\CommandRunner;
use Installer\Actions\MigrationRunner;

/**
 * Step 5: Database Migrations
 *
 * Runs Laravel migrations to create all required tables.
 */
class MigrationsStep extends AbstractStep
{
    public function getId(): string
    {
        return 'migrations';
    }

    public function validate(array $data): ValidationResult
    {
        // Check that database is configured
        if (! $this->state->has('database')) {
            return ValidationResult::invalid([
                'database' => 'Debe configurar la base de datos primero',
            ]);
        }

        return ValidationResult::valid();
    }

    public function execute(array $data): ExecutionResult
    {
        $runner = new MigrationRunner;

        // Determine migration type - handle both boolean and string "1"
        $fresh = $data['fresh'] ?? false;
        if (is_string($fresh)) {
            $fresh = $fresh === '1' || strtolower($fresh) === 'true';
        }

        // Clear config cache first
        $commandRunner = new CommandRunner;
        $commandRunner->artisan('config:clear');

        // Run migrations
        if ($fresh) {
            $result = $runner->fresh();
        } else {
            $result = $runner->migrate();
        }

        if (! $result->isSuccess()) {
            return $this->failure(
                __('migrations.error', ['error' => $result->getError()]),
                [
                    'output' => $result->get('output', []),
                    'error' => $result->getError(),
                ]
            );
        }

        // Run seeders if requested
        if ($data['run_seeders'] ?? false) {
            $seedResult = $runner->seed();

            if (! $seedResult->isSuccess()) {
                // Warning but don't fail
                $this->state->set('seeder_warning', $seedResult->getError());
            }
        }

        // Save migration status
        $this->state->set('migrations', [
            'type' => $fresh ? 'fresh' : 'migrate',
            'output' => $result->get('output', []),
            'seeded' => $data['run_seeders'] ?? false,
        ]);

        return $this->success(__('migrations.success'), [
            'output' => $result->get('output', []),
            'type' => $fresh ? 'fresh' : 'migrate',
        ]);
    }

    public function getViewData(): array
    {
        // Check if tables already exist
        $commandRunner = new CommandRunner;
        $statusResult = $commandRunner->artisan('migrate:status');

        $hasTables = false;
        if ($statusResult->isSuccess()) {
            $output = $statusResult->get('output', []);
            // If we get output, tables exist
            $hasTables = count($output) > 2;
        }

        return [
            'hasTables' => $hasTables,
            'recommendFresh' => $hasTables,
        ];
    }
}
