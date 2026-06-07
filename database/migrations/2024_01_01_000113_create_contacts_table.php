<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('contactable_type'); // Polymorphic: Customer, Supplier
            $table->unsignedBigInteger('contactable_id');
            $table->string('name');
            $table->string('phone', 20);
            $table->string('email')->nullable();
            $table->string('position', 100)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['contactable_type', 'contactable_id']);
            $table->index('is_primary');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
