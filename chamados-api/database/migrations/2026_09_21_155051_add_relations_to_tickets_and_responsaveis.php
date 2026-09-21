<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1:1 opcional responsavel <-> usuario
        Schema::table('responsaveis', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->unique()
                ->after('email')->constrained('users')->nullOnDelete();
        });

        // Vincula responsaveis a usuarios pelo email
        DB::table('responsaveis')->update([
            'user_id' => DB::raw('(select users.id from users where users.email = responsaveis.email)'),
        ]);

        // A FK de categoria_id já é criada em 2026_09_21_155017_create_categorias_and_tags_tables.php
        // (criar aqui de novo causa o erro 1826 - Duplicate foreign key constraint name)

        Schema::table('ticket_messages', function (Blueprint $table) {
            $table->index(['ticket_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('ticket_messages', function (Blueprint $table) {
            $table->dropIndex(['ticket_id', 'created_at']);
        });

        Schema::table('responsaveis', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};