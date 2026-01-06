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
        Schema::create('surveys', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('banner')->nullable();
            $table->string('og_image')->nullable()->comment('Imagen para Facebook/Open Graph (1200x630)');
            $table->string('slug')->unique();
            $table->string('public_slug', 20)->unique()->nullable();
            $table->boolean('show_results')->default(true);
            $table->enum('results_display_mode', ['all', 'collapsible'])->default('all');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_finished')->default(false);
            $table->timestamp('finished_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('views_count')->default(0);
            $table->boolean('one_vote_per_minute_per_option')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surveys');
    }
};
