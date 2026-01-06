<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Models\SurveyToken;
use Illuminate\Http\Request;

class TokenController extends Controller
{
    public function index(Request $request, Survey $survey)
    {
        $query = $survey->tokens();

        // Filtro por estado
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filtro por fuente
        if ($request->filled('source') && $request->source !== 'all') {
            $query->where('source', $request->source);
        }

        // Filtro por intentos múltiples
        if ($request->filled('multiple_attempts') && $request->multiple_attempts === '1') {
            $query->where('vote_attempts', '>', 1);
        }

        // Filtro por respuesta (opción de pregunta)
        if ($request->filled('question_option') && $request->question_option !== 'all') {
            $query->whereHas('votes', function($q) use ($request) {
                $q->where('question_option_id', $request->question_option)
                  ->where(function($subQ) {
                      $subQ->whereNotNull('survey_token_id')
                           ->orWhere('is_manual', true);
                  });
            });
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $tokens = $query->paginate(50)->withQueryString();

        $stats = [
            'total' => $survey->tokens()->count(),
            'pending' => $survey->tokens()->where('status', 'pending')->count(),
            'used' => $survey->tokens()->where('status', 'used')->count(),
            'expired' => $survey->tokens()->where('status', 'expired')->count(),
            'multiple_attempts' => $survey->tokens()->where('vote_attempts', '>', 1)->count(),
        ];

        // Obtener todas las fuentes únicas para el filtro
        $sources = $survey->tokens()
            ->select('source')
            ->distinct()
            ->whereNotNull('source')
            ->pluck('source');

        // Obtener todas las preguntas con sus opciones para el filtro
        $questions = $survey->questions()
            ->with('options')
            ->orderBy('order')
            ->get();

        return view('admin.surveys.tokens.index', compact('survey', 'tokens', 'stats', 'sources', 'questions'));
    }

    public function generate(Request $request, Survey $survey)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:1000000',
            'source' => 'required|string|max:255',
            'campaign_id' => 'nullable|string|max:255',
        ]);

        $quantity = $request->quantity;
        $batchSize = 1000;
        $totalBatches = ceil($quantity / $batchSize);

        // Generar todos los tokens únicos primero
        $allTokens = [];
        for ($i = 0; $i < $quantity; $i++) {
            do {
                $token = \Illuminate\Support\Str::random(32);
            } while (isset($allTokens[$token]));

            $allTokens[$token] = true;
        }

        $tokenKeys = array_keys($allTokens);
        $now = now();

        // Insertar en lotes
        for ($batch = 0; $batch < $totalBatches; $batch++) {
            $offset = $batch * $batchSize;
            $batchTokens = array_slice($tokenKeys, $offset, $batchSize);

            $insertData = [];
            foreach ($batchTokens as $token) {
                $insertData[] = [
                    'survey_id' => $survey->id,
                    'token' => $token,
                    'source' => $request->source,
                    'campaign_id' => $request->campaign_id,
                    'status' => 'pending',
                    'vote_attempts' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            SurveyToken::insert($insertData);
        }

        return redirect()
            ->route('admin.surveys.tokens.index', $survey)
            ->with('success', "Se generaron " . number_format($quantity) . " tokens exitosamente.");
    }

    public function export(Survey $survey)
    {
        $tokens = $survey->tokens()->where('status', 'pending')->get();

        $baseUrl = url("/t/{$survey->public_slug}");

        $content = $tokens->map(function ($token) use ($baseUrl) {
            return $baseUrl . '?token=' . $token->token;
        })->implode("\n");

        return response($content)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', "attachment; filename=tokens-{$survey->public_slug}.txt");
    }

    public function destroy(Survey $survey, SurveyToken $token)
    {
        $token->delete();

        return redirect()
            ->route('admin.surveys.tokens.index', $survey)
            ->with('success', 'Token eliminado exitosamente.');
    }

    public function bulkDelete(Request $request, Survey $survey)
    {
        $request->validate([
            'status' => 'required|in:pending,used,expired',
        ]);

        $deleted = $survey->tokens()->where('status', $request->status)->delete();

        return redirect()
            ->route('admin.surveys.tokens.index', $survey)
            ->with('success', "Se eliminaron {$deleted} tokens con estado '{$request->status}'.");
    }

    public function analytics(Survey $survey)
    {
        $tokensBySource = $survey->tokens()
            ->selectRaw('source, status, COUNT(*) as count')
            ->groupBy('source', 'status')
            ->get();

        $suspiciousTokens = $survey->tokens()
            ->where('vote_attempts', '>', 1)
            ->orderBy('vote_attempts', 'desc')
            ->limit(100)
            ->get();

        $recentActivity = $survey->tokens()
            ->whereNotNull('last_attempt_at')
            ->orderBy('last_attempt_at', 'desc')
            ->limit(50)
            ->get();

        // Obtener votos agrupados por opción (solo votos con tokens)
        $votesByOption = [];
        foreach ($survey->questions as $question) {
            $questionData = [
                'question_text' => $question->question_text,
                'options' => []
            ];

            foreach ($question->options as $option) {
                $voteCount = $option->votes()->whereNotNull('survey_token_id')->count();
                $questionData['options'][] = [
                    'option_text' => $option->option_text,
                    'color' => $option->color ?? '#0d6efd',
                    'votes' => $voteCount
                ];
            }

            $votesByOption[] = $questionData;
        }

        return view('admin.surveys.tokens.analytics', compact(
            'survey',
            'tokensBySource',
            'suspiciousTokens',
            'recentActivity',
            'votesByOption'
        ));
    }

    public function show(Survey $survey, SurveyToken $token)
    {
        // Verificar que el token pertenece a la encuesta
        if ($token->survey_id !== $survey->id) {
            abort(404);
        }

        // Obtener todos los votos asociados a este token
        $votes = \App\Models\Vote::where('survey_token_id', $token->id)
            ->with(['question', 'option'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Agrupar votos por sesión (usando fingerprint y fecha cercana)
        $voteAttempts = $votes->groupBy(function($vote) {
            return $vote->created_at->format('Y-m-d H:i') . '_' . $vote->fingerprint;
        });

        return view('admin.surveys.tokens.show', compact('survey', 'token', 'votes', 'voteAttempts'));
    }
}
