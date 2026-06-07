<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('country', 100)->nullable()->default(null)->change();
            $table->string('city', 100)->nullable()->change();
            $table->string('postal_code', 20)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('country', 100)->default('Morocco')->change();
        });
    }
};
