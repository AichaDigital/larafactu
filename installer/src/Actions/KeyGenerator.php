<?php

declare(strict_types=1);

namespace Installer\Actions;

/**
 * Generates application encryption key.
 *
 * CRITICAL: This must run BEFORE any encryption operations.
 */
class KeyGenerator
{
    private EnvFileWriter $envWriter;

    public function __construct(?EnvFileWriter $envWriter = null)
    {
        $this->envWriter = $envWriter ?? new EnvFileWriter;
    }

    /**
     * Generate a new application key
     */
    public function generate(): ActionResult
    {
        try {
            // Generate 32 bytes (256 bits) for AES-256
            $key = random_bytes(32);
            $encodedKey = 'base64:'.base64_encode($key);

            // Write to .env
            $this->envWriter->set('APP_KEY', $encodedKey);
            $result = $this->envWriter->write();

            if (! $result->isSuccess()) {
                return ActionResult::failure(
                    'No se pudo guardar la clave en .env',
                    $result->getError()
                );
            }

            return ActionResult::success(
                __('appkey.generated'),
                ['key' => $encodedKey]
            );

        } catch (\Throwable $e) {
            return ActionResult::failure(
                'Error al generar la clave: '.$e->getMessage(),
                $e->getMessage()
            );
        }
    }

    /**
     * Check if a valid key already exists
     */
    public function keyExists(): bool
    {
        $currentKey = $this->envWriter->get('APP_KEY');

        if ($currentKey === null || $currentKey === '') {
            return false;
        }

        // Verify it's a valid base64 key
        if (! str_starts_with($currentKey, 'base64:')) {
            return false;
        }

        $decoded = base64_decode(substr($currentKey, 7), true);

        // Should be 32 bytes for AES-256
        return $decoded !== false && strlen($decoded) === 32;
    }

    /**
     * Get the current key (if valid)
     */
    public function getCurrentKey(): ?string
    {
        if (! $this->keyExists()) {
            return null;
        }

        return $this->envWriter->get('APP_KEY');
    }

    /**
     * Validate a key format
     */
    public static function isValidKey(string $key): bool
    {
        if (! str_starts_with($key, 'base64:')) {
            return false;
        }

        $decoded = base64_decode(substr($key, 7), true);

        return $decoded !== false && strlen($decoded) === 32;
    }
}
