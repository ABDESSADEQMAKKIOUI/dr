<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->tinyInteger('month')->nullable()->after('employee_id');
            $table->smallInteger('year')->nullable()->after('month');
            // Individual allowances
            $table->decimal('housing_allowance', 15, 2)->default(0)->after('allowances');
            $table->decimal('transport_allowance', 15, 2)->default(0)->after('housing_allowance');
            $table->decimal('overtime', 15, 2)->default(0)->after('transport_allowance');
            $table->decimal('bonus', 15, 2)->default(0)->after('overtime');
            // Individual deductions
            $table->decimal('tax', 15, 2)->default(0)->after('deductions');
            $table->decimal('social_security', 15, 2)->default(0)->after('tax');
            $table->decimal('insurance', 15, 2)->default(0)->after('social_security');
            $table->decimal('other_deductions', 15, 2)->default(0)->after('insurance');
            // Notes
            $table->text('notes')->nullable()->after('other_deductions');
        });
    }

    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn([
                'month', 'year',
                'housing_allowance', 'transport_allowance', 'overtime', 'bonus',
                'tax', 'social_security', 'insurance', 'other_deductions', 'notes',
            ]);
        });
    }
};
