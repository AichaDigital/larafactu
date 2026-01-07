<?php

declare(strict_types=1);

namespace Installer\Actions;

/**
 * Runs system commands with whitelist protection.
 */
class CommandRunner
{
    /**
     * Whitelisted commands that can be executed
     */
    private const WHITELIST = [
        'php' => [
            ['artisan', 'migrate', '--force'],
            ['artisan', 'migrate:fresh', '--force'],
            ['artisan', 'migrate:status'],
            ['artisan', 'db:seed', '--force'],
            ['artisan', 'config:cache'],
            ['artisan', 'config:clear'],
            ['artisan', 'cache:clear'],
            ['artisan', 'view:clear'],
            ['artisan', 'route:clear'],
            ['artisan', 'storage:link'],
            ['artisan', 'key:generate', '--force'],
            ['artisan', 'optimize'],
            ['artisan', 'optimize:clear'],
        ],
        'composer' => [
            ['dump-autoload'],
            ['dump-autoload', '--optimize'],
        ],
    ];

    private string $workingDir;

    private array $output = [];

    private int $exitCode = 0;

    public function __construct(?string $workingDir = null)
    {
        $this->workingDir = $workingDir ?? LARAFACTU_ROOT;
    }

    /**
     * Run a whitelisted command
     */
    public function run(string $binary, array $args): ActionResult
    {
        // Verify command is whitelisted
        if (! $this->isWhitelisted($binary, $args)) {
            return ActionResult::failure(
                'Comando no permitido por seguridad',
                "Command not whitelisted: {$binary} ".implode(' ', $args)
            );
        }

        // Build full command
        $command = $this->buildCommand($binary, $args);

        // Execute
        return $this->execute($command);
    }

    /**
     * Run artisan command (shorthand)
     */
    public function artisan(string ...$args): ActionResult
    {
        return $this->run('php', array_merge(['artisan'], $args));
    }

    /**
     * Check if command is whitelisted
     */
    public function isWhitelisted(string $binary, array $args): bool
    {
        if (! isset(self::WHITELIST[$binary])) {
            return false;
        }

        foreach (self::WHITELIST[$binary] as $allowed) {
            if ($this->matchesPattern($args, $allowed)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if args match an allowed pattern
     */
    private function matchesPattern(array $args, array $pattern): bool
    {
        // Args must start with the pattern
        if (count($args) < count($pattern)) {
            return false;
        }

        for ($i = 0; $i < count($pattern); $i++) {
            if ($args[$i] !== $pattern[$i]) {
                return false;
            }
        }

        return true;
    }

    /**
     * Build command string
     */
    private function buildCommand(string $binary, array $args): string
    {
        $parts = [$binary];

        foreach ($args as $arg) {
            $parts[] = escapeshellarg($arg);
        }

        return implode(' ', $parts);
    }

    /**
     * Execute a command
     */
    private function execute(string $command): ActionResult
    {
        $this->output = [];
        $this->exitCode = 0;

        // Change to working directory
        $cwd = getcwd();
        chdir($this->workingDir);

        try {
            // Execute command
            exec($command.' 2>&1', $this->output, $this->exitCode);

            // Restore working directory
            chdir($cwd);

            if ($this->exitCode !== 0) {
                return ActionResult::failure(
                    'El comando falló con código '.$this->exitCode,
                    implode("\n", $this->output)
                );
            }

            return ActionResult::success(
                'Comando ejecutado correctamente',
                [
                    'command' => $command,
                    'output' => $this->output,
                    'exitCode' => $this->exitCode,
                ]
            );

        } catch (\Throwable $e) {
            chdir($cwd);

            return ActionResult::failure(
                'Error al ejecutar comando: '.$e->getMessage(),
                $e->getMessage()
            );
        }
    }

    /**
     * Get last command output
     */
    public function getOutput(): array
    {
        return $this->output;
    }

    /**
     * Get last exit code
     */
    public function getExitCode(): int
    {
        return $this->exitCode;
    }

    /**
     * Get output as string
     */
    public function getOutputString(): string
    {
        return implode("\n", $this->output);
    }

    /**
     * Get all whitelisted commands (for documentation)
     */
    public static function getWhitelist(): array
    {
        return self::WHITELIST;
    }
}
