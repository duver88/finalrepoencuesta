@extends('layouts.admin')

@section('title', 'Crear Encuesta')

@section('content')
<div class="container-fluid px-0">
    <div class="mb-4">
        <h1 class="h2 fw-bold mb-1" style="color: #1e293b;">
            <i class="bi bi-plus-circle"></i> Crear Nueva Encuesta
        </h1>
        <p class="text-muted mb-0">Completa el formulario para crear una encuesta</p>
    </div>

    <div class="modern-card">
        <div style="padding: 1.5rem;">
            <form method="POST" action="{{ route('admin.surveys.store') }}" enctype="multipart/form-data" id="surveyForm">
                @csrf

                <!-- Título -->
                <div class="mb-3">
                    <label for="title" class="form-label fw-semibold">
                        <i class="bi bi-card-heading"></i> Título de la Encuesta *
                    </label>
                    <input type="text" class="form-control" name="title" id="title" required
                           placeholder="Ej: Encuesta de Satisfacción 2025">
                    @error('title')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Descripción -->
                <div class="mb-3">
                    <label for="description" class="form-label fw-semibold">
                        <i class="bi bi-textarea-t"></i> Descripción
                    </label>
                    <textarea class="form-control" name="description" id="description" rows="3"
                              placeholder="Descripción breve de la encuesta (opcional)"></textarea>
                </div>

                <!-- Banner -->
                <div class="mb-4">
                    <label for="banner" class="form-label fw-semibold">
                        <i class="bi bi-image"></i> Banner/Imagen de la Encuesta
                    </label>
                    <input type="file" class="form-control" name="banner" id="banner" accept="image/*">
                    <small class="text-muted">Imagen que se muestra en la página de la encuesta</small>
                </div>

                <!-- Banner para Facebook/Open Graph -->
                <div class="mb-4">
                    <label for="og_image" class="form-label fw-semibold">
                        <i class="bi bi-facebook"></i> Imagen para Facebook (Open Graph)
                    </label>
                    <input type="file" class="form-control" name="og_image" id="og_image" accept="image/*">
                    <div class="alert alert-info mt-2 py-2">
                        <small>
                            <i class="bi bi-info-circle"></i> <strong>Recomendado:</strong> 1200x630 píxeles para Facebook, WhatsApp y redes sociales.
                            <br>Si no subes una imagen, se usará el banner principal.
                        </small>
                    </div>
                </div>

                <!-- Mostrar Resultados -->
                <div class="mb-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="show_results" id="show_results" value="1" checked>
                        <label class="form-check-label fw-semibold" for="show_results">
                            <i class="bi bi-bar-chart"></i> Mostrar resultados después de votar
                        </label>
                    </div>
                    <small class="text-muted ms-4">Si está activado, los usuarios verán los resultados en tiempo real después de votar.</small>
                </div>

                <!-- 2 Votos por Minuto por Opción -->
                <div class="mb-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="one_vote_per_minute_per_option" id="one_vote_per_minute_per_option" value="1">
                        <label class="form-check-label fw-semibold" for="one_vote_per_minute_per_option">
                            <i class="bi bi-clock-history"></i> Limitar a 2 votos por minuto por opción
                        </label>
                    </div>
                    <small class="text-muted ms-4">Si está activado, cada opción solo podrá recibir 2 votos válidos por minuto. Los votos adicionales se guardarán como no válidos.</small>
                </div>

                <hr class="my-4">

                <!-- Preguntas -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0 fw-bold" style="color: #1e293b;">
                            <i class="bi bi-question-circle"></i> Preguntas
                        </h5>
                        <button type="button" onclick="addQuestion()" class="btn btn-sm"
                                style="background: linear-gradient(135deg, rgba(17, 153, 142, 0.15) 0%, rgba(56, 239, 125, 0.15) 100%); color: #11998e; border: 1px solid rgba(17, 153, 142, 0.3); padding: 0.5rem 0.875rem; border-radius: 8px; font-weight: 500;">
                            <i class="bi bi-plus-circle"></i> Agregar Pregunta
                        </button>
                    </div>

                    <div id="questionsContainer">
                        <!-- Las preguntas se agregarán dinámicamente aquí -->
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="d-flex gap-2 justify-content-end mt-4">
                    <a href="{{ route('admin.surveys.index') }}" class="btn btn-sm"
                       style="background: #f1f5f9; color: #64748b; border: none; padding: 0.5rem 0.875rem; border-radius: 8px; font-weight: 500;">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-sm btn-gradient-primary">
                        <i class="bi bi-check-circle"></i> Crear Encuesta
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let questionCount = 0;

function addQuestion() {
    const container = document.getElementById('questionsContainer');
    const questionHtml = `
        <div class="modern-card mb-3 question-block" data-question="${questionCount}">
            <div class="d-flex justify-content-between align-items-center" style="border-bottom: 1px solid #e2e8f0; padding: 1rem; background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);">
                <h6 class="mb-0 fw-bold" style="color: #667eea;">Pregunta ${questionCount + 1}</h6>
                <button type="button" onclick="removeQuestion(${questionCount})" class="btn btn-sm" style="background: linear-gradient(135deg, rgba(238, 9, 121, 0.15) 0%, rgba(255, 106, 0, 0.15) 100%); color: #ee0979; border: 1px solid rgba(238, 9, 121, 0.3); padding: 0.375rem 0.75rem; border-radius: 8px; font-weight: 500;">
                    <i class="bi bi-trash"></i> Eliminar
                </button>
            </div>
            <div style="padding: 1.5rem;">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Texto de la Pregunta *</label>
                    <input type="text" name="questions[${questionCount}][question_text]" required
                           class="form-control" placeholder="Escribe tu pregunta aquí">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Tipo de Pregunta *</label>
                    <select name="questions[${questionCount}][question_type]" required
                            class="form-select">
                        <option value="single_choice">Opción Única (radio)</option>
                        <option value="multiple_choice">Opción Múltiple (checkbox)</option>
                    </select>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label fw-semibold mb-0">Opciones de Respuesta *</label>
                        <button type="button" onclick="addOption(${questionCount})" class="btn btn-sm" style="background: linear-gradient(135deg, rgba(102, 126, 234, 0.15) 0%, rgba(118, 75, 162, 0.15) 100%); color: #667eea; border: 1px solid rgba(102, 126, 234, 0.3); padding: 0.375rem 0.75rem; border-radius: 8px; font-weight: 500;">
                            <i class="bi bi-plus"></i> Agregar Opción
                        </button>
                    </div>
                    <div id="optionsContainer${questionCount}">
                        <!-- Opciones se agregarán aquí -->
                    </div>
                </div>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', questionHtml);

    // Agregar 2 opciones por defecto
    addOption(questionCount);
    addOption(questionCount);

    questionCount++;
}

function removeQuestion(index) {
    const questionBlock = document.querySelector(`[data-question="${index}"]`);
    questionBlock.remove();
}

let optionCounters = {};

function addOption(questionIndex) {
    if (!optionCounters[questionIndex]) {
        optionCounters[questionIndex] = 0;
    }

    const container = document.getElementById(`optionsContainer${questionIndex}`);
    const optionIndex = optionCounters[questionIndex];

    const optionHtml = `
        <div class="mb-3 option-row" data-option="${optionIndex}">
            <div class="input-group mb-2">
                <span class="input-group-text bg-light">${optionIndex + 1}</span>
                <input type="text" name="questions[${questionIndex}][options][]" required
                       placeholder="Texto de la opción"
                       class="form-control">
                <input type="color" name="questions[${questionIndex}][colors][]"
                       class="form-control form-control-color"
                       value="#${Math.floor(Math.random()*16777215).toString(16).padStart(6, '0')}"
                       title="Elige un color para esta opción">
                <button type="button" onclick="removeOption(this)" class="btn" style="background: linear-gradient(135deg, rgba(238, 9, 121, 0.15) 0%, rgba(255, 106, 0, 0.15) 100%); color: #ee0979; border: 1px solid rgba(238, 9, 121, 0.3);">
                    <i class="bi bi-x"></i>
                </button>
            </div>
            <div class="ms-4">
                <label class="form-label small text-muted mb-1">
                    <i class="bi bi-image"></i> Imagen de la opción (opcional, 800x800px recomendado)
                </label>
                <input type="file" name="questions[${questionIndex}][option_images][]"
                       class="form-control form-control-sm" accept="image/*">
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', optionHtml);
    optionCounters[questionIndex]++;
}

function removeOption(button) {
    button.closest('.option-row').remove();
}

// Agregar primera pregunta al cargar
document.addEventListener('DOMContentLoaded', function() {
    addQuestion();
});
</script>
@endsection
