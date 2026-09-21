<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // Relacionamento correto usado pelo Model: responsavel_id -> responsaveis
            // (diferente de atendente_id -> users, que ficou obsoleto)
            $table->foreignId('responsavel_id')->nullable()
                ->after('categoria_id')
                ->constrained('responsaveis')
                ->nullOnDelete();

            // Data em que o chamado foi aberto (usada pelo Model/Seeder)
            $table->timestamp('aberto_em')->nullable()->after('responsavel_id');
        });

        // Preenche aberto_em com o created_at existente, para registros ja criados
        DB::table('tickets')->whereNull('aberto_em')->update([
            'aberto_em' => DB::raw('created_at'),
        ]);
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('aberto_em');
            $table->dropConstrainedForeignId('responsavel_id');
        });
    }
};