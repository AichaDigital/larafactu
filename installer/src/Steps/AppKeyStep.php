<?php

declare(strict_types=1);

namespace Installer\Steps;

use Installer\Actions\KeyGenerator;

/**
 * Step 3: Application Key Generation
 *
 * CRITICAL: This step must run BEFORE any encryption operations.
 * Generates the APP_KEY used for all data encryption.
 */
class AppKeyStep extends AbstractStep
{
    private KeyGenerator $keyGenerator;

    public function getId(): string
    {
        return 'appkey';
    }

    public function validate(array $data): ValidationResult
    {
        // No input required - key is auto-generated
        return ValidationResult::valid();
    }

    public function execute(array $data): ExecutionResult
    {
        $this->keyGenerator = new KeyGenerator;

        // Check if key already exists
        if ($this->keyGenerator->keyExists()) {
            $existingKey = $this->keyGenerator->getCurrentKey();

            // If user wants to regenerate, allow it
            if (! ($data['force_regenerate'] ?? false)) {
                $this->state->set('app_key', $existingKey);

                return $this->success(
                    'Clave existente detectada y válida',
                    ['key_exists' => true, 'regenerated' => false]
                );
            }
        }

        // Generate new key
        $result = $this->keyGenerator->generate();

        if (! $result->isSuccess()) {
            return $this->failure(
                'Error al generar la clave de aplicación',
                ['error' => $result->getError()]
            );
        }

        // Save key to state for use in later steps
        $this->state->set('app_key', $result->get('key'));

        return $this->success(__('appkey.generated'), [
            'key_exists' => false,
            'regenerated' => true,
        ]);
    }

    public function getViewData(): array
    {
        $this->keyGenerator = new KeyGenerator;

        return [
            'keyExists' => $this->keyGenerator->keyExists(),
            'currentKey' => $this->keyGenerator->keyExists()
                ? $this->maskKey($this->keyGenerator->getCurrentKey())
                : null,
        ];
    }

    /**
     * Mask key for display (show only first/last chars)
     */
    private function maskKey(string $key): string
    {
        // base64:XXXXXXX -> base64:XXX...XXX
        $parts = explode(':', $key);
        if (count($parts) !== 2) {
            return '***';
        }

        $encoded = $parts[1];
        $visible = 6;

        if (strlen($encoded) <= $visible * 2) {
            return $parts[0].':***';
        }

        return $parts[0].':'.substr($encoded, 0, $visible).'...'.substr($encoded, -$visible);
    }
}
