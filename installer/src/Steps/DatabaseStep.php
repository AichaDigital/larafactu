<?php

declare(strict_types=1);

namespace Installer\Steps;

use Installer\Actions\EnvFileWriter;
use Installer\Validators\DatabaseValidator;

/**
 * Step 4: Database Configuration
 *
 * Collects database credentials and tests connection.
 */
class DatabaseStep extends AbstractStep
{
    public function getId(): string
    {
        return 'database';
    }

    public function validate(array $data): ValidationResult
    {
        $errors = [];

        // Required fields
        $required = ['host', 'port', 'database', 'username'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $errors[$field] = __('errors.required');
            }
        }

        // Port must be numeric
        if (! empty($data['port']) && ! is_numeric($data['port'])) {
            $errors['port'] = 'El puerto debe ser numérico';
        }

        if (! empty($errors)) {
            return ValidationResult::invalid($errors);
        }

        // Test connection
        $validator = DatabaseValidator::fromArray($data);
        $result = $validator->check();

        if ($result->isError()) {
            return ValidationResult::invalid([
                'connection' => $result->getMessage(),
            ]);
        }

        return ValidationResult::valid();
    }

    public function execute(array $data): ExecutionResult
    {
        // Test connection first
        $validator = DatabaseValidator::fromArray([
            'host' => $data['host'],
            'port' => (int) $data['port'],
            'database' => $data['database'],
            'username' => $data['username'],
            'password' => $data['password'] ?? '',
            'create_if_not_exists' => $data['create_if_not_exists'] ?? true,
        ]);

        $result = $validator->check();

        if ($result->isError()) {
            return $this->failure(
                __('database.connection_failed', ['error' => $result->getMessage()]),
                ['error' => $result->getMessage()]
            );
        }

        // Write to .env
        $envWriter = new EnvFileWriter;
        $envWriter->setMany([
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => $data['host'],
            'DB_PORT' => $data['port'],
            'DB_DATABASE' => $data['database'],
            'DB_USERNAME' => $data['username'],
            'DB_PASSWORD' => $data['password'] ?? '',
        ]);

        $writeResult = $envWriter->write();

        if (! $writeResult->isSuccess()) {
            return $this->failure(
                'Error al guardar configuración de base de datos',
                ['error' => $writeResult->getError()]
            );
        }

        // Save to state
        $this->state->set('database', [
            'host' => $data['host'],
            'port' => $data['port'],
            'database' => $data['database'],
            'username' => $data['username'],
            // Don't store password in state
        ]);

        return $this->success(__('database.connection_success'), [
            'mysql_version' => $result->getDetails()['mysql_version'] ?? 'unknown',
        ]);
    }

    public function getViewData(): array
    {
        // Get current values from .env if they exist
        $envWriter = new EnvFileWriter;

        return [
            'defaults' => [
                'host' => $envWriter->get('DB_HOST') ?? '127.0.0.1',
                'port' => $envWriter->get('DB_PORT') ?? '3306',
                'database' => $envWriter->get('DB_DATABASE') ?? 'larafactu',
                'username' => $envWriter->get('DB_USERNAME') ?? 'root',
            ],
        ];
    }
}
