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
        Schema::create('survey_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained()->onDelete('cascade');
            $table->string('token', 64)->unique();
            $table->string('source')->default('manual');
            $table->string('campaign_id')->nullable();
            $table->enum('status', ['pending', 'used', 'expired'])->default('pending');
            $table->timestamp('used_at')->nullable();
            $table->string('used_by_fingerprint')->nullable();
            $table->text('user_agent')->nullable();
            $table->integer('vote_attempts')->default(0);
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamps();

            $table->index(['survey_id', 'status']);
            $table->index('token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_tokens');
    }
};
