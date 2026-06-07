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
        Schema::create('recurring_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->json('items');
            $table->decimal('total', 12, 2);
            $table->enum('frequency', ['daily','weekly','monthly','yearly'])->default('monthly');
            $table->date('next_run_at');
            $table->date('last_run_at')->nullable();
            $table->enum('status', ['active','paused','cancelled'])->default('active');
            $table->boolean('send_email')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_invoices');
    }
};
