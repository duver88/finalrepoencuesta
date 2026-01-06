@extends('layouts.admin')

@section('title', $group->name)

@section('content')
<div class="container-fluid px-0">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h2 fw-bold mb-1" style="color: #1e293b;">
                <i class="bi bi-collection"></i> {{ $group->name }}
            </h1>
            @if($group->description)
                <p class="text-muted mb-0">{{ $group->description }}</p>
            @endif
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.survey-groups.report', $group) }}" class="btn btn-primary btn-sm">
                <i class="bi bi-file-text-fill"></i> Ver Reporte
            </a>
            <a href="{{ url('admin/survey-groups/' . $group->id . '/edit') }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-pencil"></i> Editar
            </a>
            <a href="{{ route('admin.survey-groups.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="modern-card">
                <h6 class="fw-semibold mb-2" style="color: #64748b; font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.5px;">
                    Encuestas en el Grupo
                </h6>
                <div class="h2 fw-bold mb-0" style="color: #1e293b;">
                    {{ $group->surveys->count() }}
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="modern-card">
                <h6 class="fw-semibold mb-2" style="color: #64748b; font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.5px;">
                    Restricción de Voto
                </h6>
                <div class="fw-bold" style="color: #1e293b;">
                    @if($group->restrict_voting)
                        <span class="badge" style="background: rgba(239, 68, 68, 0.15); color: #dc2626; border: 1px solid rgba(239, 68, 68, 0.3); font-size: 0.875rem; padding: 0.5rem 0.875rem;">
                            <i class="bi bi-shield-lock-fill"></i> Activa
                        </span>
                    @else
                        <span class="badge" style="background: #f1f5f9; color: #64748b; font-size: 0.875rem; padding: 0.5rem 0.875rem;">
                            <i class="bi bi-unlock-fill"></i> Desactivada
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Encuestas actuales del grupo -->
    <div class="modern-card mb-4">
        <h5 class="fw-bold mb-3" style="color: #1e293b;">
            <i class="bi bi-clipboard-data"></i> Encuestas en este Grupo
        </h5>

        @if($group->surveys->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                        <tr>
                            <th style="color: #475569; font-weight: 600; font-size: 0.8125rem; padding: 1rem;">Encuesta</th>
                            <th style="color: #475569; font-weight: 600; font-size: 0.8125rem; padding: 1rem;">Estado</th>
                            <th style="color: #475569; font-weight: 600; font-size: 0.8125rem; padding: 1rem;">Votos</th>
                            <th style="color: #475569; font-weight: 600; font-size: 0.8125rem; padding: 1rem;" class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($group->surveys as $survey)
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 1rem;">
                                    <div class="fw-semibold" style="color: #1e293b;">{{ $survey->title }}</div>
                                    <small class="text-muted">{{ $survey->questions->count() }} preguntas</small>
                                </td>
                                <td style="padding: 1rem;">
                                    @if($survey->is_active)
                                        <span class="badge" style="background: rgba(17, 153, 142, 0.15); color: #11998e;">
                                            <i class="bi bi-check-circle-fill"></i> Activa
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">Inactiva</span>
                                    @endif
                                </td>
                                <td style="padding: 1rem;">
                                    {{ $survey->votes()->countable()->count() }}
                                </td>
                                <td style="padding: 1rem;" class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.surveys.show', $survey) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-eye"></i> Ver
                                        </a>
                                        <form action="{{ url('admin/survey-groups/' . $group->id . '/surveys/' . $survey->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Remover esta encuesta del grupo?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                                <i class="bi bi-x-circle"></i> Remover
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-4">
                <i class="bi bi-inbox" style="font-size: 2rem; color: #cbd5e1;"></i>
                <p class="text-muted mt-2 mb-0">No hay encuestas en este grupo todavía</p>
            </div>
        @endif
    </div>

    <!-- Agregar encuestas al grupo -->
    @if($availableSurveys->count() > 0)
        <div class="modern-card">
            <h5 class="fw-bold mb-3" style="color: #1e293b;">
                <i class="bi bi-plus-circle"></i> Agregar Encuesta al Grupo
            </h5>

            <form action="{{ url('admin/survey-groups/' . $group->id . '/add-survey') }}" method="POST" class="row g-3 align-items-end">
                @csrf
                <div class="col-md-9">
                    <label for="survey_id" class="form-label fw-semibold" style="color: #1e293b;">
                        Seleccionar Encuesta
                    </label>
                    <select class="form-select" id="survey_id" name="survey_id" required>
                        <option value="">-- Selecciona una encuesta --</option>
                        @foreach($availableSurveys as $survey)
                            <option value="{{ $survey->id }}">
                                {{ $survey->title }} ({{ $survey->questions->count() }} preguntas)
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-gradient-primary w-100">
                        <i class="bi bi-plus-circle"></i> Agregar
                    </button>
                </div>
            </form>
        </div>
    @else
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Todas las encuestas disponibles ya están asignadas a grupos.
        </div>
    @endif
</div>
@endsection
