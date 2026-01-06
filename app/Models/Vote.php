<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    protected $fillable = [
        'survey_id',
        'survey_token_id',
        'question_id',
        'question_option_id',
        'ip_address',
        'fingerprint',
        'user_agent',
        'platform',
        'screen_resolution',
        'hardware_concurrency',
        'is_manual',
        'is_valid',
        'status',
        'fraud_score',
        'fraud_reasons',
        'reviewed_at',
        'reviewed_by',
    ];

    protected $casts = [
        'fraud_score' => 'float',
        'fraud_reasons' => 'array',
        'reviewed_at' => 'datetime',
        'is_manual' => 'boolean',
        'is_valid' => 'boolean',
    ];

    // Scope para votos válidos (con token o manuales)
    public function scopeValid($query)
    {
        return $query->where(function($q) {
            $q->whereNotNull('survey_token_id')
              ->orWhere('is_manual', true);
        });
    }

    // Scope para votos aprobados (contabilizables)
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    // Scope para votos pendientes de revisión
    public function scopePendingReview($query)
    {
        return $query->where('status', 'pending_review');
    }

    // Scope para votos rechazados
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    // Scope para votos sospechosos (alto fraud_score)
    public function scopeHighRisk($query, $threshold = 60)
    {
        return $query->where('fraud_score', '>=', $threshold);
    }

    // Scope combinado: solo votos válidos Y aprobados (para contar en resultados)
    public function scopeCountable($query)
    {
        return $query->valid()->approved();
    }

    // Verificar si el voto es sospechoso
    public function isSuspicious(): bool
    {
        return $this->status === 'pending_review' || $this->fraud_score >= 40;
    }

    // Verificar si el voto fue aprobado
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    // Verificar si el voto fue rechazado
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    // Relaciones
    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    public function token()
    {
        return $this->belongsTo(SurveyToken::class, 'survey_token_id');
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function option()
    {
        return $this->belongsTo(QuestionOption::class, 'question_option_id');
    }
}
