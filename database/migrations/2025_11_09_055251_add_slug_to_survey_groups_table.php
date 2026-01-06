<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('survey_groups', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
        });

        // Generar slugs para grupos existentes
        $groups = DB::table('survey_groups')->get();
        foreach ($groups as $group) {
            $slug = \Illuminate\Support\Str::slug($group->name);

            // Verificar si ya existe y agregar número si es necesario
            $originalSlug = $slug;
            $count = 1;
            while (DB::table('survey_groups')->where('slug', $slug)->where('id', '!=', $group->id)->exists()) {
                $slug = $originalSlug . '-' . $count;
                $count++;
            }

            DB::table('survey_groups')->where('id', $group->id)->update(['slug' => $slug]);
        }

        // Ahora hacer el campo único y no nullable
        Schema::table('survey_groups', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('survey_groups', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
