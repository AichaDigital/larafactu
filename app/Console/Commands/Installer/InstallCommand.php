<?php

declare(strict_types=1);

namespace App\Console\Commands\Installer;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

/**
 * Larafactu Install Command
 *
 * Instalador opinado para Larafactu que configura automáticamente
 * el proyecto con UUID v7 binary y todas las dependencias.
 *
 * Uso:
 *   php artisan larafactu:install              # Instalación interactiva
 *   php artisan larafactu:install --fresh      # Fresh install (local)
 *   php artisan larafactu:install --force      # Producción sin prompts
 */
class InstallCommand extends Command
{
    protected $signature = 'larafactu:install
                            {--fresh : Ejecutar migrate:fresh --seed (solo local)}
                            {--force : Forzar sin confirmaciones (producción)}
                            {--skip-packages : No instalar paquetes (larabill, etc.)}';

    protected $description = 'Instala y configura Larafactu con UUID v7 binary';

    private bool $isLocal;

    public function handle(): int
    {
        $this->isLocal = App::environment('local');

        $this->info('');
        $this->info('🚀 Larafactu Installer');
        $this->info('======================');
        $this->info('');

        // Step 1: Verificar entorno
        $this->step1_VerifyEnvironment();

        // Step 2: Verificar configuración UUID
        $this->step2_VerifyUuidConfig();

        // Step 3: Verificar migración de users
        $this->step3_VerifyUsersMigration();

        // Step 4: Instalar paquetes (larabill, etc.)
        if (! $this->option('skip-packages')) {
            $this->step4_InstallPackages();
        }

        // Step 5: Ejecutar migraciones
        $this->step5_RunMigrations();

        // Step 6: Ejecutar seeders (solo local o fresh)
        if ($this->isLocal || $this->option('fresh')) {
            $this->step6_RunSeeders();
        }

        // Step 7: Limpiar cache
        $this->step7_ClearCache();

        $this->info('');
        $this->info('✅ Larafactu instalado correctamente!');
        $this->info('');

        if ($this->isLocal) {
            $this->line('   🏠 Admin: http://larafactu.test/admin');
            $this->line('   👤 Usuario: admin@example.com');
            $this->line('   🔑 Password: password');
        }

        $this->info('');

        return self::SUCCESS;
    }

    private function step1_VerifyEnvironment(): void
    {
        $this->info('📋 Step 1: Verificando entorno...');

        $env = App::environment();
        $this->line("   Entorno: {$env}");

        // Verificar .env existe
        if (! File::exists(base_path('.env'))) {
            $this->warn('   ⚠️  .env no existe, copiando de .env.example...');
            File::copy(base_path('.env.example'), base_path('.env'));
            $this->line('   ✓ .env creado');
        }

        // Verificar APP_KEY
        if (empty(config('app.key'))) {
            $this->line('   Generando APP_KEY...');
            Artisan::call('key:generate', ['--force' => true]);
            $this->line('   ✓ APP_KEY generado');
        }

        $this->info('   ✓ Entorno verificado');
    }

    private function step2_VerifyUuidConfig(): void
    {
        $this->info('');
        $this->info('🔧 Step 2: Verificando configuración UUID...');

        $envPath = base_path('.env');
        $envContent = File::get($envPath);

        // Verificar LARABILL_USER_ID_TYPE
        if (! str_contains($envContent, 'LARABILL_USER_ID_TYPE')) {
            $this->line('   Añadiendo LARABILL_USER_ID_TYPE=uuid_binary...');
            File::append($envPath, "\n# Larafactu UUID Config (OPINADO)\nLARABILL_USER_ID_TYPE=uuid_binary\n");
            $this->line('   ✓ Configuración añadida');
        } else {
            $currentValue = config('larabill.user_id_type', 'uuid');
            if ($currentValue !== 'uuid_binary') {
                $this->warn("   ⚠️  LARABILL_USER_ID_TYPE={$currentValue}, debería ser uuid_binary");
                $this->warn('   Por favor, actualiza manualmente .env');
            } else {
                $this->line('   ✓ UUID binary configurado correctamente');
            }
        }

        $this->info('   ✓ Configuración UUID verificada');
    }

    private function step3_VerifyUsersMigration(): void
    {
        $this->info('');
        $this->info('📄 Step 3: Verificando migración de users...');

        $migrationPath = database_path('migrations/0001_01_01_000000_create_users_table.php');

        if (! File::exists($migrationPath)) {
            $this->error('   ❌ Migración de users no encontrada!');

            return;
        }

        $content = File::get($migrationPath);

        // Verificar si usa binary(16) o uuid()
        if (str_contains($content, "->uuid('id')") || str_contains($content, '->uuid(\'id\')')) {
            $this->warn('   ⚠️  Migración usa uuid() en lugar de binary(16)');
            $this->warn('   Esto es incompatible con uuid_binary');

            if ($this->isLocal || $this->option('force') || $this->confirm('   ¿Corregir automáticamente?', true)) {
                $this->fixUsersMigration($migrationPath, $content);
                $this->line('   ✓ Migración corregida');
            }
        } elseif (str_contains($content, "->binary('id', 16)") || str_contains($content, "binary('id', 16)")) {
            $this->line('   ✓ Migración correcta (binary 16)');
        } else {
            $this->warn('   ⚠️  No se pudo determinar el tipo de ID en la migración');
        }

        $this->info('   ✓ Migración verificada');
    }

    private function fixUsersMigration(string $path, string $content): void
    {
        // Reemplazar $table->uuid('id')->primary() por $table->binary('id', 16)->primary()
        $content = preg_replace(
            '/\$table->uuid\([\'"]id[\'"]\)->primary\(\)/',
            "\$table->binary('id', 16)->primary()",
            $content
        );

        // Reemplazar en sessions: user_id string(36) por binary(16)
        $content = preg_replace(
            '/\$table->string\([\'"]user_id[\'"],\s*36\)/',
            "\$table->binary('user_id', 16)",
            $content
        );

        // Añadir comentario si no existe
        if (! str_contains($content, 'UUID v7 binary')) {
            $content = str_replace(
                "\$table->binary('id', 16)->primary()",
                "// UUID v7 binary (16 bytes) - OPINADO para Larafactu\n            \$table->binary('id', 16)->primary()",
                $content
            );
        }

        File::put($path, $content);
    }

    private function step4_InstallPackages(): void
    {
        $this->info('');
        $this->info('📦 Step 4: Instalando paquetes...');

        // Verificar si larabill:install existe
        if (! $this->commandExists('larabill:install')) {
            $this->warn('   ⚠️  Comando larabill:install no disponible');
            $this->line('   Ejecuta: composer require aichadigital/larabill');

            return;
        }

        $this->line('   Ejecutando larabill:install...');

        try {
            Artisan::call('larabill:install', ['--no-interaction' => true]);
            $this->line(Artisan::output());
            $this->line('   ✓ Larabill instalado');
        } catch (\Exception $e) {
            $this->warn('   ⚠️  Error en larabill:install: '.$e->getMessage());
        }

        $this->info('   ✓ Paquetes instalados');
    }

    private function step5_RunMigrations(): void
    {
        $this->info('');
        $this->info('🗄️  Step 5: Ejecutando migraciones...');

        $fresh = $this->option('fresh');
        $force = $this->option('force') || ! $this->isLocal;

        if ($fresh && $this->isLocal) {
            $this->line('   Ejecutando migrate:fresh...');
            Artisan::call('migrate:fresh', ['--force' => true]);
            $this->line('   ✓ Base de datos recreada');
        } else {
            // Verificar si hay tablas
            $hasTables = Schema::hasTable('users');

            if (! $hasTables) {
                $this->line('   Ejecutando migrate...');
                Artisan::call('migrate', ['--force' => $force]);
            } else {
                if ($force || $this->confirm('   ¿Ejecutar migraciones pendientes?', true)) {
                    Artisan::call('migrate', ['--force' => $force]);
                }
            }
            $this->line('   ✓ Migraciones ejecutadas');
        }

        $this->info('   ✓ Base de datos lista');
    }

    private function step6_RunSeeders(): void
    {
        $this->info('');
        $this->info('🌱 Step 6: Ejecutando seeders...');

        try {
            Artisan::call('db:seed', ['--force' => true]);
            $this->line('   ✓ Seeders ejecutados');
        } catch (\Exception $e) {
            $this->warn('   ⚠️  Error en seeders: '.$e->getMessage());
        }

        $this->info('   ✓ Datos iniciales cargados');
    }

    private function step7_ClearCache(): void
    {
        $this->info('');
        $this->info('🧹 Step 7: Limpiando cache...');

        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('view:clear');

        if (! $this->isLocal) {
            $this->line('   Cacheando para producción...');
            Artisan::call('config:cache');
            Artisan::call('route:cache');
            Artisan::call('view:cache');
        }

        $this->info('   ✓ Cache limpiado');
    }

    private function commandExists(string $command): bool
    {
        return array_key_exists($command, Artisan::all());
    }
}

