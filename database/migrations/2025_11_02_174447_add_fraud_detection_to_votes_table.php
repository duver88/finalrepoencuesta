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
            // Estado del voto: approved, pending_review, rejected
            $table->enum('status', ['approved', 'pending_review', 'rejected'])
                  ->default('approved')
                  ->after('is_manual');

            // Puntuación de fraude (0-100, donde 100 es muy sospechoso)
            $table->decimal('fraud_score', 5, 2)
                  ->default(0)
                  ->after('status');

            // Razones por las que se marcó como sospechoso (JSON)
            $table->json('fraud_reasons')->nullable()->after('fraud_score');

            // Timestamp de cuando se revisó (si aplica)
            $table->timestamp('reviewed_at')->nullable()->after('fraud_reasons');

            // Admin que revisó el voto
            $table->foreignId('reviewed_by')->nullable()->after('reviewed_at');
        });

        // Agregar índices para consultas rápidas
        Schema::table('votes', function (Blueprint $table) {
            $table->index(['status', 'created_at']);
            $table->index(['fraud_score', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('votes', function (Blueprint $table) {
            $table->dropIndex(['status', 'created_at']);
            $table->dropIndex(['fraud_score', 'status']);
            $table->dropColumn([
                'status',
                'fraud_score',
                'fraud_reasons',
                'reviewed_at',
                'reviewed_by'
            ]);
        });
    }
};
