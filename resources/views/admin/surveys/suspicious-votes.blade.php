@extends('layouts.admin')

@section('title', 'Votos Sospechosos - ' . $survey->title)

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="mb-4 d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div>
            <h1 class="h2 fw-bold mb-1" style="color: #1e293b;">
                <i class="bi bi-shield-exclamation"></i> Votos Sospechosos
            </h1>
            <p class="text-muted mb-0">{{ $survey->title }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.surveys.show', $survey) }}" class="btn btn-sm"
               style="background: #f1f5f9; color: #64748b; border: none; padding: 0.5rem 0.875rem; border-radius: 8px; font-weight: 500;">
                <i class="bi bi-arrow-left"></i> Volver a Resultados
            </a>
        </div>
    </div>

    <!-- Alerta informativa -->
    <div class="alert alert-info d-flex align-items-start mb-4" role="alert">
        <i class="bi bi-info-circle-fill me-3" style="font-size: 1.5rem;"></i>
        <div>
            <h6 class="alert-heading mb-2">Sistema de Detección de Fraude</h6>
            <p class="mb-0 small">
                Los votos marcados como sospechosos NO se contabilizan en los resultados hasta que sean aprobados manualmente.
                Revisa cada voto y decide si aprobar o rechazar basándote en los patrones detectados.
            </p>
        </div>
    </div>

    @if($suspiciousVotes->count() > 0)
        <!-- Acciones masivas -->
        <div class="modern-card mb-4">
            <div class="p-3">
                <form id="bulkActionForm" method="POST">
                    @csrf
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <input type="checkbox" id="selectAll" class="form-check-input">
                            <label for="selectAll" class="form-check-label ms-2">Seleccionar todos</label>
                        </div>
                        <div class="col-auto">
                            <button type="button" class="btn btn-sm btn-success" onclick="bulkAction('approve')">
                                <i class="bi bi-check-circle"></i> Aprobar seleccionados
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" onclick="bulkAction('reject')">
                                <i class="bi bi-x-circle"></i> Rechazar seleccionados
                            </button>
                        </div>
                        <div class="col-auto ms-auto">
                            <span class="text-muted small">
                                <i class="bi bi-funnel"></i> {{ $suspiciousVotes->total() }} votos sospechosos
                            </span>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Lista de votos sospechosos -->
        <div class="modern-card">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                        <tr>
                            <th style="width: 40px; padding: 1rem;"></th>
                            <th style="padding: 1rem;">Pregunta / Respuesta</th>
                            <th style="padding: 1rem;">Puntuación</th>
                            <th style="padding: 1rem;">Razones</th>
                            <th style="padding: 1rem;">Fecha/Hora</th>
                            <th style="padding: 1rem;">Detalles</th>
                            <th class="text-end" style="padding: 1rem;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($suspiciousVotes as $vote)
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 1rem;">
                                <input type="checkbox" class="form-check-input vote-checkbox" value="{{ $vote->id }}">
                            </td>
                            <td style="padding: 1rem;">
                                <div class="fw-semibold mb-1" style="color: #1e293b; font-size: 0.9rem;">
                                    {{ Str::limit($vote->question->question_text, 50) }}
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="badge" style="background: {{ $vote->option->color ?? '#6c757d' }}22; color: {{ $vote->option->color ?? '#6c757d' }}; border: 1px solid {{ $vote->option->color ?? '#6c757d' }}44;">
                                        {{ $vote->option->option_text }}
                                    </div>
                                    @if(!$vote->is_valid)
                                        <span class="badge bg-danger">
                                            <i class="bi bi-x-circle"></i> NO VÁLIDO
                                        </span>
                                    @endif
                                </div>
                                @if(!$vote->is_valid && $vote->invalid_reason)
                                    <div class="small text-danger mt-1">
                                        <i class="bi bi-exclamation-triangle"></i>
                                        @if($vote->invalid_reason === 'two_votes_per_minute_per_option')
                                            Excede límite: 2 votos/minuto por opción
                                        @else
                                            {{ $vote->invalid_reason }}
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td style="padding: 1rem;">
                                @php
                                    $score = $vote->fraud_score;
                                    $color = $score >= 80 ? 'danger' : ($score >= 60 ? 'warning' : 'info');
                                @endphp
                                <span class="badge bg-{{ $color }} fs-6">{{ number_format($score, 0) }}</span>
                            </td>
                            <td style="padding: 1rem;">
                                @if($vote->fraud_reasons && count($vote->fraud_reasons) > 0)
                                    <button type="button" class="btn btn-sm btn-outline-secondary"
                                            data-bs-toggle="modal" data-bs-target="#reasonsModal{{ $vote->id }}">
                                        <i class="bi bi-list-ul"></i> {{ count($vote->fraud_reasons) }}
                                    </button>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td style="padding: 1rem;">
                                <div class="small text-muted">
                                    {{ $vote->created_at->format('d/m/Y H:i') }}
                                </div>
                            </td>
                            <td style="padding: 1rem;">
                                <div class="small text-muted">
                                    @if($vote->screen_resolution)
                                        <div>{{ $vote->screen_resolution }}</div>
                                    @endif
                                    @if($vote->ip_address)
                                        <div>{{ $vote->ip_address }}</div>
                                    @endif
                                </div>
                            </td>
                            <td class="text-end" style="padding: 1rem;">
                                <div class="btn-group btn-group-sm">
                                    <form method="POST" action="{{ route('admin.surveys.votes.approve', [$survey, $vote]) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm" title="Aprobar">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.surveys.votes.reject', [$survey, $vote]) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-danger btn-sm" title="Rechazar">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        <!-- Modal de razones -->
                        @if($vote->fraud_reasons && count($vote->fraud_reasons) > 0)
                        <div class="modal fade" id="reasonsModal{{ $vote->id }}" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Razones de Sospecha - Voto #{{ $vote->id }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="alert alert-warning">
                                            <strong>Puntuación de fraude:</strong> {{ number_format($vote->fraud_score, 2) }} / 100
                                        </div>
                                        <ul class="list-group">
                                            @foreach($vote->fraud_reasons as $reason)
                                            <li class="list-group-item">
                                                <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                                                {{ $reason }}
                                            </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            @if($suspiciousVotes->hasPages())
            <div class="p-3 border-top">
                {{ $suspiciousVotes->links() }}
            </div>
            @endif
        </div>
    @else
        <!-- Sin votos sospechosos -->
        <div class="modern-card">
            <div class="text-center py-5">
                <div class="mb-4">
                    <div style="width: 80px; height: 80px; background: linear-gradient(135deg, rgba(17, 153, 142, 0.1) 0%, rgba(56, 239, 125, 0.1) 100%); border-radius: 20px; display: inline-flex; align-items-center; justify-content: center;">
                        <i class="bi bi-shield-check" style="font-size: 2.5rem; color: #11998e;"></i>
                    </div>
                </div>
                <h5 class="fw-bold mb-2" style="color: #1e293b;">No hay votos sospechosos</h5>
                <p class="text-muted mb-4">¡Excelente! Todos los votos parecen legítimos.</p>
            </div>
        </div>
    @endif
</div>

<script>
document.getElementById('selectAll')?.addEventListener('change', function(e) {
    const checkboxes = document.querySelectorAll('.vote-checkbox');
    checkboxes.forEach(cb => cb.checked = e.target.checked);
});

function bulkAction(action) {
    const checkboxes = document.querySelectorAll('.vote-checkbox:checked');
    const voteIds = Array.from(checkboxes).map(cb => cb.value);

    if (voteIds.length === 0) {
        alert('Por favor selecciona al menos un voto');
        return;
    }

    const actionText = action === 'approve' ? 'aprobar' : 'rechazar';
    if (!confirm(`¿Estás seguro de ${actionText} ${voteIds.length} voto(s)?`)) {
        return;
    }

    const form = document.getElementById('bulkActionForm');
    const route = action === 'approve'
        ? '{{ route("admin.surveys.votes.bulk-approve", $survey) }}'
        : '{{ route("admin.surveys.votes.bulk-reject", $survey) }}';

    form.action = route;

    voteIds.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'vote_ids[]';
        input.value = id;
        form.appendChild(input);
    });

    form.submit();
}
</script>
@endsection
