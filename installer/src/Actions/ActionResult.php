<?php

declare(strict_types=1);

namespace Installer\Actions;

/**
 * Result of an action execution.
 */
class ActionResult
{
    private bool $success;

    private string $message;

    private array $data;

    private ?string $error;

    private function __construct(bool $success, string $message, array $data = [], ?string $error = null)
    {
        $this->success = $success;
        $this->message = $message;
        $this->data = $data;
        $this->error = $error;
    }

    public static function success(string $message = '', array $data = []): self
    {
        return new self(true, $message, $data);
    }

    public static function failure(string $message, ?string $error = null): self
    {
        return new self(false, $message, [], $error);
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'data' => $this->data,
            'error' => $this->error,
        ];
    }
}
