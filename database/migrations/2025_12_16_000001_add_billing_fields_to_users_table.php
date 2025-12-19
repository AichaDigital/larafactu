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
        Schema::table('users', function (Blueprint $table) {
            // Self-reference for delegation hierarchy
            // NULL = direct client of company, UUID = delegated client of another user
            $table->uuid('parent_user_id')->nullable()->after('id');

            // Relationship type: 0 = DIRECT, 1 = DELEGATED (UserRelationshipType enum)
            $table->unsignedTinyInteger('relationship_type')->default(0)->after('parent_user_id');

            // Display name for billing (commercial name, can differ from fiscal name)
            $table->string('display_name')->nullable()->after('name');

            // Legal entity type code (FK to legal_entity_types.code)
            $table->string('legal_entity_type_code', 50)->nullable()->after('display_name');

            // Indexes
            $table->index('parent_user_id', 'idx_users_parent');
            $table->index('relationship_type', 'idx_users_relationship_type');

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
            $table->dropColumn([
                'parent_user_id',
                'relationship_type',
                'display_name',
                'legal_entity_type_code',
            ]);
        });
    }
};
