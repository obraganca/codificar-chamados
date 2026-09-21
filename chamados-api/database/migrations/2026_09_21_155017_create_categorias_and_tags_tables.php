<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create 'categorias' first
        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->unique();
            $table->string('cor', 7)->default('#6366f1');
            $table->timestamps();
        });

        // 2. Add foreign key to 'tickets' after 'categorias' exists
        Schema::table('tickets', function (Blueprint $table) {
            $table->foreign('categoria_id')
                ->references('id')
                ->on('categorias')
                ->nullOnDelete();
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->unique();
            $table->timestamps();
        });

        // N:N com tickets
        Schema::create('ticket_tag', function (Blueprint $table) {
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['ticket_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['categoria_id']);
        });

        Schema::dropIfExists('ticket_tag');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('categorias');
    }
};