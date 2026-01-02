<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Access levels for granular permissions.
 *
 * @see ADR-004 for authorization architecture
 */
enum AccessLevel: int
{
    case FULL = 0;   // Read + Write + Delete + Escalate
    case WRITE = 1;  // Read + Write (no escalation)
    case READ = 2;   // Read only (observer/trainee)
    case NONE = 3;   // No access (explicit)

    public function label(): string
    {
        return match ($this) {
            self::FULL => 'Acceso completo',
            self::WRITE => 'Lectura y escritura',
            self::READ => 'Solo lectura',
            self::NONE => 'Sin acceso',
        };
    }

    public function canRead(): bool
    {
        return in_array($this, [self::FULL, self::WRITE, self::READ], true);
    }

    public function canWrite(): bool
    {
        return in_array($this, [self::FULL, self::WRITE], true);
    }

    public function canDelete(): bool
    {
        return $this === self::FULL;
    }

    public function canEscalate(): bool
    {
        return $this === self::FULL;
    }

    /**
     * Check if this level is higher or equal to another.
     */
    public function isAtLeast(self $level): bool
    {
        return $this->value <= $level->value;
    }
}
