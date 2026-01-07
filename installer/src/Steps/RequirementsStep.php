<?php

declare(strict_types=1);

namespace Installer\Steps;

use Installer\Validators\ExtensionsValidator;
use Installer\Validators\PhpVersionValidator;
use Installer\Validators\WritablePathsValidator;

/**
 * Step 2: Requirements Check
 *
 * Validates:
 * - PHP version (>= 8.4)
 * - Required PHP extensions
 * - Writable directories
 */
class RequirementsStep extends AbstractStep
{
    private array $validators = [];

    private array $results = [];

    public function getId(): string
    {
        return 'requirements';
    }

    public function validate(array $data): ValidationResult
    {
        // Run all validators
        $this->runValidators();

        // Check if any failed
        $failed = array_filter($this->results, fn ($r) => $r['result']->isError());

        if (! empty($failed)) {
            return ValidationResult::invalid([
                'requirements' => __('requirements.some_failed'),
            ]);
        }

        return ValidationResult::valid();
    }

    public function execute(array $data): ExecutionResult
    {
        // Validation already done, just save results to state
        $this->runValidators();

        // Check if all passed
        $failed = array_filter($this->results, fn ($r) => $r['result']->isError());

        if (! empty($failed)) {
            return $this->failure(
                __('requirements.some_failed'),
                ['results' => $this->formatResults()]
            );
        }

        // Save to state for reference
        $this->state->set('requirements', $this->formatResults());

        return $this->success(__('requirements.all_passed'), [
            'results' => $this->formatResults(),
        ]);
    }

    public function getViewData(): array
    {
        $this->runValidators();

        return [
            'results' => $this->formatResults(),
            'allPassed' => $this->allPassed(),
        ];
    }

    /**
     * Run all validators
     */
    private function runValidators(): void
    {
        if (! empty($this->results)) {
            return; // Already run
        }

        $this->validators = [
            'php_version' => new PhpVersionValidator,
            'extensions' => new ExtensionsValidator,
            'writable_paths' => new WritablePathsValidator,
        ];

        foreach ($this->validators as $key => $validator) {
            $this->results[$key] = [
                'name' => $validator->getName(),
                'description' => $validator->getDescription(),
                'result' => $validator->check(),
            ];
        }
    }

    /**
     * Format results for display
     */
    private function formatResults(): array
    {
        $formatted = [];

        foreach ($this->results as $key => $data) {
            $formatted[$key] = [
                'name' => $data['name'],
                'description' => $data['description'],
                'status' => $data['result']->getStatus(),
                'message' => $data['result']->getMessage(),
                'details' => $data['result']->getDetails(),
            ];
        }

        return $formatted;
    }

    /**
     * Check if all requirements passed
     */
    private function allPassed(): bool
    {
        foreach ($this->results as $data) {
            if ($data['result']->isError()) {
                return false;
            }
        }

        return true;
    }
}
