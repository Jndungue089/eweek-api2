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
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voterId')->constrained('users')->onDelete('cascade');
            $table->foreignId('votedId')->constrained('users')->onDelete('cascade');
            $table->foreignId('concursoId')->constrained('concursos')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['voterId', 'concursoId']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};
