<?php

declare(strict_types=1);

namespace Installer\Steps;

use Installer\Security\Encryption;
use PDO;
use Ramsey\Uuid\Uuid;

/**
 * Step 8: Administrator Creation
 *
 * Creates the superadmin user.
 */
class AdminStep extends AbstractStep
{
    public function getId(): string
    {
        return 'admin';
    }

    public function validate(array $data): ValidationResult
    {
        $errors = [];

        // Required fields
        if (empty($data['name'])) {
            $errors['name'] = __('errors.required');
        }

        if (empty($data['email'])) {
            $errors['email'] = __('errors.required');
        } elseif (! $this->validateEmail($data['email'])) {
            $errors['email'] = __('admin.email_invalid');
        }

        if (empty($data['password'])) {
            $errors['password'] = __('errors.required');
        } elseif (! $this->isStrongPassword($data['password'])) {
            $errors['password'] = __('admin.password_weak');
        }

        if (empty($data['password_confirm'])) {
            $errors['password_confirm'] = __('errors.required');
        } elseif ($data['password'] !== $data['password_confirm']) {
            $errors['password_confirm'] = __('admin.passwords_mismatch');
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

            // Check if email already exists
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->execute([$data['email']]);

            if ($stmt->fetch()) {
                return $this->failure(
                    'El email ya está registrado',
                    ['error' => 'Email already exists']
                );
            }

            // Generate UUID v7 for user ID
            $userId = $this->generateUuidV7();

            // Hash password
            $hashedPassword = Encryption::hashPassword($data['password']);

            $now = date('Y-m-d H:i:s');

            // Insert user
            $userData = [
                'id' => $userId,
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $hashedPassword,
                'user_type' => 1, // STAFF (from UserType enum)
                'is_superadmin' => 1,
                'is_active' => 1,
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $columns = implode(', ', array_keys($userData));
            $placeholders = implode(', ', array_fill(0, count($userData), '?'));

            $stmt = $pdo->prepare("INSERT INTO users ({$columns}) VALUES ({$placeholders})");
            $stmt->execute(array_values($userData));

            // Save to state
            $this->state->set('admin', [
                'id' => $userId,
                'name' => $data['name'],
                'email' => $data['email'],
            ]);

            return $this->success(
                __('finalize.admin_created'),
                [
                    'user_id' => $userId,
                    'email' => $data['email'],
                ]
            );

        } catch (\Throwable $e) {
            return $this->failure(
                'Error al crear administrador: '.$e->getMessage(),
                ['error' => $e->getMessage()]
            );
        }
    }

    public function getViewData(): array
    {
        return [
            'minPasswordLength' => 8,
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
     * Check if password is strong enough
     */
    private function isStrongPassword(string $password): bool
    {
        // At least 8 characters
        if (strlen($password) < 8) {
            return false;
        }

        // Must have uppercase
        if (! preg_match('/[A-Z]/', $password)) {
            return false;
        }

        // Must have lowercase
        if (! preg_match('/[a-z]/', $password)) {
            return false;
        }

        // Must have number
        if (! preg_match('/[0-9]/', $password)) {
            return false;
        }

        return true;
    }

    /**
     * Generate UUID v7 (time-ordered)
     */
    private function generateUuidV7(): string
    {
        // UUID v7 format: time-based with random
        // We'll use a simplified version that's sortable by time

        $time = (int) (microtime(true) * 1000);
        $timeBits = str_pad(dechex($time), 12, '0', STR_PAD_LEFT);

        // Random bits
        $randomBits = bin2hex(random_bytes(8));

        // Construct UUID v7-like format
        return sprintf(
            '%s-%s-7%s-%s-%s',
            substr($timeBits, 0, 8),
            substr($timeBits, 8, 4),
            substr($randomBits, 0, 3),
            sprintf('%x', 0x8 | (hexdec(substr($randomBits, 3, 1)) & 0x3)).substr($randomBits, 4, 3),
            substr($randomBits, 7, 12)
        );
    }
}
