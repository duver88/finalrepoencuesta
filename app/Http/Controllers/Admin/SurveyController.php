<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Vote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;

class SurveyController extends Controller
{
    public function index()
    {
        // Solo contar votos aprobados y válidos (con token o manuales)
        $surveys = Survey::withCount(['votes' => function ($query) {
            $query->where('status', 'approved')
                  ->where(function($q) {
                      $q->whereNotNull('survey_token_id')
                        ->orWhere('is_manual', true);
                  });
        }])->latest()->get();
        return view('admin.surveys.index', compact('surveys'));
    }

    public function create()
    {
        return view('admin.surveys.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255|min:3',
            'description' => 'nullable|string|max:1000',
            'banner' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:2048|dimensions:min_width=100,min_height=100,max_width=4000,max_height=4000',
            'show_results' => 'nullable|boolean',
            'one_vote_per_minute_per_option' => 'nullable|boolean',
            'questions' => 'required|array|min:1|max:50',
            'questions.*.question_text' => 'required|string|max:500',
            'questions.*.question_type' => 'required|in:single_choice,multiple_choice',
            'questions.*.options' => 'required|array|min:2|max:20',
            'questions.*.options.*' => 'required|string|max:255',
            'questions.*.colors' => 'required|array|min:2|max:20',
            'questions.*.colors.*' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'questions.*.option_images' => 'nullable|array',
            'questions.*.option_images.*' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
        ]);

        try {
            DB::beginTransaction();

            $survey = Survey::create([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'show_results' => $request->has('show_results') ? true : false,
                'one_vote_per_minute_per_option' => $request->has('one_vote_per_minute_per_option') ? true : false,
            ]);

            // Subir banner si existe con validación adicional
            if ($request->hasFile('banner')) {
                $file = $request->file('banner');

                // Validación extra de seguridad
                $extension = $file->getClientOriginalExtension();
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                if (!in_array(strtolower($extension), $allowedExtensions)) {
                    throw new \Exception('Tipo de archivo no permitido.');
                }

                // Verificar que sea realmente una imagen
                $mimeType = $file->getMimeType();
                $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];

                if (!in_array($mimeType, $allowedMimes)) {
                    throw new \Exception('El archivo no es una imagen válida.');
                }

                $path = $file->store('banners', 'public');
                $survey->update(['banner' => $path]);
            }

            // Subir imagen Open Graph (Facebook) si existe
            if ($request->hasFile('og_image')) {
                $file = $request->file('og_image');

                // Validación extra de seguridad
                $extension = $file->getClientOriginalExtension();
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                if (!in_array(strtolower($extension), $allowedExtensions)) {
                    throw new \Exception('Tipo de archivo no permitido para imagen OG.');
                }

                // Verificar que sea realmente una imagen
                $mimeType = $file->getMimeType();
                $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];

                if (!in_array($mimeType, $allowedMimes)) {
                    throw new \Exception('El archivo de imagen OG no es válido.');
                }

                $path = $file->store('og-images', 'public');
                $survey->update(['og_image' => $path]);
            }

            // Crear preguntas y opciones
            foreach ($validated['questions'] as $index => $questionData) {
                $question = $survey->questions()->create([
                    'question_text' => $questionData['question_text'],
                    'question_type' => $questionData['question_type'],
                    'order' => $index,
                ]);

                foreach ($questionData['options'] as $optionIndex => $optionText) {
                    $optionData = [
                        'option_text' => $optionText,
                        'order' => $optionIndex,
                        'color' => $questionData['colors'][$optionIndex] ?? null,
                    ];

                    // Procesar imagen de la opción si existe
                    $imageFile = $request->file("questions.{$index}.option_images.{$optionIndex}");

                    if ($imageFile && $imageFile->isValid()) {
                        // Validación extra de seguridad
                        $extension = $imageFile->getClientOriginalExtension();
                        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

                        if (in_array(strtolower($extension), $allowedExtensions)) {
                            $mimeType = $imageFile->getMimeType();
                            $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];

                            if (in_array($mimeType, $allowedMimes)) {
                                $path = $imageFile->store('option-images', 'public');
                                $optionData['image'] = $path;
                            }
                        }
                    }

                    $question->options()->create($optionData);
                }
            }

            DB::commit();

            return redirect()->route('admin.surveys.show', $survey)
                ->with('success', 'Encuesta creada exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al crear la encuesta: ' . $e->getMessage());
        }
    }

    public function show(Survey $survey)
    {
        $survey->load(['questions.options.votes', 'votes']);

        // Calcular estadísticas - Solo contar votos válidos Y aprobados
        $uniqueVoters = $survey->votes()->countable()->distinct('fingerprint')->count();
        $totalVotes = $survey->votes()->countable()->count();

        // Estadísticas de detección de fraude
        $pendingReview = $survey->votes()->pendingReview()->count();
        $rejectedVotes = $survey->votes()->rejected()->count();
        $totalAllVotes = $survey->votes()->valid()->count();

        // Contar votos no válidos (por cualquier razón)
        $invalidVotes = $survey->votes()->where('is_valid', false)->count();

        $questionStats = [];
        foreach ($survey->questions as $question) {
            $questionVotes = $question->votes()->countable()->count();
            $options = [];

            foreach ($question->options as $option) {
                $optionVotes = $option->votes()->countable()->count();
                $percentage = $questionVotes > 0 ? round(($optionVotes / $questionVotes) * 100, 2) : 0;

                $options[] = [
                    'text' => $option->option_text,
                    'votes' => $optionVotes,
                    'percentage' => $percentage,
                ];
            }

            $questionStats[] = [
                'question' => $question->question_text,
                'total_votes' => $questionVotes,
                'options' => $options,
            ];
        }

        return view('admin.surveys.show', compact(
            'survey',
            'uniqueVoters',
            'totalVotes',
            'questionStats',
            'pendingReview',
            'rejectedVotes',
            'totalAllVotes',
            'invalidVotes'
        ));
    }

    public function edit(Survey $survey)
    {
        $survey->load('questions.options');
        return view('admin.surveys.edit', compact('survey'));
    }

    public function update(Request $request, Survey $survey)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'banner' => 'nullable|image|max:2048',
            'og_image' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
            'show_results' => 'nullable|boolean',
            'one_vote_per_minute_per_option' => 'nullable|boolean',
            'questions' => 'required|array|min:1',
            'questions.*.id' => 'nullable|exists:questions,id',
            'questions.*.question_text' => 'required|string',
            'questions.*.question_type' => 'required|in:single_choice,multiple_choice',
            'questions.*.options' => 'required|array|min:2',
            'questions.*.options.*.id' => 'nullable|exists:question_options,id',
            'questions.*.options.*.option_text' => 'required|string',
            'questions.*.options.*.color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'questions.*.options.*.image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
        ]);

        try {
            DB::beginTransaction();

            // Preparar datos de actualización
            $updateData = [
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'is_active' => $request->boolean('is_active'),
            ];

            // Solo actualizar show_results si el campo existe en la tabla
            if (Schema::hasColumn('surveys', 'show_results')) {
                $updateData['show_results'] = $request->boolean('show_results');
            }

            // Solo actualizar one_vote_per_minute_per_option si el campo existe en la tabla
            if (Schema::hasColumn('surveys', 'one_vote_per_minute_per_option')) {
                $updateData['one_vote_per_minute_per_option'] = $request->boolean('one_vote_per_minute_per_option');
            }

            $survey->update($updateData);

            // Actualizar banner si existe
            if ($request->hasFile('banner')) {
                if ($survey->banner) {
                    Storage::disk('public')->delete($survey->banner);
                }
                $path = $request->file('banner')->store('banners', 'public');
                $survey->update(['banner' => $path]);
            }

            // Actualizar imagen Open Graph (Facebook) si existe
            if ($request->hasFile('og_image')) {
                if ($survey->og_image) {
                    Storage::disk('public')->delete($survey->og_image);
                }
                $path = $request->file('og_image')->store('og-images', 'public');
                $survey->update(['og_image' => $path]);
            }

            // IDs de preguntas que vienen en el request
            $questionIds = collect($validated['questions'])->pluck('id')->filter()->toArray();

            // Eliminar preguntas que no están en el request
            $survey->questions()->whereNotIn('id', $questionIds)->delete();

            // Actualizar o crear preguntas
            foreach ($validated['questions'] as $index => $questionData) {
                if (isset($questionData['id'])) {
                    $question = Question::find($questionData['id']);
                    $question->update([
                        'question_text' => $questionData['question_text'],
                        'question_type' => $questionData['question_type'],
                        'order' => $index,
                    ]);
                } else {
                    $question = $survey->questions()->create([
                        'question_text' => $questionData['question_text'],
                        'question_type' => $questionData['question_type'],
                        'order' => $index,
                    ]);
                }

                // IDs de opciones que vienen en el request
                $optionIds = collect($questionData['options'])->pluck('id')->filter()->toArray();

                // Eliminar opciones que no están en el request
                $question->options()->whereNotIn('id', $optionIds)->delete();

                // Actualizar o crear opciones
                foreach ($questionData['options'] as $optionIndex => $optionData) {
                    $optionUpdateData = [
                        'option_text' => $optionData['option_text'],
                        'order' => $optionIndex,
                        'color' => $optionData['color'] ?? null,
                    ];

                    // Procesar imagen de la opción si existe
                    // Laravel convierte los puntos en arrays anidados automáticamente
                    $imageFile = $request->file("questions.{$index}.options.{$optionIndex}.image");

                    if ($imageFile && $imageFile->isValid()) {
                        // Validación extra de seguridad
                        $extension = $imageFile->getClientOriginalExtension();
                        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

                        if (in_array(strtolower($extension), $allowedExtensions)) {
                            $mimeType = $imageFile->getMimeType();
                            $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];

                            if (in_array($mimeType, $allowedMimes)) {
                                // Si estamos actualizando una opción existente, eliminar la imagen anterior
                                if (isset($optionData['id'])) {
                                    $existingOption = QuestionOption::find($optionData['id']);
                                    if ($existingOption && $existingOption->image) {
                                        Storage::disk('public')->delete($existingOption->image);
                                    }
                                }

                                $path = $imageFile->store('option-images', 'public');
                                $optionUpdateData['image'] = $path;
                            }
                        }
                    }

                    if (isset($optionData['id'])) {
                        $option = QuestionOption::find($optionData['id']);
                        $option->update($optionUpdateData);
                    } else {
                        $question->options()->create($optionUpdateData);
                    }
                }
            }

            DB::commit();

            return redirect()->route('admin.surveys.show', $survey)
                ->with('success', 'Encuesta actualizada exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al actualizar la encuesta: ' . $e->getMessage());
        }
    }

    public function destroy(Survey $survey)
    {
        try {
            if ($survey->banner) {
                Storage::disk('public')->delete($survey->banner);
            }

            $survey->delete();

            return redirect()->route('admin.surveys.index')
                ->with('success', 'Encuesta eliminada exitosamente.');

        } catch (\Exception $e) {
            return back()->with('error', 'Error al eliminar la encuesta: ' . $e->getMessage());
        }
    }

    public function publish(Survey $survey)
    {
        $survey->update([
            'is_active' => true,
            'published_at' => now(),
        ]);

        return back()->with('success', 'Encuesta publicada exitosamente.');
    }

    public function unpublish(Survey $survey)
    {
        $survey->update([
            'is_active' => false,
        ]);

        return back()->with('success', 'Encuesta despublicada exitosamente.');
    }

    public function finish(Survey $survey)
    {
        $survey->update([
            'is_finished' => true,
            'finished_at' => now(),
            'is_active' => false, // Desactivar también para que no se pueda votar más
        ]);

        return back()->with('success', 'Encuesta marcada como terminada exitosamente.');
    }

    public function unfinish(Survey $survey)
    {
        $survey->update([
            'is_finished' => false,
            'finished_at' => null,
        ]);

        return back()->with('success', 'Encuesta reactivada exitosamente.');
    }

    public function reset(Survey $survey)
    {
        try {
            DB::beginTransaction();

            // Contar votos antes de eliminar (para el mensaje)
            $totalVotes = Vote::where('survey_id', $survey->id)->count();

            // Eliminar todos los votos de esta encuesta
            Vote::where('survey_id', $survey->id)->delete();

            DB::commit();

            return redirect()->route('admin.surveys.show', $survey)
                ->with('success', "✅ Reset exitoso. Se eliminaron {$totalVotes} votos. La encuesta está lista para recibir nuevos votos.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al resetear la encuesta: ' . $e->getMessage());
        }
    }

    public function editVotes(Survey $survey)
    {
        $survey->load(['questions.options.votes']);

        // Calcular votantes únicos actuales
        $currentUniqueVoters = $survey->votes()->distinct('ip_address')->count();

        // Preparar datos de votos por opción
        $votesData = [];
        foreach ($survey->questions as $question) {
            $questionData = [
                'id' => $question->id,
                'question_text' => $question->question_text,
                'options' => []
            ];

            foreach ($question->options as $option) {
                $questionData['options'][] = [
                    'id' => $option->id,
                    'option_text' => $option->option_text,
                    'color' => $option->color,
                    'vote_count' => $option->votes()->count()
                ];
            }

            $votesData[] = $questionData;
        }

        return view('admin.surveys.edit-votes', compact('survey', 'votesData', 'currentUniqueVoters'));
    }

    public function updateVotes(Request $request, Survey $survey)
    {
        $validated = $request->validate([
            'unique_voters' => 'required|integer|min:0|max:999999',
            'votes' => 'required|array',
            'votes.*' => 'required|integer|min:0|max:999999',
        ]);

        try {
            DB::beginTransaction();

            $desiredUniqueVoters = $validated['unique_voters'];

            // Primero, eliminar TODOS los votos de esta encuesta para empezar limpio
            Vote::where('survey_id', $survey->id)->delete();

            // Calcular el total de votos para distribuir entre personas únicas
            $totalVotesToCreate = array_sum($validated['votes']);

            // Si no hay votos, no hacer nada
            if ($totalVotesToCreate === 0) {
                DB::commit();
                return redirect()->route('admin.surveys.show', $survey)
                    ->with('success', 'Votos actualizados exitosamente.');
            }
 
            // Ajustar personas únicas si es necesario
            // No puede haber más personas únicas que votos totales
            $actualUniqueVoters = min($desiredUniqueVoters, $totalVotesToCreate);

            // Crear un array de opciones con sus votos
            $optionsVotes = [];
            foreach ($validated['votes'] as $optionId => $voteCount) {
                if ($voteCount > 0) {
                    $option = QuestionOption::findOrFail($optionId);
                    // Verificar que la opción pertenece a esta encuesta
                    if ($option->question->survey_id !== $survey->id) {
                        throw new \Exception('Opción no pertenece a esta encuesta');
                    }
                    $optionsVotes[] = [
                        'option' => $option,
                        'votes_needed' => $voteCount,
                        'votes_created' => 0
                    ];
                }
            }

            // Distribuir votos entre personas únicas
            $personCounter = 0;
            $allVotesCreated = false;

            while (!$allVotesCreated) {
                $allVotesCreated = true;

                // Para cada opción que necesita votos
                foreach ($optionsVotes as &$optionData) {
                    if ($optionData['votes_created'] < $optionData['votes_needed']) {
                        $allVotesCreated = false;

                        // Generar IP única para esta persona
                        $ipAddress = '192.168.' . floor($personCounter / 255) . '.' . ($personCounter % 255);
                        $fingerprint = 'admin-voter-' . $survey->id . '-' . $personCounter;

                        // Crear el voto
                        Vote::create([
                            'survey_id' => $survey->id,
                            'question_id' => $optionData['option']->question_id,
                            'question_option_id' => $optionData['option']->id,
                            'ip_address' => $ipAddress,
                            'fingerprint' => $fingerprint,
                            'user_agent' => 'Admin Manual Edit',
                            'platform' => 'Admin Panel',
                            'is_manual' => true,
                            'is_valid' => true,
                        ]);

                        $optionData['votes_created']++;
                        $personCounter++;

                        // Si alcanzamos el número deseado de personas únicas, repetir IPs
                        if ($personCounter >= $actualUniqueVoters) {
                            $personCounter = 0;
                        }

                        break; // Una opción por persona por iteración
                    }
                }
            }

            DB::commit();

            return redirect()->route('admin.surveys.show', $survey)
                ->with('success', 'Votos actualizados exitosamente. Se crearon ' . $totalVotesToCreate . ' votos distribuidos entre ' . $actualUniqueVoters . ' personas únicas.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al actualizar los votos: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function duplicate(Survey $survey)
    {
        try {
            DB::beginTransaction();

            // Preparar datos para la nueva encuesta
            $newSurveyData = [
                'title' => $survey->title . ' (Copia)',
                'description' => $survey->description,
                'is_active' => false, // Inicia inactiva
            ];

            // Solo incluir show_results si la columna existe
            if (Schema::hasColumn('surveys', 'show_results')) {
                $newSurveyData['show_results'] = $survey->show_results;
            }

            // Mantener el grupo si la encuesta original pertenece a uno
            if ($survey->survey_group_id) {
                $newSurveyData['survey_group_id'] = $survey->survey_group_id;
            }

            // Crear una nueva encuesta con los mismos datos
            $newSurvey = Survey::create($newSurveyData);

            // Copiar el banner si existe
            if ($survey->banner) {
                $extension = pathinfo($survey->banner, PATHINFO_EXTENSION);
                $newBannerPath = 'banners/' . uniqid() . '.' . $extension;
                Storage::disk('public')->copy($survey->banner, $newBannerPath);
                $newSurvey->update(['banner' => $newBannerPath]);
            }

            // Copiar la imagen OG si existe
            if ($survey->og_image) {
                $extension = pathinfo($survey->og_image, PATHINFO_EXTENSION);
                $newOgImagePath = 'og-images/' . uniqid() . '.' . $extension;
                Storage::disk('public')->copy($survey->og_image, $newOgImagePath);
                $newSurvey->update(['og_image' => $newOgImagePath]);
            }

            // Copiar las preguntas y opciones
            foreach ($survey->questions as $question) {
                $newQuestion = $newSurvey->questions()->create([
                    'question_text' => $question->question_text,
                    'question_type' => $question->question_type,
                    'order' => $question->order,
                ]);

                // Copiar las opciones de la pregunta
                foreach ($question->options as $option) {
                    $optionData = [
                        'option_text' => $option->option_text,
                        'order' => $option->order,
                        'color' => $option->color,
                    ];

                    // Copiar la imagen de la opción si existe
                    if ($option->image) {
                        $extension = pathinfo($option->image, PATHINFO_EXTENSION);
                        $newImagePath = 'option-images/' . uniqid() . '.' . $extension;
                        Storage::disk('public')->copy($option->image, $newImagePath);
                        $optionData['image'] = $newImagePath;
                    }

                    $newQuestion->options()->create($optionData);
                }
            }

            DB::commit();

            return redirect()->route('admin.surveys.show', $newSurvey)
                ->with('success', 'Encuesta duplicada exitosamente. La nueva encuesta está inactiva por defecto.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al duplicar la encuesta: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar votos sospechosos para revisión
     */
    public function suspiciousVotes(Survey $survey)
    {
        $suspiciousVotes = $survey->votes()
            ->where(function($query) {
                $query->where('status', 'pending_review')
                      ->orWhere('fraud_score', '>=', 40)
                      ->orWhere('is_valid', false); // Incluir votos no válidos
            })
            ->with(['question', 'option', 'token'])
            ->orderBy('is_valid', 'asc') // Mostrar primero los no válidos
            ->orderBy('fraud_score', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('admin.surveys.suspicious-votes', compact('survey', 'suspiciousVotes'));
    }

    /**
     * Aprobar un voto sospechoso
     */
    public function approveVote(Survey $survey, Vote $vote)
    {
        $vote->update([
            'status' => 'approved',
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
        ]);

        return back()->with('success', 'Voto aprobado exitosamente.');
    }

    /**
     * Rechazar un voto sospechoso
     */
    public function rejectVote(Survey $survey, Vote $vote)
    {
        $vote->update([
            'status' => 'rejected',
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
        ]);

        return back()->with('success', 'Voto rechazado exitosamente.');
    }

    /**
     * Aprobar múltiples votos
     */
    public function bulkApproveVotes(Request $request, Survey $survey)
    {
        $validated = $request->validate([
            'vote_ids' => 'required|array',
            'vote_ids.*' => 'required|exists:votes,id',
        ]);

        Vote::whereIn('id', $validated['vote_ids'])
            ->update([
                'status' => 'approved',
                'reviewed_at' => now(),
                'reviewed_by' => auth()->id(),
            ]);

        $count = count($validated['vote_ids']);
        return back()->with('success', "{$count} voto(s) aprobado(s) exitosamente.");
    }

    /**
     * Rechazar múltiples votos
     */
    public function bulkRejectVotes(Request $request, Survey $survey)
    {
        $validated = $request->validate([
            'vote_ids' => 'required|array',
            'vote_ids.*' => 'required|exists:votes,id',
        ]);

        Vote::whereIn('id', $validated['vote_ids'])
            ->update([
                'status' => 'rejected',
                'reviewed_at' => now(),
                'reviewed_by' => auth()->id(),
            ]);

        $count = count($validated['vote_ids']);
        return back()->with('success', "{$count} voto(s) rechazado(s) exitosamente.");
    }
}
