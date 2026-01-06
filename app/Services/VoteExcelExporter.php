<?php

namespace App\Services;

use App\Models\Survey;
use App\Models\SurveyGroup;
use App\Models\Vote;
use Illuminate\Support\Facades\DB;

class VoteExcelExporter
{
    protected $suspiciousTokenDetector;

    public function __construct(SuspiciousTokenDetector $suspiciousTokenDetector)
    {
        $this->suspiciousTokenDetector = $suspiciousTokenDetector;
    }
    /**
     * Genera un archivo CSV con los datos de votación de una encuesta
     * Incluye: Token, Pregunta 1, Pregunta 2, Hora de voto, IP, Fingerprint, Estado
     */
    public function exportSurveyVotes(Survey $survey)
    {
        // Obtener todas las preguntas de la encuesta ordenadas por posición
        $questions = $survey->questions()->orderBy('order')->get();

        // Obtener todos los votos agrupados por token
        $votesByToken = Vote::where('survey_id', $survey->id)
            ->with(['token', 'question', 'option'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('survey_token_id');

        // Preparar el nombre del archivo
        $filename = 'votos_' . $survey->public_slug . '_' . now()->format('Y-m-d_His') . '.csv';
        $filepath = storage_path('app/public/reports/' . $filename);

        // Crear directorio si no existe
        if (!file_exists(storage_path('app/public/reports'))) {
            mkdir(storage_path('app/public/reports'), 0755, true);
        }

        // Abrir archivo CSV
        $file = fopen($filepath, 'w');

        // Escribir BOM para UTF-8 (para que Excel lo reconozca correctamente)
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

        // Preparar encabezados dinámicos según las preguntas
        $headers = ['Token', 'Token Status', 'Hora de Uso del Token'];

        foreach ($questions as $index => $question) {
            $questionNumber = $index + 1;
            $headers[] = "Pregunta {$questionNumber}";
            $headers[] = "Respuesta Pregunta {$questionNumber}";
        }

        $headers = array_merge($headers, [
            'IP Address',
            'Fingerprint',
            'User Agent',
            'Platform',
            'Screen Resolution',
            'Estado del Voto',
            'Fraud Score',
            'Es Válido',
            'Razón de Invalidez',
            'Fecha/Hora de Voto'
        ]);

        // Escribir encabezados
        fputcsv($file, $headers);

        // Escribir filas de datos
        foreach ($votesByToken as $tokenId => $votes) {
            $firstVote = $votes->first();
            $token = $firstVote->token;

            $row = [
                $token ? $token->token : 'Sin Token',
                $token ? ucfirst($token->status) : 'N/A',
                $token && $token->used_at ? $token->used_at->format('Y-m-d H:i:s') : 'No usado',
            ];

            // Agregar respuestas para cada pregunta
            foreach ($questions as $question) {
                // Buscar el voto correspondiente a esta pregunta
                $vote = $votes->firstWhere('question_id', $question->id);

                $row[] = $question->question_text;
                $row[] = $vote && $vote->option ? $vote->option->option_text : 'Sin respuesta';
            }

            // Agregar datos adicionales del primer voto (todos comparten token)
            $row = array_merge($row, [
                $firstVote->ip_address ?? 'N/A',
                $firstVote->fingerprint ?? 'N/A',
                $firstVote->user_agent ?? 'N/A',
                $firstVote->platform ?? 'N/A',
                $firstVote->screen_resolution ?? 'N/A',
                ucfirst($firstVote->status),
                $firstVote->fraud_score ?? 0,
                $firstVote->is_valid ? 'Sí' : 'No',
                $firstVote->invalid_reason ?? 'N/A',
                $firstVote->created_at->format('Y-m-d H:i:s'),
            ]);

            fputcsv($file, $row);
        }

        fclose($file);

        return [
            'filepath' => $filepath,
            'filename' => $filename,
            'url' => asset('storage/reports/' . $filename)
        ];
    }

    /**
     * Genera CSV solo con votos válidos y aprobados
     */
    public function exportValidVotes(Survey $survey)
    {
        // Similar al método anterior pero filtrando solo votos válidos
        $questions = $survey->questions()->orderBy('order')->get();

        $votesByToken = Vote::where('survey_id', $survey->id)
            ->valid() // Scope que filtra votos válidos
            ->approved() // Scope que filtra votos aprobados
            ->with(['token', 'question', 'option'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('survey_token_id');

        $filename = 'votos_validos_' . $survey->public_slug . '_' . now()->format('Y-m-d_His') . '.csv';
        $filepath = storage_path('app/public/reports/' . $filename);

        if (!file_exists(storage_path('app/public/reports'))) {
            mkdir(storage_path('app/public/reports'), 0755, true);
        }

        $file = fopen($filepath, 'w');
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

        $headers = ['Token', 'Hora de Uso'];
        foreach ($questions as $index => $question) {
            $questionNumber = $index + 1;
            $headers[] = "Pregunta {$questionNumber}";
            $headers[] = "Respuesta Pregunta {$questionNumber}";
        }
        $headers[] = 'Fecha/Hora de Voto';

        fputcsv($file, $headers);

        foreach ($votesByToken as $tokenId => $votes) {
            $firstVote = $votes->first();
            $token = $firstVote->token;

            $row = [
                $token ? $token->token : 'Sin Token',
                $token && $token->used_at ? $token->used_at->format('Y-m-d H:i:s') : 'No usado',
            ];

            foreach ($questions as $question) {
                $vote = $votes->firstWhere('question_id', $question->id);
                $row[] = $question->question_text;
                $row[] = $vote && $vote->option ? $vote->option->option_text : 'Sin respuesta';
            }

            $row[] = $firstVote->created_at->format('Y-m-d H:i:s');

            fputcsv($file, $row);
        }

        fclose($file);

        return [
            'filepath' => $filepath,
            'filename' => $filename,
            'url' => asset('storage/reports/' . $filename)
        ];
    }

    /**
     * Exportar todos los votos de un grupo consolidado de encuestas
     * Incluye identificador de encuesta para diferenciar
     */
    public function exportGroupVotes(SurveyGroup $group)
    {
        // Obtener todas las encuestas del grupo
        $surveys = $group->surveys()->with(['questions' => function($query) {
            $query->orderBy('order');
        }])->get();

        $filename = 'votos_grupo_' . $group->slug . '_' . now()->format('Y-m-d_His') . '.csv';
        $filepath = storage_path('app/public/reports/' . $filename);

        if (!file_exists(storage_path('app/public/reports'))) {
            mkdir(storage_path('app/public/reports'), 0755, true);
        }

        $file = fopen($filepath, 'w');
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

        // Determinar el número máximo de preguntas entre todas las encuestas
        $maxQuestions = 0;
        foreach ($surveys as $survey) {
            $questionCount = $survey->questions->count();
            if ($questionCount > $maxQuestions) {
                $maxQuestions = $questionCount;
            }
        }

        // Preparar encabezados dinámicos
        $headers = ['ID Encuesta', 'Título Encuesta', 'Token', 'Token Status', 'Hora de Uso del Token'];

        for ($i = 1; $i <= $maxQuestions; $i++) {
            $headers[] = "Pregunta {$i}";
            $headers[] = "Respuesta Pregunta {$i}";
        }

        $headers = array_merge($headers, [
            'IP Address',
            'Fingerprint',
            'User Agent',
            'Platform',
            'Screen Resolution',
            'Estado del Voto',
            'Fraud Score',
            'Es Válido',
            'Razón de Invalidez',
            'Fecha/Hora de Voto'
        ]);

        fputcsv($file, $headers);

        // Iterar sobre cada encuesta del grupo
        foreach ($surveys as $survey) {
            $questions = $survey->questions;

            // Obtener todos los votos de esta encuesta agrupados por token
            $votesByToken = Vote::where('survey_id', $survey->id)
                ->with(['token', 'question', 'option'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy('survey_token_id');

            // Escribir filas de datos
            foreach ($votesByToken as $tokenId => $votes) {
                $firstVote = $votes->first();
                $token = $firstVote->token;

                $row = [
                    $survey->id,
                    $survey->title,
                    $token ? $token->token : 'Sin Token',
                    $token ? ucfirst($token->status) : 'N/A',
                    $token && $token->used_at ? $token->used_at->format('Y-m-d H:i:s') : 'No usado',
                ];

                // Agregar respuestas para cada pregunta (hasta el máximo)
                for ($i = 0; $i < $maxQuestions; $i++) {
                    if (isset($questions[$i])) {
                        $question = $questions[$i];
                        $vote = $votes->firstWhere('question_id', $question->id);
                        $row[] = $question->question_text;
                        $row[] = $vote && $vote->option ? $vote->option->option_text : 'Sin respuesta';
                    } else {
                        // Si esta encuesta tiene menos preguntas, rellenar con N/A
                        $row[] = 'N/A';
                        $row[] = 'N/A';
                    }
                }

                // Agregar datos adicionales del primer voto
                $row = array_merge($row, [
                    $firstVote->ip_address ?? 'N/A',
                    $firstVote->fingerprint ?? 'N/A',
                    $firstVote->user_agent ?? 'N/A',
                    $firstVote->platform ?? 'N/A',
                    $firstVote->screen_resolution ?? 'N/A',
                    ucfirst($firstVote->status),
                    $firstVote->fraud_score ?? 0,
                    $firstVote->is_valid ? 'Sí' : 'No',
                    $firstVote->invalid_reason ?? 'N/A',
                    $firstVote->created_at->format('Y-m-d H:i:s'),
                ]);

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
     * Exportar solo votos válidos de un grupo consolidado
     */
    public function exportGroupValidVotes(SurveyGroup $group)
    {
        $surveys = $group->surveys()->with(['questions' => function($query) {
            $query->orderBy('order');
        }])->get();

        $filename = 'votos_validos_grupo_' . $group->slug . '_' . now()->format('Y-m-d_His') . '.csv';
        $filepath = storage_path('app/public/reports/' . $filename);

        if (!file_exists(storage_path('app/public/reports'))) {
            mkdir(storage_path('app/public/reports'), 0755, true);
        }

        $file = fopen($filepath, 'w');
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

        // Determinar el número máximo de preguntas
        $maxQuestions = 0;
        foreach ($surveys as $survey) {
            $questionCount = $survey->questions->count();
            if ($questionCount > $maxQuestions) {
                $maxQuestions = $questionCount;
            }
        }

        // Preparar encabezados
        $headers = ['ID Encuesta', 'Título Encuesta', 'Token', 'Hora de Uso'];

        for ($i = 1; $i <= $maxQuestions; $i++) {
            $headers[] = "Pregunta {$i}";
            $headers[] = "Respuesta Pregunta {$i}";
        }

        $headers[] = 'Fecha/Hora de Voto';

        fputcsv($file, $headers);

        // Iterar sobre cada encuesta del grupo
        foreach ($surveys as $survey) {
            $questions = $survey->questions;

            $votesByToken = Vote::where('survey_id', $survey->id)
                ->valid()
                ->approved()
                ->with(['token', 'question', 'option'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy('survey_token_id');

            foreach ($votesByToken as $tokenId => $votes) {
                $firstVote = $votes->first();
                $token = $firstVote->token;

                $row = [
                    $survey->id,
                    $survey->title,
                    $token ? $token->token : 'Sin Token',
                    $token && $token->used_at ? $token->used_at->format('Y-m-d H:i:s') : 'No usado',
                ];

                for ($i = 0; $i < $maxQuestions; $i++) {
                    if (isset($questions[$i])) {
                        $question = $questions[$i];
                        $vote = $votes->firstWhere('question_id', $question->id);
                        $row[] = $question->question_text;
                        $row[] = $vote && $vote->option ? $vote->option->option_text : 'Sin respuesta';
                    } else {
                        $row[] = 'N/A';
                        $row[] = 'N/A';
                    }
                }

                $row[] = $firstVote->created_at->format('Y-m-d H:i:s');

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
     * Exporta estadísticas de votos con datos para gráficos
     * Incluye: Opción, Foto URL, Votos, Porcentaje
     */
    public function exportVoteStatistics(Survey $survey)
    {
        $filename = 'estadisticas_' . $survey->public_slug . '_' . now()->format('Y-m-d_His') . '.csv';
        $filepath = storage_path('app/public/reports/' . $filename);

        if (!file_exists(storage_path('app/public/reports'))) {
            mkdir(storage_path('app/public/reports'), 0755, true);
        }

        $file = fopen($filepath, 'w');
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

        // Obtener preguntas con opciones y conteos de votos
        $questions = $survey->questions()->with(['options' => function($query) {
            $query->withCount(['votes' => function($query) {
                $query->valid()->approved(); // Solo votos válidos y aprobados
            }]);
        }])->orderBy('order')->get();

        // Calcular total de votos válidos por pregunta
        foreach ($questions as $question) {
            fputcsv($file, []); // Línea vacía
            fputcsv($file, ['PREGUNTA: ' . $question->question_text]);
            fputcsv($file, ['Opción', 'URL Imagen/Foto', 'Votos', 'Porcentaje', 'Barra Gráfico (para copiar)']);

            $totalVotes = $question->options->sum('votes_count');

            foreach ($question->options as $option) {
                $votes = $option->votes_count;
                $percentage = $totalVotes > 0 ? round(($votes / $totalVotes) * 100, 2) : 0;

                // Generar URL de imagen si existe
                $imageUrl = $option->image_url ?? $option->photo_url ?? asset('images/no-photo.png');

                // Generar barra visual (para Excel - usar caracteres de bloque)
                $barLength = round($percentage / 2); // 50% = 25 caracteres
                $bar = str_repeat('█', $barLength);

                $row = [
                    $option->option_text,
                    $imageUrl,
                    $votes,
                    $percentage . '%',
                    $bar,
                ];

                fputcsv($file, $row);
            }

            // Totales
            fputcsv($file, ['TOTAL', '', $totalVotes, '100%', '']);
        }

        fclose($file);

        return [
            'filepath' => $filepath,
            'filename' => $filename,
            'url' => asset('storage/reports/' . $filename)
        ];
    }

    /**
     * Exportar votos excluyendo tokens sospechosos
     * NO modifica la base de datos - solo filtra en la consulta
     */
    public function exportVotesExcludingSuspicious(Survey $survey)
    {
        // Obtener lista de tokens sospechosos (NO modifica BD)
        $suspiciousTokens = $this->suspiciousTokenDetector->getSuspiciousTokensList($survey);

        // Obtener todas las preguntas de la encuesta ordenadas por posición
        $questions = $survey->questions()->orderBy('order')->get();

        // Obtener votos EXCLUYENDO los tokens sospechosos
        $votesByToken = Vote::where('survey_id', $survey->id)
            ->with(['token', 'question', 'option'])
            ->whereHas('token', function($query) use ($suspiciousTokens) {
                $query->whereNotIn('token', $suspiciousTokens);
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('survey_token_id');

        // Preparar el nombre del archivo
        $filename = 'votos_limpios_' . $survey->public_slug . '_' . now()->format('Y-m-d_His') . '.csv';
        $filepath = storage_path('app/public/reports/' . $filename);

        // Crear directorio si no existe
        if (!file_exists(storage_path('app/public/reports'))) {
            mkdir(storage_path('app/public/reports'), 0755, true);
        }

        // Abrir archivo CSV
        $file = fopen($filepath, 'w');

        // Escribir BOM para UTF-8
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

        // Preparar encabezados dinámicos según las preguntas
        $headers = ['Token', 'Token Status', 'Hora de Uso del Token'];

        foreach ($questions as $index => $question) {
            $questionNumber = $index + 1;
            $headers[] = "Pregunta {$questionNumber}";
            $headers[] = "Respuesta Pregunta {$questionNumber}";
        }

        $headers = array_merge($headers, [
            'IP Address',
            'Fingerprint',
            'User Agent',
            'Platform',
            'Screen Resolution',
            'Estado del Voto',
            'Fraud Score',
            'Es Válido',
            'Razón de Invalidez',
            'Fecha/Hora de Voto'
        ]);

        // Escribir encabezados
        fputcsv($file, $headers);

        // Escribir información sobre filtrado
        fputcsv($file, ['NOTA: Este archivo excluye ' . count($suspiciousTokens) . ' tokens sospechosos identificados.']);
        fputcsv($file, ['Los datos originales en la base de datos NO han sido modificados.']);
        fputcsv($file, []);

        // Escribir filas de datos
        foreach ($votesByToken as $tokenId => $votes) {
            $firstVote = $votes->first();
            $token = $firstVote->token;

            $row = [
                $token ? $token->token : 'Sin Token',
                $token ? ucfirst($token->status) : 'N/A',
                $token && $token->used_at ? $token->used_at->format('Y-m-d H:i:s') : 'No usado',
            ];

            // Agregar respuestas para cada pregunta
            foreach ($questions as $question) {
                $vote = $votes->firstWhere('question_id', $question->id);
                $row[] = $question->question_text;
                $row[] = $vote && $vote->option ? $vote->option->option_text : 'Sin respuesta';
            }

            // Agregar datos adicionales del primer voto
            $row = array_merge($row, [
                $firstVote->ip_address ?? 'N/A',
                $firstVote->fingerprint ?? 'N/A',
                $firstVote->user_agent ?? 'N/A',
                $firstVote->platform ?? 'N/A',
                $firstVote->screen_resolution ?? 'N/A',
                ucfirst($firstVote->status),
                $firstVote->fraud_score ?? 0,
                $firstVote->is_valid ? 'Sí' : 'No',
                $firstVote->invalid_reason ?? 'N/A',
                $firstVote->created_at->format('Y-m-d H:i:s'),
            ]);

            fputcsv($file, $row);
        }

        fclose($file);

        return [
            'filepath' => $filepath,
            'filename' => $filename,
            'url' => asset('storage/reports/' . $filename),
            'excluded_count' => count($suspiciousTokens)
        ];
    }

    /**
     * Exportar votos de grupo excluyendo tokens sospechosos
     * NO modifica la base de datos - solo filtra en la consulta
     */
    public function exportGroupVotesExcludingSuspicious(SurveyGroup $group)
    {
        // Obtener lista de tokens sospechosos (NO modifica BD)
        $suspiciousTokens = $this->suspiciousTokenDetector->getSuspiciousTokensListInGroup($group);

        // Obtener todas las encuestas del grupo
        $surveys = $group->surveys()->with(['questions' => function($query) {
            $query->orderBy('order');
        }])->get();

        $filename = 'votos_limpios_grupo_' . $group->slug . '_' . now()->format('Y-m-d_His') . '.csv';
        $filepath = storage_path('app/public/reports/' . $filename);

        if (!file_exists(storage_path('app/public/reports'))) {
            mkdir(storage_path('app/public/reports'), 0755, true);
        }

        $file = fopen($filepath, 'w');
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

        // Determinar el número máximo de preguntas entre todas las encuestas
        $maxQuestions = 0;
        foreach ($surveys as $survey) {
            $questionCount = $survey->questions->count();
            if ($questionCount > $maxQuestions) {
                $maxQuestions = $questionCount;
            }
        }

        // Preparar encabezados dinámicos
        $headers = ['ID Encuesta', 'Título Encuesta', 'Token', 'Token Status', 'Hora de Uso del Token'];

        for ($i = 1; $i <= $maxQuestions; $i++) {
            $headers[] = "Pregunta {$i}";
            $headers[] = "Respuesta Pregunta {$i}";
        }

        $headers = array_merge($headers, [
            'IP Address',
            'Fingerprint',
            'User Agent',
            'Platform',
            'Screen Resolution',
            'Estado del Voto',
            'Fraud Score',
            'Es Válido',
            'Razón de Invalidez',
            'Fecha/Hora de Voto'
        ]);

        fputcsv($file, $headers);

        // Escribir información sobre filtrado
        fputcsv($file, ['NOTA: Este archivo excluye ' . count($suspiciousTokens) . ' tokens sospechosos identificados.']);
        fputcsv($file, ['Los datos originales en la base de datos NO han sido modificados.']);
        fputcsv($file, []);

        // Iterar sobre cada encuesta del grupo
        foreach ($surveys as $survey) {
            $questions = $survey->questions;

            // Obtener votos EXCLUYENDO tokens sospechosos
            $votesByToken = Vote::where('survey_id', $survey->id)
                ->with(['token', 'question', 'option'])
                ->whereHas('token', function($query) use ($suspiciousTokens) {
                    $query->whereNotIn('token', $suspiciousTokens);
                })
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy('survey_token_id');

            // Escribir filas de datos
            foreach ($votesByToken as $tokenId => $votes) {
                $firstVote = $votes->first();
                $token = $firstVote->token;

                $row = [
                    $survey->id,
                    $survey->title,
                    $token ? $token->token : 'Sin Token',
                    $token ? ucfirst($token->status) : 'N/A',
                    $token && $token->used_at ? $token->used_at->format('Y-m-d H:i:s') : 'No usado',
                ];

                // Agregar respuestas para cada pregunta (hasta el máximo)
                for ($i = 0; $i < $maxQuestions; $i++) {
                    if (isset($questions[$i])) {
                        $question = $questions[$i];
                        $vote = $votes->firstWhere('question_id', $question->id);
                        $row[] = $question->question_text;
                        $row[] = $vote && $vote->option ? $vote->option->option_text : 'Sin respuesta';
                    } else {
                        $row[] = 'N/A';
                        $row[] = 'N/A';
                    }
                }

                // Agregar datos adicionales del primer voto
                $row = array_merge($row, [
                    $firstVote->ip_address ?? 'N/A',
                    $firstVote->fingerprint ?? 'N/A',
                    $firstVote->user_agent ?? 'N/A',
                    $firstVote->platform ?? 'N/A',
                    $firstVote->screen_resolution ?? 'N/A',
                    ucfirst($firstVote->status),
                    $firstVote->fraud_score ?? 0,
                    $firstVote->is_valid ? 'Sí' : 'No',
                    $firstVote->invalid_reason ?? 'N/A',
                    $firstVote->created_at->format('Y-m-d H:i:s'),
                ]);

                fputcsv($file, $row);
            }
        }

        fclose($file);

        return [
            'filepath' => $filepath,
            'filename' => $filename,
            'url' => asset('storage/reports/' . $filename),
            'excluded_count' => count($suspiciousTokens)
        ];
    }
}
