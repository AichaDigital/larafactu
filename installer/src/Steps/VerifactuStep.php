<?php

declare(strict_types=1);

namespace Installer\Steps;

use Installer\Actions\CertificateStore;
use Installer\Actions\EnvFileWriter;

/**
 * Step 7: Verifactu Configuration
 *
 * Configures AEAT Verifactu integration with encrypted certificates.
 */
class VerifactuStep extends AbstractStep
{
    public function getId(): string
    {
        return 'verifactu';
    }

    public function validate(array $data): ValidationResult
    {
        // Skip validation if disabled
        if (($data['mode'] ?? 'disabled') === 'disabled') {
            return ValidationResult::valid();
        }

        $errors = [];

        // Environment required
        if (empty($data['environment'])) {
            $errors['environment'] = __('errors.required');
        }

        // For native mode, certificate is required
        if ($data['mode'] === 'native') {
            if (empty($_FILES['certificate']) || $_FILES['certificate']['error'] !== UPLOAD_ERR_OK) {
                $errors['certificate'] = __('errors.required');
            }

            if (empty($data['certificate_password'])) {
                $errors['certificate_password'] = __('errors.required');
            }
        }

        return empty($errors)
            ? ValidationResult::valid()
            : ValidationResult::invalid($errors);
    }

    public function execute(array $data): ExecutionResult
    {
        $envWriter = new EnvFileWriter;
        $mode = $data['mode'] ?? 'disabled';

        // Save mode and environment to .env
        $envWriter->set('VERIFACTU_MODE', $mode);

        if ($mode === 'disabled') {
            $envWriter->write();

            $this->state->set('verifactu', [
                'mode' => 'disabled',
                'configured' => false,
            ]);

            return $this->success(
                __('verifactu.skip_warning'),
                ['skipped' => true]
            );
        }

        $envWriter->set('VERIFACTU_ENVIRONMENT', $data['environment']);

        // For native mode, store certificate
        if ($mode === 'native' && isset($_FILES['certificate'])) {
            try {
                $certStore = new CertificateStore;

                $result = $certStore->storeUpload(
                    $_FILES['certificate'],
                    'verifactu',
                    $data['certificate_password']
                );

                if (! $result->isSuccess()) {
                    return $this->failure(
                        'Error al almacenar certificado: '.$result->getMessage(),
                        ['error' => $result->getError()]
                    );
                }

                // Save certificate path to env
                $envWriter->set('VERIFACTU_CERTIFICATE_PATH', $result->get('path'));

            } catch (\Throwable $e) {
                return $this->failure(
                    'Error al procesar certificado: '.$e->getMessage(),
                    ['error' => $e->getMessage()]
                );
            }
        }

        // Get company NIF for Verifactu
        $companyData = $this->state->get('company');
        if ($companyData) {
            $envWriter->set('VERIFACTU_NIF', $companyData['tax_id']);
        }

        // Write all env values
        $envWriter->write();

        // Save to state
        $this->state->set('verifactu', [
            'mode' => $mode,
            'environment' => $data['environment'],
            'configured' => true,
        ]);

        return $this->success(
            __('verifactu.test_success'),
            [
                'mode' => $mode,
                'environment' => $data['environment'],
            ]
        );
    }

    public function getViewData(): array
    {
        $companyData = $this->state->get('company');

        return [
            'modes' => [
                'native' => __('verifactu.mode_native'),
                'api' => __('verifactu.mode_api'),
                'disabled' => __('verifactu.mode_disabled'),
            ],
            'environments' => [
                'sandbox' => __('verifactu.environment_sandbox'),
                'production' => __('verifactu.environment_production'),
            ],
            'companyTaxId' => $companyData['tax_id'] ?? '',
        ];
    }
}
