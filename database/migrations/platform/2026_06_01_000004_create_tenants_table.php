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
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('slug', 63)->unique();
            $table->string('database_name', 64)->unique();
            $table->enum('status', ['provisioning', 'active', 'suspended', 'expired', 'failed', 'archived'])
                ->default('provisioning');
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->string('contact_name', 150)->nullable();
            $table->string('contact_email', 190);
            $table->string('contact_phone', 30)->nullable();
            $table->string('locale', 5)->default('fr');
            $table->char('currency', 3)->default('MAD');
            $table->string('timezone', 64)->default('Africa/Casablanca');
            $table->timestamp('provisioned_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->text('notes')->nullable();
            $table->text('provision_error')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('status');
            $table->index('contact_email');

            // Foreign keys
            $table->foreign('plan_id')->references('id')->on('plans')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
