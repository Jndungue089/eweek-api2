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
        Schema::table('user_respostas', function (Blueprint $table) {
            // Primeiro, remover a foreign key atual
            $table->dropForeign(['userId']);

            // Em seguida, tornar a coluna nullable
            $table->unsignedBigInteger('userId')->nullable()->change();

            // E depois, re-adicionar a foreign key
            $table->foreign('userId')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_respostas', function (Blueprint $table) {
            //
        });
    }
};
