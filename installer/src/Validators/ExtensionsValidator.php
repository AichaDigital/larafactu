<?php

declare(strict_types=1);

namespace Installer\Validators;

/**
 * Validates required PHP extensions.
 */
class ExtensionsValidator implements ValidatorInterface
{
    /**
     * Required extensions (installation will fail without these)
     */
    private const REQUIRED_EXTENSIONS = [
        'pdo_mysql' => 'MySQL database connection',
        'openssl' => 'Encryption and security',
        'mbstring' => 'Multi-byte string handling',
        'tokenizer' => 'PHP tokenizer',
        'xml' => 'XML parsing',
        'ctype' => 'Character type checking',
        'json' => 'JSON encoding/decoding',
        'bcmath' => 'Arbitrary precision mathematics',
        'fileinfo' => 'File information',
        'curl' => 'HTTP requests',
    ];

    /**
     * Optional extensions (warnings only)
     */
    private const OPTIONAL_EXTENSIONS = [
        'intl' => 'Internationalization',
        'gd' => 'Image processing',
        'zip' => 'ZIP file handling',
        'redis' => 'Redis cache support',
        'opcache' => 'PHP opcode caching',
    ];

    public function check(): ValidatorResult
    {
        $missing = [];
        $warnings = [];
        $loaded = [];

        // Check required extensions
        foreach (self::REQUIRED_EXTENSIONS as $ext => $description) {
            if (extension_loaded($ext)) {
                $loaded[$ext] = [
                    'name' => $ext,
                    'description' => $description,
                    'status' => 'ok',
                ];
            } else {
                $missing[$ext] = [
                    'name' => $ext,
                    'description' => $description,
                    'status' => 'error',
                ];
            }
        }

        // Check optional extensions
        foreach (self::OPTIONAL_EXTENSIONS as $ext => $description) {
            if ($this->isExtensionLoaded($ext)) {
                $loaded[$ext] = [
                    'name' => $ext,
                    'description' => $description,
                    'status' => 'ok',
                ];
            } else {
                $warnings[$ext] = [
                    'name' => $ext,
                    'description' => $description,
                    'status' => 'warning',
                ];
            }
        }

        // Determine overall result
        if (! empty($missing)) {
            $missingNames = implode(', ', array_keys($missing));

            return ValidatorResult::error(
                __('requirements.extension_required', ['name' => $missingNames]),
                [
                    'loaded' => $loaded,
                    'missing' => $missing,
                    'warnings' => $warnings,
                ]
            );
        }

        if (! empty($warnings)) {
            return ValidatorResult::warning(
                count($loaded).' extensiones cargadas, '.count($warnings).' opcionales no disponibles',
                [
                    'loaded' => $loaded,
                    'missing' => $missing,
                    'warnings' => $warnings,
                ]
            );
        }

        return ValidatorResult::ok(
            count($loaded).' extensiones cargadas',
            [
                'loaded' => $loaded,
                'missing' => $missing,
                'warnings' => $warnings,
            ]
        );
    }

    public function getName(): string
    {
        return __('requirements.extensions');
    }

    public function getDescription(): string
    {
        return 'Extensiones PHP requeridas para Larafactu';
    }

    /**
     * Get list of required extensions
     */
    public static function getRequiredExtensions(): array
    {
        return self::REQUIRED_EXTENSIONS;
    }

    /**
     * Get list of optional extensions
     */
    public static function getOptionalExtensions(): array
    {
        return self::OPTIONAL_EXTENSIONS;
    }

    /**
     * Check if extension is loaded (handles special cases like OPcache)
     */
    private function isExtensionLoaded(string $extension): bool
    {
        // OPcache is a Zend extension, extension_loaded('opcache') returns false
        if ($extension === 'opcache') {
            return function_exists('opcache_get_status');
        }

        return extension_loaded($extension);
    }
}
