<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create("holidays", function (Blueprint $t) {
            $t->id();
            $t->string("name");
            $t->date("date");
            $t->boolean("is_recurring")->default(false);
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists("holidays"); }
};