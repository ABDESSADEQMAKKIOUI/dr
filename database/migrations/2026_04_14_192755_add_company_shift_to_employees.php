<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table("employees", function (Blueprint $t) {
            $t->foreignId("company_id")->nullable()->constrained()->nullOnDelete()->after("id");
            $t->foreignId("shift_id")->nullable()->constrained("office_shifts")->nullOnDelete()->after("company_id");
        });
    }
    public function down(): void {
        Schema::table("employees", function (Blueprint $t) {
            $t->dropForeignIdFor(App\Models\Company::class);
            $t->dropForeignIdFor(App\Models\OfficeShift::class);
        });
    }
};