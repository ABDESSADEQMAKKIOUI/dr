<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create("office_shifts", function (Blueprint $t) {
            $t->id();
            $t->string("name");
            $t->time("start_time");
            $t->time("end_time");
            $t->unsignedInteger("late_after_minutes")->default(15);
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists("office_shifts"); }
};