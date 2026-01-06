<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Models\SurveyGroup;
use Illuminate\Http\Request;

class SurveyGroupController extends Controller
{
    /**
     * Mostrar todos los grupos de encuestas
     */
    public function index()
    {
        $groups = SurveyGroup::withCount('surveys')->latest()->paginate(20);
        return view('admin.survey-groups.index', compact('groups'));
    }

    /**
     * Mostrar formulario para crear grupo
     */
    public function create()
    {
        return view('admin.survey-groups.create');
    }

    /**
     * Guardar nuevo grupo
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'restrict_voting' => 'boolean',
        ]);

        $group = SurveyGroup::create($validated);

        return redirect()->route('admin.survey-groups.show', ['survey_group' => $group->id])
            ->with('success', 'Grupo creado exitosamente.');
    }

    /**
     * Mostrar detalles del grupo
     */
    public function show(SurveyGroup $surveyGroup)
    {
        $surveyGroup->load('surveys');
        $availableSurveys = Survey::whereNull('survey_group_id')->get();

        return view('admin.survey-groups.show', [
            'group' => $surveyGroup,
            'availableSurveys' => $availableSurveys
        ]);
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(SurveyGroup $surveyGroup)
    {
        return view('admin.survey-groups.edit', ['group' => $surveyGroup]);
    }

    /**
     * Actualizar grupo
     */
    public function update(Request $request, SurveyGroup $surveyGroup)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'restrict_voting' => 'boolean',
        ]);

        $surveyGroup->update($validated);

        return redirect()->route('admin.survey-groups.show', ['survey_group' => $surveyGroup->id])
            ->with('success', 'Grupo actualizado exitosamente.');
    }

    /**
     * Eliminar grupo
     */
    public function destroy(SurveyGroup $surveyGroup)
    {
        // Desasociar encuestas antes de eliminar
        $surveyGroup->surveys()->update(['survey_group_id' => null]);

        $surveyGroup->delete();

        return redirect()->route('admin.survey-groups.index')
            ->with('success', 'Grupo eliminado exitosamente.');
    }

    /**
     * Agregar encuesta al grupo
     */
    public function addSurvey(Request $request, SurveyGroup $group)
    {
        $validated = $request->validate([
            'survey_id' => 'required|exists:surveys,id',
        ]);

        $survey = Survey::findOrFail($validated['survey_id']);
        $survey->update(['survey_group_id' => $group->id]);

        return back()->with('success', 'Encuesta agregada al grupo exitosamente.');
    }

    /**
     * Remover encuesta del grupo
     */
    public function removeSurvey(SurveyGroup $group, Survey $survey)
    {
        if ($survey->survey_group_id !== $group->id) {
            return back()->with('error', 'Esta encuesta no pertenece a este grupo.');
        }

        $survey->update(['survey_group_id' => null]);

        return back()->with('success', 'Encuesta removida del grupo exitosamente.');
    }
}
