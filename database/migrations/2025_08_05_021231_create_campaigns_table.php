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
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');                    // Nome da campanha
            $table->text('description')->nullable();   // Descrição
            $table->longText('message_text');          // Texto da mensagem
            $table->string('image_url')->nullable();   // URL da imagem
            $table->string('attachment_url')->nullable(); // URL do anexo
            $table->string('link_url')->nullable();    // URL do link
            $table->enum('status', ['draft', 'active', 'paused', 'completed'])->default('draft');
            $table->timestamp('scheduled_at')->nullable(); // Data/hora agendada
            $table->unsignedBigInteger('created_by')->nullable(); // Quem criou
            $table->integer('total_contacts')->default(0); // Total de contatos
            $table->integer('sent_count')->default(0);    // Quantos foram enviados
            $table->integer('failed_count')->default(0);  // Quantos falharam
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
