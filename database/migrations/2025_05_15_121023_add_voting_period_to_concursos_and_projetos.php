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
        // Adicionar campos de período de votação à tabela concursos
        Schema::table('concursos', function (Blueprint $table) {
            $table->timestamp('voting_starts_at')->nullable()->after('place');
            $table->timestamp('voting_ends_at')->nullable()->after('voting_starts_at');
        });

        // Adicionar campos de período de votação à tabela projetos
        Schema::table('projetos', function (Blueprint $table) {
            $table->timestamp('voting_starts_at')->nullable()->after('hasPrototype');
            $table->timestamp('voting_ends_at')->nullable()->after('voting_starts_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remover campos da tabela concursos
        Schema::table('concursos', function (Blueprint $table) {
            $table->dropColumn(['voting_starts_at', 'voting_ends_at']);
        });

        // Remover campos da tabela projetos
        Schema::table('projetos', function (Blueprint $table) {
            $table->dropColumn(['voting_starts_at', 'voting_ends_at']);
        });
    }
};