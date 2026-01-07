<?php

declare(strict_types=1);

namespace Installer\Steps;

/**
 * Result of step validation.
 */
class ValidationResult
{
    private bool $valid;

    private array $errors;

    private function __construct(bool $valid, array $errors = [])
    {
        $this->valid = $valid;
        $this->errors = $errors;
    }

    /**
     * Create valid result
     */
    public static function valid(): self
    {
        return new self(true, []);
    }

    /**
     * Create invalid result with errors
     */
    public static function invalid(array $errors): self
    {
        return new self(false, $errors);
    }

    /**
     * Merge multiple validation results
     */
    public static function merge(ValidationResult ...$results): self
    {
        $errors = [];

        foreach ($results as $result) {
            if (! $result->isValid()) {
                $errors = array_merge($errors, $result->getErrors());
            }
        }

        return empty($errors) ? self::valid() : self::invalid($errors);
    }

    /**
     * Check if validation passed
     */
    public function isValid(): bool
    {
        return $this->valid;
    }

    /**
     * Get validation errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get first error message
     */
    public function getFirstError(): ?string
    {
        return $this->errors[array_key_first($this->errors)] ?? null;
    }

    /**
     * Check if specific field has error
     */
    public function hasError(string $field): bool
    {
        return isset($this->errors[$field]);
    }

    /**
     * Get error for specific field
     */
    public function getError(string $field): ?string
    {
        return $this->errors[$field] ?? null;
    }
}
