<?php

declare(strict_types=1);

use AichaDigital\Larabill\Support\MigrationHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * ADR-005: User preferences for theme, locale, timezone, etc.
     */
    public function up(): void
    {
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            MigrationHelper::userIdColumn($table, 'user_id');
            $table->string('locale', 5)->default('es');
            $table->string('theme', 20)->default('cupcake');
            $table->string('timezone', 50)->default('Europe/Madrid');
            $table->json('notifications')->nullable();
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
    }
};
