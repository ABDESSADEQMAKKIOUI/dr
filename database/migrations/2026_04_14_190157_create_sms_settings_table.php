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
        Schema::create('sms_settings', function (Blueprint $table) {
            $table->id();
            $table->string('gateway')->default('twilio'); // twilio|nexmo|infobip|termii|whatsapp
            // Twilio
            $table->string('twilio_sid')->nullable();
            $table->string('twilio_token')->nullable();
            $table->string('twilio_from')->nullable();
            // Nexmo/Vonage
            $table->string('nexmo_key')->nullable();
            $table->string('nexmo_secret')->nullable();
            $table->string('nexmo_from')->nullable();
            // InfoBip
            $table->string('infobip_api_key')->nullable();
            $table->string('infobip_base_url')->nullable();
            $table->string('infobip_from')->nullable();
            // Termii
            $table->string('termii_api_key')->nullable();
            $table->string('termii_sender_id')->nullable();
            // WhatsApp (Meta Cloud API)
            $table->string('whatsapp_token')->nullable();
            $table->string('whatsapp_phone_id')->nullable();
            // Notification toggles
            $table->boolean('notify_sale')->default(true);
            $table->boolean('notify_purchase')->default(true);
            $table->boolean('notify_quotation')->default(true);
            $table->boolean('notify_payment')->default(true);
            $table->boolean('notify_sale_return')->default(false);
            $table->boolean('notify_purchase_return')->default(false);
            $table->boolean('notify_whatsapp_sale')->default(false);
            $table->boolean('notify_whatsapp_purchase')->default(false);
            $table->boolean('sms_enabled')->default(false);
            $table->boolean('whatsapp_enabled')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_settings');
    }
};
