<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('descricao');
            $table->enum('status', ['aberto', 'em_andamento', 'resolvido', 'fechado'])->default('aberto');
            $table->enum('prioridade', ['baixa', 'media', 'alta', 'urgente'])->default('media');
            
            $table->foreignId('solicitante_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('atendente_id')->nullable()->constrained('users')->nullOnDelete();
            
            // Declare column without foreign key constraint for now
            $table->foreignId('categoria_id')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};