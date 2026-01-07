<?php

declare(strict_types=1);

namespace Installer\Security;

/**
 * Access control for the installer.
 *
 * Uses token-based authentication with IP locking and rate limiting.
 */
class AccessControl
{
    private const TOKEN_FILE = '.token';

    private const FAILED_LOG = 'failed_attempts.log';

    private const MAX_FAILED_ATTEMPTS = 5;

    private const LOCKOUT_MINUTES = 15;

    private const SESSION_TIMEOUT_MINUTES = 60;

    private string $storagePath;

    public function __construct(?string $storagePath = null)
    {
        $this->storagePath = $storagePath ?? STORAGE_PATH;
    }

    /**
     * Ensure token file exists
     */
    public function ensureTokenExists(): string
    {
        $tokenFile = $this->storagePath.'/'.self::TOKEN_FILE;

        if (! file_exists($tokenFile)) {
            $token = $this->generateToken();

            $tokenData = [
                'token' => $token,
                'created_at' => time(),
                'ip' => null, // Will be set on first valid access
            ];

            file_put_contents($tokenFile, json_encode($tokenData, JSON_PRETTY_PRINT));
            chmod($tokenFile, 0600);

            return $token;
        }

        $data = json_decode(file_get_contents($tokenFile), true);

        return $data['token'] ?? '';
    }

    /**
     * Check access with provided token
     */
    public function checkAccess(?string $providedToken): AccessResult
    {
        // Check if blocked due to failed attempts
        if ($this->isBlocked()) {
            return AccessResult::denied('blocked');
        }

        // Check if token provided
        if ($providedToken === null || $providedToken === '') {
            return AccessResult::requiresToken('missing');
        }

        // Validate token
        $validation = $this->validateToken($providedToken);

        if (! $validation['valid']) {
            $this->logFailedAttempt();

            return AccessResult::denied($validation['reason']);
        }

        // Check session timeout
        if ($this->isSessionExpired()) {
            return AccessResult::expired('expired');
        }

        // Update session timestamp
        $_SESSION['installer_last_activity'] = time();

        return AccessResult::granted();
    }

    /**
     * Validate token
     */
    public function validateToken(string $providedToken): array
    {
        $tokenFile = $this->storagePath.'/'.self::TOKEN_FILE;

        if (! file_exists($tokenFile)) {
            return ['valid' => false, 'reason' => 'missing'];
        }

        $data = json_decode(file_get_contents($tokenFile), true);

        if ($data === null || ! isset($data['token'])) {
            return ['valid' => false, 'reason' => 'invalid'];
        }

        // Compare tokens (timing-safe)
        if (! hash_equals($data['token'], $providedToken)) {
            return ['valid' => false, 'reason' => 'invalid'];
        }

        // Check IP lock (if set)
        $clientIp = $this->getClientIp();

        if (isset($data['ip']) && $data['ip'] !== null && $data['ip'] !== $clientIp) {
            return ['valid' => false, 'reason' => 'ip_mismatch'];
        }

        // Lock to this IP if not already locked
        if (! isset($data['ip']) || $data['ip'] === null) {
            $data['ip'] = $clientIp;
            file_put_contents($tokenFile, json_encode($data, JSON_PRETTY_PRINT));
        }

        return ['valid' => true, 'reason' => null];
    }

    /**
     * Generate a new token
     */
    public function regenerateToken(): string
    {
        $tokenFile = $this->storagePath.'/'.self::TOKEN_FILE;

        // Delete old token
        if (file_exists($tokenFile)) {
            unlink($tokenFile);
        }

        // Clear session
        unset($_SESSION['installer_token']);
        unset($_SESSION['installer_last_activity']);

        return $this->ensureTokenExists();
    }

    /**
     * Check if IP is blocked
     */
    private function isBlocked(): bool
    {
        $logFile = $this->storagePath.'/'.self::FAILED_LOG;

        if (! file_exists($logFile)) {
            return false;
        }

        $content = file_get_contents($logFile);
        $attempts = json_decode($content, true) ?? [];

        $clientIp = $this->getClientIp();
        $now = time();
        $cutoff = $now - (self::LOCKOUT_MINUTES * 60);

        // Count recent attempts from this IP
        $recentAttempts = 0;
        foreach ($attempts as $attempt) {
            if ($attempt['ip'] === $clientIp && $attempt['time'] > $cutoff) {
                $recentAttempts++;
            }
        }

        return $recentAttempts >= self::MAX_FAILED_ATTEMPTS;
    }

    /**
     * Log a failed attempt
     */
    public function logFailedAttempt(): void
    {
        $logFile = $this->storagePath.'/'.self::FAILED_LOG;

        $attempts = [];
        if (file_exists($logFile)) {
            $content = file_get_contents($logFile);
            $attempts = json_decode($content, true) ?? [];
        }

        $attempts[] = [
            'ip' => $this->getClientIp(),
            'time' => time(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        ];

        // Keep only recent attempts (last 24 hours)
        $cutoff = time() - 86400;
        $attempts = array_filter($attempts, fn ($a) => $a['time'] > $cutoff);

        file_put_contents($logFile, json_encode($attempts, JSON_PRETTY_PRINT));
    }

    /**
     * Check if session has expired
     */
    private function isSessionExpired(): bool
    {
        $lastActivity = $_SESSION['installer_last_activity'] ?? null;

        if ($lastActivity === null) {
            $_SESSION['installer_last_activity'] = time();

            return false;
        }

        $elapsed = time() - $lastActivity;

        return $elapsed > (self::SESSION_TIMEOUT_MINUTES * 60);
    }

    /**
     * Generate a secure token
     */
    private function generateToken(): string
    {
        return sprintf(
            '%s-%s-%s-%s-%s',
            bin2hex(random_bytes(4)),
            bin2hex(random_bytes(2)),
            bin2hex(random_bytes(2)),
            bin2hex(random_bytes(2)),
            bin2hex(random_bytes(6))
        );
    }

    /**
     * Get client IP address
     */
    private function getClientIp(): string
    {
        // Check for proxied IP
        $headers = [
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_X_FORWARDED_FOR',      // Standard proxy
            'HTTP_X_REAL_IP',            // Nginx
            'HTTP_CLIENT_IP',            // Some proxies
            'REMOTE_ADDR',               // Direct connection
        ];

        foreach ($headers as $header) {
            if (! empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];

                // X-Forwarded-For can contain multiple IPs
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }

                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return '0.0.0.0';
    }

    /**
     * Get storage path
     */
    public function getStoragePath(): string
    {
        return $this->storagePath;
    }
}
