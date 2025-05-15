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
        Schema::table('concursos', function (Blueprint $table) {
            $table->boolean('has_voting')->default(false)->after('place');
            $table->boolean('is_voting_open')->default(false)->after('has_voting');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('concursos', function (Blueprint $table) {
            $table->dropColumn('has_voting');
            $table->dropColumn('is_voting_open');
        });
    }
};
