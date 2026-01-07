<?php

declare(strict_types=1);

namespace Installer\Steps;

use Installer\I18n\Translator;
use Installer\Session\InstallState;

/**
 * Registry for wizard steps.
 */
class StepRegistry
{
    private array $steps = [];

    private array $order = [];

    private InstallState $state;

    private Translator $translator;

    public function __construct(?InstallState $state = null, ?Translator $translator = null)
    {
        $this->state = $state ?? new InstallState;
        $this->translator = $translator ?? new Translator;

        $this->registerDefaultSteps();
    }

    /**
     * Register default steps in order
     */
    private function registerDefaultSteps(): void
    {
        $this->order = [
            'welcome',
            'requirements',
            'dependencies',  // NEW: Install composer dependencies (Laravel)
            'appkey',
            'database',
            'migrations',
            'company',
            'verifactu',
            'admin',
            'finalize',
        ];

        // Create step instances lazily
        $this->steps = [
            'welcome' => fn () => new WelcomeStep($this->state, $this->translator),
            'requirements' => fn () => new RequirementsStep($this->state, $this->translator),
            'dependencies' => fn () => new DependenciesStep($this->state, $this->translator),
            'appkey' => fn () => new AppKeyStep($this->state, $this->translator),
            'database' => fn () => new DatabaseStep($this->state, $this->translator),
            'migrations' => fn () => new MigrationsStep($this->state, $this->translator),
            'company' => fn () => new CompanyStep($this->state, $this->translator),
            'verifactu' => fn () => new VerifactuStep($this->state, $this->translator),
            'admin' => fn () => new AdminStep($this->state, $this->translator),
            'finalize' => fn () => new FinalizeStep($this->state, $this->translator),
        ];
    }

    /**
     * Register a custom step
     */
    public function register(string $id, callable|StepInterface $step, ?string $after = null): void
    {
        if ($step instanceof StepInterface) {
            $this->steps[$id] = fn () => $step;
        } else {
            $this->steps[$id] = $step;
        }

        if ($after !== null) {
            $pos = array_search($after, $this->order);
            if ($pos !== false) {
                array_splice($this->order, $pos + 1, 0, [$id]);

                return;
            }
        }

        $this->order[] = $id;
    }

    /**
     * Get a step by ID
     */
    public function get(string $id): ?StepInterface
    {
        if (! isset($this->steps[$id])) {
            return null;
        }

        $step = $this->steps[$id];

        // Lazy instantiation
        if (is_callable($step)) {
            $step = $step();
            $this->steps[$id] = fn () => $step;
        }

        return $step;
    }

    /**
     * Get all steps (ordered)
     */
    public function getAll(): array
    {
        $steps = [];

        foreach ($this->order as $id) {
            $step = $this->get($id);
            if ($step !== null) {
                $steps[$id] = [
                    'id' => $step->getId(),
                    'title' => $step->getTitle(),
                    'description' => $step->getDescription(),
                ];
            }
        }

        return $steps;
    }

    /**
     * Get next step
     */
    public function getNext(string $currentId): ?StepInterface
    {
        $pos = array_search($currentId, $this->order);

        if ($pos === false || $pos >= count($this->order) - 1) {
            return null;
        }

        return $this->get($this->order[$pos + 1]);
    }

    /**
     * Get previous step
     */
    public function getPrevious(string $currentId): ?StepInterface
    {
        $pos = array_search($currentId, $this->order);

        if ($pos === false || $pos <= 0) {
            return null;
        }

        return $this->get($this->order[$pos - 1]);
    }

    /**
     * Get step order
     */
    public function getOrder(): array
    {
        return $this->order;
    }

    /**
     * Get step index
     */
    public function getIndex(string $id): int
    {
        $pos = array_search($id, $this->order);

        return $pos !== false ? $pos : -1;
    }
}
