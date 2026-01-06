@extends('layouts.admin')

@section('title', 'Editar Votos - ' . $survey->title)

@section('content')
<div class="container-fluid px-0">
    <!-- Header Section -->
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h1 class="h2 fw-bold mb-1" style="color: #1e293b;">
                    <i class="bi bi-pencil-square"></i> Editar Votos
                </h1>
                <p class="text-muted mb-0">{{ $survey->title }}</p>
            </div>
            <a href="{{ route('admin.surveys.show', $survey) }}" class="btn btn-sm"
               style="background: #f1f5f9; color: #64748b; border: none; padding: 0.5rem 0.875rem; border-radius: 8px; font-weight: 500;">
                <i class="bi bi-arrow-left"></i> <span class="d-none d-md-inline">Volver a Resultados</span>
            </a>
        </div>
    </div>

    <!-- Alert de información -->
    <div class="modern-card mb-4" style="background: linear-gradient(135deg, rgba(79, 172, 254, 0.05) 0%, rgba(0, 242, 254, 0.05) 100%); border: 1px solid rgba(79, 172, 254, 0.2);">
        <div class="d-flex align-items-start" style="padding: 1.25rem;">
            <i class="bi bi-info-circle-fill fs-4 me-3" style="color: #4facfe;"></i>
            <div>
                <h5 class="mb-2 fw-bold" style="color: #1e293b;">Instrucciones</h5>
                <ul class="mb-0 ps-3">
                    <li>Define cuántas <strong>personas únicas</strong> quieres que aparezcan como votantes</li>
                    <li>Ingresa el número de votos para cada opción de cada pregunta</li>
                    <li>El sistema distribuirá los votos automáticamente entre las personas únicas</li>
                    <li>Los votos editados manualmente usan IPs simuladas (192.168.x.x) para identificación</li>
                    <li><strong>Nota:</strong> Si los votos totales son menores que las personas únicas, se ajustará automáticamente</li>
                </ul>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h6 class="alert-heading mb-2">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>Errores de validación:
            </h6>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Formulario de edición -->
    <form action="{{ route('admin.surveys.votes.update', $survey) }}" method="POST" id="editVotesForm">
        @csrf
        @method('PUT')

        <!-- Control de Personas Únicas -->
        <div class="modern-card mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div style="padding: 1.5rem;" class="text-white">
                <div class="row align-items-center">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <h5 class="mb-2">
                            <i class="bi bi-people-fill"></i> Personas que Han Votado
                        </h5>
                        <p class="mb-0 opacity-75 small">
                            Define cuántas personas únicas quieres que aparezcan en las estadísticas
                        </p>
                    </div>
                    <div class="col-md-6">
                        <div class="bg-white bg-opacity-25 rounded-3 p-3">
                            <label for="unique_voters" class="form-label mb-2 fw-semibold">
                                Número de Personas Únicas
                            </label>
                            <div class="input-group input-group-lg">
                                <button type="button" class="btn btn-light" onclick="decrementUniqueVoters()">
                                    <i class="bi bi-dash-lg"></i>
                                </button>
                                <input type="number"
                                       class="form-control text-center fw-bold fs-3 bg-white"
                                       id="unique_voters"
                                       name="unique_voters"
                                       value="{{ old('unique_voters', $currentUniqueVoters) }}"
                                       min="0"
                                       max="999999"
                                       required>
                                <button type="button" class="btn btn-light" onclick="incrementUniqueVoters()">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </div>
                            <small class="d-block mt-2 opacity-75">
                                Actual: <strong>{{ $currentUniqueVoters }}</strong> personas
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @foreach($votesData as $questionIndex => $question)
            <div class="modern-card mb-4">
                <div style="border-bottom: 1px solid #e2e8f0; padding: 1.25rem; background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);">
                    <h5 class="mb-0 fw-bold" style="color: #667eea;">
                        <i class="bi bi-question-circle-fill"></i>
                        Pregunta {{ $questionIndex + 1 }}: {{ $question['question_text'] }}
                    </h5>
                </div>
                <div style="padding: 1.5rem;">
                    <div class="row g-3">
                        @foreach($question['options'] as $option)
                            <div class="col-md-6">
                                <div class="option-card p-3 border rounded-3 h-100"
                                     style="border-left: 4px solid {{ $option['color'] ?? '#6c757d' }} !important;">
                                    <label for="vote_{{ $option['id'] }}" class="form-label fw-semibold mb-3">
                                        <span class="badge rounded-pill me-2"
                                              style="background-color: {{ $option['color'] ?? '#6c757d' }}">
                                            &nbsp;
                                        </span>
                                        {{ $option['option_text'] }}
                                    </label>

                                    <div class="input-group input-group-lg">
                                        <button type="button" class="btn btn-outline-danger"
                                                onclick="decrementVote({{ $option['id'] }})">
                                            <i class="bi bi-dash-lg"></i>
                                        </button>
                                        <input type="number"
                                               class="form-control text-center fw-bold fs-4"
                                               id="vote_{{ $option['id'] }}"
                                               name="votes[{{ $option['id'] }}]"
                                               value="{{ old('votes.' . $option['id'], $option['vote_count']) }}"
                                               min="0"
                                               max="999999"
                                               required>
                                        <button type="button" class="btn btn-outline-success"
                                                onclick="incrementVote({{ $option['id'] }})">
                                            <i class="bi bi-plus-lg"></i>
                                        </button>
                                    </div>

                                    <small class="text-muted mt-2 d-block">
                                        <i class="bi bi-info-circle"></i>
                                        Votos actuales: <strong>{{ $option['vote_count'] }}</strong>
                                    </small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach

        <!-- Botones de acción -->
        <div class="modern-card sticky-bottom">
            <div style="padding: 1.25rem;">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div class="text-muted">
                        <i class="bi bi-shield-check"></i>
                        Revisa los cambios antes de guardar
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.surveys.show', $survey) }}" class="btn btn-sm"
                           style="background: #f1f5f9; color: #64748b; border: none; padding: 0.5rem 0.875rem; border-radius: 8px; font-weight: 500;">
                            <i class="bi bi-x-lg"></i> Cancelar
                        </a>
                        <button type="button" class="btn btn-sm" onclick="resetForm()"
                                style="background: linear-gradient(135deg, rgba(240, 147, 251, 0.15) 0%, rgba(245, 87, 108, 0.15) 100%); color: #f093fb; border: 1px solid rgba(240, 147, 251, 0.3); padding: 0.5rem 0.875rem; border-radius: 8px; font-weight: 500;">
                            <i class="bi bi-arrow-counterclockwise"></i> Restablecer
                        </button>
                        <button type="submit" class="btn btn-gradient-primary" style="padding: 0.5rem 1rem;">
                            <i class="bi bi-save"></i> Guardar Cambios
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
.option-card {
    transition: all 0.3s ease;
}

.option-card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.sticky-bottom {
    position: sticky;
    bottom: 0;
    z-index: 1020;
    box-shadow: 0 -0.5rem 1rem rgba(0, 0, 0, 0.1);
}

input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
    opacity: 1;
}
</style>

<script>
// Guardar valores originales
const originalValues = {};
let originalUniqueVoters = 0;
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('input[name^="votes"]').forEach(input => {
        originalValues[input.id] = input.value;
    });
    originalUniqueVoters = document.getElementById('unique_voters').value;
});

// Incrementar/Decrementar personas únicas
function incrementUniqueVoters() {
    const input = document.getElementById('unique_voters');
    const currentValue = parseInt(input.value) || 0;
    if (currentValue < 999999) {
        input.value = currentValue + 1;
        highlightChange(input);
    }
}

function decrementUniqueVoters() {
    const input = document.getElementById('unique_voters');
    const currentValue = parseInt(input.value) || 0;
    if (currentValue > 0) {
        input.value = currentValue - 1;
        highlightChange(input);
    }
}

// Incrementar votos
function incrementVote(optionId) {
    const input = document.getElementById('vote_' + optionId);
    const currentValue = parseInt(input.value) || 0;
    if (currentValue < 999999) {
        input.value = currentValue + 1;
        highlightChange(input);
    }
}

// Decrementar votos
function decrementVote(optionId) {
    const input = document.getElementById('vote_' + optionId);
    const currentValue = parseInt(input.value) || 0;
    if (currentValue > 0) {
        input.value = currentValue - 1;
        highlightChange(input);
    }
}

// Resaltar cambios
function highlightChange(input) {
    if (input.value !== originalValues[input.id]) {
        input.classList.add('border-warning');
        input.classList.add('bg-warning-subtle');
    } else {
        input.classList.remove('border-warning');
        input.classList.remove('bg-warning-subtle');
    }
}

// Restablecer formulario
function resetForm() {
    if (confirm('¿Estás seguro de que deseas restablecer todos los valores?')) {
        document.querySelectorAll('input[name^="votes"]').forEach(input => {
            input.value = originalValues[input.id];
            input.classList.remove('border-warning');
            input.classList.remove('bg-warning-subtle');
        });
        document.getElementById('unique_voters').value = originalUniqueVoters;
        highlightChange(document.getElementById('unique_voters'));
    }
}

// Monitorear cambios en inputs
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('input[name^="votes"]').forEach(input => {
        input.addEventListener('input', function() {
            highlightChange(this);
        });
    });
});

// Confirmar antes de salir con cambios sin guardar
window.addEventListener('beforeunload', function (e) {
    let hasChanges = false;
    document.querySelectorAll('input[name^="votes"]').forEach(input => {
        if (input.value !== originalValues[input.id]) {
            hasChanges = true;
        }
    });

    if (hasChanges) {
        e.preventDefault();
        e.returnValue = '';
    }
});

// No preguntar al enviar el formulario
document.getElementById('editVotesForm').addEventListener('submit', function() {
    window.removeEventListener('beforeunload', function() {});
});
</script>
@endsection
