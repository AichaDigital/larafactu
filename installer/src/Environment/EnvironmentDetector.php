<?php

declare(strict_types=1);

namespace Installer\Environment;

/**
 * Detects the installation environment and provides
 * configuration options based on the detected mode.
 */
class EnvironmentDetector
{
    public const MODE_DOCKER = 'docker';

    public const MODE_SERVER = 'server';

    public const MODE_LOCAL = 'local';

    private ?string $detectedMode = null;

    /**
     * Docker MySQL preset configuration from docker-compose.yml
     */
    private const DOCKER_MYSQL_CONFIG = [
        'host' => 'mysql',
        'port' => 3306,
        'database' => 'larafactu_test',
        'username' => 'larafactu',
        'password' => 'larafactu',
    ];

    /**
     * Detect the current installation mode.
     */
    public function detect(): string
    {
        if ($this->detectedMode !== null) {
            return $this->detectedMode;
        }

        // Priority 1: Explicit environment variable
        $installerEnv = getenv('INSTALLER_ENV');
        if ($installerEnv === 'testing' || $installerEnv === 'docker') {
            $this->detectedMode = self::MODE_DOCKER;

            return $this->detectedMode;
        }

        // Priority 2: Docker path detection
        if (file_exists('/var/www/larafactu/artisan')) {
            $this->detectedMode = self::MODE_DOCKER;

            return $this->detectedMode;
        }

        // Priority 3: Check if running on a typical server path
        $larafactuRoot = defined('LARAFACTU_ROOT') ? LARAFACTU_ROOT : '';
        if (
            str_starts_with($larafactuRoot, '/var/www/') ||
            str_starts_with($larafactuRoot, '/home/') ||
            str_starts_with($larafactuRoot, '/srv/')
        ) {
            $this->detectedMode = self::MODE_SERVER;

            return $this->detectedMode;
        }

        // Default to local development
        $this->detectedMode = self::MODE_LOCAL;

        return $this->detectedMode;
    }

    /**
     * Check if running in Docker environment.
     */
    public function isDocker(): bool
    {
        return $this->detect() === self::MODE_DOCKER;
    }

    /**
     * Check if running on a server (not local development).
     */
    public function isServer(): bool
    {
        return $this->detect() === self::MODE_SERVER;
    }

    /**
     * Check if running in local development.
     */
    public function isLocal(): bool
    {
        return $this->detect() === self::MODE_LOCAL;
    }

    /**
     * Get Docker MySQL preset configuration.
     * Only available when Docker mode is detected.
     *
     * @return array|null Preset config or null if not in Docker
     */
    public function getDockerMysqlConfig(): ?array
    {
        if (! $this->isDocker()) {
            return null;
        }

        return self::DOCKER_MYSQL_CONFIG;
    }

    /**
     * Get available database options based on detected environment.
     *
     * @return array List of available database type options
     */
    public function getAvailableDatabaseOptions(): array
    {
        $options = [];

        // Docker MySQL option (only in Docker mode)
        if ($this->isDocker()) {
            $options['docker'] = [
                'label' => 'MySQL (Docker)',
                'description' => 'Predefined Docker MySQL: mysql:3306, larafactu_test',
                'recommended' => true,
                'config' => self::DOCKER_MYSQL_CONFIG,
            ];
        }

        // Custom MySQL (always available)
        $options['mysql'] = [
            'label' => 'MySQL (Custom)',
            'description' => 'Configure your own MySQL server',
            'recommended' => ! $this->isDocker(),
            'config' => null,
        ];

        // SQLite (always available)
        $options['sqlite'] = [
            'label' => 'SQLite',
            'description' => 'Simple file-based database',
            'recommended' => false,
            'config' => [
                'path' => 'database/database.sqlite',
            ],
        ];

        return $options;
    }

    /**
     * Check if a step should be skipped based on environment.
     */
    public function shouldSkipStep(string $stepId): bool
    {
        // Currently no steps are skipped based on environment
        // The database step shows different options instead of skipping
        return false;
    }

    /**
     * Get human-readable mode name.
     */
    public function getModeName(): string
    {
        return match ($this->detect()) {
            self::MODE_DOCKER => 'Docker Testing',
            self::MODE_SERVER => 'Server',
            self::MODE_LOCAL => 'Local Development',
        };
    }

    /**
     * Get environment information for debugging/display.
     */
    public function getEnvironmentInfo(): array
    {
        return [
            'mode' => $this->detect(),
            'mode_name' => $this->getModeName(),
            'installer_env' => getenv('INSTALLER_ENV') ?: 'not set',
            'larafactu_root' => defined('LARAFACTU_ROOT') ? LARAFACTU_ROOT : 'not defined',
            'docker_available' => $this->isDocker(),
        ];
    }
}
