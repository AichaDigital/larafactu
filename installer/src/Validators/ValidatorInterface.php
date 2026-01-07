<?php

declare(strict_types=1);

namespace Installer\Validators;

/**
 * Interface for system requirement validators.
 */
interface ValidatorInterface
{
    /**
     * Run the validation check
     */
    public function check(): ValidatorResult;

    /**
     * Get validator name for display
     */
    public function getName(): string;

    /**
     * Get validator description
     */
    public function getDescription(): string;
}
