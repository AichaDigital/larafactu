<?php

declare(strict_types=1);

namespace Installer\Steps;

use Installer\Actions\ActionResult;
use Installer\Actions\CommandRunner;

/**
 * Step 2.5: Install Dependencies
 *
 * Runs composer install to set up Laravel and all dependencies.
 * This step is critical - without it, artisan commands won't work.
 */
class DependenciesStep extends AbstractStep
{
    public function getId(): string
    {
        return 'dependencies';
    }

    public function validate(array $data): ValidationResult
    {
        // Check if composer is available
        $output = [];
        $exitCode = 0;
        exec('which composer 2>&1', $output, $exitCode);

        if ($exitCode !== 0) {
            // Try composer.phar
            exec('which composer.phar 2>&1', $output, $exitCode);
            if ($exitCode !== 0) {
                return ValidationResult::invalid([
                    'composer' => __('dependencies.composer_not_found') ?? 'Composer no está instalado o no está en el PATH',
                ]);
            }
        }

        return ValidationResult::valid();
    }

    public function execute(array $data): ExecutionResult
    {
        $larafactuRoot = LARAFACTU_ROOT;

        // Check if vendor directory exists
        $vendorExists = is_dir($larafactuRoot.'/vendor');

        if ($vendorExists && ! ($data['force_install'] ?? false)) {
            // Vendor exists, skip installation
            $this->state->set('dependencies', [
                'installed' => true,
                'skipped' => true,
                'message' => 'Dependencias ya instaladas',
            ]);

            return $this->success(
                __('dependencies.already_installed') ?? 'Las dependencias ya están instaladas',
                ['skipped' => true]
            );
        }

        // Determine composer command
        $composerCmd = $this->findComposer();

        if (! $composerCmd) {
            return $this->failure(
                __('dependencies.composer_not_found') ?? 'No se encontró Composer',
                ['error' => 'Composer not found in PATH']
            );
        }

        // Build composer install command
        $isDev = ($data['install_dev'] ?? false);
        $command = $composerCmd.' install';

        if (! $isDev) {
            $command .= ' --no-dev';
        }

        $command .= ' --no-interaction --optimize-autoloader';

        // Execute composer install
        $cwd = getcwd();
        chdir($larafactuRoot);

        $output = [];
        $exitCode = 0;

        // Use proc_open for better output handling
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open($command, $descriptors, $pipes, $larafactuRoot, [
            'COMPOSER_NO_INTERACTION' => '1',
            'COMPOSER_ALLOW_SUPERUSER' => '1',
        ]);

        if (is_resource($process)) {
            fclose($pipes[0]);

            $stdout = stream_get_contents($pipes[1]);
            $stderr = stream_get_contents($pipes[2]);

            fclose($pipes[1]);
            fclose($pipes[2]);

            $exitCode = proc_close($process);

            $output = array_filter(array_merge(
                explode("\n", $stdout),
                explode("\n", $stderr)
            ));
        }

        chdir($cwd);

        if ($exitCode !== 0) {
            return $this->failure(
                __('dependencies.install_failed') ?? 'Error al instalar dependencias',
                [
                    'error' => implode("\n", array_slice($output, -20)),
                    'exit_code' => $exitCode,
                ]
            );
        }

        // Verify vendor directory now exists
        if (! is_dir($larafactuRoot.'/vendor')) {
            return $this->failure(
                'Composer se ejecutó pero el directorio vendor no se creó',
                ['output' => $output]
            );
        }

        // Verify artisan works
        $artisanCheck = [];
        exec("cd {$larafactuRoot} && php artisan --version 2>&1", $artisanCheck, $artisanExitCode);

        if ($artisanExitCode !== 0) {
            return $this->failure(
                'Composer se ejecutó pero artisan no funciona',
                ['artisan_output' => $artisanCheck]
            );
        }

        // Save state
        $this->state->set('dependencies', [
            'installed' => true,
            'skipped' => false,
            'dev' => $isDev,
            'artisan_version' => $artisanCheck[0] ?? 'unknown',
        ]);

        return $this->success(
            __('dependencies.install_success') ?? 'Dependencias instaladas correctamente',
            [
                'artisan_version' => $artisanCheck[0] ?? 'unknown',
                'output_lines' => count($output),
            ]
        );
    }

    public function getViewData(): array
    {
        $larafactuRoot = LARAFACTU_ROOT;
        $vendorExists = is_dir($larafactuRoot.'/vendor');
        $composerCmd = $this->findComposer();

        // Check if artisan works (if vendor exists)
        $artisanWorks = false;
        if ($vendorExists) {
            $output = [];
            exec("cd {$larafactuRoot} && php artisan --version 2>&1", $output, $exitCode);
            $artisanWorks = ($exitCode === 0);
        }

        return [
            'vendor_exists' => $vendorExists,
            'composer_available' => (bool) $composerCmd,
            'composer_command' => $composerCmd,
            'artisan_works' => $artisanWorks,
            'larafactu_root' => $larafactuRoot,
        ];
    }

    /**
     * Find composer executable
     */
    private function findComposer(): ?string
    {
        // Try common locations
        $locations = [
            'composer',
            'composer.phar',
            '/usr/local/bin/composer',
            '/usr/bin/composer',
        ];

        foreach ($locations as $location) {
            $output = [];
            exec("which {$location} 2>&1", $output, $exitCode);
            if ($exitCode === 0) {
                return $location;
            }
        }

        // Check if composer.phar exists in project root
        if (file_exists(LARAFACTU_ROOT.'/composer.phar')) {
            return 'php '.LARAFACTU_ROOT.'/composer.phar';
        }

        return null;
    }
}

