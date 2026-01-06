<?php

namespace App\Services;

use App\Models\Survey;
use App\Models\SurveyGroup;
use App\Models\SurveyToken;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SuspiciousTokenDetector
{
    /**
     * Detecta tokens sospechosos según patrones de tiempo
     *
     * Criterio 1: Tokens usados con diferencia de 1 minuto (mismo minuto: 13:01 y 13:01)
     * Criterio 2: Tokens usados con diferencia de 2 minutos (13:01 y 13:02)
     *
     * @param Survey $survey
     * @return array
     */
    public function detectSuspiciousTokens(Survey $survey)
    {
        // Obtener todos los tokens usados de la encuesta con sus votos
        $usedTokens = SurveyToken::where('survey_id', $survey->id)
            ->where('status', 'used')
            ->whereNotNull('used_at')
            ->with(['votes' => function($query) {
                $query->with(['question', 'option']);
            }])
            ->orderBy('used_at')
            ->get();

        $results = [
            'same_minute' => [], // Tokens usados en el mismo minuto (diferencia 0)
            'one_minute_diff' => [], // Tokens usados con 1 minuto de diferencia
            'two_minute_diff' => [], // Tokens usados con 2 minutos de diferencia
            'summary' => [
                'total_tokens' => $usedTokens->count(),
                'same_minute_count' => 0,
                'one_minute_count' => 0,
                'two_minute_count' => 0,
                'total_suspicious' => 0,
            ]
        ];

        // Agrupar tokens por minuto para detectar patrones
        $tokensByMinute = [];

        foreach ($usedTokens as $token) {
            // Redondear al minuto (ignorar segundos)
            $minuteKey = $token->used_at->format('Y-m-d H:i');

            if (!isset($tokensByMinute[$minuteKey])) {
                $tokensByMinute[$minuteKey] = [];
            }

            $tokensByMinute[$minuteKey][] = $token;
        }

        // Detectar tokens en el mismo minuto
        foreach ($tokensByMinute as $minute => $tokens) {
            if (count($tokens) > 1) {
                // Agrupar por opción elegida (mismo candidato/respuesta)
                $tokensByOption = $this->groupTokensByAnswer($tokens);

                foreach ($tokensByOption as $optionKey => $tokensWithSameAnswer) {
                    if (count($tokensWithSameAnswer) > 1) {
                        // Solo reportar si 2+ tokens votaron por la MISMA opción
                        $group = [
                            'timestamp' => $minute,
                            'count' => count($tokensWithSameAnswer),
                            'option_voted' => $optionKey,
                            'tokens' => $this->formatTokensForReport($tokensWithSameAnswer),
                        ];

                        $results['same_minute'][] = $group;
                        $results['summary']['same_minute_count'] += count($tokensWithSameAnswer);
                    }
                }
            }
        }

        // Detectar tokens con diferencia de 1 y 2 minutos
        $sortedMinutes = array_keys($tokensByMinute);
        sort($sortedMinutes);

        for ($i = 0; $i < count($sortedMinutes); $i++) {
            $currentMinute = $sortedMinutes[$i];
            $currentTokens = $tokensByMinute[$currentMinute];

            // Verificar minuto siguiente (+1 minuto)
            if (isset($sortedMinutes[$i + 1])) {
                $nextMinute = $sortedMinutes[$i + 1];
                $currentTime = Carbon::parse($currentMinute);
                $nextTime = Carbon::parse($nextMinute);

                $diffMinutes = $currentTime->diffInMinutes($nextTime);

                if ($diffMinutes === 1) {
                    // Diferencia de exactamente 1 minuto
                    $nextTokens = $tokensByMinute[$nextMinute];

                    // Combinar ambos grupos y agrupar por respuesta
                    $allTokens = array_merge($currentTokens, $nextTokens);
                    $tokensByOption = $this->groupTokensByAnswer($allTokens);

                    foreach ($tokensByOption as $optionKey => $tokensWithSameAnswer) {
                        if (count($tokensWithSameAnswer) > 1) {
                            // Separar en los dos timestamps
                            $tokensAtTime1 = [];
                            $tokensAtTime2 = [];

                            foreach ($tokensWithSameAnswer as $token) {
                                $tokenMinute = $token->used_at->format('Y-m-d H:i');
                                if ($tokenMinute === $currentMinute) {
                                    $tokensAtTime1[] = $token;
                                } else {
                                    $tokensAtTime2[] = $token;
                                }
                            }

                            $group = [
                                'timestamp_1' => $currentMinute,
                                'timestamp_2' => $nextMinute,
                                'option_voted' => $optionKey,
                                'tokens_at_time_1' => $this->formatTokensForReport($tokensAtTime1),
                                'tokens_at_time_2' => $this->formatTokensForReport($tokensAtTime2),
                                'total_count' => count($tokensWithSameAnswer),
                            ];

                            $results['one_minute_diff'][] = $group;
                            $results['summary']['one_minute_count'] += count($tokensWithSameAnswer);
                        }
                    }
                }

                if ($diffMinutes === 2) {
                    // Diferencia de exactamente 2 minutos
                    $nextTokens = $tokensByMinute[$nextMinute];

                    $allTokens = array_merge($currentTokens, $nextTokens);
                    $tokensByOption = $this->groupTokensByAnswer($allTokens);

                    foreach ($tokensByOption as $optionKey => $tokensWithSameAnswer) {
                        if (count($tokensWithSameAnswer) > 1) {
                            $tokensAtTime1 = [];
                            $tokensAtTime2 = [];

                            foreach ($tokensWithSameAnswer as $token) {
                                $tokenMinute = $token->used_at->format('Y-m-d H:i');
                                if ($tokenMinute === $currentMinute) {
                                    $tokensAtTime1[] = $token;
                                } else {
                                    $tokensAtTime2[] = $token;
                                }
                            }

                            $group = [
                                'timestamp_1' => $currentMinute,
                                'timestamp_2' => $nextMinute,
                                'option_voted' => $optionKey,
                                'tokens_at_time_1' => $this->formatTokensForReport($tokensAtTime1),
                                'tokens_at_time_2' => $this->formatTokensForReport($tokensAtTime2),
                                'total_count' => count($tokensWithSameAnswer),
                            ];

                            $results['two_minute_diff'][] = $group;
                            $results['summary']['two_minute_count'] += count($tokensWithSameAnswer);
                        }
                    }
                }
            }
        }

        // Calcular total de tokens únicos sospechosos
        $allSuspiciousTokens = collect();

        foreach ($results['same_minute'] as $group) {
            foreach ($group['tokens'] as $tokenData) {
                $allSuspiciousTokens->push($tokenData['token']);
            }
        }

        foreach ($results['one_minute_diff'] as $group) {
            foreach ($group['tokens_at_time_1'] as $tokenData) {
                $allSuspiciousTokens->push($tokenData['token']);
            }
            foreach ($group['tokens_at_time_2'] as $tokenData) {
                $allSuspiciousTokens->push($tokenData['token']);
            }
        }

        foreach ($results['two_minute_diff'] as $group) {
            foreach ($group['tokens_at_time_1'] as $tokenData) {
                $allSuspiciousTokens->push($tokenData['token']);
            }
            foreach ($group['tokens_at_time_2'] as $tokenData) {
                $allSuspiciousTokens->push($tokenData['token']);
            }
        }

        $results['summary']['total_suspicious'] = $allSuspiciousTokens->unique()->count();

        return $results;
    }

    /**
     * Agrupa tokens por la opción que votaron (para detectar votos idénticos)
     */
    private function groupTokensByAnswer($tokens)
    {
        $grouped = [];

        foreach ($tokens as $token) {
            // Crear clave única basada en las respuestas
            $answerKey = '';
            foreach ($token->votes as $vote) {
                $answerKey .= $vote->question_option_id . '_';
            }

            if (!isset($grouped[$answerKey])) {
                $grouped[$answerKey] = [];
            }

            $grouped[$answerKey][] = $token;
        }

        return $grouped;
    }

    /**
     * Formatea tokens para el reporte con sus votos
     */
    private function formatTokensForReport($tokens)
    {
        $formatted = [];

        foreach ($tokens as $token) {
            $voteDetails = [];

            foreach ($token->votes as $vote) {
                $voteDetails[] = [
                    'question' => $vote->question->question_text ?? 'N/A',
                    'answer' => $vote->option->option_text ?? 'N/A',
                    'is_valid' => $vote->is_valid,
                    'status' => $vote->status,
                    'fraud_score' => $vote->fraud_score,
                ];
            }

            $formatted[] = [
                'token' => $token->token,
                'used_at' => $token->used_at->format('Y-m-d H:i:s'),
                'fingerprint' => $token->used_by_fingerprint ?? 'N/A',
                'user_agent' => $token->user_agent ?? 'N/A',
                'votes' => $voteDetails,
            ];
        }

        return $formatted;
    }

    /**
     * Exporta tokens sospechosos a CSV
     */
    public function exportSuspiciousTokensToCSV(Survey $survey)
    {
        $suspiciousData = $this->detectSuspiciousTokens($survey);

        $filename = 'tokens_sospechosos_' . $survey->public_slug . '_' . now()->format('Y-m-d_His') . '.csv';
        $filepath = storage_path('app/public/reports/' . $filename);

        if (!file_exists(storage_path('app/public/reports'))) {
            mkdir(storage_path('app/public/reports'), 0755, true);
        }

        $file = fopen($filepath, 'w');
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

        // Encabezados
        fputcsv($file, [
            'Tipo de Sospecha',
            'Timestamp',
            'Token',
            'Hora de Uso Exacta',
            'Fingerprint',
            'Pregunta 1',
            'Respuesta 1',
            'Pregunta 2',
            'Respuesta 2',
            'Estado',
            'Fraud Score',
            'Es Válido'
        ]);

        // Tokens en mismo minuto
        foreach ($suspiciousData['same_minute'] as $group) {
            foreach ($group['tokens'] as $tokenData) {
                $row = [
                    'Mismo Minuto',
                    $group['timestamp'],
                    $tokenData['token'],
                    $tokenData['used_at'],
                    $tokenData['fingerprint'],
                ];

                // Agregar hasta 2 preguntas
                for ($i = 0; $i < 2; $i++) {
                    if (isset($tokenData['votes'][$i])) {
                        $row[] = $tokenData['votes'][$i]['question'];
                        $row[] = $tokenData['votes'][$i]['answer'];
                    } else {
                        $row[] = 'N/A';
                        $row[] = 'N/A';
                    }
                }

                $row[] = $tokenData['votes'][0]['status'] ?? 'N/A';
                $row[] = $tokenData['votes'][0]['fraud_score'] ?? 0;
                $row[] = ($tokenData['votes'][0]['is_valid'] ?? false) ? 'Sí' : 'No';

                fputcsv($file, $row);
            }
        }

        // Tokens con 1 minuto de diferencia
        foreach ($suspiciousData['one_minute_diff'] as $group) {
            $allTokens = array_merge($group['tokens_at_time_1'], $group['tokens_at_time_2']);

            foreach ($allTokens as $tokenData) {
                $row = [
                    '1 Minuto de Diferencia',
                    $group['timestamp_1'] . ' / ' . $group['timestamp_2'],
                    $tokenData['token'],
                    $tokenData['used_at'],
                    $tokenData['fingerprint'],
                ];

                for ($i = 0; $i < 2; $i++) {
                    if (isset($tokenData['votes'][$i])) {
                        $row[] = $tokenData['votes'][$i]['question'];
                        $row[] = $tokenData['votes'][$i]['answer'];
                    } else {
                        $row[] = 'N/A';
                        $row[] = 'N/A';
                    }
                }

                $row[] = $tokenData['votes'][0]['status'] ?? 'N/A';
                $row[] = $tokenData['votes'][0]['fraud_score'] ?? 0;
                $row[] = ($tokenData['votes'][0]['is_valid'] ?? false) ? 'Sí' : 'No';

                fputcsv($file, $row);
            }
        }

        // Tokens con 2 minutos de diferencia
        foreach ($suspiciousData['two_minute_diff'] as $group) {
            $allTokens = array_merge($group['tokens_at_time_1'], $group['tokens_at_time_2']);

            foreach ($allTokens as $tokenData) {
                $row = [
                    '2 Minutos de Diferencia',
                    $group['timestamp_1'] . ' / ' . $group['timestamp_2'],
                    $tokenData['token'],
                    $tokenData['used_at'],
                    $tokenData['fingerprint'],
                ];

                for ($i = 0; $i < 2; $i++) {
                    if (isset($tokenData['votes'][$i])) {
                        $row[] = $tokenData['votes'][$i]['question'];
                        $row[] = $tokenData['votes'][$i]['answer'];
                    } else {
                        $row[] = 'N/A';
                        $row[] = 'N/A';
                    }
                }

                $row[] = $tokenData['votes'][0]['status'] ?? 'N/A';
                $row[] = $tokenData['votes'][0]['fraud_score'] ?? 0;
                $row[] = ($tokenData['votes'][0]['is_valid'] ?? false) ? 'Sí' : 'No';

                fputcsv($file, $row);
            }
        }

        fclose($file);

        return [
            'filepath' => $filepath,
            'filename' => $filename,
            'url' => asset('storage/reports/' . $filename)
        ];
    }

    /**
     * Detecta tokens sospechosos en un grupo consolidado de encuestas
     */
    public function detectSuspiciousTokensInGroup(SurveyGroup $group)
    {
        // Obtener todos los tokens usados de TODAS las encuestas del grupo
        $surveyIds = $group->surveys()->pluck('id');

        $usedTokens = SurveyToken::whereIn('survey_id', $surveyIds)
            ->where('status', 'used')
            ->whereNotNull('used_at')
            ->with(['votes' => function($query) {
                $query->with(['question', 'option', 'survey']);
            }, 'survey'])
            ->orderBy('used_at')
            ->get();

        $results = [
            'same_minute' => [],
            'one_minute_diff' => [],
            'two_minute_diff' => [],
            'summary' => [
                'total_tokens' => $usedTokens->count(),
                'same_minute_count' => 0,
                'one_minute_count' => 0,
                'two_minute_count' => 0,
                'total_suspicious' => 0,
            ]
        ];

        $tokensByMinute = [];

        foreach ($usedTokens as $token) {
            $minuteKey = $token->used_at->format('Y-m-d H:i');

            if (!isset($tokensByMinute[$minuteKey])) {
                $tokensByMinute[$minuteKey] = [];
            }

            $tokensByMinute[$minuteKey][] = $token;
        }

        // Detectar tokens en el mismo minuto
        foreach ($tokensByMinute as $minute => $tokens) {
            if (count($tokens) > 1) {
                // Agrupar por opción elegida (mismo candidato/respuesta)
                $tokensByOption = $this->groupTokensByAnswer($tokens);

                foreach ($tokensByOption as $optionKey => $tokensWithSameAnswer) {
                    if (count($tokensWithSameAnswer) > 1) {
                        // Solo reportar si 2+ tokens votaron por la MISMA opción
                        $group_data = [
                            'timestamp' => $minute,
                            'count' => count($tokensWithSameAnswer),
                            'option_voted' => $optionKey,
                            'tokens' => $this->formatTokensForReportWithSurvey($tokensWithSameAnswer),
                        ];

                        $results['same_minute'][] = $group_data;
                        $results['summary']['same_minute_count'] += count($tokensWithSameAnswer);
                    }
                }
            }
        }

        // Detectar tokens con diferencia de 1 y 2 minutos
        $sortedMinutes = array_keys($tokensByMinute);
        sort($sortedMinutes);

        for ($i = 0; $i < count($sortedMinutes); $i++) {
            $currentMinute = $sortedMinutes[$i];
            $currentTokens = $tokensByMinute[$currentMinute];

            if (isset($sortedMinutes[$i + 1])) {
                $nextMinute = $sortedMinutes[$i + 1];
                $currentTime = Carbon::parse($currentMinute);
                $nextTime = Carbon::parse($nextMinute);

                $diffMinutes = $currentTime->diffInMinutes($nextTime);

                if ($diffMinutes === 1) {
                    $nextTokens = $tokensByMinute[$nextMinute];

                    // Combinar ambos grupos y agrupar por respuesta
                    $allTokens = array_merge($currentTokens, $nextTokens);
                    $tokensByOption = $this->groupTokensByAnswer($allTokens);

                    foreach ($tokensByOption as $optionKey => $tokensWithSameAnswer) {
                        if (count($tokensWithSameAnswer) > 1) {
                            // Separar en los dos timestamps
                            $tokensAtTime1 = [];
                            $tokensAtTime2 = [];

                            foreach ($tokensWithSameAnswer as $token) {
                                $tokenMinute = $token->used_at->format('Y-m-d H:i');
                                if ($tokenMinute === $currentMinute) {
                                    $tokensAtTime1[] = $token;
                                } else {
                                    $tokensAtTime2[] = $token;
                                }
                            }

                            $group_data = [
                                'timestamp_1' => $currentMinute,
                                'timestamp_2' => $nextMinute,
                                'option_voted' => $optionKey,
                                'tokens_at_time_1' => $this->formatTokensForReportWithSurvey($tokensAtTime1),
                                'tokens_at_time_2' => $this->formatTokensForReportWithSurvey($tokensAtTime2),
                                'total_count' => count($tokensWithSameAnswer),
                            ];

                            $results['one_minute_diff'][] = $group_data;
                            $results['summary']['one_minute_count'] += count($tokensWithSameAnswer);
                        }
                    }
                }

                if ($diffMinutes === 2) {
                    $nextTokens = $tokensByMinute[$nextMinute];

                    $allTokens = array_merge($currentTokens, $nextTokens);
                    $tokensByOption = $this->groupTokensByAnswer($allTokens);

                    foreach ($tokensByOption as $optionKey => $tokensWithSameAnswer) {
                        if (count($tokensWithSameAnswer) > 1) {
                            $tokensAtTime1 = [];
                            $tokensAtTime2 = [];

                            foreach ($tokensWithSameAnswer as $token) {
                                $tokenMinute = $token->used_at->format('Y-m-d H:i');
                                if ($tokenMinute === $currentMinute) {
                                    $tokensAtTime1[] = $token;
                                } else {
                                    $tokensAtTime2[] = $token;
                                }
                            }

                            $group_data = [
                                'timestamp_1' => $currentMinute,
                                'timestamp_2' => $nextMinute,
                                'option_voted' => $optionKey,
                                'tokens_at_time_1' => $this->formatTokensForReportWithSurvey($tokensAtTime1),
                                'tokens_at_time_2' => $this->formatTokensForReportWithSurvey($tokensAtTime2),
                                'total_count' => count($tokensWithSameAnswer),
                            ];

                            $results['two_minute_diff'][] = $group_data;
                            $results['summary']['two_minute_count'] += count($tokensWithSameAnswer);
                        }
                    }
                }
            }
        }

        // Calcular total de tokens únicos sospechosos
        $allSuspiciousTokens = collect();

        foreach ($results['same_minute'] as $group_data) {
            foreach ($group_data['tokens'] as $tokenData) {
                $allSuspiciousTokens->push($tokenData['token']);
            }
        }

        foreach ($results['one_minute_diff'] as $group_data) {
            foreach ($group_data['tokens_at_time_1'] as $tokenData) {
                $allSuspiciousTokens->push($tokenData['token']);
            }
            foreach ($group_data['tokens_at_time_2'] as $tokenData) {
                $allSuspiciousTokens->push($tokenData['token']);
            }
        }

        foreach ($results['two_minute_diff'] as $group_data) {
            foreach ($group_data['tokens_at_time_1'] as $tokenData) {
                $allSuspiciousTokens->push($tokenData['token']);
            }
            foreach ($group_data['tokens_at_time_2'] as $tokenData) {
                $allSuspiciousTokens->push($tokenData['token']);
            }
        }

        $results['summary']['total_suspicious'] = $allSuspiciousTokens->unique()->count();

        return $results;
    }

    /**
     * Formatea tokens para el reporte incluyendo información de la encuesta
     */
    private function formatTokensForReportWithSurvey($tokens)
    {
        $formatted = [];

        foreach ($tokens as $token) {
            $voteDetails = [];

            foreach ($token->votes as $vote) {
                $voteDetails[] = [
                    'question' => $vote->question->question_text ?? 'N/A',
                    'answer' => $vote->option->option_text ?? 'N/A',
                    'is_valid' => $vote->is_valid,
                    'status' => $vote->status,
                    'fraud_score' => $vote->fraud_score,
                ];
            }

            $formatted[] = [
                'token' => $token->token,
                'used_at' => $token->used_at->format('Y-m-d H:i:s'),
                'fingerprint' => $token->used_by_fingerprint ?? 'N/A',
                'user_agent' => $token->user_agent ?? 'N/A',
                'survey_id' => $token->survey_id,
                'survey_title' => $token->survey->title ?? 'N/A',
                'votes' => $voteDetails,
            ];
        }

        return $formatted;
    }

    /**
     * Exporta tokens sospechosos de un grupo a CSV
     */
    public function exportSuspiciousTokensInGroupToCSV(SurveyGroup $group)
    {
        $suspiciousData = $this->detectSuspiciousTokensInGroup($group);

        $filename = 'tokens_sospechosos_grupo_' . $group->slug . '_' . now()->format('Y-m-d_His') . '.csv';
        $filepath = storage_path('app/public/reports/' . $filename);

        if (!file_exists(storage_path('app/public/reports'))) {
            mkdir(storage_path('app/public/reports'), 0755, true);
        }

        $file = fopen($filepath, 'w');
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

        // Encabezados
        fputcsv($file, [
            'Tipo de Sospecha',
            'Timestamp',
            'Token',
            'Hora de Uso Exacta',
            'ID Encuesta',
            'Título Encuesta',
            'Fingerprint',
            'Pregunta 1',
            'Respuesta 1',
            'Pregunta 2',
            'Respuesta 2',
            'Estado',
            'Fraud Score',
            'Es Válido'
        ]);

        // Tokens en mismo minuto
        foreach ($suspiciousData['same_minute'] as $group_data) {
            foreach ($group_data['tokens'] as $tokenData) {
                $row = [
                    'Mismo Minuto',
                    $group_data['timestamp'],
                    $tokenData['token'],
                    $tokenData['used_at'],
                    $tokenData['survey_id'],
                    $tokenData['survey_title'],
                    $tokenData['fingerprint'],
                ];

                for ($i = 0; $i < 2; $i++) {
                    if (isset($tokenData['votes'][$i])) {
                        $row[] = $tokenData['votes'][$i]['question'];
                        $row[] = $tokenData['votes'][$i]['answer'];
                    } else {
                        $row[] = 'N/A';
                        $row[] = 'N/A';
                    }
                }

                $row[] = $tokenData['votes'][0]['status'] ?? 'N/A';
                $row[] = $tokenData['votes'][0]['fraud_score'] ?? 0;
                $row[] = ($tokenData['votes'][0]['is_valid'] ?? false) ? 'Sí' : 'No';

                fputcsv($file, $row);
            }
        }

        // Tokens con 1 minuto de diferencia
        foreach ($suspiciousData['one_minute_diff'] as $group_data) {
            $allTokens = array_merge($group_data['tokens_at_time_1'], $group_data['tokens_at_time_2']);

            foreach ($allTokens as $tokenData) {
                $row = [
                    '1 Minuto de Diferencia',
                    $group_data['timestamp_1'] . ' / ' . $group_data['timestamp_2'],
                    $tokenData['token'],
                    $tokenData['used_at'],
                    $tokenData['survey_id'],
                    $tokenData['survey_title'],
                    $tokenData['fingerprint'],
                ];

                for ($i = 0; $i < 2; $i++) {
                    if (isset($tokenData['votes'][$i])) {
                        $row[] = $tokenData['votes'][$i]['question'];
                        $row[] = $tokenData['votes'][$i]['answer'];
                    } else {
                        $row[] = 'N/A';
                        $row[] = 'N/A';
                    }
                }

                $row[] = $tokenData['votes'][0]['status'] ?? 'N/A';
                $row[] = $tokenData['votes'][0]['fraud_score'] ?? 0;
                $row[] = ($tokenData['votes'][0]['is_valid'] ?? false) ? 'Sí' : 'No';

                fputcsv($file, $row);
            }
        }

        // Tokens con 2 minutos de diferencia
        foreach ($suspiciousData['two_minute_diff'] as $group_data) {
            $allTokens = array_merge($group_data['tokens_at_time_1'], $group_data['tokens_at_time_2']);

            foreach ($allTokens as $tokenData) {
                $row = [
                    '2 Minutos de Diferencia',
                    $group_data['timestamp_1'] . ' / ' . $group_data['timestamp_2'],
                    $tokenData['token'],
                    $tokenData['used_at'],
                    $tokenData['survey_id'],
                    $tokenData['survey_title'],
                    $tokenData['fingerprint'],
                ];

                for ($i = 0; $i < 2; $i++) {
                    if (isset($tokenData['votes'][$i])) {
                        $row[] = $tokenData['votes'][$i]['question'];
                        $row[] = $tokenData['votes'][$i]['answer'];
                    } else {
                        $row[] = 'N/A';
                        $row[] = 'N/A';
                    }
                }

                $row[] = $tokenData['votes'][0]['status'] ?? 'N/A';
                $row[] = $tokenData['votes'][0]['fraud_score'] ?? 0;
                $row[] = ($tokenData['votes'][0]['is_valid'] ?? false) ? 'Sí' : 'No';

                fputcsv($file, $row);
            }
        }

        fclose($file);

        return [
            'filepath' => $filepath,
            'filename' => $filename,
            'url' => asset('storage/reports/' . $filename)
        ];
    }

    /**
     * Obtiene lista de tokens sospechosos (para filtrar)
     * NO altera la base de datos, solo retorna array de tokens
     */
    public function getSuspiciousTokensList(Survey $survey)
    {
        $suspiciousData = $this->detectSuspiciousTokens($survey);
        $suspiciousTokens = [];

        // Recolectar todos los tokens sospechosos
        foreach ($suspiciousData['same_minute'] as $group) {
            foreach ($group['tokens'] as $tokenData) {
                $suspiciousTokens[] = $tokenData['token'];
            }
        }

        foreach ($suspiciousData['one_minute_diff'] as $group) {
            foreach ($group['tokens_at_time_1'] as $tokenData) {
                $suspiciousTokens[] = $tokenData['token'];
            }
            foreach ($group['tokens_at_time_2'] as $tokenData) {
                $suspiciousTokens[] = $tokenData['token'];
            }
        }

        foreach ($suspiciousData['two_minute_diff'] as $group) {
            foreach ($group['tokens_at_time_1'] as $tokenData) {
                $suspiciousTokens[] = $tokenData['token'];
            }
            foreach ($group['tokens_at_time_2'] as $tokenData) {
                $suspiciousTokens[] = $tokenData['token'];
            }
        }

        return array_unique($suspiciousTokens);
    }

    /**
     * Obtiene lista de tokens sospechosos de un grupo (para filtrar)
     * NO altera la base de datos, solo retorna array de tokens
     */
    public function getSuspiciousTokensListInGroup(SurveyGroup $group)
    {
        $suspiciousData = $this->detectSuspiciousTokensInGroup($group);
        $suspiciousTokens = [];

        // Recolectar todos los tokens sospechosos
        foreach ($suspiciousData['same_minute'] as $group_data) {
            foreach ($group_data['tokens'] as $tokenData) {
                $suspiciousTokens[] = $tokenData['token'];
            }
        }

        foreach ($suspiciousData['one_minute_diff'] as $group_data) {
            foreach ($group_data['tokens_at_time_1'] as $tokenData) {
                $suspiciousTokens[] = $tokenData['token'];
            }
            foreach ($group_data['tokens_at_time_2'] as $tokenData) {
                $suspiciousTokens[] = $tokenData['token'];
            }
        }

        foreach ($suspiciousData['two_minute_diff'] as $group_data) {
            foreach ($group_data['tokens_at_time_1'] as $tokenData) {
                $suspiciousTokens[] = $tokenData['token'];
            }
            foreach ($group_data['tokens_at_time_2'] as $tokenData) {
                $suspiciousTokens[] = $tokenData['token'];
            }
        }

        return array_unique($suspiciousTokens);
    }
}
