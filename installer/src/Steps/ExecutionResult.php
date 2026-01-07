<?php

declare(strict_types=1);

namespace Installer\Steps;

/**
 * Result of step execution.
 */
class ExecutionResult
{
    private bool $success;

    private string $message;

    private array $data;

    private function __construct(bool $success, string $message, array $data = [])
    {
        $this->success = $success;
        $this->message = $message;
        $this->data = $data;
    }

    /**
     * Create success result
     */
    public static function success(string $message = '', array $data = []): self
    {
        return new self(true, $message, $data);
    }

    /**
     * Create failure result
     */
    public static function fail(string $message, array $data = []): self
    {
        return new self(false, $message, $data);
    }

    /**
     * Check if execution succeeded
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * Get result message
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Get result data
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Get specific data value
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'data' => $this->data,
        ];
    }
}
