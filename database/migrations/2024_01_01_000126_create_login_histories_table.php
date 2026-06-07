<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('login_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('ip_address', 45);
            $table->text('user_agent');
            $table->string('device', 100)->nullable();
            $table->string('location', 200)->nullable();
            $table->enum('status', ['success', 'failed'])->default('success');
            $table->timestamp('logged_in_at');
            $table->timestamp('logged_out_at')->nullable();
            
            $table->index('user_id');
            $table->index('ip_address');
            $table->index('logged_in_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_histories');
    }
};
