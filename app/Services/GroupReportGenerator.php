<?php

namespace App\Services;

use App\Models\SurveyGroup;
use App\Models\Vote;
use App\Models\Question;
use App\Models\QuestionOption;
use Illuminate\Support\Facades\DB;

class GroupReportGenerator
{
    /**
     * Generar reporte completo para un grupo de encuestas
     */
    public function generate(SurveyGroup $group): array
    {
        return [
            'basic_stats' => $this->generateBasicStats($group),
            'question_stats' => $this->generateQuestionStats($group),
            'per_survey_stats' => $this->generatePerSurveyStats($group),
            'fraud_stats' => $this->generateFraudStats($group),
            'token_stats' => $this->generateTokenStats($group),
            'conversion_metrics' => $this->generateConversionMetrics($group),
        ];
    }

    /**
     * Estadísticas básicas agregadas del grupo
     */
    public function generateBasicStats(SurveyGroup $group): array
    {
        $surveys = $group->surveys;

        // Vistas totales (suma de todas las encuestas)
        $totalViews = $surveys->sum('views_count');

        // Votos totales enviados (válidos)
        $totalVotes = Vote::whereIn('survey_id', $surveys->pluck('id'))
            ->valid()
            ->count();

        // Votos contados (válidos Y aprobados)
        $validVotes = Vote::whereIn('survey_id', $surveys->pluck('id'))
            ->countable()
            ->count();

        // Votos pendientes de revisión
        $pendingReview = Vote::whereIn('survey_id', $surveys->pluck('id'))
            ->pendingReview()
            ->count();

        // Votos rechazados
        $rejectedVotes = Vote::whereIn('survey_id', $surveys->pluck('id'))
            ->rejected()
            ->count();

        // Votos no contados (sin token válido)
        $notCountedVotes = Vote::whereIn('survey_id', $surveys->pluck('id'))
            ->where('is_valid', false)
            ->count();

        // Votos duplicados/fraudulentos
        $duplicateVotes = $rejectedVotes + $pendingReview;

        // Votantes únicos en todo el grupo (por fingerprint)
        $uniqueVoters = Vote::whereIn('survey_id', $surveys->pluck('id'))
            ->countable()
            ->distinct('fingerprint')
            ->count('fingerprint');

        return [
            'total_surveys' => $surveys->count(),
            'total_views' => $totalViews,
            'total_votes_submitted' => $totalVotes,
            'valid_votes' => $validVotes,
            'pending_review' => $pendingReview,
            'rejected_votes' => $rejectedVotes,
            'not_counted_votes' => $notCountedVotes,
            'duplicate_or_fraudulent' => $duplicateVotes,
            'unique_voters' => $uniqueVoters,
        ];
    }

    /**
     * Estadísticas agregadas por pregunta y respuesta
     * Asume que todas las encuestas del grupo tienen las mismas preguntas/opciones
     */
    public function generateQuestionStats(SurveyGroup $group): array
    {
        $surveys = $group->surveys;

        if ($surveys->isEmpty()) {
            return [];
        }

        // Obtener TODAS las preguntas de TODAS las encuestas del grupo
        $allQuestionIds = Question::whereIn('survey_id', $surveys->pluck('id'))->pluck('id');

        // Usar la primera encuesta como plantilla para el orden y texto
        $templateSurvey = $surveys->first();
        $templateQuestions = $templateSurvey->questions()
            ->with(['options'])
            ->orderBy('order')
            ->get();

        $stats = [];

        foreach ($templateQuestions as $templateQuestion) {
            // Encontrar TODAS las preguntas equivalentes en todas las encuestas (mismo texto)
            $equivalentQuestionIds = Question::whereIn('survey_id', $surveys->pluck('id'))
                ->where('question_text', $templateQuestion->question_text)
                ->pluck('id');

            // Total de votos contables para esta pregunta EN TODAS LAS ENCUESTAS DEL GRUPO
            $totalVotesForQuestion = Vote::whereIn('survey_id', $surveys->pluck('id'))
                ->countable()
                ->whereIn('question_id', $equivalentQuestionIds)
                ->count();

            $optionStats = [];

            foreach ($templateQuestion->options as $templateOption) {
                // Encontrar TODAS las opciones equivalentes en todas las encuestas (mismo texto)
                $equivalentOptionIds = QuestionOption::whereIn('question_id', $equivalentQuestionIds)
                    ->where('option_text', $templateOption->option_text)
                    ->pluck('id');

                // Votos contables para esta opción EN TODAS LAS ENCUESTAS DEL GRUPO
                $votesForOption = Vote::whereIn('survey_id', $surveys->pluck('id'))
                    ->countable()
                    ->whereIn('question_id', $equivalentQuestionIds)
                    ->whereIn('question_option_id', $equivalentOptionIds)
                    ->count();

                // Calcular porcentaje
                $percentage = $totalVotesForQuestion > 0
                    ? round(($votesForOption / $totalVotesForQuestion) * 100, 2)
                    : 0;

                $optionStats[] = [
                    'option_text' => $templateOption->option_text,
                    'votes' => $votesForOption,
                    'percentage' => $percentage,
                ];
            }

            $stats[] = [
                'question_text' => $templateQuestion->question_text,
                'total_votes' => $totalVotesForQuestion,
                'options' => $optionStats,
            ];
        }

        return $stats;
    }

    /**
     * Estadísticas individuales de cada encuesta en el grupo
     */
    public function generatePerSurveyStats(SurveyGroup $group): array
    {
        $surveys = $group->surveys;
        $stats = [];

        foreach ($surveys as $survey) {
            // Votos contables de esta encuesta
            $validVotes = $survey->votes()->countable()->count();

            // Vistas de esta encuesta
            $views = $survey->views_count ?? 0;

            // Tasa de conversión
            $conversionRate = $views > 0
                ? round(($validVotes / $views) * 100, 2)
                : 0;

            $stats[] = [
                'survey_id' => $survey->id,
                'survey_title' => $survey->title,
                'survey_slug' => $survey->public_slug,
                'views' => $views,
                'valid_votes' => $validVotes,
                'conversion_rate' => $conversionRate,
            ];
        }

        // Ordenar por votos válidos (mayor a menor)
        usort($stats, function ($a, $b) {
            return $b['valid_votes'] <=> $a['valid_votes'];
        });

        return $stats;
    }

    /**
     * Estadísticas de fraude agregadas del grupo
     */
    public function generateFraudStats(SurveyGroup $group): array
    {
        $surveys = $group->surveys;

        $votes = Vote::whereIn('survey_id', $surveys->pluck('id'))
            ->valid()
            ->get();

        if ($votes->isEmpty()) {
            return [
                'average_fraud_score' => 0,
                'high_risk_count' => 0,
                'high_risk_percentage' => 0,
                'fraud_reasons_distribution' => [],
                'duplicate_token_stats' => [
                    'total_tokens_with_duplicates' => 0,
                    'total_duplicate_attempts' => 0,
                    'attempts_by_count' => [],
                    'top_duplicate_tokens' => [],
                ],
            ];
        }

        // Score promedio
        $avgFraudScore = round($votes->avg('fraud_score'), 2);

        // Votos de alto riesgo (score >= 60)
        $highRiskCount = $votes->where('fraud_score', '>=', 60)->count();
        $highRiskPercentage = round(($highRiskCount / $votes->count()) * 100, 2);

        // Distribución de razones de fraude
        $fraudReasonsDistribution = [];
        foreach ($votes as $vote) {
            if ($vote->fraud_reasons) {
                $reasons = is_string($vote->fraud_reasons)
                    ? json_decode($vote->fraud_reasons, true)
                    : $vote->fraud_reasons;

                if (is_array($reasons)) {
                    foreach ($reasons as $reason) {
                        if (!isset($fraudReasonsDistribution[$reason])) {
                            $fraudReasonsDistribution[$reason] = 0;
                        }
                        $fraudReasonsDistribution[$reason]++;
                    }
                }
            }
        }

        // Votos duplicados por token (agregado del grupo)
        $duplicateTokenStats = $this->generateDuplicateTokenStats($group);

        return [
            'average_fraud_score' => $avgFraudScore,
            'high_risk_count' => $highRiskCount,
            'high_risk_percentage' => $highRiskPercentage,
            'fraud_reasons_distribution' => $fraudReasonsDistribution,
            'duplicate_token_stats' => $duplicateTokenStats,
        ];
    }

    /**
     * Estadísticas de votos duplicados por token (agregado del grupo)
     */
    public function generateDuplicateTokenStats(SurveyGroup $group): array
    {
        $surveys = $group->surveys;

        // Obtener todos los tokens con múltiples intentos de todas las encuestas del grupo
        $tokensWithMultipleAttempts = collect();
        foreach ($surveys as $survey) {
            $tokens = $survey->tokens()
                ->where('vote_attempts', '>', 1)
                ->get();
            $tokensWithMultipleAttempts = $tokensWithMultipleAttempts->merge($tokens);
        }

        // Ordenar por intentos descendente
        $tokensWithMultipleAttempts = $tokensWithMultipleAttempts->sortByDesc('vote_attempts');

        $totalDuplicateAttempts = $tokensWithMultipleAttempts->sum('vote_attempts');
        $tokensCount = $tokensWithMultipleAttempts->count();

        // Agrupar por cantidad de intentos
        $attemptsByCount = [];
        foreach ($tokensWithMultipleAttempts as $token) {
            $attempts = $token->vote_attempts;
            if (!isset($attemptsByCount[$attempts])) {
                $attemptsByCount[$attempts] = 0;
            }
            $attemptsByCount[$attempts]++;
        }

        // Ordenar de mayor a menor
        krsort($attemptsByCount);

        return [
            'total_tokens_with_duplicates' => $tokensCount,
            'total_duplicate_attempts' => $totalDuplicateAttempts,
            'attempts_by_count' => $attemptsByCount,
            'top_duplicate_tokens' => $tokensWithMultipleAttempts->take(10)->map(function($token) {
                return [
                    'token' => substr($token->token, 0, 8) . '...',
                    'full_token' => $token->token,
                    'attempts' => $token->vote_attempts,
                    'status' => $token->status,
                    'survey_id' => $token->survey_id,
                    'last_attempt_at' => $token->last_attempt_at?->format('Y-m-d H:i:s'),
                ];
            })->toArray(),
        ];
    }

    /**
     * Estadísticas de tokens agregadas del grupo
     */
    public function generateTokenStats(SurveyGroup $group): array
    {
        $surveys = $group->surveys;
        $totalTokens = 0;
        $pendingTokens = 0;
        $reservedTokens = 0;
        $usedTokens = 0;
        $expiredTokens = 0;

        foreach ($surveys as $survey) {
            $tokens = $survey->tokens;
            $totalTokens += $tokens->count();
            $pendingTokens += $tokens->where('status', 'pending')->count();
            $reservedTokens += $tokens->where('status', 'reserved')->count();
            $usedTokens += $tokens->where('status', 'used')->count();
            $expiredTokens += $tokens->where('status', 'expired')->count();
        }

        return [
            'total_tokens' => $totalTokens,
            'pending_tokens' => $pendingTokens,
            'reserved_tokens' => $reservedTokens,
            'used_tokens' => $usedTokens,
            'expired_tokens' => $expiredTokens,
            'usage_rate' => $totalTokens > 0
                ? round(($usedTokens / $totalTokens) * 100, 2)
                : 0,
        ];
    }

    /**
     * Métricas de conversión agregadas del grupo
     */
    public function generateConversionMetrics(SurveyGroup $group): array
    {
        $surveys = $group->surveys;

        $totalViews = $surveys->sum('views_count');
        $totalVotes = Vote::whereIn('survey_id', $surveys->pluck('id'))
            ->valid()
            ->count();
        $approvedVotes = Vote::whereIn('survey_id', $surveys->pluck('id'))
            ->countable()
            ->count();

        // Tasa de conversión: vistas -> votos
        $viewToVoteRate = $totalViews > 0
            ? round(($totalVotes / $totalViews) * 100, 2)
            : 0;

        // Tasa de aprobación: votos -> aprobados
        $voteApprovalRate = $totalVotes > 0
            ? round(($approvedVotes / $totalVotes) * 100, 2)
            : 0;

        // Tasa de conversión completa: vistas -> aprobados
        $completeConversionRate = $totalViews > 0
            ? round(($approvedVotes / $totalViews) * 100, 2)
            : 0;

        return [
            'view_to_vote_rate' => $viewToVoteRate,
            'vote_approval_rate' => $voteApprovalRate,
            'complete_conversion_rate' => $completeConversionRate,
        ];
    }
}
