<?php

declare(strict_types=1);

namespace App\Console\Commands\Installer;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\warning;

/**
 * Resetea composer.json a la versión de producción (GitHub repos).
 *
 * Útil antes de hacer commit/push para asegurar que no se suben paths locales.
 */
class ComposerResetCommand extends Command
{
    protected $signature = 'larafactu:composer-reset
                            {--force : No pedir confirmación}';

    protected $description = 'Resetea composer.json a versión producción (GitHub repos)';

    public function handle(): int
    {
        $this->newLine();
        info('🔄 Reseteando composer.json a versión producción...');
        $this->newLine();

        // Verificar si tiene skip-worktree
        $lsResult = Process::run('git ls-files -v composer.json');
        $hasSkipWorktree = str_starts_with($lsResult->output(), 'S');

        if ($hasSkipWorktree) {
            info('   composer.json tiene skip-worktree activo');

            // Quitar skip-worktree primero
            Process::run('git update-index --no-skip-worktree composer.json');
            info('   ✓ skip-worktree removido');
        }

        // Confirmar
        if (! $this->option('force') && ! $this->option('no-interaction')) {
            if (! confirm('¿Restaurar composer.json desde git? (perderás paths locales)', true)) {
                warning('   Cancelado');

                return self::FAILURE;
            }
        }

        // Restaurar desde git
        $result = Process::run('git checkout composer.json');

        if ($result->successful()) {
            info('   ✓ composer.json restaurado a versión producción');
            $this->newLine();
            info('   Ahora puedes hacer commit/push sin paths locales');
            $this->newLine();
            warning('   Para volver a desarrollo local:');
            $this->line('   php artisan larafactu:install --local');
        } else {
            warning('   ⚠️  Error al restaurar: '.$result->errorOutput());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
