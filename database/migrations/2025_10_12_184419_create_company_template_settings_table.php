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
        Schema::create('company_template_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('setting_type', 50);
            $table->string('invoice_type', 50)->default('fiscal');
            $table->string('scope', 50)->default('global');
            $table->string('client_id', 100)->nullable();
            $table->text('value');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'setting_type', 'invoice_type', 'scope', 'client_id'], 'user_setting_unique');
            $table->index(['user_id', 'invoice_type']);
            $table->index(['setting_type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_template_settings');
    }
};
