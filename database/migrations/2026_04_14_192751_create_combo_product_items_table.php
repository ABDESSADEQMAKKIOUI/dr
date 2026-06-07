<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create("combo_product_items", function (Blueprint $t) {
            $t->id();
            $t->foreignId("combo_id")->constrained("combo_products")->cascadeOnDelete();
            $t->foreignId("product_id")->constrained()->cascadeOnDelete();
            $t->unsignedInteger("quantity")->default(1);
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists("combo_product_items"); }
};