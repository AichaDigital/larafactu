<?php

declare(strict_types=1);

use AichaDigital\Larabill\Support\MigrationHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create user_department_access table for staff authorization.
 *
 * This table links users (staff) to departments with specific access levels.
 * It references laratickets' departments table.
 *
 * @see ADR-004 for authorization architecture
 * @see ADR-005 for application-level authorization decision
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_department_access', function (Blueprint $table) {
            $table->id();

            // FK to users (uses MigrationHelper for UUID compatibility)
            MigrationHelper::userIdColumn($table, 'user_id');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            // FK to laratickets departments
            $table->foreignId('department_id')
                ->constrained('departments')
                ->cascadeOnDelete();

            // Access level (uses App\Enums\AccessLevel)
            // 0=FULL, 1=WRITE, 2=READ, 3=NONE
            $table->unsignedTinyInteger('access_level')->default(2);

            // Audit fields (uses MigrationHelper for UUID compatibility)
            MigrationHelper::userIdColumn($table, 'granted_by', nullable: true);
            $table->foreign('granted_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
            $table->timestamp('granted_at')->useCurrent();
            $table->timestamp('expires_at')->nullable();

            $table->timestamps();

            // Constraints
            $table->unique(['user_id', 'department_id']);

            // Indexes (user_id already indexed by MigrationHelper)
            $table->index(['user_id', 'access_level']);
            $table->index('department_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_department_access');
    }
};
