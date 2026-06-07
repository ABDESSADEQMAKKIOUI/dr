<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_counts', function (Blueprint $table) {
            $table->json('counted_data')->nullable()->after('notes');
        });

        // Expand the status enum to include workflow states
        DB::statement("ALTER TABLE inventory_counts MODIFY COLUMN status ENUM('draft','counting','completed','adjusted') DEFAULT 'draft'");
    }

    public function down(): void
    {
        Schema::table('inventory_counts', function (Blueprint $table) {
            $table->dropColumn('counted_data');
        });

        DB::statement("ALTER TABLE inventory_counts MODIFY COLUMN status ENUM('draft','completed') DEFAULT 'draft'");
    }
};
