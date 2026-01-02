<?php

declare(strict_types=1);

use AichaDigital\Larabill\Support\MigrationHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create user_customer_access table for delegate permissions.
 *
 * This table allows delegates to access customer accounts with specific permissions.
 * A delegate can have different access levels to different customers.
 *
 * @see ADR-004 for authorization architecture
 * @see ADR-006 for consolidated state
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_customer_access', function (Blueprint $table) {
            $table->id();

            // FK to users - the delegate who receives access
            MigrationHelper::userIdColumn($table, 'user_id');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            // FK to users - the customer account being accessed
            MigrationHelper::userIdColumn($table, 'customer_user_id');
            $table->foreign('customer_user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            // Access level (uses App\Enums\AccessLevel)
            // 0=FULL, 1=WRITE, 2=READ, 3=NONE
            $table->unsignedTinyInteger('access_level')->default(2);

            // Granular permissions (in addition to access level)
            $table->boolean('can_view_invoices')->default(false);
            $table->boolean('can_view_services')->default(false);
            $table->boolean('can_manage_tickets')->default(false);
            $table->boolean('can_manage_delegates')->default(false);

            // Audit fields
            MigrationHelper::userIdColumn($table, 'granted_by', nullable: true);
            $table->foreign('granted_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
            $table->timestamp('granted_at')->useCurrent();
            $table->timestamp('expires_at')->nullable();

            $table->timestamps();

            // Constraints - one delegate can only have one access record per customer
            $table->unique(['user_id', 'customer_user_id']);

            // Indexes for common queries
            // Note: user_id, customer_user_id already indexed by MigrationHelper
            $table->index(['user_id', 'access_level']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_customer_access');
    }
};
