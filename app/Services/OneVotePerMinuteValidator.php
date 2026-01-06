<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Validador de 2 votos por minuto por opción
 *
 * Esta validación asegura que cada opción de respuesta solo puede recibir
 * 2 votos válidos por minuto. Si llegan más votos en ese minuto, solo los
 * primeros 2 se cuentan como válidos y los demás se marcan como no válidos.
 *
 * El usuario siempre ve "éxito" pero internamente el voto no cuenta.
 */
class OneVotePerMinuteValidator
{
    /**
     * Número máximo de votos permitidos por minuto
     */
    private const MAX_VOTES_PER_MINUTE = 2;

    /**
     * Ventana de tiempo en segundos (1 minuto)
     */
    private const TIME_WINDOW_SECONDS = 60;

    /**
     * Verifica si una opción puede recibir un voto válido
     *
     * @param int $optionId ID de la opción de respuesta
     * @return array ['allowed' => bool, 'reason' => string|null, 'votes_in_window' => int]
     */
    public function canVoteForOption(int $optionId): array
    {
        $cacheKey = $this->getCacheKey($optionId);
        $timestamps = Cache::get($cacheKey, []);

        // Si no hay timestamps, es el primer voto
        if (empty($timestamps)) {
            return [
                'allowed' => true,
                'reason' => null,
                'votes_in_window' => 0,
            ];
        }

        // Limpiar timestamps antiguos (fuera de la ventana de 60 segundos)
        $now = Carbon::now()->timestamp;
        $windowStart = $now - self::TIME_WINDOW_SECONDS;

        $recentTimestamps = array_filter($timestamps, function($ts) use ($windowStart) {
            return $ts >= $windowStart;
        });

        // Contar votos en la ventana actual
        $votesInWindow = count($recentTimestamps);

        // Si hay menos de 2 votos en el último minuto, permitir
        if ($votesInWindow < self::MAX_VOTES_PER_MINUTE) {
            return [
                'allowed' => true,
                'reason' => null,
                'votes_in_window' => $votesInWindow,
            ];
        }

        // Ya hay 2 votos en el último minuto, rechazar
        $oldestInWindow = min($recentTimestamps);
        $secondsSinceOldest = $now - $oldestInWindow;
        $remainingSeconds = self::TIME_WINDOW_SECONDS - $secondsSinceOldest;

        return [
            'allowed' => false,
            'reason' => 'two_votes_per_minute_per_option',
            'message' => "Esta opción ya recibió {$votesInWindow} votos en el último minuto. Debe esperar {$remainingSeconds} segundos.",
            'votes_in_window' => $votesInWindow,
            'remaining_seconds' => max(0, $remainingSeconds),
        ];
    }

    /**
     * Registra un voto válido para una opción
     * Agrega el timestamp actual al array de votos
     *
     * @param int $optionId ID de la opción de respuesta
     * @return void
     */
    public function recordValidVote(int $optionId): void
    {
        $cacheKey = $this->getCacheKey($optionId);
        $now = Carbon::now()->timestamp;

        // Obtener timestamps existentes
        $timestamps = Cache::get($cacheKey, []);

        // Agregar el nuevo timestamp
        $timestamps[] = $now;

        // Limpiar timestamps antiguos (más de 60 segundos)
        $windowStart = $now - self::TIME_WINDOW_SECONDS;
        $timestamps = array_filter($timestamps, function($ts) use ($windowStart) {
            return $ts >= $windowStart;
        });

        // Guardar en caché por 2 minutos (1 minuto de ventana + 1 minuto de margen)
        Cache::put($cacheKey, array_values($timestamps), now()->addMinutes(2));
    }

    /**
     * Obtiene la clave de caché para una opción
     *
     * @param int $optionId
     * @return string
     */
    private function getCacheKey(int $optionId): string
    {
        return "two_votes_per_minute:option:{$optionId}";
    }

    /**
     * Obtiene estadísticas de la última votación por opción
     * Útil para debugging y monitoreo
     *
     * @param int $optionId
     * @return array
     */
    public function getOptionStats(int $optionId): array
    {
        $cacheKey = $this->getCacheKey($optionId);
        $timestamps = Cache::get($cacheKey, []);

        if (empty($timestamps)) {
            return [
                'has_recent_votes' => false,
                'votes_in_window' => 0,
                'can_vote_now' => true,
                'slots_available' => self::MAX_VOTES_PER_MINUTE,
            ];
        }

        $now = Carbon::now()->timestamp;
        $windowStart = $now - self::TIME_WINDOW_SECONDS;

        // Filtrar solo timestamps recientes (últimos 60 segundos)
        $recentTimestamps = array_filter($timestamps, function($ts) use ($windowStart) {
            return $ts >= $windowStart;
        });

        $votesInWindow = count($recentTimestamps);
        $canVoteNow = $votesInWindow < self::MAX_VOTES_PER_MINUTE;
        $slotsAvailable = self::MAX_VOTES_PER_MINUTE - $votesInWindow;

        $remainingSeconds = 0;
        if (!$canVoteNow && !empty($recentTimestamps)) {
            $oldestInWindow = min($recentTimestamps);
            $remainingSeconds = self::TIME_WINDOW_SECONDS - ($now - $oldestInWindow);
        }

        return [
            'has_recent_votes' => true,
            'votes_in_window' => $votesInWindow,
            'can_vote_now' => $canVoteNow,
            'slots_available' => max(0, $slotsAvailable),
            'remaining_seconds' => max(0, $remainingSeconds),
        ];
    }

    /**
     * Limpia la restricción de una opción (solo para testing)
     *
     * @param int $optionId
     * @return void
     * @throws \Exception Si se intenta usar en producción
     */
    public function clearOptionRestriction(int $optionId): void
    {
        if (app()->environment('production')) {
            throw new \Exception('Cannot clear option restriction in production');
        }

        $cacheKey = $this->getCacheKey($optionId);
        Cache::forget($cacheKey);
    }

    /**
     * Limpia todas las restricciones (solo para testing)
     *
     * @return void
     * @throws \Exception Si se intenta usar en producción
     */
    public function clearAllRestrictions(): void
    {
        if (app()->environment('production')) {
            throw new \Exception('Cannot clear restrictions in production');
        }

        // En producción esto nunca se ejecuta
        // En testing, simplemente permitimos que expire naturalmente
        // o usamos clearOptionRestriction() para opciones específicas
    }
}
