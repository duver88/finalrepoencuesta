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
        Schema::table('surveys', function (Blueprint $table) {
            $table->string('public_slug', 20)->unique()->nullable()->after('slug');
        });

        // Generar public_slug para encuestas existentes
        \App\Models\Survey::chunk(100, function($surveys) {
            foreach ($surveys as $survey) {
                $survey->public_slug = \Illuminate\Support\Str::random(12);
                $survey->save();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            $table->dropColumn('public_slug');
        });
    }
};
