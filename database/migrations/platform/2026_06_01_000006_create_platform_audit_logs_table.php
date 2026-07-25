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
        Schema::create('platform_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('platform_user_id')->nullable();
            $table->string('actor_email', 190)->nullable();
            $table->string('action', 80);
            $table->string('auditable_type', 120)->nullable();
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->string('description', 255)->nullable();
            $table->json('properties')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();

            // Indexes
            $table->index(['auditable_type', 'auditable_id'], 'platform_audit_logs_auditable_index');
            $table->index('tenant_id');
            $table->index('platform_user_id');
            $table->index(['action', 'created_at']);

            // Foreign keys
            $table->foreign('platform_user_id')->references('id')->on('platform_users')->nullOnDelete();
            $table->foreign('tenant_id')->references('id')->on('tenants')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platform_audit_logs');
    }
};
