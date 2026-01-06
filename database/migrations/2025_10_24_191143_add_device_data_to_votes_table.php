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
            $table->string('user_agent', 500)->nullable()->after('fingerprint');
            $table->string('platform', 100)->nullable()->after('user_agent');
            $table->string('screen_resolution', 50)->nullable()->after('platform');
            $table->integer('hardware_concurrency')->nullable()->after('screen_resolution');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('votes', function (Blueprint $table) {
            $table->dropColumn(['user_agent', 'platform', 'screen_resolution', 'hardware_concurrency']);
        });
    }
};
