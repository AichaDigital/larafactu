<?php

declare(strict_types=1);

namespace App\Console\Commands\Installer;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Schema;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\note;
use function Laravel\Prompts\select;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\warning;

/**
 * Larafactu Install Command
 *
 * Instalador inteligente que detecta el entorno y configura automáticamente:
 * - LOCAL: Crea symlinks a paquetes locales y modifica composer.json
 * - PRODUCCIÓN: Usa paquetes de GitHub directamente
 *
 * Uso interactivo:
 *   php artisan larafactu:install
 *
 * Uso en scripts (no interactivo):
 *   php artisan larafactu:install --local --packages-path=/ruta --fresh --no-interaction
 *   php artisan larafactu:install --production --no-interaction
 */
class InstallCommand extends Command
{
    protected $signature = 'larafactu:install
                            {--local : Instalar en modo LOCAL (symlinks a paquetes)}
                            {--production : Instalar en modo PRODUCCIÓN (GitHub)}
                            {--fresh : Ejecutar migrate:fresh --seed}
                            {--seed : Ejecutar seeders después de migrar}
                            {--skip-composer : No ejecutar composer install}
                            {--skip-migrations : No ejecutar migraciones}
                            {--skip-seeders : No ejecutar seeders}
                            {--packages-path= : Ruta a los paquetes locales (solo modo local)}';

    protected $description = 'Instala y configura Larafactu (detecta entorno automáticamente)';

    private const PACKAGES = [
        'larabill' => 'aichadigital/larabill',
        'lara-verifactu' => 'aichadigital/lara-verifactu',
        'laratickets' => 'aichadigital/laratickets',
        'lararoi' => 'aichadigital/lararoi',
    ];

    private const DEFAULT_PACKAGES_PATH = '../../development/packages/aichadigital';

    private string $installMode;

    private string $packagesPath;

    private bool $isInteractive;

    public function handle(): int
    {
        $this->isInteractive = ! $this->option('no-interaction');

        $this->showBanner();

        // ═══════════════════════════════════════════════════════════
        // PASO 1: Seleccionar modo de instalación (PRIMERO)
        // ═══════════════════════════════════════════════════════════
        $this->installMode = $this->selectInstallMode();

        if (! $this->installMode) {
            warning('❌ Instalación cancelada');

            return self::FAILURE;
        }

        info("📋 Modo de instalación: {$this->installMode}");
        $this->newLine();

        // ═══════════════════════════════════════════════════════════
        // PASO 2: Si es LOCAL, configurar symlinks y composer.json
        // ═══════════════════════════════════════════════════════════
        if ($this->installMode === 'local') {
            if (! $this->setupLocalPackages()) {
                return self::FAILURE;
            }
        }

        // ═══════════════════════════════════════════════════════════
        // PASO 3: Ejecutar composer install
        // ═══════════════════════════════════════════════════════════
        if (! $this->option('skip-composer')) {
            if (! $this->runComposerInstall()) {
                return self::FAILURE;
            }
        }

        // ═══════════════════════════════════════════════════════════
        // PASO 4: Configurar entorno (.env, APP_KEY)
        // ═══════════════════════════════════════════════════════════
        $this->setupEnvironment();

        // ═══════════════════════════════════════════════════════════
        // PASO 5: Ejecutar migraciones
        // ═══════════════════════════════════════════════════════════
        if (! $this->option('skip-migrations')) {
            $this->runMigrations();
        }

        // ═══════════════════════════════════════════════════════════
        // PASO 6: Ejecutar seeders
        // ═══════════════════════════════════════════════════════════
        if (! $this->option('skip-seeders')) {
            $shouldSeed = $this->option('seed')
                || $this->option('fresh')
                || $this->installMode === 'local';

            if ($shouldSeed) {
                $this->runSeeders();
            }
        }

        // ═══════════════════════════════════════════════════════════
        // PASO 7: Limpiar/optimizar cache
        // ═══════════════════════════════════════════════════════════
        $this->clearCache();

        $this->showSuccess();

        return self::SUCCESS;
    }

    private function showBanner(): void
    {
        $this->newLine();
        $this->line('╔══════════════════════════════════════════╗');
        $this->line('║       🚀 <fg=cyan>LARAFACTU INSTALLER</> 🚀          ║');
        $this->line('║                                          ║');
        $this->line('║  Billing & Invoicing for Hosting Spain   ║');
        $this->line('╚══════════════════════════════════════════╝');
        $this->newLine();
    }

    /**
     * Selecciona el modo de instalación.
     * Prioridad: opción CLI > interactivo
     */
    private function selectInstallMode(): ?string
    {
        // Si se especificó explícitamente por CLI, usar eso
        if ($this->option('local')) {
            return 'local';
        }

        if ($this->option('production')) {
            return 'production';
        }

        // Si no es interactivo y no se especificó modo, error
        if (! $this->isInteractive) {
            $this->error('❌ Debes especificar --local o --production en modo no interactivo');

            return null;
        }

        // Modo interactivo: preguntar al usuario
        $isLocalEnv = App::environment('local');
        $hasLocalPackages = File::isDirectory(base_path('packages/aichadigital'));

        // Construir hint informativo
        $hints = [];
        if ($isLocalEnv) {
            $hints[] = 'APP_ENV=local';
        }
        if ($hasLocalPackages) {
            $hints[] = 'packages/ existente';
        }
        $hint = $hints ? 'Detectado: '.implode(', ', $hints) : null;

        return select(
            label: '¿Qué tipo de instalación deseas?',
            options: [
                'local' => '🏠 LOCAL - Desarrollo con paquetes locales (symlinks)',
                'production' => '🌐 PRODUCCIÓN - Paquetes desde GitHub',
            ],
            default: $isLocalEnv ? 'local' : 'production',
            hint: $hint
        );
    }

    /**
     * Configura los paquetes locales: symlinks + composer.json
     */
    private function setupLocalPackages(): bool
    {
        info('📦 Configurando paquetes locales...');
        $this->newLine();

        // Determinar ruta a paquetes
        $this->packagesPath = $this->resolvePackagesPath();

        if (! $this->packagesPath) {
            warning('❌ No se encontró la ruta a los paquetes locales');
            $this->line('   Usa --packages-path=/ruta/a/paquetes');

            return false;
        }

        // Resolver ruta absoluta para mostrar
        $absolutePath = realpath($this->packagesPath) ?: $this->packagesPath;
        info("   Ruta de paquetes: {$absolutePath}");
        $this->newLine();

        // Crear directorio packages/aichadigital si no existe
        $localPackagesDir = base_path('packages/aichadigital');
        if (! File::isDirectory($localPackagesDir)) {
            File::makeDirectory($localPackagesDir, 0755, true);
            info('   ✓ Creado packages/aichadigital/');
        }

        // Crear symlinks
        $this->createSymlinks($localPackagesDir);

        // Modificar composer.json para usar paths locales
        $this->updateComposerForLocal();

        return true;
    }

    /**
     * Resuelve la ruta a los paquetes de desarrollo.
     * Prioridad: opción CLI > detección automática > preguntar
     */
    private function resolvePackagesPath(): ?string
    {
        // 1. Si se especificó por CLI
        if ($this->option('packages-path')) {
            $path = $this->option('packages-path');
            if (File::isDirectory($path)) {
                return $path;
            }
            warning("   ⚠️  Ruta especificada no existe: {$path}");

            return null;
        }

        // 2. Intentar ruta por defecto relativa
        $defaultPath = base_path(self::DEFAULT_PACKAGES_PATH);
        if (File::isDirectory($defaultPath)) {
            return $defaultPath;
        }

        // 3. Intentar ruta absoluta común
        $absolutePath = '/Users/'.get_current_user().'/development/packages/aichadigital';
        if (File::isDirectory($absolutePath)) {
            return $absolutePath;
        }

        // 4. Si no es interactivo, fallar
        if (! $this->isInteractive) {
            return null;
        }

        // 5. Preguntar al usuario
        $customPath = $this->ask(
            '¿Dónde están los paquetes de desarrollo?',
            $absolutePath
        );

        if ($customPath && File::isDirectory($customPath)) {
            return $customPath;
        }

        return null;
    }

    /**
     * Crea los symlinks a los paquetes de desarrollo.
     */
    private function createSymlinks(string $localPackagesDir): void
    {
        foreach (self::PACKAGES as $package => $composerName) {
            $sourcePath = $this->packagesPath.'/'.$package;
            $linkPath = $localPackagesDir.'/'.$package;

            // Verificar que el paquete source existe
            if (! File::isDirectory($sourcePath)) {
                warning("   ⚠️  Paquete no encontrado: {$package}");

                continue;
            }

            // Si ya existe el symlink, verificar que apunta al lugar correcto
            if (is_link($linkPath)) {
                $currentTarget = readlink($linkPath);
                // Comparar rutas normalizadas
                $normalizedCurrent = realpath($currentTarget) ?: $currentTarget;
                $normalizedSource = realpath($sourcePath) ?: $sourcePath;

                if ($normalizedCurrent === $normalizedSource) {
                    $this->line("   ✓ Symlink OK: {$package}");

                    continue;
                }
                // Symlink incorrecto, eliminar
                unlink($linkPath);
            } elseif (File::exists($linkPath)) {
                // Es un directorio real, no symlink
                warning("   ⚠️  {$package} existe pero no es symlink, saltando");

                continue;
            }

            // Crear symlink
            symlink($sourcePath, $linkPath);
            info("   ✓ Symlink creado: {$package}");
        }

        $this->newLine();
    }

    /**
     * Actualiza composer.json para usar paths locales.
     */
    private function updateComposerForLocal(): void
    {
        info('📝 Actualizando composer.json para desarrollo local...');

        $composerPath = base_path('composer.json');
        $composer = json_decode(File::get($composerPath), true);

        // Construir nuevos repositories con paths locales
        $newRepositories = [];
        foreach (self::PACKAGES as $package => $composerName) {
            $newRepositories[] = [
                'type' => 'path',
                'url' => './packages/aichadigital/'.$package,
                'options' => [
                    'symlink' => true,
                ],
            ];
        }

        $composer['repositories'] = $newRepositories;

        // Guardar
        File::put(
            $composerPath,
            json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n"
        );

        info('   ✓ composer.json actualizado con paths locales');
        warning('   ⚠️  IMPORTANTE: No commitear este cambio a Git');
        $this->newLine();
    }

    /**
     * Ejecuta composer install.
     */
    private function runComposerInstall(): bool
    {
        info('📦 Ejecutando composer install...');

        if ($this->isInteractive) {
            $result = spin(
                callback: fn () => Process::timeout(600)->run('composer install --no-interaction'),
                message: 'Instalando dependencias...'
            );
        } else {
            $this->line('   Instalando dependencias...');
            $result = Process::timeout(600)->run('composer install --no-interaction');
        }

        if ($result->successful()) {
            info('   ✓ Dependencias instaladas');
            $this->newLine();

            return true;
        }

        warning('   ⚠️  Error en composer install:');
        $this->line($result->errorOutput());

        if ($this->isInteractive && confirm('¿Continuar de todos modos?', false)) {
            return true;
        }

        return $this->isInteractive ? false : true; // En scripts, continuar
    }

    /**
     * Configura el entorno (.env, APP_KEY).
     */
    private function setupEnvironment(): void
    {
        info('🔧 Configurando entorno...');

        // Verificar .env existe
        if (! File::exists(base_path('.env'))) {
            if (File::exists(base_path('.env.example'))) {
                File::copy(base_path('.env.example'), base_path('.env'));
                info('   ✓ .env creado desde .env.example');
            } else {
                warning('   ⚠️  No existe .env ni .env.example');
            }
        }

        // Generar APP_KEY si no existe
        $envContent = File::exists(base_path('.env')) ? File::get(base_path('.env')) : '';
        if (! str_contains($envContent, 'APP_KEY=base64:')) {
            Artisan::call('key:generate', ['--force' => true]);
            info('   ✓ APP_KEY generado');
        }

        info('   ✓ Entorno configurado');
        $this->newLine();
    }

    /**
     * Ejecuta las migraciones.
     */
    private function runMigrations(): void
    {
        info('🗄️  Migraciones...');

        // Verificar si hay tablas existentes
        $hasTables = false;
        try {
            $hasTables = Schema::hasTable('users');
        } catch (\Exception $e) {
            // No hay conexión o no hay tablas
        }

        // Determinar tipo de migración
        $migrationType = $this->determineMigrationType($hasTables);

        if ($migrationType === 'none') {
            info('   ⏭️  Migraciones omitidas');
            $this->newLine();

            return;
        }

        // Ejecutar migración según tipo
        if ($migrationType === 'fresh') {
            $this->executeMigrateFresh();
        } else {
            $this->executeMigrate();
        }

        $this->newLine();
    }

    /**
     * Determina el tipo de migración a ejecutar.
     */
    private function determineMigrationType(bool $hasTables): string
    {
        // Si se especificó --fresh por CLI, usar fresh
        if ($this->option('fresh')) {
            return 'fresh';
        }

        // Si no es interactivo
        if (! $this->isInteractive) {
            // Sin tablas → migrate normal
            // Con tablas → migrate normal (no fresh sin confirmación)
            return 'migrate';
        }

        // Modo interactivo: preguntar al usuario
        if (! $hasTables) {
            // No hay tablas, preguntar si quiere continuar
            if (confirm('No hay tablas. ¿Ejecutar migraciones?', true)) {
                return 'migrate';
            }

            return 'none';
        }

        // Hay tablas existentes, preguntar qué hacer
        $options = [
            'fresh' => '🔄 FRESH - Borrar todo y recrear (recomendado en desarrollo)',
            'migrate' => '➕ MIGRATE - Solo ejecutar migraciones pendientes',
            'none' => '⏭️  OMITIR - No ejecutar migraciones',
        ];

        // En local, recomendar fresh
        $default = $this->installMode === 'local' ? 'fresh' : 'migrate';

        return select(
            label: 'Ya existen tablas en la base de datos. ¿Qué deseas hacer?',
            options: $options,
            default: $default,
            hint: $this->installMode === 'local'
                ? 'En desarrollo local, fresh evita problemas de estado inconsistente'
                : 'En producción, usa migrate para preservar datos'
        );
    }

    /**
     * Ejecuta migrate:fresh.
     */
    private function executeMigrateFresh(): void
    {
        if ($this->isInteractive) {
            spin(
                callback: fn () => Artisan::call('migrate:fresh', ['--force' => true]),
                message: 'Ejecutando migrate:fresh...'
            );
        } else {
            Artisan::call('migrate:fresh', ['--force' => true]);
        }
        info('   ✓ Base de datos recreada (fresh)');
    }

    /**
     * Ejecuta migrate normal.
     */
    private function executeMigrate(): void
    {
        if ($this->isInteractive) {
            spin(
                callback: fn () => Artisan::call('migrate', ['--force' => true]),
                message: 'Ejecutando migraciones...'
            );
        } else {
            Artisan::call('migrate', ['--force' => true]);
        }
        info('   ✓ Migraciones ejecutadas');
    }

    /**
     * Ejecuta los seeders.
     */
    private function runSeeders(): void
    {
        info('🌱 Ejecutando seeders...');

        try {
            if ($this->isInteractive) {
                spin(
                    callback: fn () => Artisan::call('db:seed', ['--force' => true]),
                    message: 'Ejecutando seeders...'
                );
            } else {
                Artisan::call('db:seed', ['--force' => true]);
            }
            info('   ✓ Datos de prueba cargados');
        } catch (\Exception $e) {
            warning('   ⚠️  Error en seeders: '.$e->getMessage());
        }

        $this->newLine();
    }

    /**
     * Limpia y optimiza el cache.
     */
    private function clearCache(): void
    {
        info('🧹 Limpiando cache...');

        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('view:clear');

        if ($this->installMode === 'production') {
            Artisan::call('config:cache');
            Artisan::call('route:cache');
            Artisan::call('view:cache');
            info('   ✓ Cache optimizado para producción');
        } else {
            info('   ✓ Cache limpiado');
        }

        $this->newLine();
    }

    /**
     * Muestra el mensaje de éxito final.
     */
    private function showSuccess(): void
    {
        $this->line('╔══════════════════════════════════════════╗');
        $this->line('║     ✅ <fg=green>INSTALACIÓN COMPLETADA</> ✅         ║');
        $this->line('╚══════════════════════════════════════════╝');
        $this->newLine();

        if ($this->installMode === 'local') {
            info('🏠 Modo LOCAL configurado');
            $this->newLine();
            $this->line('   📍 Admin: <fg=cyan>http://larafactu.test/admin</>');
            $this->line('   👤 Usuario: <fg=yellow>admin@example.com</>');
            $this->line('   🔑 Password: <fg=yellow>password</>');
            $this->newLine();
            note('   Los paquetes están enlazados vía symlinks');
            $this->newLine();
            warning('   ⚠️  RECUERDA: No commitear composer.json modificado');
        } else {
            info('🌐 Modo PRODUCCIÓN configurado');
            $this->newLine();
            $this->line('   Los paquetes se instalaron desde GitHub');
        }

        $this->newLine();
    }
}
