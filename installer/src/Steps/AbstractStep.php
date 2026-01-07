<?php

declare(strict_types=1);

namespace Installer\Steps;

use Installer\I18n\Translator;
use Installer\Session\InstallState;

/**
 * Base class for wizard steps.
 */
abstract class AbstractStep implements StepInterface
{
    protected InstallState $state;

    protected Translator $translator;

    public function __construct(?InstallState $state = null, ?Translator $translator = null)
    {
        $this->state = $state ?? new InstallState;
        $this->translator = $translator ?? new Translator;
    }

    /**
     * Get step ID
     */
    abstract public function getId(): string;

    /**
     * Get step title (translated)
     */
    public function getTitle(): string
    {
        return $this->translator->translate($this->getId().'.title');
    }

    /**
     * Get step description (translated)
     */
    public function getDescription(): string
    {
        return $this->translator->translate($this->getId().'.description');
    }

    /**
     * Get view name for this step
     */
    public function getView(): string
    {
        return 'steps/'.$this->getId();
    }

    /**
     * Get view data for this step
     */
    public function getViewData(): array
    {
        return [];
    }

    /**
     * Create success result
     */
    protected function success(string $message = '', array $data = []): ExecutionResult
    {
        // Mark step as completed
        $this->state->completeStep($this->getId());

        return ExecutionResult::success($message, $data);
    }

    /**
     * Create failure result
     */
    protected function failure(string $message, array $data = []): ExecutionResult
    {
        return ExecutionResult::fail($message, $data);
    }

    /**
     * Validate email format
     */
    protected function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Translate a key
     */
    protected function __(?string $key, array $replace = []): string
    {
        if ($key === null) {
            return '';
        }

        return $this->translator->translate($key, $replace);
    }
}
