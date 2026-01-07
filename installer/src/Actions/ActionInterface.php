<?php

declare(strict_types=1);

namespace Installer\Actions;

/**
 * Interface for system actions.
 */
interface ActionInterface
{
    /**
     * Execute the action
     */
    public function execute(): ActionResult;

    /**
     * Check if action can be executed
     */
    public function canExecute(): bool;

    /**
     * Get action name
     */
    public function getName(): string;
}
