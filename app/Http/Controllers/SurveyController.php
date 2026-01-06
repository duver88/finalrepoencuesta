<?php

namespace App\Http\Controllers;

use App\Models\Survey;
use App\Models\SurveyToken;
use App\Models\Vote;
use App\Services\DeviceFingerprintMatcher;
use App\Services\VoteFraudDetector;
use App\Services\VoteRateLimiter;
use App\Services\OneVotePerMinuteValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SurveyController extends Controller
{
    public function show(Request $request, $publicSlug)
    {
        // Buscar la encuesta por public_slug (ofuscado)
        $survey = Survey::where('public_slug', $publicSlug)
            ->with(['questions.options', 'group'])
            ->firstOrFail();

        // Si la encuesta pertenece a un grupo, redirigir a la URL con el grupo
        if ($survey->survey_group_id && $survey->group && $survey->group->slug) {
            $queryParams = $request->query(); // Mantener todos los parámetros de la URL (token, etc.)
            return redirect()->route('surveys.show.group', [
                'groupSlug' => $survey->group->slug,
                'publicSlug' => $publicSlug
            ] + $queryParams);
        }

        // Incrementar contador de visitas usando sesión para evitar múltiples conteos
        $sessionKey = 'survey_viewed_' . $survey->id;
        if (!session()->has($sessionKey)) {
            $survey->incrementViews();
            session()->put($sessionKey, true);
        }

        // Si la encuesta está terminada, redirigir a resultados finales
        if ($survey->is_finished) {
            return redirect()->route('surveys.finished', $survey->public_slug);
        }

        // Si la encuesta no está activa, mostrar mensaje
        if (!$survey->is_active) {
            return view('surveys.inactive', compact('survey'));
        }

        // OBLIGAR EL USO DE TOKENS: Si no hay token en la URL, redirigir a /t/{publicSlug}
        $tokenString = $request->query('token');
        if (!$tokenString) {
            return redirect()->route('token.redirect', $publicSlug);
        }

        // Verificar que el token existe y obtener su estado
        $tokenRecord = SurveyToken::where('token', $tokenString)
            ->where('survey_id', $survey->id)
            ->first();

        // Si el token no existe, redirigir a /t/{publicSlug} para generar uno nuevo
        if (!$tokenRecord) {
            return redirect()->route('token.redirect', $publicSlug);
        }

        // Si el token ya fue usado, incrementar intentos y mostrar como si ya votó
        // NO redirigir, mantener la misma URL
        if ($tokenRecord->status === 'used') {
            // Incrementar contador de intentos para tracking
            $tokenRecord->incrementAttempt();

            // Mostrar la vista como si ya hubiera votado (sin redirigir)
            $hasVoted = true;
            $token = $tokenString;
            return view('surveys.show', compact('survey', 'hasVoted', 'token'))
                ->with('info', 'Este enlace ya fue utilizado anteriormente.');
        }

        // Verificar si ya votó usando detector avanzado de dispositivos
        $fingerprint = $request->cookie('survey_fingerprint');
        $deviceMatcher = new DeviceFingerprintMatcher();

        $deviceData = [
            'fingerprint' => $fingerprint,
            'user_agent' => $request->userAgent(),
            'platform' => $request->header('Sec-CH-UA-Platform') ?? $request->input('platform'),
            'screen_resolution' => $request->input('screen_resolution'),
            'hardware_concurrency' => $request->input('hardware_concurrency')
        ];

        $hasVoted = $deviceMatcher->hasDeviceVotedInSurvey($survey->id, $deviceData);

        // Pasar el token a la vista
        $token = $tokenString;

        return view('surveys.show', compact('survey', 'hasVoted', 'token'));
    }

    /**
     * Mostrar encuesta usando slug del grupo
     */
    public function showWithGroup(Request $request, $groupSlug, $publicSlug)
    {
        // Buscar el grupo por slug
        $group = \App\Models\SurveyGroup::where('slug', $groupSlug)->firstOrFail();

        // Buscar la encuesta por public_slug y verificar que pertenece al grupo
        $survey = Survey::where('public_slug', $publicSlug)
            ->where('survey_group_id', $group->id)
            ->with('questions.options')
            ->firstOrFail();

        // Incrementar contador de visitas usando sesión para evitar múltiples conteos
        $sessionKey = 'survey_viewed_' . $survey->id;
        if (!session()->has($sessionKey)) {
            $survey->incrementViews();
            session()->put($sessionKey, true);
        }

        // Si la encuesta está terminada, redirigir a resultados finales
        if ($survey->is_finished) {
            return redirect()->route('surveys.finished.group', [$groupSlug, $survey->public_slug]);
        }

        // Si la encuesta no está activa, mostrar mensaje
        if (!$survey->is_active) {
            return view('surveys.inactive', compact('survey'));
        }

        // OBLIGAR EL USO DE TOKENS: Si no hay token en la URL, redirigir a /t/{groupSlug}/{publicSlug}
        $tokenString = $request->query('token');
        if (!$tokenString) {
            return redirect()->route('token.redirect.group', [$groupSlug, $publicSlug]);
        }

        // Verificar que el token existe y obtener su estado
        $tokenRecord = SurveyToken::where('token', $tokenString)->first();

        $hasVoted = false;

        // Si el token existe y ya fue usado
        if ($tokenRecord && $tokenRecord->status === 'used') {
            // Verificar si el token pertenece a ESTA encuesta
            if ($tokenRecord->survey_id === $survey->id) {
                // El token es de esta encuesta - mostrar como votado
                $tokenRecord->incrementAttempt();
                $hasVoted = true;
                $token = $tokenString;
                return view('surveys.show', compact('survey', 'hasVoted', 'token'))
                    ->with('info', 'Este enlace ya fue utilizado anteriormente.');
            } else {
                // El token es de OTRA encuesta del grupo - verificar si pertenece al mismo grupo
                $tokenSurvey = Survey::find($tokenRecord->survey_id);
                if ($tokenSurvey && $tokenSurvey->survey_group_id === $survey->survey_group_id) {
                    // Token de otra encuesta del MISMO grupo - mostrar como votado sin contar voto
                    $tokenRecord->incrementAttempt();
                    $hasVoted = true;
                    $token = $tokenString;
                    return view('surveys.show', compact('survey', 'hasVoted', 'token'))
                        ->with('info', 'Ya has votado en otra encuesta de este grupo.');
                }
            }
        }

        // Verificar si ya votó usando detector avanzado de dispositivos
        $fingerprint = $request->cookie('survey_fingerprint');
        $deviceMatcher = new DeviceFingerprintMatcher();

        $deviceData = [
            'fingerprint' => $fingerprint,
            'user_agent' => $request->userAgent(),
            'platform' => $request->header('Sec-CH-UA-Platform') ?? $request->input('platform'),
            'screen_resolution' => $request->input('screen_resolution'),
            'hardware_concurrency' => $request->input('hardware_concurrency')
        ];

        // VALIDACIÓN DE GRUPO: Si el grupo tiene restricción de votación
        if ($group->restrict_voting) {
            // Verificar si ya votó en ESTA encuesta
            $hasVotedInThisSurvey = $deviceMatcher->hasDeviceVotedInSurvey($survey->id, $deviceData);

            if ($hasVotedInThisSurvey) {
                $hasVoted = true;
                $token = $tokenString;
                return view('surveys.show', compact('survey', 'hasVoted', 'token'))
                    ->with('info', 'Ya has votado en esta encuesta.');
            }

            // Verificar si ya votó en OTRA encuesta del grupo
            if ($fingerprint) {
                $votedSurvey = $group->getVotedSurvey($fingerprint);

                if ($votedSurvey && $votedSurvey->id !== $survey->id) {
                    // Ya votó en otra encuesta del grupo - Mostrar como votado
                    $hasVoted = true;
                    $token = $tokenString;
                    return view('surveys.show', compact('survey', 'hasVoted', 'token'))
                        ->with('info', 'Ya has votado en otra encuesta de este grupo.');
                }
            }
        } else {
            // Si no hay restricción de grupo, solo verificar si votó en esta encuesta
            $hasVoted = $deviceMatcher->hasDeviceVotedInSurvey($survey->id, $deviceData);
        }

        $token = $tokenString;

        return view('surveys.show', compact('survey', 'hasVoted', 'token'));
    }

    public function vote(Request $request, $publicSlug)
    {
        $survey = Survey::where('public_slug', $publicSlug)->firstOrFail();

        // Verificar que la encuesta esté activa
        if (!$survey->is_active) {
            return redirect()->route('surveys.show', $publicSlug)
                ->with('error', 'Esta encuesta no está disponible para votar en este momento.');
        }

        $validated = $request->validate([
            'answers' => 'required|array|min:1|max:50',
            'answers.*' => 'required|exists:question_options,id',
            'fingerprint' => 'required|string|max:100',
            'token' => 'nullable|string|max:100',
            'device_data' => 'nullable|array',
            'device_data.user_agent' => 'nullable|string|max:500',
            'device_data.platform' => 'nullable|string|max:100',
            'device_data.screen_resolution' => 'nullable|string|max:50',
            'device_data.hardware_concurrency' => 'nullable|integer',
        ]);

        // Validar que las respuestas correspondan a preguntas de esta encuesta
        foreach ($validated['answers'] as $questionId => $optionId) {
            $validOption = \App\Models\QuestionOption::where('id', $optionId)
                ->whereHas('question', function($q) use ($survey, $questionId) {
                    $q->where('survey_id', $survey->id)
                      ->where('id', $questionId);
                })
                ->exists();

            if (!$validOption) {
                abort(422, 'Respuesta inválida detectada.');
            }
        }

        $ipAddress = $request->ip();
        $fingerprint = $request->input('fingerprint') ?? Str::random(40);
        $deviceData = $request->input('device_data', []);
        $tokenString = $request->input('token');

        // ===================================================================
        // VALIDACIÓN DE TOKEN OBLIGATORIA
        // ===================================================================
        if (!$tokenString) {
            // SIEMPRE mostrar éxito para no revelar el problema
            return redirect()->route('surveys.thanks', $survey->public_slug)
                ->with('success', '¡Gracias por tu participación!');
        }

        $tokenRecord = SurveyToken::where('token', $tokenString)->first();

        // ===================================================================
        // VALIDACIÓN PRINCIPAL: 1 TOKEN = 1 VOTO (sin importar el dispositivo)
        // ===================================================================
        // Si el token no existe o ya fue usado, mostrar éxito pero NO contar el voto
        if (!$tokenRecord || !$tokenRecord->isValid()) {
            if ($tokenRecord) {
                // Incrementar intentos para rastreo de actividad sospechosa
                $tokenRecord->incrementAttempt();
            }

            // SIEMPRE mostrar éxito para no revelar que el token es inválido
            return redirect()->route('surveys.thanks', $survey->public_slug)
                ->with('success', '¡Gracias por tu participación!');
        }

        // ===================================================================
        // VALIDACIÓN DE TOKEN DE ENCUESTA: Verificar que el token pertenece a ESTA encuesta
        // ===================================================================
        if ($tokenRecord->survey_id !== $survey->id) {
            // Token de otra encuesta - NO contar el voto
            $tokenRecord->incrementAttempt();

            // SIEMPRE mostrar éxito para no revelar el problema
            return redirect()->route('surveys.thanks', $survey->public_slug)
                ->with('success', '¡Gracias por tu participación!');
        }

        // TOKEN VÁLIDO - Incrementar intentos del token
        $tokenRecord->incrementAttempt();

        // ===================================================================
        // VALIDACIÓN DE DISPOSITIVO: Detectar si el mismo dispositivo físico ya votó
        // Esto previene votos duplicados desde modo incógnito o diferentes navegadores
        // ===================================================================
        $deviceMatcher = new DeviceFingerprintMatcher();

        $currentDeviceData = [
            'fingerprint' => $fingerprint,
            'user_agent' => $deviceData['user_agent'] ?? $request->userAgent(),
            'platform' => $deviceData['platform'] ?? null,
            'screen_resolution' => $deviceData['screen_resolution'] ?? null,
            'hardware_concurrency' => $deviceData['hardware_concurrency'] ?? null
        ];

        // Verificar si este dispositivo ya votó en ESTA encuesta
        if ($deviceMatcher->hasDeviceVotedInSurvey($survey->id, $currentDeviceData)) {
            // Ya votó desde este dispositivo - MOSTRAR ÉXITO pero NO contar el voto
            return redirect()->route('surveys.thanks', $survey->public_slug)
                ->with('success', '¡Gracias por tu participación!');
        }

        // ===================================================================
        // VALIDACIÓN DE GRUPO: Si la encuesta pertenece a un grupo con restricción,
        // verificar que el usuario no haya votado en otra encuesta del grupo
        // ===================================================================
        if ($survey->survey_group_id) {
            $group = $survey->group;

            if ($group && $group->restrict_voting) {
                // Verificar si ya votó en alguna encuesta del grupo
                $votedSurvey = $group->getVotedSurvey($fingerprint);

                if ($votedSurvey && $votedSurvey->id !== $survey->id) {
                    // Ya votó en otra encuesta del grupo - MOSTRAR ÉXITO pero NO contar el voto
                    return redirect()->route('surveys.thanks', $survey->public_slug)
                        ->with('success', '¡Gracias por tu participación!');
                }
            }
        }

        // ===================================================================
        // VALIDACIÓN: 1 VOTO POR MINUTO POR OPCIÓN (si está habilitada)
        // ===================================================================
        $oneVotePerMinuteValidator = new OneVotePerMinuteValidator();
        $invalidVotes = []; // Almacenar votos que no se validarán

        if ($survey->one_vote_per_minute_per_option) {
            foreach ($validated['answers'] as $questionId => $optionId) {
                $validationResult = $oneVotePerMinuteValidator->canVoteForOption($optionId);

                if (!$validationResult['allowed']) {
                    // Esta opción ya recibió un voto en el último minuto
                    // Marcar este voto como NO VÁLIDO
                    $invalidVotes[$questionId] = [
                        'optionId' => $optionId,
                        'reason' => 'one_vote_per_minute_per_option',
                        'message' => $validationResult['message'] ?? 'Opción votada recientemente',
                    ];
                }
            }
        }

        // ===================================================================
        // RATE LIMITING: Verificar límites ANTES de procesar el voto
        // ===================================================================
        $rateLimiter = new VoteRateLimiter();

        // Verificar cada respuesta contra el rate limiter
        foreach ($validated['answers'] as $questionId => $optionId) {
            $rateLimitCheck = $rateLimiter->allowVote(
                $survey->id,
                $optionId,
                $ipAddress,
                $fingerprint
            );

            if (!$rateLimitCheck['allowed']) {
                // Rate limit excedido - MOSTRAR ÉXITO pero NO contar el voto
                // Esto previene que atacantes sepan que fueron bloqueados
                return redirect()->route('surveys.thanks', $survey->public_slug)
                    ->with('success', '¡Gracias por tu participación!')
                    ->with('rate_limited', true); // Flag interno (no se muestra al usuario)
            }
        }

        try {
            DB::beginTransaction();

            // Instanciar el detector de fraude
            $fraudDetector = new VoteFraudDetector();

            foreach ($validated['answers'] as $questionId => $optionId) {
                // Verificar si este voto fue marcado como NO VÁLIDO
                $isInvalidVote = isset($invalidVotes[$questionId]);
                $invalidReason = $isInvalidVote ? $invalidVotes[$questionId]['reason'] : null;

                // Preparar datos del voto para análisis de fraude
                $voteData = [
                    'user_agent' => $deviceData['user_agent'] ?? null,
                    'platform' => $deviceData['platform'] ?? null,
                    'screen_resolution' => $deviceData['screen_resolution'] ?? null,
                    'browser_language' => $request->header('Accept-Language'),
                    'hardware_concurrency' => $deviceData['hardware_concurrency'] ?? null,
                ];

                // Analizar el voto para detectar patrones sospechosos
                $fraudAnalysis = $fraudDetector->analyzeVote($voteData, $questionId, $optionId);

                // Crear el voto con los datos de fraude incluidos
                Vote::create([
                    'survey_id' => $survey->id,
                    'survey_token_id' => $tokenRecord->id,
                    'question_id' => $questionId,
                    'question_option_id' => $optionId,
                    'ip_address' => $ipAddress,
                    'fingerprint' => $fingerprint,
                    'user_agent' => $deviceData['user_agent'] ?? null,
                    'platform' => $deviceData['platform'] ?? null,
                    'screen_resolution' => $deviceData['screen_resolution'] ?? null,
                    'hardware_concurrency' => $deviceData['hardware_concurrency'] ?? null,
                    'is_valid' => !$isInvalidVote, // FALSE si fue invalidado por 1 voto/minuto
                    'invalid_reason' => $invalidReason, // Razón de invalidación
                    // Campos de detección de fraude
                    'status' => $fraudAnalysis['status'],
                    'fraud_score' => $fraudAnalysis['fraud_score'],
                    'fraud_reasons' => !empty($fraudAnalysis['fraud_reasons'])
                        ? json_encode($fraudAnalysis['fraud_reasons'])
                        : null,
                ]);

                // Registrar el voto en el sistema de rate limiting
                $rateLimiter->recordVote(
                    $survey->id,
                    $optionId,
                    $ipAddress,
                    $fingerprint
                );

                // Si el voto ES VÁLIDO, registrar en el sistema de 1 voto/minuto
                if (!$isInvalidVote && $survey->one_vote_per_minute_per_option) {
                    $oneVotePerMinuteValidator->recordValidVote($optionId);
                }
            }

            // Marcar el token como usado (ya validamos que es válido arriba)
            $tokenRecord->markAsUsed(
                $fingerprint,
                $request->userAgent() ?? ''
            );

            DB::commit();

            // SIEMPRE mostrar mensaje de éxito al usuario
            $response = redirect()->route('surveys.thanks', $survey->public_slug)
                ->with('success', '¡Gracias por tu participación!');

            // Establecer MÚLTIPLES cookies para máxima persistencia
            return $response
                ->cookie('survey_fingerprint', $fingerprint, 525600) // 1 año
                ->cookie('device_fingerprint', $fingerprint, 525600) // 1 año
                ->cookie('survey_' . $survey->id . '_voted', 'true', 525600) // Cookie específica de encuesta
                ->cookie('survey_' . $survey->id . '_fp', $fingerprint, 525600); // Fingerprint por encuesta

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al registrar tu voto: ' . $e->getMessage());
        }
    }

    public function thanks($publicSlug)
    {
        $survey = Survey::where('public_slug', $publicSlug)
            ->with(['questions.options' => function($query) {
                $query->withCount([
                    'votes as valid_votes_count' => function($q) {
                        $q->valid();
                    },
                    'votes as invalid_votes_count' => function($q) {
                        $q->where('is_valid', false);
                    }
                ]);
            }])
            ->firstOrFail();

        // Si la encuesta está terminada, redirigir a la página de resultados finales
        if ($survey->is_finished) {
            return redirect()->route('surveys.finished', $survey->public_slug);
        }

        // Si la encuesta está despublicada/inactiva, mostrar página de inactiva
        if (!$survey->is_active) {
            return view('surveys.inactive', compact('survey'));
        }

        // Verificar si se deben mostrar resultados
        $showResults = $survey->show_results;
        $totalVotes = 0;
        $statistics = [];

        if ($showResults) {
            // Calcular estadísticas generales - Solo votos válidos
            $totalVotes = Vote::where('survey_id', $survey->id)
                ->valid()
                ->distinct('fingerprint')
                ->count('fingerprint');

            // Si no hay votos por fingerprint, contar por IP
            if ($totalVotes == 0) {
                $totalVotes = Vote::where('survey_id', $survey->id)
                    ->valid()
                    ->distinct('ip_address')
                    ->count('ip_address');
            }

            // Preparar datos para los gráficos
            foreach ($survey->questions as $question) {
                $questionStats = [
                    'question' => $question->question_text,
                    'type' => $question->question_type,
                    'options' => [],
                    'total_responses' => 0
                ];

                $totalQuestionVotes = $question->options->sum('valid_votes_count');
                $questionStats['total_responses'] = $totalQuestionVotes;

                foreach ($question->options as $option) {
                    $percentage = $totalQuestionVotes > 0
                        ? round(($option->valid_votes_count / $totalQuestionVotes) * 100, 1)
                        : 0;

                    $questionStats['options'][] = [
                        'text' => $option->option_text,
                        'votes' => $option->valid_votes_count,
                        'invalid_votes' => $option->invalid_votes_count,
                        'percentage' => $percentage,
                        'color' => $option->color ?? null
                    ];
                }

                $statistics[] = $questionStats;
            }
        }

        return view('surveys.thanks', compact('survey', 'totalVotes', 'statistics', 'showResults'));
    }

    public function finished($publicSlug)
    {
        $survey = Survey::where('public_slug', $publicSlug)
            ->where('is_finished', true)
            ->with(['questions.options' => function($query) {
                $query->withCount([
                    'votes as valid_votes_count' => function($q) {
                        $q->valid();
                    },
                    'votes as invalid_votes_count' => function($q) {
                        $q->where('is_valid', false);
                    }
                ]);
            }])
            ->firstOrFail();

        // Calcular estadísticas generales - Solo votos válidos
        $uniqueVoters = Vote::where('survey_id', $survey->id)
            ->valid()
            ->distinct('ip_address')
            ->count('ip_address');

        // El total de votos es la suma de votos de la primera pregunta
        $firstQuestion = $survey->questions->first();
        $totalVotes = $firstQuestion ? $firstQuestion->options->sum('valid_votes_count') : 0;

        // Preparar datos para los gráficos
        $statistics = [];
        foreach ($survey->questions as $question) {
            $questionStats = [
                'question' => $question->question_text,
                'type' => $question->question_type,
                'options' => [],
                'total_responses' => 0
            ];

            $totalQuestionVotes = $question->options->sum('valid_votes_count');
            $questionStats['total_responses'] = $totalQuestionVotes;

            foreach ($question->options as $option) {
                $percentage = $totalQuestionVotes > 0
                    ? round(($option->valid_votes_count / $totalQuestionVotes) * 100, 1)
                    : 0;

                $questionStats['options'][] = [
                    'text' => $option->option_text,
                    'votes' => $option->valid_votes_count,
                    'invalid_votes' => $option->invalid_votes_count,
                    'percentage' => $percentage,
                    'color' => $option->color ?? null
                ];
            }

            $statistics[] = $questionStats;
        }

        return view('surveys.finished', compact('survey', 'uniqueVoters', 'totalVotes', 'statistics'));
    }

    public function checkVote($publicSlug)
    {
        $survey = Survey::where('public_slug', $publicSlug)->where('is_active', true)->firstOrFail();

        $fingerprint = request()->input('fingerprint');

        $hasVoted = false;
        if ($fingerprint) {
            $hasVoted = Vote::where('survey_id', $survey->id)
                ->where('fingerprint', $fingerprint)
                ->exists();
        }

        return response()->json(['has_voted' => $hasVoted]);
    }
}

