@extends('layouts.admin')

@section('title', 'Resultados - ' . $survey->title)

@section('content')
<div class="container-fluid px-0">
    <!-- Header Section -->
    <div class="mb-4 d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div>
            <h1 class="h2 fw-bold mb-1" style="color: #1e293b;">
                <i class="bi bi-bar-chart"></i> {{ $survey->title }}
            </h1>
            @if($survey->description)
                <p class="text-muted mb-0">{{ $survey->description }}</p>
            @endif
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ url('/t/' . $survey->public_slug) }}" target="_blank"
               class="btn btn-sm"
               style="background: linear-gradient(135deg, rgba(17, 153, 142, 0.15) 0%, rgba(56, 239, 125, 0.15) 100%); color: #11998e; border: 1px solid rgba(17, 153, 142, 0.3); padding: 0.5rem 0.875rem; border-radius: 8px; font-weight: 500;">
                <i class="bi bi-link-45deg"></i> <span class="d-none d-md-inline">Ver Pública</span>
            </a>
            <a href="{{ route('admin.surveys.edit', $survey) }}"
               class="btn btn-sm"
               style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 0.5rem 0.875rem; border-radius: 8px; font-weight: 500;">
                <i class="bi bi-pencil"></i> <span class="d-none d-md-inline">Editar</span>
            </a>
            <a href="{{ route('admin.surveys.report', $survey) }}"
               class="btn btn-sm"
               style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; border: none; padding: 0.5rem 0.875rem; border-radius: 8px; font-weight: 500;">
                <i class="bi bi-file-text-fill"></i> <span class="d-none d-md-inline">Reporte</span>
            </a>
            <a href="{{ route('admin.surveys.tokens.index', $survey) }}"
               class="btn btn-sm"
               style="background: linear-gradient(135deg, rgba(79, 172, 254, 0.15) 0%, rgba(0, 242, 254, 0.15) 100%); color: #4facfe; border: 1px solid rgba(79, 172, 254, 0.3); padding: 0.5rem 0.875rem; border-radius: 8px; font-weight: 500;">
                <i class="bi bi-key-fill"></i> <span class="d-none d-md-inline">Tokens</span>
            </a>
            <a href="{{ route('admin.surveys.votes.edit', $survey) }}"
               class="btn btn-sm"
               style="background: linear-gradient(135deg, rgba(240, 147, 251, 0.15) 0%, rgba(245, 87, 108, 0.15) 100%); color: #f093fb; border: 1px solid rgba(240, 147, 251, 0.3); padding: 0.5rem 0.875rem; border-radius: 8px; font-weight: 500;">
                <i class="bi bi-pencil-square"></i> <span class="d-none d-md-inline">Editar Votos</span>
            </a>
            @if($pendingReview > 0 || $invalidVotes > 0)
            <a href="{{ route('admin.surveys.suspicious-votes', $survey) }}"
               class="btn btn-sm position-relative"
               style="background: linear-gradient(135deg, rgba(255, 193, 7, 0.15) 0%, rgba(255, 152, 0, 0.15) 100%); color: #ff9800; border: 1px solid rgba(255, 152, 0, 0.3); padding: 0.5rem 0.875rem; border-radius: 8px; font-weight: 500;">
                <i class="bi bi-shield-exclamation"></i> <span class="d-none d-md-inline">Votos Sospechosos</span>
                @if($pendingReview > 0 || $invalidVotes > 0)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark" style="font-size: 0.7rem;">
                    {{ $pendingReview + $invalidVotes }}
                </span>
                @endif
            </a>
            @endif
            <form action="{{ route('admin.surveys.duplicate', $survey) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm"
                        style="background: linear-gradient(135deg, rgba(255, 184, 0, 0.15) 0%, rgba(255, 143, 0, 0.15) 100%); color: #ff8f00; border: 1px solid rgba(255, 143, 0, 0.3); padding: 0.5rem 0.875rem; border-radius: 8px; font-weight: 500;">
                    <i class="bi bi-files"></i> <span class="d-none d-md-inline">Duplicar</span>
                </button>
            </form>
            @if($survey->is_finished)
                <form action="{{ route('admin.surveys.unfinish', $survey) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm"
                            style="background: linear-gradient(135deg, rgba(79, 172, 254, 0.15) 0%, rgba(0, 242, 254, 0.15) 100%); color: #4facfe; border: 1px solid rgba(79, 172, 254, 0.3); padding: 0.5rem 0.875rem; border-radius: 8px; font-weight: 500;">
                        <i class="bi bi-arrow-counterclockwise"></i> <span class="d-none d-md-inline">Reactivar</span>
                    </button>
                </form>
                <a href="{{ route('surveys.finished', $survey->public_slug) }}" target="_blank"
                   class="btn btn-sm"
                   style="background: #f1f5f9; color: #64748b; border: none; padding: 0.5rem 0.875rem; border-radius: 8px; font-weight: 500;">
                    <i class="bi bi-eye-fill"></i> <span class="d-none d-md-inline">Ver Resultados</span>
                </a>
            @else
                <button type="button" class="btn btn-sm" onclick="confirmFinish()"
                        style="background: #f1f5f9; color: #64748b; border: none; padding: 0.5rem 0.875rem; border-radius: 8px; font-weight: 500;">
                    <i class="bi bi-check-circle"></i> <span class="d-none d-md-inline">Terminar</span>
                </button>
            @endif
            <button type="button" class="btn btn-sm" onclick="confirmReset()"
                    style="background: linear-gradient(135deg, rgba(238, 9, 121, 0.15) 0%, rgba(255, 106, 0, 0.15) 100%); color: #ee0979; border: 1px solid rgba(238, 9, 121, 0.3); padding: 0.5rem 0.875rem; border-radius: 8px; font-weight: 500;">
                <i class="bi bi-arrow-clockwise"></i> <span class="d-none d-md-inline">Reset</span>
            </button>
            <a href="{{ route('admin.surveys.index') }}" class="btn btn-sm"
               style="background: #f1f5f9; color: #64748b; border: none; padding: 0.5rem 0.875rem; border-radius: 8px; font-weight: 500;">
                <i class="bi bi-arrow-left"></i> <span class="d-none d-md-inline">Volver</span>
            </a>
        </div>
    </div>

    <!-- Estadísticas de Detección de Fraude -->
    @if($pendingReview > 0 || $rejectedVotes > 0 || $invalidVotes > 0)
    <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2" style="font-size: 1.5rem;"></i>
        <div>
            <strong>Detección de Fraude Activa</strong><br>
            <span class="small">
                @if($pendingReview > 0)
                    Se detectaron {{ $pendingReview }} votos sospechosos pendientes de revisión.
                @endif
                @if($invalidVotes > 0)
                    {{ $pendingReview > 0 ? 'Además, hay' : 'Hay' }} {{ $invalidVotes }} votos no válidos (exceden límites de rate limiting).
                @endif
                <a href="{{ route('admin.surveys.suspicious-votes', $survey) }}" class="alert-link">Ver todos los votos sospechosos</a>
            </span>
        </div>
    </div>
    @endif

    <!-- Estadísticas Detalladas -->
    <div class="row g-4 mb-4">
        <!-- Lado Izquierdo: VISITAS -->
        <div class="col-lg-6">
            <div class="modern-card h-100">
                <div style="border-bottom: 1px solid #e2e8f0; padding: 1.25rem;">
                    <h5 class="mb-0 fw-bold" style="color: #667eea;">
                        <i class="bi bi-eye-fill"></i> Visitas Totales
                    </h5>
                </div>
                <div style="padding: 1.5rem;">
                    <div class="text-center mb-4">
                        <div class="display-3 fw-bold mb-2" style="color: #667eea;">
                            {{ number_format($survey->views_count ?? 0) }}
                        </div>
                        <p class="text-muted mb-0">Personas han visto esta encuesta</p>
                    </div>

                    <!-- Desglose de visitas -->
                    <div class="desglose-box p-3 rounded-3" style="background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);">
                        <h6 class="fw-semibold mb-3">
                            <i class="bi bi-info-circle"></i> Información Detallada
                        </h6>

                        <div class="row g-3 text-center">
                            <div class="col-12">
                                <div class="p-3 bg-white rounded-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted">
                                            <i class="bi bi-eye"></i> Total de vistas de página
                                        </span>
                                        <span class="fw-bold fs-5">{{ number_format($survey->views_count ?? 0) }}</span>
                                    </div>
                                </div>
                            </div>

                            @if(($survey->views_count ?? 0) > 0)
                            <div class="col-6">
                                <div class="p-3 bg-white rounded-3">
                                    <div class="text-success">
                                        <i class="bi bi-check-circle-fill" style="font-size: 1.5rem;"></i>
                                    </div>
                                    <div class="fw-bold fs-4 mt-2">{{ number_format($uniqueVoters) }}</div>
                                    <small class="text-muted">Votaron</small>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="p-3 bg-white rounded-3">
                                    <div class="text-warning">
                                        <i class="bi bi-x-circle-fill" style="font-size: 1.5rem;"></i>
                                    </div>
                                    <div class="fw-bold fs-4 mt-2">{{ number_format(($survey->views_count ?? 0) - $uniqueVoters) }}</div>
                                    <small class="text-muted">No votaron</small>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lado Derecho: VOTANTES -->
        <div class="col-lg-6">
            <div class="modern-card h-100">
                <div style="border-bottom: 1px solid #e2e8f0; padding: 1.25rem;">
                    <h5 class="mb-0 fw-bold" style="color: #11998e;">
                        <i class="bi bi-people-fill"></i> Votantes
                    </h5>
                </div>
                <div style="padding: 1.5rem;">
                    <div class="text-center mb-4">
                        <div class="display-3 fw-bold mb-2" style="color: #11998e;">
                            {{ number_format($uniqueVoters) }}
                        </div>
                        <p class="text-muted mb-0">Personas han completado la encuesta</p>
                    </div>

                    <!-- Desglose de votos -->
                    <div class="desglose-box p-3 rounded-3" style="background: linear-gradient(135deg, rgba(67, 233, 123, 0.1) 0%, rgba(56, 249, 215, 0.1) 100%);">
                        <h6 class="fw-semibold mb-3">
                            <i class="bi bi-graph-up"></i> Desglose de Datos
                        </h6>

                        <div class="row g-3">
                            <div class="col-12">
                                <div class="p-3 bg-white rounded-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted">
                                            <i class="bi bi-person-check-fill text-success"></i> Votantes únicos
                                        </span>
                                        <span class="fw-bold fs-5">{{ number_format($uniqueVoters) }}</span>
                                    </div>
                                    <small class="text-muted">Por IP/Fingerprint único</small>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="p-3 bg-white rounded-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted">
                                            <i class="bi bi-chat-dots-fill text-primary"></i> Total de respuestas aprobadas
                                        </span>
                                        <span class="fw-bold fs-5">{{ number_format($totalVotes) }}</span>
                                    </div>
                                    <small class="text-muted">{{ $survey->questions->count() }} pregunta(s) × {{ $uniqueVoters }} votantes</small>
                                </div>
                            </div>

                            @if($pendingReview > 0 || $rejectedVotes > 0)
                            <div class="col-12">
                                <div class="p-3 bg-white rounded-3 border border-warning">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted">
                                            <i class="bi bi-shield-exclamation text-warning"></i> Votos en revisión
                                        </span>
                                        <span class="fw-bold fs-5 text-warning">{{ number_format($pendingReview) }}</span>
                                    </div>
                                    <small class="text-muted">Marcados como sospechosos por el sistema</small>
                                </div>
                            </div>
                            @if($rejectedVotes > 0)
                            <div class="col-12">
                                <div class="p-3 bg-white rounded-3 border border-danger">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted">
                                            <i class="bi bi-x-circle text-danger"></i> Votos rechazados
                                        </span>
                                        <span class="fw-bold fs-5 text-danger">{{ number_format($rejectedVotes) }}</span>
                                    </div>
                                    <small class="text-muted">Confirmados como fraudulentos</small>
                                </div>
                            </div>
                            @endif
                            @endif

                            @if(($survey->views_count ?? 0) > 0)
                            <div class="col-12">
                                <div class="p-3 bg-white rounded-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted">
                                            <i class="bi bi-percent text-info"></i> Tasa de conversión
                                        </span>
                                        <span class="fw-bold fs-5 text-success">
                                            {{ number_format(($uniqueVoters / $survey->views_count) * 100, 1) }}%
                                        </span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-success" role="progressbar"
                                             style="width: {{ min(($uniqueVoters / $survey->views_count) * 100, 100) }}%"></div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Link para compartir -->
    <div class="modern-card mb-4" style="background: linear-gradient(135deg, rgba(17, 153, 142, 0.05) 0%, rgba(56, 239, 125, 0.05) 100%); border: 1px solid rgba(17, 153, 142, 0.2);">
        <div class="d-flex align-items-center flex-wrap gap-3" style="padding: 1.25rem;">
            <i class="bi bi-key-fill" style="font-size: 1.5rem; color: #11998e;"></i>
            <div class="flex-grow-1">
                <strong style="color: #1e293b;">Link Público de la Encuesta:</strong>
                <code class="ms-2 d-inline-block text-break" style="background: rgba(255, 255, 255, 0.7); padding: 0.25rem 0.5rem; border-radius: 4px; color: #667eea;">{{ url('/t/' . $survey->public_slug) }}</code>
                <div class="mt-2">
                    <small class="text-muted">
                        <i class="bi bi-lightbulb"></i> Este link genera un token único para cada persona automáticamente.
                        Puedes agregar: <code style="background: rgba(255, 255, 255, 0.7); padding: 0.125rem 0.375rem; border-radius: 4px;">?source=facebook-ads</code> o <code style="background: rgba(255, 255, 255, 0.7); padding: 0.125rem 0.375rem; border-radius: 4px;">?source=whatsapp&campaign_id=verano-2025</code>
                    </small>
                </div>
            </div>
            <button class="btn btn-sm" onclick="copyToClipboard('{{ url('/t/' . $survey->public_slug) }}')"
                    style="background: linear-gradient(135deg, rgba(17, 153, 142, 0.15) 0%, rgba(56, 239, 125, 0.15) 100%); color: #11998e; border: 1px solid rgba(17, 153, 142, 0.3); padding: 0.5rem 0.875rem; border-radius: 8px; font-weight: 500;">
                <i class="bi bi-clipboard"></i> Copiar
            </button>
        </div>
    </div>

    <!-- Resultados por pregunta -->
    @foreach($questionStats as $index => $stat)
        <div class="modern-card mb-4 question-card">
            <div style="border-bottom: 1px solid #e2e8f0; padding: 1.25rem;">
                <div class="d-flex align-items-start gap-3">
                    <span class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                          style="width: 40px; height: 40px; font-size: 1.1rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-weight: 600;">
                        {{ $index + 1 }}
                    </span>
                    <div class="flex-grow-1">
                        <h5 class="mb-2 fw-semibold" style="color: #1e293b;">{{ $stat['question'] }}</h5>
                        <small class="text-muted">
                            <i class="bi bi-graph-up"></i> Total de votos: <strong>{{ number_format($stat['total_votes']) }}</strong>
                        </small>
                    </div>
                </div>
            </div>
            <div style="padding: 1.5rem;">
                <div class="row">
                    <!-- Gráfico de pastel (solo en desktop) -->
                    <div class="col-lg-5 d-none d-lg-block mb-4 mb-lg-0">
                        <div class="chart-container" style="position: relative; height: 300px;">
                            <canvas id="chart-{{ $index }}"></canvas>
                        </div>
                    </div>

                    <!-- Lista de opciones con barras -->
                    <div class="col-12 col-lg-7">
                        @foreach($stat['options'] as $optIndex => $option)
                            <div class="option-stat mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-medium" style="color: #1e293b;">{{ $option['text'] }}</span>
                                    <span class="badge rounded-pill" style="background: linear-gradient(135deg, rgba(102, 126, 234, 0.15) 0%, rgba(118, 75, 162, 0.15) 100%); color: #667eea; border: 1px solid rgba(102, 126, 234, 0.3); padding: 0.375rem 0.75rem; font-weight: 600;">
                                        {{ number_format($option['votes']) }} votos ({{ $option['percentage'] }}%)
                                    </span>
                                </div>
                                <div class="progress" style="height: 28px; background: #f1f5f9; border-radius: 8px;">
                                    <div class="progress-bar position-relative"
                                         role="progressbar"
                                         style="width: {{ $option['percentage'] }}%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px;"
                                         aria-valuenow="{{ $option['percentage'] }}"
                                         aria-valuemin="0"
                                         aria-valuemax="100">
                                        @if($option['percentage'] > 10)
                                            <strong style="color: white;">{{ $option['percentage'] }}%</strong>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    @if(count($questionStats) === 0)
        <div class="modern-card">
            <div class="text-center py-5" style="padding: 3rem 1rem;">
                <div class="mb-4">
                    <div style="width: 80px; height: 80px; background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%); border-radius: 20px; display: inline-flex; align-items: center; justify-content: center;">
                        <i class="bi bi-graph-down" style="font-size: 2.5rem; color: #667eea;"></i>
                    </div>
                </div>
                <h5 class="fw-bold mb-2" style="color: #1e293b;">Aún no hay votos</h5>
                <p class="text-muted mb-4">Comparte el link de la encuesta para comenzar a recibir respuestas.</p>
                <a href="{{ url('/t/' . $survey->public_slug) }}" target="_blank" class="btn btn-gradient-primary">
                    <i class="bi bi-link-45deg"></i> Ver Encuesta Pública
                </a>
            </div>
        </div>
    @endif
</div>

<!-- Formulario oculto para reset -->
<form id="resetForm" method="POST" action="{{ route('admin.surveys.reset', $survey) }}" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<style>
/* Animaciones y efectos */
.modern-card {
    animation: fadeInUp 0.5s ease-out;
    transition: all 0.3s ease;
}

.modern-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.08) !important;
}

.question-card {
    animation: fadeInUp 0.6s ease-out;
    transition: all 0.3s ease;
}

.question-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.08) !important;
}

.option-stat {
    transition: all 0.3s ease;
    padding: 10px;
    border-radius: 8px;
}

.option-stat:hover {
    background: rgba(102, 126, 234, 0.05);
    transform: translateX(5px);
}

.progress-bar {
    transition: width 1.5s ease-out;
}

.desglose-box {
    animation: fadeIn 0.8s ease-out 0.3s both;
}

.desglose-box .bg-white {
    transition: all 0.3s ease;
}

.desglose-box .bg-white:hover {
    transform: scale(1.02);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

/* Números grandes */
.display-3 {
    line-height: 1.2;
}

/* Responsive */
@media (max-width: 992px) {
    .display-3 {
        font-size: 3rem !important;
    }
}

@media (max-width: 768px) {
    .display-3 {
        font-size: 2.5rem !important;
    }

    .desglose-box {
        padding: 1rem !important;
    }

    .desglose-box h6 {
        font-size: 0.9rem !important;
    }
}

@media (max-width: 576px) {
    .display-3 {
        font-size: 2rem !important;
    }

    .fs-4 {
        font-size: 1.1rem !important;
    }

    .fs-5 {
        font-size: 1rem !important;
    }
}
</style>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('¡Link copiado al portapapeles!');
    });
}

function confirmFinish() {
    if (confirm(`📊 ¿Terminar esta encuesta?\n\n` +
                `Esta acción hará lo siguiente:\n` +
                `• La encuesta dejará de aceptar votos\n` +
                `• Se mostrará una página de resultados finales\n` +
                `• Los usuarios serán redirigidos a ver los resultados\n\n` +
                `Puedes reactivarla después si lo necesitas.\n\n` +
                `¿Continuar?`)) {

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("admin.surveys.finish", $survey) }}';

        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';

        form.appendChild(csrfToken);
        document.body.appendChild(form);
        form.submit();
    }
}

function confirmReset() {
    const totalVotes = {{ $totalVotes }};
    const uniqueVoters = {{ $uniqueVoters }};

    if (confirm(`⚠️ ADVERTENCIA: Esta acción es IRREVERSIBLE\n\n` +
                `Se eliminarán:\n` +
                `• ${totalVotes} votos totales\n` +
                `• ${uniqueVoters} votantes únicos\n` +
                `• Todos los resultados de esta encuesta\n\n` +
                `¿Estás SEGURO de que deseas continuar?`)) {

        // Segunda confirmación
        if (confirm(`🔴 ÚLTIMA CONFIRMACIÓN\n\n` +
                    `Escribe "RESET" en la próxima ventana para confirmar`)) {

            const confirmation = prompt('Escribe "RESET" para confirmar (en mayúsculas):');

            if (confirmation === 'RESET') {
                const form = document.getElementById('resetForm');
                form.method = 'POST';
                form.submit();
            } else {
                alert('❌ Operación cancelada. El texto no coincide.');
            }
        }
    }
}

// Crear gráficos de pastel (solo en desktop)
@if(count($questionStats) > 0)
document.addEventListener('DOMContentLoaded', function() {
    const isMobile = window.innerWidth < 992;

    if (!isMobile) {
        const questionStats = @json($questionStats);
        const defaultColors = [
            '#667eea', '#f093fb', '#4facfe', '#43e97b',
            '#fa709a', '#30cfd0', '#a8edea', '#fed6e3'
        ];

        questionStats.forEach((question, index) => {
            const ctx = document.getElementById(`chart-${index}`);
            if (!ctx) return;

            const labels = question.options.map(opt => opt.text);
            const data = question.options.map(opt => opt.percentage);
            const colors = defaultColors.slice(0, question.options.length);

            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: colors,
                        borderColor: '#ffffff',
                        borderWidth: 3,
                        hoverOffset: 15
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                font: {
                                    size: 12,
                                    family: "'Inter', system-ui, sans-serif",
                                    weight: '500'
                                },
                                color: '#374151',
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.9)',
                            padding: 12,
                            titleFont: { size: 14, weight: 'bold' },
                            bodyFont: { size: 13 },
                            cornerRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    return ' ' + context.label + ': ' + context.parsed.toFixed(1) + '%';
                                }
                            }
                        }
                    },
                    animation: {
                        animateRotate: true,
                        animateScale: true,
                        duration: 1500
                    }
                }
            });
        });
    }
});
@endif
</script>
@endsection
