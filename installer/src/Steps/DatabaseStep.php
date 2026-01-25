<?php

declare(strict_types=1);

namespace Installer\Steps;

use Installer\Actions\EnvFileWriter;
use Installer\Environment\EnvironmentDetector;
use Installer\I18n\Translator;
use Installer\Session\InstallState;
use Installer\Validators\DatabaseValidator;
use Installer\Validators\SqliteValidator;

/**
 * Step 4: Database Configuration
 *
 * Supports multiple database types:
 * - docker: Predefined MySQL from docker-compose.yml
 * - mysql: Custom MySQL configuration
 * - sqlite: File-based SQLite database
 */
class DatabaseStep extends AbstractStep
{
    private EnvironmentDetector $environmentDetector;

    public function __construct(?InstallState $state = null, ?Translator $translator = null)
    {
        parent::__construct($state, $translator);
        $this->environmentDetector = new EnvironmentDetector;
    }

    public function getId(): string
    {
        return 'database';
    }

    public function validate(array $data): ValidationResult
    {
        $dbType = $data['db_type'] ?? 'mysql';

        return match ($dbType) {
            'docker' => $this->validateDocker($data),
            'sqlite' => $this->validateSqlite($data),
            'mysql' => $this->validateMysql($data),
            default => ValidationResult::invalid(['db_type' => 'Tipo de base de datos no válido']),
        };
    }

    public function execute(array $data): ExecutionResult
    {
        $dbType = $data['db_type'] ?? 'mysql';

        return match ($dbType) {
            'docker' => $this->executeDocker($data),
            'sqlite' => $this->executeSqlite($data),
            'mysql' => $this->executeMysql($data),
            default => $this->failure('Tipo de base de datos no válido'),
        };
    }

    public function getViewData(): array
    {
        $envWriter = new EnvFileWriter;
        $dbOptions = $this->environmentDetector->getAvailableDatabaseOptions();

        // Determine default selection
        $defaultDbType = 'mysql';
        if ($this->environmentDetector->isDocker()) {
            $defaultDbType = 'docker';
        }

        return [
            'defaults' => [
                'host' => $envWriter->get('DB_HOST') ?? '127.0.0.1',
                'port' => $envWriter->get('DB_PORT') ?? '3306',
                'database' => $envWriter->get('DB_DATABASE') ?? 'larafactu',
                'username' => $envWriter->get('DB_USERNAME') ?? 'root',
                'sqlite_path' => 'database/database.sqlite',
            ],
            'docker_available' => $this->environmentDetector->isDocker(),
            'docker_config' => $this->environmentDetector->getDockerMysqlConfig(),
            'db_options' => $dbOptions,
            'default_db_type' => $defaultDbType,
            'environment_info' => $this->environmentDetector->getEnvironmentInfo(),
        ];
    }

    /**
     * Validate Docker MySQL (no validation needed - predefined values)
     */
    private function validateDocker(array $data): ValidationResult
    {
        // Docker MySQL uses predefined values, no user input to validate
        // Just check that Docker mode is actually available
        if (! $this->environmentDetector->isDocker()) {
            return ValidationResult::invalid([
                'db_type' => 'Docker MySQL no está disponible en este entorno',
            ]);
        }

        return ValidationResult::valid();
    }

    /**
     * Validate SQLite configuration
     */
    private function validateSqlite(array $data): ValidationResult
    {
        $path = $data['sqlite_path'] ?? 'database/database.sqlite';

        if (empty($path)) {
            return ValidationResult::invalid([
                'sqlite_path' => __('errors.required'),
            ]);
        }

        $validator = SqliteValidator::fromArray(['path' => $path]);
        $result = $validator->check();

        if ($result->isError()) {
            return ValidationResult::invalid([
                'sqlite_path' => $result->getMessage(),
            ]);
        }

        return ValidationResult::valid();
    }

    /**
     * Validate custom MySQL configuration
     */
    private function validateMysql(array $data): ValidationResult
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

    /**
     * Execute Docker MySQL configuration
     */
    private function executeDocker(array $data): ExecutionResult
    {
        $dockerConfig = $this->environmentDetector->getDockerMysqlConfig();

        if ($dockerConfig === null) {
            return $this->failure('Docker MySQL no está disponible');
        }

        // Determine ID type
        $idType = $data['id_type'] ?? 'uuid';
        if (! in_array($idType, ['uuid', 'integer'], true)) {
            $idType = 'uuid';
        }

        // Write to .env with Docker values
        $envWriter = new EnvFileWriter;
        $envWriter->setMany([
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => $dockerConfig['host'],
            'DB_PORT' => (string) $dockerConfig['port'],
            'DB_DATABASE' => $dockerConfig['database'],
            'DB_USERNAME' => $dockerConfig['username'],
            'DB_PASSWORD' => $dockerConfig['password'],
            'LARABILL_USER_ID_TYPE' => $idType,
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
            'type' => 'docker',
            'host' => $dockerConfig['host'],
            'port' => $dockerConfig['port'],
            'database' => $dockerConfig['database'],
            'username' => $dockerConfig['username'],
            'id_type' => $idType,
        ]);

        return $this->success(__('database.docker_configured') ?? 'Docker MySQL configurado correctamente', [
            'db_type' => 'docker',
            'database' => $dockerConfig['database'],
            'id_type' => $idType,
        ]);
    }

    /**
     * Execute SQLite configuration
     */
    private function executeSqlite(array $data): ExecutionResult
    {
        $path = $data['sqlite_path'] ?? 'database/database.sqlite';

        // Determine ID type
        $idType = $data['id_type'] ?? 'uuid';
        if (! in_array($idType, ['uuid', 'integer'], true)) {
            $idType = 'uuid';
        }

        // Validate and create database
        $validator = SqliteValidator::fromArray(['path' => $path]);
        $result = $validator->createDatabase();

        if ($result->isError()) {
            return $this->failure(
                __('database.sqlite_create_failed') ?? 'Error al crear base de datos SQLite',
                ['error' => $result->getMessage()]
            );
        }

        // Write to .env
        $envWriter = new EnvFileWriter;
        $envWriter->setMany([
            'DB_CONNECTION' => 'sqlite',
            'DB_DATABASE' => $validator->getAbsolutePath(),
            // Clear MySQL-specific values
            'DB_HOST' => '',
            'DB_PORT' => '',
            'DB_USERNAME' => '',
            'DB_PASSWORD' => '',
            'LARABILL_USER_ID_TYPE' => $idType,
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
            'type' => 'sqlite',
            'path' => $validator->getAbsolutePath(),
            'id_type' => $idType,
        ]);

        return $this->success(__('database.sqlite_configured') ?? 'SQLite configurado correctamente', [
            'db_type' => 'sqlite',
            'path' => $validator->getAbsolutePath(),
            'id_type' => $idType,
        ]);
    }

    /**
     * Execute custom MySQL configuration
     */
    private function executeMysql(array $data): ExecutionResult
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

        // Determine ID type
        $idType = $data['id_type'] ?? 'uuid';
        if (! in_array($idType, ['uuid', 'integer'], true)) {
            $idType = 'uuid';
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
            'LARABILL_USER_ID_TYPE' => $idType,
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
            'type' => 'mysql',
            'host' => $data['host'],
            'port' => $data['port'],
            'database' => $data['database'],
            'username' => $data['username'],
            'id_type' => $idType,
            // Don't store password in state
        ]);

        return $this->success(__('database.connection_success'), [
            'db_type' => 'mysql',
            'mysql_version' => $result->getDetails()['mysql_version'] ?? 'unknown',
            'id_type' => $idType,
        ]);
    }
}
