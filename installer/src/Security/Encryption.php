<?php

declare(strict_types=1);

namespace Installer\Security;

/**
 * AES-256-GCM encryption compatible with Laravel.
 */
class Encryption
{
    private string $key;

    private const CIPHER = 'aes-256-gcm';

    private const TAG_LENGTH = 16;

    /**
     * @param  string  $appKey  Laravel APP_KEY format: "base64:xxxxx"
     */
    public function __construct(string $appKey)
    {
        if (! str_starts_with($appKey, 'base64:')) {
            throw new \InvalidArgumentException('APP_KEY must be in base64: format');
        }

        $decoded = base64_decode(substr($appKey, 7), true);

        if ($decoded === false || strlen($decoded) !== 32) {
            throw new \InvalidArgumentException('Invalid APP_KEY: must be 32 bytes when decoded');
        }

        $this->key = $decoded;
    }

    /**
     * Encrypt a value
     */
    public function encrypt(string $value): string
    {
        $ivLength = openssl_cipher_iv_length(self::CIPHER);
        $iv = random_bytes($ivLength);
        $tag = '';

        $encrypted = openssl_encrypt(
            $value,
            self::CIPHER,
            $this->key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '',
            self::TAG_LENGTH
        );

        if ($encrypted === false) {
            throw new \RuntimeException('Encryption failed: '.openssl_error_string());
        }

        // Format: base64(iv + tag + encrypted)
        return base64_encode($iv.$tag.$encrypted);
    }

    /**
     * Decrypt a value
     */
    public function decrypt(string $payload): string
    {
        $data = base64_decode($payload, true);

        if ($data === false) {
            throw new \RuntimeException('Invalid payload: base64 decode failed');
        }

        $ivLength = openssl_cipher_iv_length(self::CIPHER);

        if (strlen($data) < $ivLength + self::TAG_LENGTH) {
            throw new \RuntimeException('Invalid payload: too short');
        }

        $iv = substr($data, 0, $ivLength);
        $tag = substr($data, $ivLength, self::TAG_LENGTH);
        $encrypted = substr($data, $ivLength + self::TAG_LENGTH);

        $decrypted = openssl_decrypt(
            $encrypted,
            self::CIPHER,
            $this->key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        if ($decrypted === false) {
            throw new \RuntimeException('Decryption failed: '.openssl_error_string());
        }

        return $decrypted;
    }

    /**
     * Encrypt a value and return as JSON (Laravel compatible format)
     */
    public function encryptJson(string $value): string
    {
        $ivLength = openssl_cipher_iv_length(self::CIPHER);
        $iv = random_bytes($ivLength);
        $tag = '';

        $encrypted = openssl_encrypt(
            $value,
            self::CIPHER,
            $this->key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '',
            self::TAG_LENGTH
        );

        if ($encrypted === false) {
            throw new \RuntimeException('Encryption failed');
        }

        return base64_encode(json_encode([
            'iv' => base64_encode($iv),
            'value' => base64_encode($encrypted),
            'tag' => base64_encode($tag),
            'mac' => '', // Not used in GCM mode, tag provides authentication
        ]));
    }

    /**
     * Generate a new encryption key
     */
    public static function generateKey(): string
    {
        return 'base64:'.base64_encode(random_bytes(32));
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

    /**
     * Hash a password (bcrypt compatible with Laravel)
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /**
     * Verify a password against a hash
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}
