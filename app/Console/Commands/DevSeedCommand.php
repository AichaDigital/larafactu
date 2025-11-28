<?php

namespace App\Console\Commands;

use Database\Seeders\DevelopmentSeeder;
use Illuminate\Console\Command;

class DevSeedCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:seed
                            {--fresh : Drop all tables and re-run migrations before seeding}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed development data (users, fiscal settings) - LOCAL ONLY';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Safety check: only run in local/testing
        if (! app()->environment(['local', 'testing'])) {
            $this->error('❌ This command can only run in local/testing environments.');
            $this->error('   Current environment: '.app()->environment());

            return self::FAILURE;
        }

        $this->info('🚀 Development Seeder');
        $this->newLine();

        // Optional: fresh migration
        if ($this->option('fresh')) {
            if ($this->confirm('⚠️  This will DROP ALL TABLES. Continue?', true)) {
                $this->call('migrate:fresh');
                $this->newLine();
            } else {
                $this->info('Cancelled.');

                return self::SUCCESS;
            }
        }

        // Run development seeder
        $this->call('db:seed', ['--class' => DevelopmentSeeder::class]);

        return self::SUCCESS;
    }
}
