<?php

declare(strict_types=1);

namespace Installer\Steps;

use Installer\Actions\CommandRunner;
use PDO;

/**
 * Step 9: Finalization
 *
 * - Creates installation marker
 * - Verifies superadmin exists
 * - Optimizes caches
 * - Shows summary
 */
class FinalizeStep extends AbstractStep
{
    public function getId(): string
    {
        return 'finalize';
    }

    public function validate(array $data): ValidationResult
    {
        // Verify all required steps are completed
        $required = ['welcome', 'requirements', 'appkey', 'database', 'migrations', 'company', 'admin'];
        $completed = $this->state->getCompletedSteps();

        $missing = array_diff($required, $completed);

        if (! empty($missing)) {
            return ValidationResult::invalid([
                'steps' => 'Faltan pasos por completar: '.implode(', ', $missing),
            ]);
        }

        return ValidationResult::valid();
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

            // Verify superadmin exists
            $stmt = $pdo->query('SELECT COUNT(*) FROM users WHERE is_superadmin = 1');
            $adminCount = (int) $stmt->fetchColumn();

            if ($adminCount === 0) {
                return $this->failure(
                    'No se encontró ningún superadministrador',
                    ['error' => 'No superadmin found']
                );
            }

            // Create installation marker in settings table
            $this->createInstallationMarker($pdo);

            // Optimize caches
            $commandRunner = new CommandRunner;
            $commandRunner->artisan('config:cache');
            $commandRunner->artisan('route:clear');
            $commandRunner->artisan('view:clear');

            // Create .done file for installer
            $this->createDoneFile();

            // Get summary data
            $summary = $this->getSummary();

            return $this->success(
                __('finalize.title'),
                $summary
            );

        } catch (\Throwable $e) {
            return $this->failure(
                'Error al finalizar instalación: '.$e->getMessage(),
                ['error' => $e->getMessage()]
            );
        }
    }

    public function getViewData(): array
    {
        return [
            'summary' => $this->getSummary(),
            'appUrl' => $this->getAppUrl(),
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
     * Create installation marker in database
     */
    private function createInstallationMarker(PDO $pdo): void
    {
        // Check if settings table exists
        $result = $pdo->query("SHOW TABLES LIKE 'settings'");
        $tableExists = $result->rowCount() > 0;

        $installData = json_encode([
            'version' => '1.0.0',
            'installed_at' => date('Y-m-d H:i:s'),
            'installer_version' => INSTALLER_VERSION,
            'php_version' => PHP_VERSION,
        ]);

        if ($tableExists) {
            // Use settings table
            $stmt = $pdo->prepare("
                INSERT INTO settings (`key`, `value`, `created_at`, `updated_at`) 
                VALUES ('installed', ?, NOW(), NOW())
                ON DUPLICATE KEY UPDATE `value` = ?, `updated_at` = NOW()
            ");
            $stmt->execute([$installData, $installData]);
        } else {
            // Create larabill_settings if it exists
            $result = $pdo->query("SHOW TABLES LIKE 'larabill_settings'");
            if ($result->rowCount() > 0) {
                $stmt = $pdo->prepare("
                    INSERT INTO larabill_settings (`key`, `value`, `type`, `group`, `created_at`, `updated_at`) 
                    VALUES ('larafactu.installed', ?, 'json', 'system', NOW(), NOW())
                    ON DUPLICATE KEY UPDATE `value` = ?, `updated_at` = NOW()
                ");
                $stmt->execute([$installData, $installData]);
            }
        }
    }

    /**
     * Create .done file for installer
     */
    private function createDoneFile(): void
    {
        $doneData = [
            'completed_at' => time(),
            'version' => INSTALLER_VERSION,
            'admin_email' => $this->state->get('admin.email'),
        ];

        file_put_contents(
            INSTALLER_ROOT.'/.done',
            json_encode($doneData, JSON_PRETTY_PRINT)
        );
    }

    /**
     * Get installation summary
     */
    private function getSummary(): array
    {
        $company = $this->state->get('company', []);
        $admin = $this->state->get('admin', []);
        $verifactu = $this->state->get('verifactu', []);
        $database = $this->state->get('database', []);

        return [
            'company' => [
                'name' => $company['business_name'] ?? 'N/A',
                'tax_id' => $company['tax_id'] ?? 'N/A',
                'is_roi' => $company['is_roi'] ?? false,
            ],
            'admin' => [
                'name' => $admin['name'] ?? 'N/A',
                'email' => $admin['email'] ?? 'N/A',
            ],
            'database' => [
                'host' => $database['host'] ?? 'N/A',
                'database' => $database['database'] ?? 'N/A',
            ],
            'verifactu' => [
                'configured' => $verifactu['configured'] ?? false,
                'mode' => $verifactu['mode'] ?? 'disabled',
                'environment' => $verifactu['environment'] ?? null,
            ],
        ];
    }

    /**
     * Get application URL
     */
    private function getAppUrl(): string
    {
        $protocol = (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];

        // Remove /installer from path
        $path = dirname($_SERVER['SCRIPT_NAME']);
        $path = str_replace('/installer/public', '', $path);
        $path = str_replace('/installer', '', $path);

        return $protocol.'://'.$host.$path;
    }
}
