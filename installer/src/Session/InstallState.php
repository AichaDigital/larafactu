<?php

declare(strict_types=1);

namespace Installer\Session;

/**
 * Manages installation wizard state.
 *
 * Persists state to a JSON file for recovery across requests.
 */
class InstallState
{
    private string $storagePath;

    private array $data = [];

    private bool $loaded = false;

    public function __construct(?string $storagePath = null)
    {
        $this->storagePath = $storagePath ?? STORAGE_PATH.'/install_session.json';
    }

    /**
     * Load state from storage
     */
    public function load(): void
    {
        if ($this->loaded) {
            return;
        }

        if (file_exists($this->storagePath)) {
            $content = file_get_contents($this->storagePath);
            $this->data = json_decode($content, true) ?? [];
        }

        $this->loaded = true;
    }

    /**
     * Save state to storage
     */
    public function save(): void
    {
        $this->data['updated_at'] = time();

        file_put_contents(
            $this->storagePath,
            json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    /**
     * Get a value from state (supports dot notation)
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $this->load();

        // Support dot notation
        $keys = explode('.', $key);
        $value = $this->data;

        foreach ($keys as $k) {
            if (! is_array($value) || ! isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    /**
     * Set a value in state (supports dot notation)
     */
    public function set(string $key, mixed $value): void
    {
        $this->load();

        $keys = explode('.', $key);
        $data = &$this->data;

        foreach ($keys as $i => $k) {
            if ($i === count($keys) - 1) {
                $data[$k] = $value;
            } else {
                if (! isset($data[$k]) || ! is_array($data[$k])) {
                    $data[$k] = [];
                }
                $data = &$data[$k];
            }
        }

        $this->save();
    }

    /**
     * Check if key exists
     */
    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    /**
     * Remove a key from state
     */
    public function remove(string $key): void
    {
        $this->load();

        $keys = explode('.', $key);
        $data = &$this->data;

        foreach ($keys as $i => $k) {
            if ($i === count($keys) - 1) {
                unset($data[$k]);
            } else {
                if (! isset($data[$k])) {
                    return;
                }
                $data = &$data[$k];
            }
        }

        $this->save();
    }

    /**
     * Clear all state
     */
    public function clear(): void
    {
        $this->data = [];

        if (file_exists($this->storagePath)) {
            unlink($this->storagePath);
        }
    }

    /**
     * Get all data
     */
    public function all(): array
    {
        $this->load();

        return $this->data;
    }

    /**
     * Mark a step as completed
     */
    public function completeStep(string $stepId): void
    {
        $completed = $this->get('completed_steps', []);

        if (! in_array($stepId, $completed)) {
            $completed[] = $stepId;
            $this->set('completed_steps', $completed);
        }
    }

    /**
     * Get completed steps
     */
    public function getCompletedSteps(): array
    {
        return $this->get('completed_steps', []);
    }

    /**
     * Check if step is completed
     */
    public function isStepCompleted(string $stepId): bool
    {
        return in_array($stepId, $this->getCompletedSteps());
    }

    /**
     * Get current step
     */
    public function getCurrentStep(): string
    {
        return $this->get('current_step', 'welcome');
    }

    /**
     * Set current step
     */
    public function setCurrentStep(string $stepId): void
    {
        $this->set('current_step', $stepId);
    }
}
