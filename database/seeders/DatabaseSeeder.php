<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Automatically seed development data in local/testing environments
        if (app()->environment(['local', 'testing'])) {
            $this->call(DevelopmentSeeder::class);
        }

        // Add production seeders here if needed
        // $this->call(ProductionDataSeeder::class);
    }
}
