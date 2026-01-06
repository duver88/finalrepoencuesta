<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Sistema avanzado de Rate Limiting para votos
 * Protege contra ataques de fraude masivo usando múltiples capas
 */
class VoteRateLimiter
{
    /**
     * Configuración de rate limiting por capas
     * Ajustado para encuestas virales con ~20k usuarios
     *
     * NOTA: No se usa rate limiting por IP porque puede bloquear redes compartidas
     * (empresas, universidades, cafés) donde muchos usuarios legítimos comparten la misma IP
     */
    private array $limits = [
        // Capa 1: Rate limit POR OPCIÓN (previene spam a una opción específica)
        'per_option' => [
            ['window' => 60, 'max_votes' => 50],      // 50 votos por minuto
            ['window' => 300, 'max_votes' => 150],    // 150 votos en 5 minutos
            ['window' => 600, 'max_votes' => 250],    // 250 votos en 10 minutos
            ['window' => 1800, 'max_votes' => 600],   // 600 votos en 30 minutos
        ],

        // Capa 2: Rate limit por fingerprint (único por dispositivo/navegador)
        'per_fingerprint' => [
            ['window' => 300, 'max_votes' => 3],      // 3 votos en 5 minutos
            ['window' => 3600, 'max_votes' => 5],     // 5 votos por hora
        ],

        // Capa 3: Rate limit GLOBAL de la encuesta (previene DDoS)
        'global_survey' => [
            ['window' => 60, 'max_votes' => 100],     // 100 votos por minuto
            ['window' => 300, 'max_votes' => 400],    // 400 votos en 5 minutos
        ],
    ];

    /**
     * Verifica si un voto debe ser permitido según todas las capas de rate limiting
     */
    public function allowVote(
        int $surveyId,
        int $optionId,
        string $ipAddress,
        string $fingerprint
    ): array {
        // Capa 1: Verificar rate limit por opción
        $optionCheck = $this->checkPerOptionLimit($optionId);
        if (!$optionCheck['allowed']) {
            return [
                'allowed' => false,
                'reason' => 'rate_limit_option',
                'message' => 'Esta opción está recibiendo demasiados votos. Por favor intenta más tarde.',
                'retry_after' => $optionCheck['retry_after'],
                'details' => $optionCheck['details'],
            ];
        }

        // Capa 2: Verificar rate limit por fingerprint (ÚNICA IDENTIDAD CONFIABLE)
        // No usamos IP porque bloquearía redes compartidas (oficinas, universidades, etc.)
        $fingerprintCheck = $this->checkPerFingerprintLimit($surveyId, $fingerprint);
        if (!$fingerprintCheck['allowed']) {
            return [
                'allowed' => false,
                'reason' => 'rate_limit_fingerprint',
                'message' => 'Detectamos actividad sospechosa. Por favor intenta más tarde.',
                'retry_after' => $fingerprintCheck['retry_after'],
                'details' => $fingerprintCheck['details'],
            ];
        }

        // Capa 3: Verificar rate limit global
        $globalCheck = $this->checkGlobalLimit($surveyId);
        if (!$globalCheck['allowed']) {
            return [
                'allowed' => false,
                'reason' => 'rate_limit_global',
                'message' => 'La encuesta está recibiendo mucho tráfico. Por favor intenta en unos segundos.',
                'retry_after' => $globalCheck['retry_after'],
                'details' => $globalCheck['details'],
            ];
        }

        return [
            'allowed' => true,
            'reason' => null,
            'message' => null,
        ];
    }

    /**
     * Registra un voto en el sistema de rate limiting
     */
    public function recordVote(
        int $surveyId,
        int $optionId,
        string $ipAddress,
        string $fingerprint
    ): void {
        $now = time();

        // Registrar solo en las capas activas (sin IP)
        $this->incrementCounter("vote:option:{$optionId}", $now);
        $this->incrementCounter("vote:fingerprint:{$surveyId}:{$fingerprint}", $now);
        $this->incrementCounter("vote:global:{$surveyId}", $now);
    }

    /**
     * Capa 1: Verificar rate limit por opción
     */
    private function checkPerOptionLimit(int $optionId): array
    {
        $key = "vote:option:{$optionId}";
        return $this->checkLimit($key, $this->limits['per_option']);
    }

    /**
     * Capa 2: Verificar rate limit por fingerprint
     */
    private function checkPerFingerprintLimit(int $surveyId, string $fingerprint): array
    {
        $key = "vote:fingerprint:{$surveyId}:{$fingerprint}";
        return $this->checkLimit($key, $this->limits['per_fingerprint']);
    }

    /**
     * Capa 3: Verificar rate limit global
     */
    private function checkGlobalLimit(int $surveyId): array
    {
        $key = "vote:global:{$surveyId}";
        return $this->checkLimit($key, $this->limits['global_survey']);
    }

    /**
     * Verificar límites contra una clave y configuración de límites
     */
    private function checkLimit(string $key, array $limitConfig): array
    {
        $now = time();
        $timestamps = $this->getTimestamps($key);

        foreach ($limitConfig as $limit) {
            $window = $limit['window'];
            $maxVotes = $limit['max_votes'];
            $windowStart = $now - $window;

            // Contar votos en esta ventana
            $votesInWindow = count(array_filter($timestamps, function($timestamp) use ($windowStart) {
                return $timestamp >= $windowStart;
            }));

            if ($votesInWindow >= $maxVotes) {
                // Calcular cuándo expirará el voto más antiguo en la ventana
                $oldestInWindow = min(array_filter($timestamps, function($timestamp) use ($windowStart) {
                    return $timestamp >= $windowStart;
                }));
                $retryAfter = ($oldestInWindow + $window) - $now;

                Log::warning("Rate limit exceeded", [
                    'key' => $key,
                    'window' => $window,
                    'max_votes' => $maxVotes,
                    'current_votes' => $votesInWindow,
                    'retry_after' => $retryAfter,
                ]);

                return [
                    'allowed' => false,
                    'retry_after' => max(1, $retryAfter),
                    'details' => [
                        'window_seconds' => $window,
                        'max_votes' => $maxVotes,
                        'current_votes' => $votesInWindow,
                    ],
                ];
            }
        }

        return ['allowed' => true];
    }

    /**
     * Obtener timestamps de votos para una clave
     */
    private function getTimestamps(string $key): array
    {
        $data = Cache::get($key, []);
        return is_array($data) ? $data : [];
    }

    /**
     * Incrementar contador de votos
     */
    private function incrementCounter(string $key, int $timestamp): void
    {
        $timestamps = $this->getTimestamps($key);
        $timestamps[] = $timestamp;

        // Limpiar timestamps antiguos (mantener solo últimas 2 horas)
        $cutoff = $timestamp - 7200;
        $timestamps = array_filter($timestamps, function($ts) use ($cutoff) {
            return $ts >= $cutoff;
        });

        // Guardar en cache por 2 horas
        Cache::put($key, array_values($timestamps), now()->addHours(2));
    }

    /**
     * Obtener estadísticas de rate limiting para monitoreo
     */
    public function getStats(int $surveyId, int $optionId): array
    {
        $now = time();

        // Stats por opción
        $optionKey = "vote:option:{$optionId}";
        $optionTimestamps = $this->getTimestamps($optionKey);

        // Stats globales
        $globalKey = "vote:global:{$surveyId}";
        $globalTimestamps = $this->getTimestamps($globalKey);

        return [
            'option' => [
                'total_recent' => count($optionTimestamps),
                'last_minute' => $this->countInWindow($optionTimestamps, $now, 60),
                'last_5_minutes' => $this->countInWindow($optionTimestamps, $now, 300),
                'last_10_minutes' => $this->countInWindow($optionTimestamps, $now, 600),
            ],
            'global' => [
                'total_recent' => count($globalTimestamps),
                'last_minute' => $this->countInWindow($globalTimestamps, $now, 60),
                'last_5_minutes' => $this->countInWindow($globalTimestamps, $now, 300),
            ],
        ];
    }

    /**
     * Contar votos en una ventana de tiempo
     */
    private function countInWindow(array $timestamps, int $now, int $window): int
    {
        $windowStart = $now - $window;
        return count(array_filter($timestamps, function($ts) use ($windowStart) {
            return $ts >= $windowStart;
        }));
    }

    /**
     * Limpiar rate limiting para testing (solo usar en desarrollo)
     */
    public function clearLimits(int $surveyId, int $optionId): void
    {
        if (app()->environment('production')) {
            throw new \Exception('Cannot clear rate limits in production');
        }

        Cache::forget("vote:option:{$optionId}");
        Cache::forget("vote:global:{$surveyId}");
    }
}
