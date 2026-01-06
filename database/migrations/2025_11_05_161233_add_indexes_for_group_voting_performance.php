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
        Schema::table('votes', function (Blueprint $table) {
            // Índice compuesto para verificación de grupo + fingerprint (muy importante para rendimiento)
            $table->index(['fingerprint', 'is_valid'], 'votes_fp_valid_idx');

            // Índice para question_id (usado en JOINs con questions)
            $table->index('question_id', 'votes_question_idx');
        });

        Schema::table('questions', function (Blueprint $table) {
            // Índice para survey_id (usado en JOINs con surveys)
            $table->index('survey_id', 'questions_survey_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('votes', function (Blueprint $table) {
            $table->dropIndex('votes_fp_valid_idx');
            $table->dropIndex('votes_question_idx');
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->dropIndex('questions_survey_idx');
        });
    }
};
