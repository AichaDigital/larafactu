<?php

declare(strict_types=1);

namespace Installer\Validators;

/**
 * Result of a validation check.
 */
class ValidatorResult
{
    public const STATUS_OK = 'ok';

    public const STATUS_WARNING = 'warning';

    public const STATUS_ERROR = 'error';

    private string $status;

    private string $message;

    private array $details;

    private function __construct(string $status, string $message, array $details = [])
    {
        $this->status = $status;
        $this->message = $message;
        $this->details = $details;
    }

    public static function ok(string $message = '', array $details = []): self
    {
        return new self(self::STATUS_OK, $message, $details);
    }

    public static function warning(string $message, array $details = []): self
    {
        return new self(self::STATUS_WARNING, $message, $details);
    }

    public static function error(string $message, array $details = []): self
    {
        return new self(self::STATUS_ERROR, $message, $details);
    }

    public function isOk(): bool
    {
        return $this->status === self::STATUS_OK;
    }

    public function isWarning(): bool
    {
        return $this->status === self::STATUS_WARNING;
    }

    public function isError(): bool
    {
        return $this->status === self::STATUS_ERROR;
    }

    public function isPassing(): bool
    {
        return $this->status !== self::STATUS_ERROR;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getDetails(): array
    {
        return $this->details;
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'message' => $this->message,
            'details' => $this->details,
        ];
    }
}
