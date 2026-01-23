<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Detect ID type: read directly from .env to avoid config cache issues
        $idType = $this->getUserIdType();

        Schema::create('users', function (Blueprint $table) use ($idType) {
            // Dynamic ID column based on LARABILL_USER_ID_TYPE
            if ($idType === 'integer') {
                $table->id(); // bigint unsigned auto_increment
            } else {
                // UUID v7 string (char 36) - default
                $table->uuid('id')->primary();
            }

            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) use ($idType) {
            $table->string('id')->primary();

            // Dynamic user_id FK based on ID type
            if ($idType === 'integer') {
                $table->foreignId('user_id')
                    ->nullable()
                    ->index()
                    ->constrained()
                    ->cascadeOnDelete();
            } else {
                $table->foreignUuid('user_id')
                    ->nullable()
                    ->index()
                    ->constrained()
                    ->cascadeOnDelete();
            }

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }

    /**
     * Get user ID type directly from .env file to avoid config cache issues.
     */
    private function getUserIdType(): string
    {
        // Try config first (in case it's properly loaded)
        $fromConfig = config('larabill.user_id_type');
        if ($fromConfig && in_array($fromConfig, ['uuid', 'integer', 'int', 'ulid'])) {
            return $fromConfig === 'int' ? 'integer' : $fromConfig;
        }

        // Read directly from .env file as fallback
        $envPath = base_path('.env');
        if (file_exists($envPath)) {
            $content = file_get_contents($envPath);
            if (preg_match('/^LARABILL_USER_ID_TYPE=(.+)$/m', $content, $matches)) {
                $value = trim($matches[1], '"\'');
                if (in_array($value, ['integer', 'int'])) {
                    return 'integer';
                }
            }
        }

        // Default to uuid
        return 'uuid';
    }
};
