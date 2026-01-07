<?php

declare(strict_types=1);

namespace Installer\Validators;

use PDO;
use PDOException;

/**
 * Validates database connection.
 */
class DatabaseValidator implements ValidatorInterface
{
    private string $host;

    private int $port;

    private string $database;

    private string $username;

    private string $password;

    private bool $createIfNotExists;

    private ?string $lastError = null;

    public function __construct(
        string $host = 'localhost',
        int $port = 3306,
        string $database = '',
        string $username = '',
        string $password = '',
        bool $createIfNotExists = false
    ) {
        $this->host = $host;
        $this->port = $port;
        $this->database = $database;
        $this->username = $username;
        $this->password = $password;
        $this->createIfNotExists = $createIfNotExists;
    }

    /**
     * Create from array of config values
     */
    public static function fromArray(array $config): self
    {
        return new self(
            $config['host'] ?? 'localhost',
            (int) ($config['port'] ?? 3306),
            $config['database'] ?? '',
            $config['username'] ?? '',
            $config['password'] ?? '',
            (bool) ($config['create_if_not_exists'] ?? false)
        );
    }

    public function check(): ValidatorResult
    {
        // First, try to connect without database (to check server connection)
        try {
            $dsn = "mysql:host={$this->host};port={$this->port}";
            $pdo = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5,
            ]);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();

            return ValidatorResult::error(
                __('database.connection_failed', ['error' => $this->parseError($e)]),
                [
                    'host' => $this->host,
                    'port' => $this->port,
                    'error' => $e->getMessage(),
                ]
            );
        }

        // Check if database exists
        try {
            $stmt = $pdo->query('SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '.$pdo->quote($this->database));
            $exists = $stmt->fetchColumn() !== false;

            if (! $exists) {
                if ($this->createIfNotExists) {
                    // Try to create database
                    $pdo->exec("CREATE DATABASE `{$this->database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                } else {
                    return ValidatorResult::warning(
                        "La base de datos '{$this->database}' no existe. Se creará durante la instalación.",
                        [
                            'host' => $this->host,
                            'port' => $this->port,
                            'database' => $this->database,
                            'exists' => false,
                        ]
                    );
                }
            }
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();

            return ValidatorResult::error(
                'No se pudo verificar/crear la base de datos: '.$this->parseError($e),
                [
                    'host' => $this->host,
                    'port' => $this->port,
                    'database' => $this->database,
                    'error' => $e->getMessage(),
                ]
            );
        }

        // Try to connect to specific database
        try {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->database}";
            $pdo = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);

            // Get MySQL version
            $version = $pdo->query('SELECT VERSION()')->fetchColumn();

            return ValidatorResult::ok(
                __('database.connection_success'),
                [
                    'host' => $this->host,
                    'port' => $this->port,
                    'database' => $this->database,
                    'mysql_version' => $version,
                ]
            );

        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();

            return ValidatorResult::error(
                __('database.connection_failed', ['error' => $this->parseError($e)]),
                [
                    'host' => $this->host,
                    'port' => $this->port,
                    'database' => $this->database,
                    'error' => $e->getMessage(),
                ]
            );
        }
    }

    public function getName(): string
    {
        return 'Database Connection';
    }

    public function getDescription(): string
    {
        return 'MySQL database connection test';
    }

    /**
     * Get the last error message
     */
    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * Parse PDO error to user-friendly message
     */
    private function parseError(PDOException $e): string
    {
        $message = $e->getMessage();

        // Common error translations
        $translations = [
            'Access denied' => 'Acceso denegado. Verifique usuario y contraseña.',
            'Unknown database' => 'Base de datos no encontrada.',
            'Connection refused' => 'Conexión rechazada. Verifique que MySQL está ejecutándose.',
            'Host not found' => 'Servidor no encontrado. Verifique el host.',
            'Can\'t connect' => 'No se puede conectar al servidor MySQL.',
            'Unknown MySQL server host' => 'Host de MySQL desconocido.',
        ];

        foreach ($translations as $search => $translation) {
            if (stripos($message, $search) !== false) {
                return $translation;
            }
        }

        return $message;
    }

    /**
     * Get a PDO connection (for use after validation)
     */
    public function getConnection(): ?PDO
    {
        try {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->database};charset=utf8mb4";

            return new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();

            return null;
        }
    }
}
