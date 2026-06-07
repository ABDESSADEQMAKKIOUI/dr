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
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('has_serial_numbers')->default(false)->after('stock_quantity');
            $table->boolean('has_warranty')->default(false)->after('has_serial_numbers');
            $table->unsignedInteger('warranty_duration')->nullable()->after('has_warranty');
            $table->enum('warranty_type', ['full', 'parts', 'none'])->default('none')->after('warranty_duration');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['has_serial_numbers', 'has_warranty', 'warranty_duration', 'warranty_type']);
        });
    }
};
