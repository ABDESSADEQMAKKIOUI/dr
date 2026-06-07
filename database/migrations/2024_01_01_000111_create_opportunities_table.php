<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('title');
            $table->decimal('value', 15, 2);
            $table->integer('probability')->default(50); // 0-100%
            $table->string('stage', 100); // prospecting, qualification, proposal, negotiation, closed
            $table->date('expected_close_date');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('customer_id');
            $table->index('stage');
            $table->index('assigned_to');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opportunities');
    }
};
