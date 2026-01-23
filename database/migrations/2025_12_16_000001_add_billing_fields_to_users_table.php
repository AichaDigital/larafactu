<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ADR-003 Fase 2: Add billing fields to users table.
 *
 * Unifies Customer into User with parent_user_id for delegation hierarchy.
 */
return new class extends Migration
{
    public function up(): void
    {
        $idType = $this->getUserIdType();

        Schema::table('users', function (Blueprint $table) use ($idType) {
            // Self-reference for delegation hierarchy
            // NULL = direct client of company, ID = delegated client of another user
            if ($idType === 'integer') {
                $table->unsignedBigInteger('parent_user_id')->nullable()->after('id');
            } else {
                $table->uuid('parent_user_id')->nullable()->after('id');
            }

            // Relationship type: 0 = DIRECT, 1 = DELEGATED (UserRelationshipType enum)
            $table->unsignedTinyInteger('relationship_type')->default(0)->after('parent_user_id');

            // Display name for billing (commercial name, can differ from fiscal name)
            $table->string('display_name')->nullable()->after('name');

            // Legal entity type code (FK to legal_entity_types.code)
            $table->string('legal_entity_type_code', 50)->nullable()->after('display_name');

            // ADR-004: Current tax profile (shared profiles pattern)
            // Multiple users can point to the same tax profile
            $table->foreignId('current_tax_profile_id')->nullable();

            // Indexes
            $table->index('parent_user_id', 'idx_users_parent');
            $table->index('relationship_type', 'idx_users_relationship_type');
            $table->index('current_tax_profile_id', 'idx_users_current_tax_profile');

            // Foreign key self-reference
            $table->foreign('parent_user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            // Foreign key to legal_entity_types
            $table->foreign('legal_entity_type_code')
                ->references('code')
                ->on('legal_entity_types')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['parent_user_id']);
            $table->dropForeign(['legal_entity_type_code']);
            $table->dropIndex('idx_users_parent');
            $table->dropIndex('idx_users_relationship_type');
            $table->dropIndex('idx_users_current_tax_profile');
            $table->dropColumn([
                'parent_user_id',
                'relationship_type',
                'display_name',
                'legal_entity_type_code',
                'current_tax_profile_id',
            ]);
        });
    }

    /**
     * Get user ID type directly from .env file to avoid config cache issues.
     */
    private function getUserIdType(): string
    {
        $fromConfig = config('larabill.user_id_type');
        if ($fromConfig && in_array($fromConfig, ['uuid', 'integer', 'int', 'ulid'])) {
            return $fromConfig === 'int' ? 'integer' : $fromConfig;
        }

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

        return 'uuid';
    }
};
