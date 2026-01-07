<?php

declare(strict_types=1);

namespace Installer\Steps;

/**
 * Interface for wizard steps.
 */
interface StepInterface
{
    /**
     * Get unique step identifier
     */
    public function getId(): string;

    /**
     * Get step title (translated)
     */
    public function getTitle(): string;

    /**
     * Get step description (translated)
     */
    public function getDescription(): string;

    /**
     * Validate step data before execution
     */
    public function validate(array $data): ValidationResult;

    /**
     * Execute step actions
     */
    public function execute(array $data): ExecutionResult;

    /**
     * Get view/template name for this step
     */
    public function getView(): string;

    /**
     * Get additional data for the view
     */
    public function getViewData(): array;
}
