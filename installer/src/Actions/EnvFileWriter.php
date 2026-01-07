<?php

declare(strict_types=1);

namespace Installer\Actions;

/**
 * Writes and modifies .env file.
 */
class EnvFileWriter
{
    private string $envPath;

    private array $values = [];

    public function __construct(?string $envPath = null)
    {
        $this->envPath = $envPath ?? LARAFACTU_ROOT.'/.env';
    }

    /**
     * Set a single environment variable
     */
    public function set(string $key, string $value): self
    {
        $this->values[$key] = $value;

        return $this;
    }

    /**
     * Set multiple environment variables
     */
    public function setMany(array $values): self
    {
        foreach ($values as $key => $value) {
            $this->values[$key] = $value;
        }

        return $this;
    }

    /**
     * Write all pending values to .env file
     */
    public function write(): ActionResult
    {
        try {
            // Read existing content or create from example
            $content = $this->getOrCreateEnvContent();

            // Apply each value
            foreach ($this->values as $key => $value) {
                $content = $this->setEnvValue($content, $key, $value);
            }

            // Write back
            $written = file_put_contents($this->envPath, $content);

            if ($written === false) {
                return ActionResult::failure(
                    'No se pudo escribir el archivo .env',
                    'file_put_contents failed'
                );
            }

            // Clear values after successful write
            $writtenValues = $this->values;
            $this->values = [];

            return ActionResult::success(
                'Archivo .env actualizado correctamente',
                ['written' => array_keys($writtenValues)]
            );

        } catch (\Throwable $e) {
            return ActionResult::failure(
                'Error al escribir .env: '.$e->getMessage(),
                $e->getMessage()
            );
        }
    }

    /**
     * Get current value from .env
     */
    public function get(string $key): ?string
    {
        if (! file_exists($this->envPath)) {
            return null;
        }

        $content = file_get_contents($this->envPath);

        if (preg_match("/^{$key}=(.*)$/m", $content, $matches)) {
            $value = trim($matches[1]);
            // Remove quotes if present
            $value = trim($value, '"\'');

            return $value;
        }

        return null;
    }

    /**
     * Check if a key exists in .env
     */
    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    /**
     * Get or create .env content
     */
    private function getOrCreateEnvContent(): string
    {
        if (file_exists($this->envPath)) {
            return file_get_contents($this->envPath);
        }

        // Try to copy from .env.example
        $examplePath = LARAFACTU_ROOT.'/.env.example';
        if (file_exists($examplePath)) {
            $content = file_get_contents($examplePath);
            file_put_contents($this->envPath, $content);

            return $content;
        }

        // Create minimal .env
        $content = $this->getMinimalEnvContent();
        file_put_contents($this->envPath, $content);

        return $content;
    }

    /**
     * Set a value in env content string
     */
    private function setEnvValue(string $content, string $key, string $value): string
    {
        // Escape value if it contains spaces or special characters
        $escapedValue = $this->escapeValue($value);

        // Check if key exists
        $pattern = "/^{$key}=.*$/m";

        if (preg_match($pattern, $content)) {
            // Replace existing
            return preg_replace($pattern, "{$key}={$escapedValue}", $content);
        }

        // Add new line
        $content = rtrim($content)."\n{$key}={$escapedValue}\n";

        return $content;
    }

    /**
     * Escape value for .env format
     */
    private function escapeValue(string $value): string
    {
        // If value contains spaces, quotes, or special chars, wrap in quotes
        if (preg_match('/[\s"\'#]/', $value) || $value === '') {
            // Escape existing quotes
            $value = str_replace('"', '\\"', $value);

            return '"'.$value.'"';
        }

        return $value;
    }

    /**
     * Get minimal .env content for new installation
     */
    private function getMinimalEnvContent(): string
    {
        return <<<'ENV'
APP_NAME=Larafactu
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_TIMEZONE=Europe/Madrid
APP_URL=http://localhost

APP_LOCALE=es
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=es_ES

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=larafactu
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
SESSION_DRIVER=database
SESSION_LIFETIME=120

CACHE_STORE=database

# Larabill
LARABILL_USER_ID_TYPE=uuid

# Verifactu
VERIFACTU_MODE=disabled
VERIFACTU_ENVIRONMENT=sandbox

# Laravel Boost (development only)
BOOST_ENABLED=false
BOOST_BROWSER_LOGS_WATCHER=false

ENV;
    }

    /**
     * Get path to .env file
     */
    public function getPath(): string
    {
        return $this->envPath;
    }
}
