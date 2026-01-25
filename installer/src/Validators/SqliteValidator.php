<?php

declare(strict_types=1);

namespace Installer\Validators;

/**
 * Validates SQLite database configuration.
 */
class SqliteValidator implements ValidatorInterface
{
    private string $path;

    private ?string $lastError = null;

    public function __construct(string $path = 'database/database.sqlite')
    {
        $this->path = $path;
    }

    /**
     * Create from array of config values
     */
    public static function fromArray(array $config): self
    {
        return new self(
            $config['path'] ?? 'database/database.sqlite'
        );
    }

    public function check(): ValidatorResult
    {
        // Resolve absolute path
        $absolutePath = $this->resolveAbsolutePath();

        // Check if directory exists
        $directory = dirname($absolutePath);
        if (! is_dir($directory)) {
            // Try to create directory
            if (! @mkdir($directory, 0755, true)) {
                $this->lastError = "Cannot create directory: {$directory}";

                return ValidatorResult::error(
                    __('database.sqlite_dir_error') ?? "No se puede crear el directorio: {$directory}",
                    [
                        'path' => $absolutePath,
                        'directory' => $directory,
                    ]
                );
            }
        }

        // Check directory is writable
        if (! is_writable($directory)) {
            $this->lastError = "Directory not writable: {$directory}";

            return ValidatorResult::error(
                __('database.sqlite_not_writable') ?? "El directorio no tiene permisos de escritura: {$directory}",
                [
                    'path' => $absolutePath,
                    'directory' => $directory,
                ]
            );
        }

        // If file exists, check it's valid SQLite
        if (file_exists($absolutePath)) {
            if (! is_writable($absolutePath)) {
                $this->lastError = "SQLite file not writable: {$absolutePath}";

                return ValidatorResult::error(
                    __('database.sqlite_file_not_writable') ?? 'El archivo SQLite no tiene permisos de escritura',
                    ['path' => $absolutePath]
                );
            }

            // Try to open it
            try {
                $pdo = new \PDO("sqlite:{$absolutePath}");
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                $pdo->query('SELECT 1');

                return ValidatorResult::ok(
                    __('database.sqlite_valid') ?? 'Archivo SQLite existente validado correctamente',
                    [
                        'path' => $absolutePath,
                        'exists' => true,
                        'size' => filesize($absolutePath),
                    ]
                );
            } catch (\PDOException $e) {
                $this->lastError = $e->getMessage();

                return ValidatorResult::error(
                    __('database.sqlite_invalid') ?? 'El archivo existe pero no es una base de datos SQLite válida',
                    [
                        'path' => $absolutePath,
                        'error' => $e->getMessage(),
                    ]
                );
            }
        }

        // File doesn't exist - will be created
        return ValidatorResult::ok(
            __('database.sqlite_will_create') ?? 'El archivo SQLite se creará durante la instalación',
            [
                'path' => $absolutePath,
                'exists' => false,
                'will_create' => true,
            ]
        );
    }

    public function getName(): string
    {
        return 'SQLite Database';
    }

    public function getDescription(): string
    {
        return 'SQLite file validation';
    }

    /**
     * Get the last error message
     */
    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * Get the resolved absolute path
     */
    public function getAbsolutePath(): string
    {
        return $this->resolveAbsolutePath();
    }

    /**
     * Create the SQLite database file if it doesn't exist
     */
    public function createDatabase(): ValidatorResult
    {
        $absolutePath = $this->resolveAbsolutePath();

        // Ensure directory exists
        $directory = dirname($absolutePath);
        if (! is_dir($directory)) {
            if (! @mkdir($directory, 0755, true)) {
                $this->lastError = "Cannot create directory: {$directory}";

                return ValidatorResult::error(
                    "No se puede crear el directorio: {$directory}",
                    ['path' => $absolutePath]
                );
            }
        }

        // Create empty file if it doesn't exist
        if (! file_exists($absolutePath)) {
            if (@touch($absolutePath) === false) {
                $this->lastError = "Cannot create SQLite file: {$absolutePath}";

                return ValidatorResult::error(
                    "No se puede crear el archivo SQLite: {$absolutePath}",
                    ['path' => $absolutePath]
                );
            }
        }

        // Verify it works
        try {
            $pdo = new \PDO("sqlite:{$absolutePath}");
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $pdo->query('SELECT 1');

            return ValidatorResult::ok(
                'Base de datos SQLite creada correctamente',
                ['path' => $absolutePath]
            );
        } catch (\PDOException $e) {
            $this->lastError = $e->getMessage();

            return ValidatorResult::error(
                'Error al inicializar la base de datos SQLite: '.$e->getMessage(),
                [
                    'path' => $absolutePath,
                    'error' => $e->getMessage(),
                ]
            );
        }
    }

    /**
     * Resolve the path to absolute
     */
    private function resolveAbsolutePath(): string
    {
        // If already absolute, return as is
        if (str_starts_with($this->path, '/')) {
            return $this->path;
        }

        // Resolve relative to LARAFACTU_ROOT
        $root = defined('LARAFACTU_ROOT') ? LARAFACTU_ROOT : dirname(dirname(dirname(__DIR__)));

        return rtrim($root, '/').'/'.$this->path;
    }
}
