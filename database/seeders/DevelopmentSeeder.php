<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DevelopmentSeeder extends Seeder
{
    /**
     * Seed the application's database for development.
     */
    public function run(): void
    {
        // Create test user with UUID
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@larafactu.local',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $this->command->info("✓ User created: {$user->email} (UUID: {$user->id})");

        // TODO: Add more seeders when models are ready:
        // - Customers (with tax profiles)
        // - Invoices (with items)
        // - Tickets (with departments)
        // - Tax rates (Spanish: 21%, 10%, 4%)

        $this->command->info('✓ Development data seeded successfully');
    }
}
