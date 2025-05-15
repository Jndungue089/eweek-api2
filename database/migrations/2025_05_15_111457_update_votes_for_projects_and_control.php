<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        // Remover restrições e colunas antigas com segurança
        try {
            DB::statement('ALTER TABLE votes DROP CONSTRAINT IF EXISTS votes_voterid_concursoid_unique');
        } catch (\Throwable $e) {
            // ignora se não existir
        }

        try {
            DB::statement('ALTER TABLE votes DROP CONSTRAINT IF EXISTS votes_concursoid_foreign');
        } catch (\Throwable $e) {}

        try {
            DB::statement('ALTER TABLE votes DROP CONSTRAINT IF EXISTS votes_votedid_foreign');
        } catch (\Throwable $e) {}

        // Remover colunas se existirem
        Schema::table('votes', function (Blueprint $table) {
            if (Schema::hasColumn('votes', 'concursoId')) {
                $table->dropColumn('concursoId');
            }
            if (Schema::hasColumn('votes', 'votedId')) {
                $table->dropColumn('votedId');
            }
        });

        // Adicionar novos campos
        Schema::table('votes', function (Blueprint $table) {
            $table->foreignId('votedId')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('concursoId')->nullable()->constrained('concursos')->onDelete('cascade');
            $table->foreignId('projectId')->nullable()->constrained('projetos')->onDelete('cascade');
            $table->boolean('enabled')->default(true);
            $table->unique(['voterId', 'concursoId']);
        });
    }

    public function down(): void
    {
        Schema::table('votes', function (Blueprint $table) {
            $table->dropForeign(['concursoId']);
            $table->dropForeign(['projectId']);
            $table->dropForeign(['votedId']);
            $table->dropColumn(['concursoId', 'projectId', 'votedId', 'enabled']);
        });

        // Restaurar estrutura anterior
        Schema::table('votes', function (Blueprint $table) {
            $table->foreignId('votedId')->constrained('users')->onDelete('cascade');
            $table->foreignId('concursoId')->constrained('concursos')->onDelete('cascade');
            $table->unique(['voterId', 'concursoId']);
        });
    }
};
