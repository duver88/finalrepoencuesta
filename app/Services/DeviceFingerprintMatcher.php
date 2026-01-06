<?php

namespace App\Services;

use App\Models\Vote;
use Illuminate\Support\Facades\DB;

class DeviceFingerprintMatcher
{
    /**
     * Detectar si el dispositivo actual ya votó en la encuesta
     * Compara múltiples características de hardware para identificar el mismo dispositivo físico
     * incluso si cambian navegador, modo incógnito, o borran cookies
     */
    public function hasDeviceVotedInSurvey(int $surveyId, array $deviceData): bool
    {
        // 1. Buscar por fingerprint exacto (rápido)
        if (!empty($deviceData['fingerprint'])) {
            $exactMatch = Vote::where('survey_id', $surveyId)
                ->where('fingerprint', $deviceData['fingerprint'])
                ->where('is_valid', true)
                ->exists();

            if ($exactMatch) {
                return true;
            }
        }

        // 2. Buscar por características de hardware (detecta mismo dispositivo físico)
        return $this->findSimilarDevice($surveyId, $deviceData);
    }

    /**
     * Buscar votos de dispositivos con características de hardware similares
     */
    private function findSimilarDevice(int $surveyId, array $deviceData): bool
    {
        // Extraer características clave del dispositivo
        $userAgent = $deviceData['user_agent'] ?? null;
        $platform = $deviceData['platform'] ?? null;
        $screenResolution = $deviceData['screen_resolution'] ?? null;
        $hardwareConcurrency = $deviceData['hardware_concurrency'] ?? null;

        // Si no tenemos suficientes datos, no podemos hacer matching
        if (!$userAgent || !$screenResolution) {
            return false;
        }

        // Buscar votos con características similares
        $query = Vote::where('survey_id', $surveyId)
            ->where('is_valid', true);

        // Coincidir por User-Agent (incluye info de SO, navegador, versión)
        // Normalizar para detectar variaciones mínimas
        $normalizedUA = $this->normalizeUserAgent($userAgent);
        $query->where(DB::raw('REPLACE(REPLACE(user_agent, " ", ""), ".", "")'), 'LIKE', '%' . $normalizedUA . '%');

        // Coincidir por resolución de pantalla (muy específico por dispositivo)
        if ($screenResolution) {
            $query->where('screen_resolution', $screenResolution);
        }

        // Coincidir por plataforma
        if ($platform) {
            $query->where('platform', $platform);
        }

        // Coincidir por CPU cores (específico de hardware)
        if ($hardwareConcurrency) {
            $query->where('hardware_concurrency', $hardwareConcurrency);
        }

        return $query->exists();
    }

    /**
     * Normalizar User-Agent para comparación
     * Elimina espacios y puntos para detectar variaciones menores
     */
    private function normalizeUserAgent(string $userAgent): string
    {
        // Extraer componentes clave del UA (SO, arquitectura)
        // Ejemplo: "Windows NT 10.0; Win64; x64" es único por PC
        preg_match('/\(([^)]+)\)/', $userAgent, $matches);

        if (isset($matches[1])) {
            $platform = $matches[1];
            // Normalizar: eliminar espacios, puntos, mayúsculas
            return strtolower(str_replace([' ', '.', ';'], '', $platform));
        }

        // Fallback: normalizar todo el UA
        return strtolower(str_replace([' ', '.'], '', substr($userAgent, 0, 100)));
    }

    /**
     * Calcular un score de similitud entre dos dispositivos (0-100)
     * Útil para análisis y debugging
     */
    public function calculateDeviceSimilarity(array $device1, array $device2): int
    {
        $score = 0;
        $maxScore = 0;

        // User-Agent (peso: 30)
        $maxScore += 30;
        if (isset($device1['user_agent']) && isset($device2['user_agent'])) {
            $similarity = similar_text($device1['user_agent'], $device2['user_agent']);
            $score += min(30, ($similarity / strlen($device1['user_agent'])) * 30);
        }

        // Resolución de pantalla (peso: 25) - MUY específico
        $maxScore += 25;
        if (isset($device1['screen_resolution']) && isset($device2['screen_resolution'])) {
            if ($device1['screen_resolution'] === $device2['screen_resolution']) {
                $score += 25;
            }
        }

        // Plataforma (peso: 20)
        $maxScore += 20;
        if (isset($device1['platform']) && isset($device2['platform'])) {
            if ($device1['platform'] === $device2['platform']) {
                $score += 20;
            }
        }

        // CPU cores (peso: 15)
        $maxScore += 15;
        if (isset($device1['hardware_concurrency']) && isset($device2['hardware_concurrency'])) {
            if ($device1['hardware_concurrency'] === $device2['hardware_concurrency']) {
                $score += 15;
            }
        }

        // Fingerprint (peso: 10) - bonus si coincide
        $maxScore += 10;
        if (isset($device1['fingerprint']) && isset($device2['fingerprint'])) {
            if ($device1['fingerprint'] === $device2['fingerprint']) {
                $score += 10;
            }
        }

        // Retornar porcentaje
        return $maxScore > 0 ? round(($score / $maxScore) * 100) : 0;
    }
}
