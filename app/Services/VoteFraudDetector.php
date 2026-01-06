<?php

namespace App\Services;

use App\Models\Vote;
use App\Models\QuestionOption;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class VoteFraudDetector
{
    /**
     * Umbrales de detección configurables
     * Ajustados para encuestas con ~20k participantes
     */
    private array $thresholds = [
        // Votos por opción en ventana de tiempo
        'votes_per_option_window' => [
            'limit' => 30,           // Máximo 30 votos por opción
            'minutes' => 5,          // En 5 minutos
            'score' => 25,           // Puntos de sospecha
        ],

        // Votos con mismo user agent en ventana de tiempo
        'same_user_agent_window' => [
            'limit' => 15,           // Máximo 15 votos con mismo user agent
            'minutes' => 10,         // En 10 minutos
            'score' => 30,
        ],

        // Votos con misma resolución de pantalla
        'same_screen_resolution' => [
            'limit' => 20,           // Máximo 20 votos con misma resolución
            'minutes' => 10,         // En 10 minutos
            'score' => 20,
        ],

        // Ráfaga repentina de votos (spike detection)
        'vote_spike' => [
            'limit' => 50,           // Más de 50 votos
            'minutes' => 2,          // En 2 minutos
            'score' => 35,
        ],

        // Porcentaje anormal hacia una opción
        'option_dominance' => [
            'percentage' => 80,      // Si una opción tiene más del 80%
            'min_votes' => 100,      // Con al menos 100 votos
            'minutes' => 30,         // En los últimos 30 minutos
            'score' => 40,
        ],
    ];

    /**
     * Analiza un voto y calcula su puntuación de fraude
     */
    public function analyzeVote(array $voteData, int $questionId, int $optionId): array
    {
        $fraudScore = 0;
        $reasons = [];

        // 1. Verificar votos por opción en ventana de tiempo
        $optionVotesCheck = $this->checkVotesPerOption($optionId);
        if ($optionVotesCheck['suspicious']) {
            $fraudScore += $this->thresholds['votes_per_option_window']['score'];
            $reasons[] = $optionVotesCheck['reason'];
        }

        // 2. Verificar votos con mismo user agent
        if (isset($voteData['user_agent'])) {
            $userAgentCheck = $this->checkSameUserAgent($questionId, $voteData['user_agent']);
            if ($userAgentCheck['suspicious']) {
                $fraudScore += $this->thresholds['same_user_agent_window']['score'];
                $reasons[] = $userAgentCheck['reason'];
            }
        }

        // 3. Verificar votos con misma resolución
        if (isset($voteData['screen_resolution'])) {
            $resolutionCheck = $this->checkSameResolution($questionId, $voteData['screen_resolution']);
            if ($resolutionCheck['suspicious']) {
                $fraudScore += $this->thresholds['same_screen_resolution']['score'];
                $reasons[] = $resolutionCheck['reason'];
            }
        }

        // 4. Detectar ráfagas de votos (spike detection)
        $spikeCheck = $this->checkVoteSpike($questionId);
        if ($spikeCheck['suspicious']) {
            $fraudScore += $this->thresholds['vote_spike']['score'];
            $reasons[] = $spikeCheck['reason'];
        }

        // 5. Verificar dominancia anormal de una opción
        $dominanceCheck = $this->checkOptionDominance($questionId, $optionId);
        if ($dominanceCheck['suspicious']) {
            $fraudScore += $this->thresholds['option_dominance']['score'];
            $reasons[] = $dominanceCheck['reason'];
        }

        // Determinar el estado basado en la puntuación
        $status = $this->determineStatus($fraudScore);

        return [
            'status' => $status,
            'fraud_score' => min($fraudScore, 100), // Máximo 100
            'fraud_reasons' => $reasons,
        ];
    }

    /**
     * Verifica cuántos votos ha recibido una opción recientemente
     */
    private function checkVotesPerOption(int $optionId): array
    {
        $threshold = $this->thresholds['votes_per_option_window'];
        $since = Carbon::now()->subMinutes($threshold['minutes']);

        $count = Vote::where('question_option_id', $optionId)
            ->where('created_at', '>=', $since)
            ->count();

        return [
            'suspicious' => $count >= $threshold['limit'],
            'reason' => "Esta opción recibió {$count} votos en {$threshold['minutes']} minutos (límite: {$threshold['limit']})",
            'count' => $count,
        ];
    }

    /**
     * Verifica votos con el mismo user agent
     */
    private function checkSameUserAgent(int $questionId, string $userAgent): array
    {
        $threshold = $this->thresholds['same_user_agent_window'];
        $since = Carbon::now()->subMinutes($threshold['minutes']);

        $count = Vote::where('question_id', $questionId)
            ->where('user_agent', $userAgent)
            ->where('created_at', '>=', $since)
            ->count();

        return [
            'suspicious' => $count >= $threshold['limit'],
            'reason' => "Detectados {$count} votos con el mismo navegador en {$threshold['minutes']} minutos",
            'count' => $count,
        ];
    }

    /**
     * Verifica votos con la misma resolución de pantalla
     */
    private function checkSameResolution(int $questionId, string $resolution): array
    {
        $threshold = $this->thresholds['same_screen_resolution'];
        $since = Carbon::now()->subMinutes($threshold['minutes']);

        $count = Vote::where('question_id', $questionId)
            ->where('screen_resolution', $resolution)
            ->where('created_at', '>=', $since)
            ->count();

        return [
            'suspicious' => $count >= $threshold['limit'],
            'reason' => "Detectados {$count} votos con la misma resolución ({$resolution}) en {$threshold['minutes']} minutos",
            'count' => $count,
        ];
    }

    /**
     * Detecta ráfagas repentinas de votos (spike detection)
     */
    private function checkVoteSpike(int $questionId): array
    {
        $threshold = $this->thresholds['vote_spike'];
        $since = Carbon::now()->subMinutes($threshold['minutes']);

        $count = Vote::where('question_id', $questionId)
            ->where('created_at', '>=', $since)
            ->count();

        return [
            'suspicious' => $count >= $threshold['limit'],
            'reason' => "Detectada ráfaga de {$count} votos en solo {$threshold['minutes']} minutos",
            'count' => $count,
        ];
    }

    /**
     * Verifica si una opción está recibiendo un porcentaje anormalmente alto de votos
     */
    private function checkOptionDominance(int $questionId, int $optionId): array
    {
        $threshold = $this->thresholds['option_dominance'];
        $since = Carbon::now()->subMinutes($threshold['minutes']);

        // Total de votos recientes para esta pregunta
        $totalVotes = Vote::where('question_id', $questionId)
            ->where('created_at', '>=', $since)
            ->count();

        if ($totalVotes < $threshold['min_votes']) {
            return ['suspicious' => false, 'reason' => ''];
        }

        // Votos para esta opción específica
        $optionVotes = Vote::where('question_id', $questionId)
            ->where('question_option_id', $optionId)
            ->where('created_at', '>=', $since)
            ->count();

        $percentage = ($optionVotes / $totalVotes) * 100;

        return [
            'suspicious' => $percentage >= $threshold['percentage'],
            'reason' => sprintf(
                "Esta opción tiene %.1f%% de los votos recientes (%d de %d en %d minutos)",
                $percentage,
                $optionVotes,
                $totalVotes,
                $threshold['minutes']
            ),
            'percentage' => $percentage,
        ];
    }

    /**
     * Determina el estado del voto según la puntuación de fraude
     */
    private function determineStatus(float $fraudScore): string
    {
        if ($fraudScore >= 60) {
            return 'pending_review';  // Muy sospechoso, requiere revisión
        }

        if ($fraudScore >= 40) {
            return 'pending_review';  // Moderadamente sospechoso, mejor revisarlo
        }

        return 'approved';  // Parece legítimo
    }

    /**
     * Obtiene estadísticas de fraude para una encuesta
     */
    public function getSurveyStats(int $surveyId): array
    {
        $votes = Vote::whereHas('question', function ($query) use ($surveyId) {
            $query->where('survey_id', $surveyId);
        })->get();

        return [
            'total_votes' => $votes->count(),
            'approved_votes' => $votes->where('status', 'approved')->count(),
            'pending_review' => $votes->where('status', 'pending_review')->count(),
            'rejected_votes' => $votes->where('status', 'rejected')->count(),
            'avg_fraud_score' => round($votes->avg('fraud_score'), 2),
            'high_risk_votes' => $votes->where('fraud_score', '>=', 60)->count(),
        ];
    }
}
