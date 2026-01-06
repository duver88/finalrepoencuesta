@extends('layouts.admin')

@section('title', 'Editar Encuesta')

@section('content')
<div class="container-fluid px-0">
    <div class="mb-4">
        <h1 class="h2 fw-bold mb-1" style="color: #1e293b;">
            <i class="bi bi-pencil"></i> Editar Encuesta
        </h1>
        <p class="text-muted mb-0">Actualiza la información de la encuesta</p>
    </div>

    <div class="modern-card">
        <div style="padding: 1.5rem;">
            <form method="POST" action="{{ route('admin.surveys.update', $survey) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Título -->
                <div class="mb-3">
                    <label for="title" class="form-label fw-semibold">
                        <i class="bi bi-card-heading"></i> Título de la Encuesta *
                    </label>
                    <input type="text" class="form-control" name="title" id="title"
                           value="{{ old('title', $survey->title) }}" required>
                    @error('title')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Descripción -->
                <div class="mb-3">
                    <label for="description" class="form-label fw-semibold">
                        <i class="bi bi-textarea-t"></i> Descripción
                    </label>
                    <textarea class="form-control" name="description" id="description" rows="3">{{ old('description', $survey->description) }}</textarea>
                </div>

                <!-- Banner -->
                <div class="mb-4">
                    <label for="banner" class="form-label fw-semibold">
                        <i class="bi bi-image"></i> Banner/Imagen de la Encuesta
                    </label>
                    @if($survey->banner)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $survey->banner) }}" alt="Banner actual"
                                 class="img-thumbnail" style="max-height: 150px;">
                            <p class="small text-muted mt-1">Banner actual (sube una nueva imagen para reemplazarla)</p>
                        </div>
                    @endif
                    <input type="file" class="form-control" name="banner" id="banner" accept="image/*">
                    <small class="text-muted">Imagen que se muestra en la página de la encuesta</small>
                </div>

                <!-- Banner para Facebook/Open Graph -->
                <div class="mb-4">
                    <label for="og_image" class="form-label fw-semibold">
                        <i class="bi bi-facebook"></i> Imagen para Facebook (Open Graph)
                    </label>
                    @if($survey->og_image)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $survey->og_image) }}" alt="Imagen OG actual"
                                 class="img-thumbnail" style="max-height: 150px;">
                            <p class="small text-muted mt-1">Imagen actual para redes sociales (sube una nueva para reemplazarla)</p>
                        </div>
                    @endif
                    <input type="file" class="form-control" name="og_image" id="og_image" accept="image/*">
                    <div class="alert alert-info mt-2 py-2">
                        <small>
                            <i class="bi bi-info-circle"></i> <strong>Recomendado:</strong> 1200x630 píxeles para Facebook, WhatsApp y redes sociales.
                            <br>Si no subes una imagen, se usará el banner principal.
                        </small>
                    </div>
                </div>

                <!-- Estado -->
                <div class="mb-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                               value="1" {{ old('is_active', $survey->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="is_active">
                            <i class="bi bi-toggle-on"></i> Encuesta Activa
                        </label>
                        <small class="d-block text-muted">Las encuestas inactivas no pueden recibir votos</small>
                    </div>
                </div>

                <!-- Mostrar Resultados -->
                <div class="mb-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="show_results" id="show_results"
                               value="1" {{ old('show_results', $survey->show_results) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="show_results">
                            <i class="bi bi-bar-chart"></i> Mostrar resultados después de votar
                        </label>
                    </div>
                    <small class="text-muted ms-4">Si está activado, los usuarios verán los resultados en tiempo real después de votar.</small>
                </div>

                <!-- Modo de visualización de resultados -->
                <div class="mb-4">
                    <label for="results_display_mode" class="form-label fw-semibold">
                        <i class="bi bi-layout-text-window"></i> Modo de visualización de resultados
                    </label>
                    <select class="form-select" name="results_display_mode" id="results_display_mode">
                        <option value="all" {{ old('results_display_mode', $survey->results_display_mode ?? 'all') == 'all' ? 'selected' : '' }}>
                            Mostrar todas las preguntas a la vez
                        </option>
                        <option value="collapsible" {{ old('results_display_mode', $survey->results_display_mode ?? 'all') == 'collapsible' ? 'selected' : '' }}>
                            Mostrar preguntas desplegables (acordeón)
                        </option>
                    </select>
                    <small class="text-muted">Selecciona cómo se mostrarán los resultados de la encuesta.</small>
                </div>

                <!-- 2 Votos por Minuto por Opción -->
                <div class="mb-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="one_vote_per_minute_per_option" id="one_vote_per_minute_per_option"
                               value="1" {{ old('one_vote_per_minute_per_option', $survey->one_vote_per_minute_per_option) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="one_vote_per_minute_per_option">
                            <i class="bi bi-clock-history"></i> Limitar a 2 votos por minuto por opción
                        </label>
                    </div>
                    <small class="text-muted ms-4">Si está activado, cada opción solo podrá recibir 2 votos válidos por minuto. Los votos adicionales se guardarán como no válidos.</small>
                </div>

                <hr class="my-4">

                <!-- Preguntas -->
                <div class="mb-4">
                    <h5 class="mb-3 fw-semibold">
                        <i class="bi bi-question-circle"></i> Preguntas
                        <small class="text-muted ms-2">
                            <i class="bi bi-grip-vertical"></i> Arrastra para reordenar
                        </small>
                    </h5>

                    <div id="questions-sortable-container">
                    @foreach($survey->questions as $qIndex => $question)
                        @php
                            $questionHasVotes = $question->votes()->count() > 0;
                        @endphp
                        <div class="card mb-3 border" id="question-card-{{ $qIndex }}">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center draggable-handle" style="cursor: move;">
                                <h6 class="mb-0 fw-semibold">
                                    <i class="bi bi-grip-vertical text-muted me-2"></i>
                                    Pregunta {{ $qIndex + 1 }}
                                    @if($questionHasVotes)
                                        <span class="badge bg-warning text-dark ms-2">
                                            <i class="bi bi-exclamation-triangle-fill"></i> {{ $question->votes()->count() }} votos
                                        </span>
                                    @endif
                                </h6>
                                <button type="button" class="btn btn-sm btn-danger" onclick="deleteExistingQuestion({{ $qIndex }}, '{{ addslashes($question->question_text) }}', {{ $question->id }}, {{ $questionHasVotes ? 'true' : 'false' }}, {{ $question->votes()->count() }})">
                                    <i class="bi bi-trash"></i> Eliminar
                                </button>
                            </div>
                            <div class="card-body">
                                <input type="hidden" name="questions[{{ $qIndex }}][id]" value="{{ $question->id }}" class="question-id-{{ $qIndex }}">

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Texto de la Pregunta *</label>
                                    <input type="text" name="questions[{{ $qIndex }}][question_text]"
                                           value="{{ old('questions.'.$qIndex.'.question_text', $question->question_text) }}"
                                           required class="form-control">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Tipo de Pregunta *</label>
                                    <select name="questions[{{ $qIndex }}][question_type]" required class="form-select">
                                        <option value="single_choice" {{ $question->question_type == 'single_choice' ? 'selected' : '' }}>
                                            Opción Única (radio)
                                        </option>
                                        <option value="multiple_choice" {{ $question->question_type == 'multiple_choice' ? 'selected' : '' }}>
                                            Opción Múltiple (checkbox)
                                        </option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Modo de Visualización *</label>
                                    <select name="questions[{{ $qIndex }}][display_mode]" required class="form-select">
                                        <option value="buttons" {{ ($question->display_mode ?? 'buttons') == 'buttons' ? 'selected' : '' }}>
                                            Botones (mostrar todas las opciones)
                                        </option>
                                        <option value="dropdown" {{ ($question->display_mode ?? 'buttons') == 'dropdown' ? 'selected' : '' }}>
                                            Desplegable (select - ideal para muchas opciones)
                                        </option>
                                    </select>
                                    <small class="text-muted">Elige "Desplegable" si la pregunta tiene muchas opciones.</small>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="questions[{{ $qIndex }}][randomize_options]"
                                               id="randomize{{ $qIndex }}" value="1" {{ old('questions.'.$qIndex.'.randomize_options', $question->randomize_options ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold" for="randomize{{ $qIndex }}">
                                            <i class="bi bi-shuffle"></i> Mostrar opciones en orden aleatorio
                                        </label>
                                    </div>
                                    <small class="text-muted">Si está activado, las opciones se mostrarán en orden aleatorio a cada votante.</small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Opciones de Respuesta *</label>
                                    <div id="options-container-{{ $qIndex }}">
                                        @foreach($question->options as $oIndex => $option)
                                            @php
                                                $optionHasVotes = $option->votes->count() > 0;
                                            @endphp
                                            <div class="mb-3 option-row" id="option-row-{{ $qIndex }}-{{ $oIndex }}">
                                                <div class="input-group mb-2" style="cursor: move;">
                                                    <span class="input-group-text bg-light draggable-option-handle">
                                                        <i class="bi bi-grip-vertical"></i>
                                                    </span>
                                                    <span class="input-group-text bg-light">{{ $oIndex + 1 }}</span>
                                                    <input type="hidden" name="questions[{{ $qIndex }}][options][{{ $oIndex }}][id]" value="{{ $option->id }}" class="option-id-{{ $qIndex }}-{{ $oIndex }}">
                                                    <input type="text" name="questions[{{ $qIndex }}][options][{{ $oIndex }}][option_text]"
                                                           value="{{ old('questions.'.$qIndex.'.options.'.$oIndex.'.option_text', $option->option_text) }}"
                                                           required placeholder="Texto de la opción" class="form-control">
                                                    <input type="color" name="questions[{{ $qIndex }}][options][{{ $oIndex }}][color]"
                                                           class="form-control form-control-color"
                                                           value="{{ old('questions.'.$qIndex.'.options.'.$oIndex.'.color', $option->color ?? '#3b82f6') }}"
                                                           title="Elige un color para esta opción">
                                                    @if($optionHasVotes)
                                                        <span class="input-group-text bg-warning text-dark" title="Esta opción tiene {{ $option->votes->count() }} voto(s)">
                                                            <i class="bi bi-exclamation-triangle-fill"></i> {{ $option->votes->count() }}
                                                        </span>
                                                    @endif
                                                    <button type="button" class="btn btn-danger" onclick="deleteExistingOption({{ $qIndex }}, {{ $oIndex }}, '{{ addslashes($option->option_text) }}', {{ $option->id }}, {{ $optionHasVotes ? 'true' : 'false' }}, {{ $option->votes->count() }})">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                                <div class="ms-5">
                                                    @if($option->image)
                                                        <div class="mb-2">
                                                            <img src="{{ asset('storage/' . $option->image) }}" alt="Imagen de opción" class="img-thumbnail" style="max-width: 100px; max-height: 100px;">
                                                            <small class="d-block text-muted">Imagen actual</small>
                                                        </div>
                                                    @endif
                                                    <label class="form-label small text-muted mb-1">
                                                        <i class="bi bi-image"></i> Cambiar/agregar imagen (opcional, 800x800px recomendado)
                                                    </label>
                                                    <input type="file" name="questions[{{ $qIndex }}][options][{{ $oIndex }}][image]"
                                                           class="form-control form-control-sm" accept="image/*">
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <button type="button" class="btn btn-sm btn-success mt-2" onclick="addNewOption({{ $qIndex }}, {{ $question->options->count() }})">
                                        <i class="bi bi-plus-circle"></i> Agregar Nueva Opción
                                    </button>
                                    <small class="d-block text-muted mt-2">
                                        <i class="bi bi-info-circle"></i> Las opciones con <i class="bi bi-lock-fill"></i> tienen votos y se mantienen intactas. Las nuevas opciones empiezan con 0 votos.
                                    </small>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    </div>

                    <div id="new-questions-container"></div>

                    <button type="button" class="btn btn-sm mt-3" onclick="addNewQuestion()"
                            style="background: linear-gradient(135deg, rgba(17, 153, 142, 0.15) 0%, rgba(56, 239, 125, 0.15) 100%); color: #11998e; border: 1px solid rgba(17, 153, 142, 0.3); padding: 0.5rem 0.875rem; border-radius: 8px; font-weight: 500;">
                        <i class="bi bi-plus-circle-fill"></i> Agregar Nueva Pregunta
                    </button>

                    <div class="alert alert-info mt-3" role="alert">
                        <i class="bi bi-info-circle-fill"></i>
                        <strong>¡Sistema de edición completamente flexible!</strong>
                        <ul class="mb-0 mt-2">
                            <li>✅ <strong>Agregar:</strong> Nuevas preguntas y opciones a encuestas publicadas</li>
                            <li>✅ <strong>Editar:</strong> Texto y colores de preguntas/opciones existentes</li>
                            <li>🗑️ <strong>Eliminar:</strong> CUALQUIER pregunta u opción (incluso con votos)</li>
                            <li>⚠️ <strong>Con votos:</strong> Badge amarillo indica que tiene votos (se pueden eliminar igual)</li>
                            <li>📊 <strong>Los votos se conservan:</strong> Al eliminar, los votos quedan en BD pero ocultos de resultados</li>
                            <li>↩️ <strong>Reversible:</strong> Puedes restaurar antes de guardar (botón amarillo)</li>
                            <li>🔄 <strong>Reordenar:</strong> Arrastra preguntas y opciones para cambiar el orden</li>
                        </ul>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="d-flex gap-2 justify-content-end mt-4">
                    <a href="{{ route('admin.surveys.show', $survey) }}" class="btn btn-sm"
                       style="background: #f1f5f9; color: #64748b; border: none; padding: 0.5rem 0.875rem; border-radius: 8px; font-weight: 500;">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-sm btn-gradient-primary">
                        <i class="bi bi-check-circle"></i> Actualizar Encuesta
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let questionCounter = {{ $survey->questions->count() }};
let newQuestionIndex = {{ $survey->questions->count() }};

// Función para agregar nueva opción a una pregunta existente
function addNewOption(questionIndex, currentOptionCount) {
    const container = document.getElementById(`options-container-${questionIndex}`);
    const newOptionIndex = currentOptionCount;

    const newOption = document.createElement('div');
    newOption.className = 'mb-3 option-row';
    newOption.innerHTML = `
        <div class="input-group mb-2" style="cursor: move;">
            <span class="input-group-text bg-light draggable-option-handle">
                <i class="bi bi-grip-vertical"></i>
            </span>
            <span class="input-group-text bg-light">${newOptionIndex + 1}</span>
            <input type="text" name="questions[${questionIndex}][options][${newOptionIndex}][option_text]"
                   required placeholder="Texto de la nueva opción" class="form-control">
            <input type="color" name="questions[${questionIndex}][options][${newOptionIndex}][color]"
                   class="form-control form-control-color"
                   value="#3b82f6"
                   title="Elige un color para esta opción">
            <button type="button" class="btn" style="background: linear-gradient(135deg, rgba(238, 9, 121, 0.15) 0%, rgba(255, 106, 0, 0.15) 100%); color: #ee0979; border: 1px solid rgba(238, 9, 121, 0.3);" onclick="this.parentElement.parentElement.remove(); renumberOptions(${questionIndex})">
                <i class="bi bi-trash"></i>
            </button>
        </div>
        <div class="ms-5">
            <label class="form-label small text-muted mb-1">
                <i class="bi bi-image"></i> Imagen de la opción (opcional, 800x800px recomendado)
            </label>
            <input type="file" name="questions[${questionIndex}][options][${newOptionIndex}][image]"
                   class="form-control form-control-sm" accept="image/*">
        </div>
    `;

    container.appendChild(newOption);

    // Actualizar el contador en el botón
    const button = container.nextElementSibling;
    button.setAttribute('onclick', `addNewOption(${questionIndex}, ${newOptionIndex + 1})`);

    renumberOptions(questionIndex);
}

// Función para renumerar opciones
function renumberOptions(questionIndex) {
    const container = document.getElementById(`options-container-${questionIndex}`);
    const options = container.querySelectorAll('.option-row');
    options.forEach((option, index) => {
        // Buscar el segundo span (el que contiene el número, no el del icono)
        const numberSpans = option.querySelectorAll('.input-group-text');
        if (numberSpans.length > 1) {
            numberSpans[1].textContent = index + 1;
        }
    });
}

// Función para agregar nueva pregunta
function addNewQuestion() {
    const container = document.getElementById('questions-sortable-container');

    const newQuestion = document.createElement('div');
    newQuestion.className = 'card mb-3 border border-primary';
    newQuestion.innerHTML = `
        <div class="card-header bg-primary bg-gradient text-white d-flex justify-content-between align-items-center draggable-handle" style="cursor: move;">
            <h6 class="mb-0 fw-semibold">
                <i class="bi bi-grip-vertical me-2"></i>
                <i class="bi bi-plus-circle-fill"></i> Nueva Pregunta ${questionCounter + 1}
            </h6>
            <button type="button" class="btn btn-sm btn-danger" onclick="this.closest('.card').remove(); updateQuestionIndices();">
                <i class="bi bi-trash"></i> Eliminar
            </button>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label fw-semibold">Texto de la Pregunta *</label>
                <input type="text" name="questions[${newQuestionIndex}][question_text]"
                       required class="form-control" placeholder="Escribe tu pregunta aquí">
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Tipo de Pregunta *</label>
                <select name="questions[${newQuestionIndex}][question_type]" required class="form-select">
                    <option value="single_choice">Opción Única (radio)</option>
                    <option value="multiple_choice">Opción Múltiple (checkbox)</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Modo de Visualización *</label>
                <select name="questions[${newQuestionIndex}][display_mode]" required class="form-select">
                    <option value="buttons">Botones (mostrar todas las opciones)</option>
                    <option value="dropdown">Desplegable (select - ideal para muchas opciones)</option>
                </select>
                <small class="text-muted">Elige "Desplegable" si la pregunta tiene muchas opciones.</small>
            </div>

            <div class="mb-3">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="questions[${newQuestionIndex}][randomize_options]"
                           id="randomize${newQuestionIndex}" value="1" checked>
                    <label class="form-check-label fw-semibold" for="randomize${newQuestionIndex}">
                        <i class="bi bi-shuffle"></i> Mostrar opciones en orden aleatorio
                    </label>
                </div>
                <small class="text-muted">Si está activado, las opciones se mostrarán en orden aleatorio a cada votante.</small>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Opciones de Respuesta *</label>
                <div id="new-options-container-${newQuestionIndex}">
                    <div class="input-group mb-2" style="cursor: move;">
                        <span class="input-group-text bg-light draggable-option-handle">
                            <i class="bi bi-grip-vertical"></i>
                        </span>
                        <span class="input-group-text bg-light">1</span>
                        <input type="text" name="questions[${newQuestionIndex}][options][0][option_text]"
                               required placeholder="Primera opción" class="form-control">
                        <input type="color" name="questions[${newQuestionIndex}][options][0][color]"
                               class="form-control form-control-color" value="#3b82f6">
                    </div>
                    <div class="input-group mb-2" style="cursor: move;">
                        <span class="input-group-text bg-light draggable-option-handle">
                            <i class="bi bi-grip-vertical"></i>
                        </span>
                        <span class="input-group-text bg-light">2</span>
                        <input type="text" name="questions[${newQuestionIndex}][options][1][option_text]"
                               required placeholder="Segunda opción" class="form-control">
                        <input type="color" name="questions[${newQuestionIndex}][options][1][color]"
                               class="form-control form-control-color" value="#10b981">
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-success mt-2" onclick="addOptionToNewQuestion(${newQuestionIndex}, 2)">
                    <i class="bi bi-plus-circle"></i> Agregar Opción
                </button>
            </div>
        </div>
    `;

    container.appendChild(newQuestion);
    questionCounter++;

    // Inicializar Sortable para las opciones de la nueva pregunta
    initializeOptionsSortable(newQuestionIndex);

    newQuestionIndex++;
}

// Función para agregar opción a una nueva pregunta
function addOptionToNewQuestion(questionIndex, optionCount) {
    const container = document.getElementById(`new-options-container-${questionIndex}`);

    const newOption = document.createElement('div');
    newOption.className = 'input-group mb-2';
    newOption.style.cursor = 'move';
    newOption.innerHTML = `
        <span class="input-group-text bg-light draggable-option-handle">
            <i class="bi bi-grip-vertical"></i>
        </span>
        <span class="input-group-text bg-light">${optionCount + 1}</span>
        <input type="text" name="questions[${questionIndex}][options][${optionCount}][option_text]"
               required placeholder="Opción ${optionCount + 1}" class="form-control">
        <input type="color" name="questions[${questionIndex}][options][${optionCount}][color]"
               class="form-control form-control-color" value="#${Math.floor(Math.random()*16777215).toString(16)}">
        <button type="button" class="btn btn-danger" onclick="this.parentElement.remove(); renumberNewOptions(${questionIndex})">
            <i class="bi bi-trash"></i>
        </button>
    `;

    container.appendChild(newOption);

    // Actualizar el botón
    const button = container.nextElementSibling;
    button.setAttribute('onclick', `addOptionToNewQuestion(${questionIndex}, ${optionCount + 1})`);

    renumberNewOptions(questionIndex);
}

// Renumerar opciones de nuevas preguntas
function renumberNewOptions(questionIndex) {
    const container = document.getElementById(`new-options-container-${questionIndex}`);
    const options = container.querySelectorAll('.input-group');
    options.forEach((option, index) => {
        // Buscar el segundo span (el que contiene el número, no el del icono)
        const numberSpans = option.querySelectorAll('.input-group-text');
        if (numberSpans.length > 1) {
            numberSpans[1].textContent = index + 1;
        }
    });
}

// ===================================================================
// FUNCIONES PARA ELIMINAR PREGUNTAS Y OPCIONES EXISTENTES
// ===================================================================

// Eliminar pregunta existente (con o sin votos)
function deleteExistingQuestion(questionIndex, questionText, questionId, hasVotes, voteCount) {
    let confirmMessage = '';

    if (hasVotes) {
        confirmMessage = `🔴 ¡ADVERTENCIA! Esta pregunta tiene ${voteCount} voto(s)\n\n` +
                        `"${questionText}"\n\n` +
                        `Si la eliminas:\n` +
                        `• Los ${voteCount} votos se conservarán en la base de datos\n` +
                        `• La pregunta NO aparecerá en los resultados\n` +
                        `• Esta acción es REVERSIBLE antes de guardar\n\n` +
                        `¿Deseas continuar?`;
    } else {
        confirmMessage = `⚠️ ¿Estás seguro de que deseas eliminar esta pregunta?\n\n"${questionText}"\n\nEsta acción es REVERSIBLE antes de guardar.`;
    }

    if (confirm(confirmMessage)) {
        const card = document.getElementById(`question-card-${questionIndex}`);
        if (card) {
            // Remover visualmente
            card.style.opacity = '0.5';
            card.style.pointerEvents = 'none';

            // Remover el ID del input hidden para que no se envíe en el formulario
            // Esto hará que el controlador la elimine
            const idInput = card.querySelector(`.question-id-${questionIndex}`);
            if (idInput) {
                idInput.remove();
            }

            // Marcar visualmente como eliminada
            const header = card.querySelector('.card-header');
            header.classList.add('bg-danger', 'text-white');
            header.innerHTML = `
                <h6 class="mb-0">
                    <i class="bi bi-trash-fill"></i> Pregunta marcada para eliminar
                </h6>
                <button type="button" class="btn btn-sm btn-warning" onclick="restoreQuestion(${questionIndex}, '${questionText.replace(/'/g, "\\'")}')">
                    <i class="bi bi-arrow-counterclockwise"></i> Restaurar
                </button>
            `;
        }
    }
}

// Restaurar pregunta marcada para eliminar
function restoreQuestion(questionIndex, questionText) {
    const card = document.getElementById(`question-card-${questionIndex}`);
    if (card) {
        card.style.opacity = '1';
        card.style.pointerEvents = 'auto';

        // Restaurar el header original
        const header = card.querySelector('.card-header');
        header.classList.remove('bg-danger', 'text-white');
        header.classList.add('bg-light');
        header.innerHTML = `
            <h6 class="mb-0 fw-semibold">Pregunta ${questionIndex + 1}</h6>
            <button type="button" class="btn btn-sm btn-danger" onclick="deleteExistingQuestion(${questionIndex}, '${questionText.replace(/'/g, "\\'")}', 0)">
                <i class="bi bi-trash"></i> Eliminar Pregunta
            </button>
        `;

        // Restaurar el input hidden del ID (necesitamos el ID original, lo añadimos de nuevo)
        // Nota: Si la pregunta fue guardada, tiene ID. Lo obtenemos del data attribute
        const cardBody = card.querySelector('.card-body');
        const existingInput = cardBody.querySelector('input[type="hidden"][name*="[id]"]');
        if (!existingInput) {
            // Restaurar desde el atributo data o dejar sin ID si es nueva
            const questionId = card.dataset.questionId || '';
            if (questionId) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `questions[${questionIndex}][id]`;
                input.value = questionId;
                input.className = `question-id-${questionIndex}`;
                cardBody.insertBefore(input, cardBody.firstChild);
            }
        }
    }
}

// Eliminar opción existente (con o sin votos)
function deleteExistingOption(questionIndex, optionIndex, optionText, optionId, hasVotes, voteCount) {
    let confirmMessage = '';

    if (hasVotes) {
        confirmMessage = `🔴 ¡ADVERTENCIA! Esta opción tiene ${voteCount} voto(s)\n\n` +
                        `"${optionText}"\n\n` +
                        `Si la eliminas:\n` +
                        `• Los ${voteCount} votos se conservarán en la base de datos\n` +
                        `• La opción NO aparecerá en los resultados\n` +
                        `• Esta acción es REVERSIBLE antes de guardar\n\n` +
                        `¿Deseas continuar?`;
    } else {
        confirmMessage = `⚠️ ¿Estás seguro de que deseas eliminar esta opción?\n\n"${optionText}"\n\nEsta acción es REVERSIBLE antes de guardar.`;
    }

    if (confirm(confirmMessage)) {
        const row = document.getElementById(`option-row-${questionIndex}-${optionIndex}`);
        if (row) {
            // Remover el ID del input hidden para que el controlador la elimine
            const idInput = row.querySelector(`.option-id-${questionIndex}-${optionIndex}`);
            if (idInput) {
                idInput.remove();
            }

            // Remover visualmente
            row.style.opacity = '0.3';
            row.style.textDecoration = 'line-through';
            row.style.pointerEvents = 'none';

            // Cambiar el fondo para indicar que será eliminada
            row.classList.add('bg-danger', 'bg-opacity-10');

            // Deshabilitar inputs
            const inputs = row.querySelectorAll('input');
            inputs.forEach(input => input.disabled = true);

            // Agregar botón de restaurar
            const deleteBtn = row.querySelector('.btn-danger');
            if (deleteBtn) {
                deleteBtn.outerHTML = `
                    <button type="button" class="btn btn-warning" onclick="restoreOption(${questionIndex}, ${optionIndex}, '${optionText.replace(/'/g, "\\'")}', ${optionId})">
                        <i class="bi bi-arrow-counterclockwise"></i> Restaurar
                    </button>
                `;
            }
        }

        renumberOptions(questionIndex);
    }
}

// Restaurar opción marcada para eliminar
function restoreOption(questionIndex, optionIndex, optionText, optionId) {
    const row = document.getElementById(`option-row-${questionIndex}-${optionIndex}`);
    if (row) {
        row.style.opacity = '1';
        row.style.textDecoration = 'none';
        row.style.pointerEvents = 'auto';
        row.classList.remove('bg-danger', 'bg-opacity-10');

        // Restaurar el input del ID
        const firstInput = row.querySelector('input');
        if (firstInput && !row.querySelector(`.option-id-${questionIndex}-${optionIndex}`)) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `questions[${questionIndex}][options][${optionIndex}][id]`;
            input.value = optionId;
            input.className = `option-id-${questionIndex}-${optionIndex}`;
            row.insertBefore(input, firstInput.nextSibling);
        }

        // Habilitar inputs
        const inputs = row.querySelectorAll('input');
        inputs.forEach(input => input.disabled = false);

        // Restaurar botón de eliminar
        const restoreBtn = row.querySelector('.btn-warning');
        if (restoreBtn) {
            restoreBtn.outerHTML = `
                <button type="button" class="btn btn-danger" onclick="deleteExistingOption(${questionIndex}, ${optionIndex}, '${optionText.replace(/'/g, "\\'")}', ${optionId})">
                    <i class="bi bi-trash"></i>
                </button>
            `;
        }
    }

    renumberOptions(questionIndex);
}

// ===================================================================
// DRAG & DROP CON SORTABLEJS
// ===================================================================

// Inicializar Sortable cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    initializeSortable();
});

function initializeSortable() {
    // Sortable para PREGUNTAS
    const questionsContainer = document.getElementById('questions-sortable-container');
    if (questionsContainer) {
        new Sortable(questionsContainer, {
            animation: 150,
            handle: '.draggable-handle',
            ghostClass: 'sortable-ghost',
            dragClass: 'sortable-drag',
            onEnd: function(evt) {
                updateQuestionIndices();
            }
        });
    }

    // Sortable para OPCIONES de cada pregunta existente
    @foreach($survey->questions as $qIndex => $question)
        initializeOptionsSortable({{ $qIndex }});
    @endforeach
}

function initializeOptionsSortable(questionIndex) {
    const optionsContainer = document.getElementById(`options-container-${questionIndex}`);
    if (optionsContainer) {
        new Sortable(optionsContainer, {
            animation: 150,
            handle: '.draggable-option-handle',
            ghostClass: 'sortable-ghost',
            dragClass: 'sortable-drag',
            onEnd: function(evt) {
                renumberOptions(questionIndex);
                updateOptionNames(questionIndex);
            }
        });
    }
}

// Actualizar índices de preguntas después de reordenar
function updateQuestionIndices() {
    const questionCards = document.querySelectorAll('#questions-sortable-container > .card');
    questionCards.forEach((card, newIndex) => {
        // Actualizar el número visual
        const header = card.querySelector('.card-header h6');
        if (header) {
            const currentText = header.innerHTML;
            const newText = currentText.replace(/Pregunta \d+/, `Pregunta ${newIndex + 1}`);
            header.innerHTML = newText;
        }

        // Actualizar los nombres de los inputs para mantener el orden correcto
        const inputs = card.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            if (input.name) {
                // Reemplazar el índice de la pregunta en el nombre
                input.name = input.name.replace(/questions\[\d+\]/, `questions[${newIndex}]`);
            }
        });
    });
}

// Actualizar nombres de inputs de opciones después de reordenar
function updateOptionNames(questionIndex) {
    const container = document.getElementById(`options-container-${questionIndex}`);
    const options = container.querySelectorAll('.option-row');

    options.forEach((option, newIndex) => {
        // Actualizar todos los inputs de esta opción
        const inputs = option.querySelectorAll('input');
        inputs.forEach(input => {
            if (input.name) {
                // Actualizar el índice de la opción en el nombre
                const regex = new RegExp(`questions\\[${questionIndex}\\]\\[options\\]\\[\\d+\\]`);
                input.name = input.name.replace(regex, `questions[${questionIndex}][options][${newIndex}]`);
            }

            // Actualizar clases que contengan índices
            if (input.className) {
                input.className = input.className.replace(/option-id-\d+-\d+/, `option-id-${questionIndex}-${newIndex}`);
            }
        });

        // Actualizar el ID del div
        option.id = `option-row-${questionIndex}-${newIndex}`;
    });
}
</script>

<!-- SortableJS CDN -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<style>
/* Estilos para el drag & drop */
.sortable-ghost {
    opacity: 0.4;
    background: #f8f9fa;
    border: 2px dashed #0d6efd;
}

.sortable-drag {
    opacity: 1;
    cursor: grabbing !important;
}

.draggable-handle:hover {
    background-color: #e9ecef !important;
    transition: background-color 0.2s;
}

.draggable-option-handle {
    cursor: grab;
}

.draggable-option-handle:active {
    cursor: grabbing;
}

.option-row:hover {
    background-color: #f8f9fa;
    transition: background-color 0.2s;
}
</style>

@endsection
