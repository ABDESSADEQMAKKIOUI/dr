<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table("employees", function (Blueprint $t) {
            $t->string("photo")->nullable()->after("shift_id");
            $t->string("facebook")->nullable();
            $t->string("twitter")->nullable();
            $t->string("linkedin")->nullable();
            $t->string("bank_name")->nullable();
            $t->string("bank_account_number")->nullable();
            $t->string("bank_routing_number")->nullable();
        });
    }
    public function down(): void {
        Schema::table("employees", function (Blueprint $t) {
            $t->dropColumn(["photo","facebook","twitter","linkedin","bank_name","bank_account_number","bank_routing_number"]);
        });
    }
};