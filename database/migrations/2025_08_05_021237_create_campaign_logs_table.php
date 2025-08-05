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
        Schema::create('campaign_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id');
            $table->unsignedBigInteger('contact_id');
            $table->enum('status', ['pending', 'sent', 'failed', 'delivered', 'read'])->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->string('whatsapp_message_id')->nullable(); // ID da mensagem no WhatsApp
            $table->json('message_data')->nullable(); // Dados da mensagem enviada
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('campaign_id')->references('id')->on('campaigns')->onDelete('cascade');
            $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('cascade');
            
            // Indexes
            $table->index(['campaign_id', 'status']);
            $table->index(['contact_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_logs');
    }
};
