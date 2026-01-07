<?php

declare(strict_types=1);

use App\Enums\UserType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ADR-004: Authorization System - User Type and Status Fields
 *
 * Adds authorization-related columns to users table:
 * - user_type: STAFF(0), CUSTOMER(1), DELEGATE(2)
 * - is_active: Account enabled/disabled
 * - suspended_at: Suspension timestamp for audit
 * - is_superadmin: Superadmin flag (bypass all checks)
 *
 * Note: relationship_type (ADR-003) is now DEPRECATED in favor of user_type.
 * Migration does NOT remove it to allow gradual transition.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // User type: STAFF(0), CUSTOMER(1), DELEGATE(2)
            // Default to CUSTOMER for existing users
            $table->unsignedTinyInteger('user_type')
                ->default(UserType::CUSTOMER->value)
                ->after('avatar_path')
                ->comment('ADR-004: 0=STAFF, 1=CUSTOMER, 2=DELEGATE');

            // Account status
            $table->boolean('is_active')
                ->default(true)
                ->after('user_type')
                ->comment('Account enabled/disabled');

            // Suspension audit
            $table->timestamp('suspended_at')
                ->nullable()
                ->after('is_active')
                ->comment('When account was suspended');

            // Superadmin flag (bypasses all authorization checks)
            $table->boolean('is_superadmin')
                ->default(false)
                ->after('suspended_at')
                ->comment('Superadmin bypasses all checks');

            // Index for common queries
            $table->index(['user_type', 'is_active'], 'idx_users_type_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_type_active');
            $table->dropColumn(['user_type', 'is_active', 'suspended_at', 'is_superadmin']);
        });
    }
};
