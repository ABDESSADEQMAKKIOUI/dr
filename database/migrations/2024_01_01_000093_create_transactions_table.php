<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_id')->constrained('journals');
            $table->foreignId('account_id')->constrained('accounts');
            $table->date('date');
            $table->string('reference', 50);
            $table->text('description')->nullable();
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->string('source_type')->nullable(); // Polymorphic
            $table->unsignedBigInteger('source_id')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();

            $table->index('journal_id');
            $table->index('account_id');
            $table->index('date');
            $table->index('reference');
            $table->index(['source_type', 'source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
