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
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->string('to', 30);
            $table->text('message');
            $table->string('gateway', 30)->default('twilio'); // twilio|nexmo|infobip|termii|whatsapp
            $table->enum('status', ['sent', 'failed', 'pending'])->default('pending');
            $table->text('error')->nullable();
            $table->string('event', 60)->nullable();  // sale_created, payment_received …
            $table->nullableMorphs('loggable');       // optional link to sale/purchase/invoice
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            $table->index('gateway');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
    }
};
