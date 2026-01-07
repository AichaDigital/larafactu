<?php

declare(strict_types=1);

namespace Installer\Steps;

use PDO;

/**
 * Step 6: Company Fiscal Configuration
 *
 * Collects complete company fiscal data for CompanyFiscalConfig.
 */
class CompanyStep extends AbstractStep
{
    public function getId(): string
    {
        return 'company';
    }

    public function validate(array $data): ValidationResult
    {
        $errors = [];

        // Required fields
        $required = [
            'business_name',
            'tax_id',
            'address_line1',
            'postal_code',
            'city',
            'province',
            'country_code',
            'email',
        ];

        foreach ($required as $field) {
            if (empty($data[$field])) {
                $errors[$field] = __('errors.required');
            }
        }

        // Validate tax_id format (Spanish CIF/NIF)
        if (! empty($data['tax_id']) && ! $this->validateSpanishTaxId($data['tax_id'])) {
            $errors['tax_id'] = __('company.tax_id_invalid');
        }

        // Validate email
        if (! empty($data['email']) && ! $this->validateEmail($data['email'])) {
            $errors['email'] = __('errors.invalid_email');
        }

        // If ROI operator, require EU VAT number
        if (! empty($data['is_roi']) && empty($data['eu_vat'])) {
            $errors['eu_vat'] = 'Requerido para operadores ROI/OSS';
        }

        return empty($errors)
            ? ValidationResult::valid()
            : ValidationResult::invalid($errors);
    }

    public function execute(array $data): ExecutionResult
    {
        try {
            $pdo = $this->getDatabase();

            if ($pdo === null) {
                return $this->failure(
                    'No se pudo conectar a la base de datos',
                    ['error' => 'Database connection failed']
                );
            }

            // Check if table exists
            $tableExists = $this->tableExists($pdo, 'company_fiscal_configs');

            if (! $tableExists) {
                return $this->failure(
                    'La tabla company_fiscal_configs no existe. Ejecute las migraciones primero.',
                    ['error' => 'Table does not exist']
                );
            }

            // Prepare data
            $now = date('Y-m-d H:i:s');
            $today = date('Y-m-d');

            $configData = [
                'business_name' => $data['business_name'],
                'trade_name' => $data['trade_name'] ?? null,
                'tax_id' => strtoupper($data['tax_id']),
                'tax_id_type' => $this->detectTaxIdType($data['tax_id']),
                'address_line1' => $data['address_line1'],
                'address_line2' => $data['address_line2'] ?? null,
                'postal_code' => $data['postal_code'],
                'city' => $data['city'],
                'province' => $data['province'],
                'country_code' => strtoupper($data['country_code']),
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'],
                'is_eu_vat_registered' => ! empty($data['is_roi']) ? 1 : 0,
                'eu_vat_number' => $data['eu_vat'] ?? null,
                'currency_code' => $data['currency'] ?? 'EUR',
                'valid_from' => $today,
                'valid_until' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            // Insert into database
            $columns = implode(', ', array_keys($configData));
            $placeholders = implode(', ', array_fill(0, count($configData), '?'));

            $stmt = $pdo->prepare("INSERT INTO company_fiscal_configs ({$columns}) VALUES ({$placeholders})");
            $stmt->execute(array_values($configData));

            $configId = $pdo->lastInsertId();

            // Save to state
            $this->state->set('company', [
                'id' => $configId,
                'business_name' => $data['business_name'],
                'tax_id' => $data['tax_id'],
                'is_roi' => ! empty($data['is_roi']),
            ]);

            return $this->success(
                __('finalize.company_created'),
                ['config_id' => $configId]
            );

        } catch (\Throwable $e) {
            return $this->failure(
                'Error al guardar datos de empresa: '.$e->getMessage(),
                ['error' => $e->getMessage()]
            );
        }
    }

    public function getViewData(): array
    {
        return [
            'countries' => $this->getCountries(),
            'currencies' => ['EUR' => 'Euro (EUR)', 'USD' => 'US Dollar (USD)', 'GBP' => 'British Pound (GBP)'],
        ];
    }

    /**
     * Get database connection
     */
    private function getDatabase(): ?PDO
    {
        $dbConfig = $this->state->get('database');

        if (! $dbConfig) {
            return null;
        }

        try {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                $dbConfig['host'],
                $dbConfig['port'],
                $dbConfig['database']
            );

            // Get password from env
            $envWriter = new \Installer\Actions\EnvFileWriter;
            $password = $envWriter->get('DB_PASSWORD') ?? '';

            return new PDO($dsn, $dbConfig['username'], $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (\PDOException $e) {
            return null;
        }
    }

    /**
     * Check if table exists
     */
    private function tableExists(PDO $pdo, string $table): bool
    {
        try {
            $result = $pdo->query("SHOW TABLES LIKE '{$table}'");

            return $result->rowCount() > 0;
        } catch (\PDOException $e) {
            return false;
        }
    }

    /**
     * Validate Spanish tax ID (CIF/NIF/NIE)
     */
    private function validateSpanishTaxId(string $taxId): bool
    {
        $taxId = strtoupper(trim($taxId));

        // CIF pattern: Letter + 7 digits + letter/digit
        // NIF pattern: 8 digits + letter
        // NIE pattern: X/Y/Z + 7 digits + letter

        $patterns = [
            '/^[ABCDEFGHJNPQRSUVW][0-9]{7}[0-9A-J]$/', // CIF
            '/^[0-9]{8}[A-Z]$/',                         // NIF
            '/^[XYZ][0-9]{7}[A-Z]$/',                    // NIE
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $taxId)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Detect tax ID type
     */
    private function detectTaxIdType(string $taxId): string
    {
        $taxId = strtoupper(trim($taxId));
        $firstChar = $taxId[0];

        if (is_numeric($firstChar)) {
            return 'NIF';
        }

        if (in_array($firstChar, ['X', 'Y', 'Z'])) {
            return 'NIE';
        }

        return 'CIF';
    }

    /**
     * Get country list
     */
    private function getCountries(): array
    {
        return [
            'ES' => 'España',
            'PT' => 'Portugal',
            'FR' => 'Francia',
            'DE' => 'Alemania',
            'IT' => 'Italia',
            'GB' => 'Reino Unido',
            'NL' => 'Países Bajos',
            'BE' => 'Bélgica',
            'AT' => 'Austria',
            'IE' => 'Irlanda',
            'PL' => 'Polonia',
            'SE' => 'Suecia',
            'DK' => 'Dinamarca',
            'FI' => 'Finlandia',
            'GR' => 'Grecia',
            'CZ' => 'República Checa',
            'RO' => 'Rumanía',
            'HU' => 'Hungría',
            'SK' => 'Eslovaquia',
            'BG' => 'Bulgaria',
            'HR' => 'Croacia',
            'SI' => 'Eslovenia',
            'LT' => 'Lituania',
            'LV' => 'Letonia',
            'EE' => 'Estonia',
            'CY' => 'Chipre',
            'LU' => 'Luxemburgo',
            'MT' => 'Malta',
        ];
    }
}
