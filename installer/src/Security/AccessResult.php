<?php

declare(strict_types=1);

namespace Installer\Security;

/**
 * Result of an access check.
 */
class AccessResult
{
    private bool $granted;

    private string $reason;

    private function __construct(bool $granted, string $reason)
    {
        $this->granted = $granted;
        $this->reason = $reason;
    }

    /**
     * Access granted
     */
    public static function granted(): self
    {
        return new self(true, 'granted');
    }

    /**
     * Access denied
     */
    public static function denied(string $reason): self
    {
        return new self(false, $reason);
    }

    /**
     * Token required
     */
    public static function requiresToken(string $reason): self
    {
        return new self(false, $reason);
    }

    /**
     * Session expired
     */
    public static function expired(string $reason): self
    {
        return new self(false, $reason);
    }

    /**
     * Check if access is granted
     */
    public function isGranted(): bool
    {
        return $this->granted;
    }

    /**
     * Get reason
     */
    public function getReason(): string
    {
        return $this->reason;
    }

    /**
     * Check if token is required
     */
    public function requiresTokenInput(): bool
    {
        return ! $this->granted && in_array($this->reason, ['missing', 'invalid']);
    }

    /**
     * Check if blocked
     */
    public function isBlocked(): bool
    {
        return $this->reason === 'blocked';
    }

    /**
     * Check if expired
     */
    public function isExpired(): bool
    {
        return $this->reason === 'expired';
    }
}
