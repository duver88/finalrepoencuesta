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
        // Tabla de grupos de encuestas
        Schema::create('survey_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('restrict_voting')->default(true); // Si está activo, solo puede votar en una del grupo
            $table->timestamps();
        });

        // Agregar campo survey_group_id a surveys
        Schema::table('surveys', function (Blueprint $table) {
            $table->foreignId('survey_group_id')->nullable()->after('id')->constrained('survey_groups')->nullOnDelete();
            $table->index('survey_group_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            $table->dropForeign(['survey_group_id']);
            $table->dropColumn('survey_group_id');
        });

        Schema::dropIfExists('survey_groups');
    }
};
