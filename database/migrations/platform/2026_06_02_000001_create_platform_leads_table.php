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
        Schema::create('platform_leads', function (Blueprint $table) {
            $table->id();
            $table->string('company_name', 150)->nullable();
            $table->string('contact_name', 150);
            $table->string('email', 190);
            $table->string('phone', 30)->nullable();
            $table->text('message')->nullable();
            $table->enum('source', ['demo', 'contact'])->default('demo');
            $table->enum('status', ['new', 'contacted', 'qualified', 'converted', 'rejected'])
                ->default('new');
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('handled_by')->nullable();
            $table->timestamp('handled_at')->nullable();
            $table->text('notes')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('status');
            $table->index('created_at');
            $table->index('email');

            // Foreign keys
            $table->foreign('plan_id')->references('id')->on('plans')->nullOnDelete();
            $table->foreign('tenant_id')->references('id')->on('tenants')->nullOnDelete();
            $table->foreign('handled_by')->references('id')->on('platform_users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platform_leads');
    }
};
