@extends('layouts.admin')

@section('title', 'Detalles del Token - ' . $survey->title)

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1" style="color: #1e293b;">
                <i class="bi bi-key-fill" style="color: #667eea;"></i> Detalles del Token
            </h1>
            <p class="text-muted mb-0">{{ $survey->title }}</p>
        </div>
        <a href="{{ route('admin.surveys.tokens.index', $survey) }}" class="btn" style="background: #f1f5f9; color: #64748b; border: none; padding: 0.75rem 1.5rem; border-radius: 12px; font-weight: 500;">
            <i class="bi bi-arrow-left"></i> Volver a Tokens
        </a>
    </div>

    <!-- Información del Token -->
    <div class="modern-card mb-4">
        <h5 class="mb-4 fw-bold" style="color: #1e293b;">
            <i class="bi bi-info-circle-fill" style="color: #667eea;"></i> Información del Token
        </h5>
        <div class="row g-4">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="text-muted small mb-1">Token</label>
                    <div class="d-flex align-items-center">
                        <code class="bg-light p-2 rounded flex-grow-1" style="font-size: 0.875rem;">{{ $token->token }}</code>
                        <button class="btn btn-sm btn-outline-primary ms-2" onclick="copyToClipboard('{{ $token->token }}')">
                            <i class="bi bi-clipboard"></i> Copiar
                        </button>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small mb-1">URL Completa</label>
                    <div class="d-flex align-items-center">
                        @php
                            $tokenUrl = $survey->survey_group_id && $survey->group && $survey->group->slug
                                ? url('/t/' . $survey->group->slug . '/' . $survey->public_slug . '?token=' . $token->token)
                                : url('/t/' . $survey->public_slug . '?token=' . $token->token);
                        @endphp
                        <input type="text" class="form-control form-control-sm" readonly value="{{ $tokenUrl }}">
                        <button class="btn btn-sm btn-outline-primary ms-2" onclick="copyToClipboard('{{ $tokenUrl }}')">
                            <i class="bi bi-clipboard"></i> Copiar
                        </button>
                    </div>
                    @if($survey->survey_group_id && $survey->group)
                        <small class="text-muted"><i class="bi bi-collection"></i> Grupo: {{ $survey->group->name }}</small>
                    @endif
                </div>
            </div>
            <div class="col-md-6">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="p-3 rounded" style="background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%); border-left: 4px solid #667eea;">
                            <small class="text-muted d-block mb-1">Estado</small>
                            @if($token->status === 'pending')
                                <span class="badge bg-warning text-dark">
                                    <i class="bi bi-clock"></i> Pendiente
                                </span>
                            @elseif($token->status === 'used')
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle"></i> Usado
                                </span>
                            @else
                                <span class="badge bg-danger">
                                    <i class="bi bi-x-circle"></i> Expirado
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded" style="background: linear-gradient(135deg, rgba(238, 9, 121, 0.1) 0%, rgba(255, 106, 0, 0.1) 100%); border-left: 4px solid #ee0979;">
                            <small class="text-muted d-block mb-1">Intentos de Voto</small>
                            <h4 class="mb-0 fw-bold" style="color: {{ $token->vote_attempts > 1 ? '#ee0979' : '#11998e' }};">
                                {{ $token->vote_attempts }}
                            </h4>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded" style="background: linear-gradient(135deg, rgba(17, 153, 142, 0.1) 0%, rgba(56, 239, 125, 0.1) 100%); border-left: 4px solid #11998e;">
                            <small class="text-muted d-block mb-1">Fuente</small>
                            <strong>{{ $token->source }}</strong>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded" style="background: linear-gradient(135deg, rgba(79, 172, 254, 0.1) 0%, rgba(0, 242, 254, 0.1) 100%); border-left: 4px solid #4facfe;">
                            <small class="text-muted d-block mb-1">Campaign ID</small>
                            <strong>{{ $token->campaign_id ?? '-' }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4">

        <div class="row g-3">
            <div class="col-md-4">
                <small class="text-muted d-block mb-1">Creado</small>
                <strong>{{ $token->created_at->format('d/m/Y H:i:s') }}</strong>
                <small class="text-muted d-block">{{ $token->created_at->diffForHumans() }}</small>
            </div>
            <div class="col-md-4">
                <small class="text-muted d-block mb-1">Usado</small>
                @if($token->used_at)
                    <strong>{{ $token->used_at->format('d/m/Y H:i:s') }}</strong>
                    <small class="text-muted d-block">{{ $token->used_at->diffForHumans() }}</small>
                @else
                    <span class="text-muted">No usado</span>
                @endif
            </div>
            <div class="col-md-4">
                <small class="text-muted d-block mb-1">Último Intento</small>
                @if($token->last_attempt_at)
                    <strong>{{ $token->last_attempt_at->format('d/m/Y H:i:s') }}</strong>
                    <small class="text-muted d-block">{{ $token->last_attempt_at->diffForHumans() }}</small>
                @else
                    <span class="text-muted">Sin intentos</span>
                @endif
            </div>
        </div>

        @if($token->used_by_fingerprint)
            <hr class="my-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <small class="text-muted d-block mb-1">Fingerprint del Votante</small>
                    <code class="bg-light p-2 rounded d-block">{{ $token->used_by_fingerprint }}</code>
                </div>
                <div class="col-md-6">
                    <small class="text-muted d-block mb-1">User Agent</small>
                    <code class="bg-light p-2 rounded d-block" style="font-size: 0.75rem; word-break: break-all;">{{ $token->user_agent ?? 'No disponible' }}</code>
                </div>
            </div>
        @endif
    </div>

    <!-- Historial de Intentos de Voto -->
    <div class="modern-card mb-4">
        <h5 class="mb-4 fw-bold" style="color: #1e293b;">
            <i class="bi bi-clock-history" style="color: #667eea;"></i> Historial de Intentos de Voto
            <span class="badge bg-secondary ms-2">{{ $votes->count() }} votos registrados</span>
        </h5>

        @if($votes->count() > 0)
            @if($token->vote_attempts > 1)
                <div class="alert alert-warning mb-4">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <strong>Atención:</strong> Este token tiene {{ $token->vote_attempts }} intentos de voto registrados.
                    Solo el primer voto es válido. Los intentos adicionales pueden indicar que el token fue compartido o reutilizado.
                </div>
            @endif

            <!-- Agrupar por sesión de voto -->
            @php
                $votesBySession = $votes->groupBy(function($vote) {
                    return $vote->fingerprint . '_' . $vote->created_at->format('Y-m-d H:i');
                });
                $sessionNumber = 0;
            @endphp

            @foreach($votesBySession as $sessionKey => $sessionVotes)
                @php
                    $sessionNumber++;
                    $firstVote = $sessionVotes->first();
                    $isValidSession = $sessionNumber === 1 && $token->status === 'used';
                @endphp

                <div class="mb-4 p-4 rounded {{ $isValidSession ? 'border border-success' : 'border border-danger' }}"
                     style="background: {{ $isValidSession ? 'rgba(17, 153, 142, 0.05)' : 'rgba(238, 9, 121, 0.05)' }};">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h6 class="fw-bold mb-1" style="color: #1e293b;">
                                <i class="bi bi-{{ $isValidSession ? 'check-circle-fill text-success' : 'x-circle-fill text-danger' }}"></i>
                                Intento #{{ $sessionNumber }}
                                @if($isValidSession)
                                    <span class="badge bg-success ms-2">VÁLIDO</span>
                                @else
                                    <span class="badge bg-danger ms-2">RECHAZADO</span>
                                @endif
                            </h6>
                            <small class="text-muted">
                                {{ $firstVote->created_at->format('d/m/Y H:i:s') }}
                                ({{ $firstVote->created_at->diffForHumans() }})
                            </small>
                        </div>
                    </div>

                    <!-- Información de la sesión -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-1">Fingerprint</small>
                            <code class="bg-white p-2 rounded d-block" style="font-size: 0.75rem;">{{ $firstVote->fingerprint }}</code>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-1">IP Address</small>
                            <code class="bg-white p-2 rounded d-block">{{ $firstVote->ip_address }}</code>
                        </div>
                    </div>

                    <!-- Respuestas -->
                    <div class="mt-3">
                        <strong class="d-block mb-2">Respuestas seleccionadas:</strong>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0 bg-white">
                                <thead class="table-light">
                                    <tr>
                                        <th>Pregunta</th>
                                        <th>Opción Seleccionada</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sessionVotes as $vote)
                                        <tr>
                                            <td>{{ $vote->question->question_text }}</td>
                                            <td>
                                                <span class="badge" style="background-color: {{ $vote->option->color ?? '#0d6efd' }};">
                                                    {{ $vote->option->option_text }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @if(!$isValidSession)
                        <div class="mt-3 p-2 rounded" style="background: rgba(238, 9, 121, 0.1); border-left: 4px solid #ee0979;">
                            <small>
                                <i class="bi bi-info-circle"></i>
                                <strong>Razón del rechazo:</strong> El token ya había sido usado previamente. Solo se cuenta el primer voto.
                            </small>
                        </div>
                    @endif
                </div>
            @endforeach

        @else
            <div class="text-center py-5">
                <div class="mb-4">
                    <div style="width: 80px; height: 80px; background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%); border-radius: 20px; display: inline-flex; align-items: center; justify-content: center;">
                        <i class="bi bi-inbox" style="font-size: 2.5rem; color: #667eea;"></i>
                    </div>
                </div>
                <p class="text-muted">Este token no tiene intentos de voto registrados.</p>
            </div>
        @endif
    </div>

    <!-- Acciones -->
    <div class="modern-card">
        <h5 class="mb-3 fw-bold" style="color: #1e293b;">
            <i class="bi bi-gear-fill" style="color: #667eea;"></i> Acciones
        </h5>
        <div class="d-flex gap-2">
            <form action="{{ route('admin.surveys.tokens.destroy', [$survey, $token]) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este token? Esta acción no se puede deshacer.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger">
                    <i class="bi bi-trash"></i> Eliminar Token
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('Copiado al portapapeles!');
    }, function(err) {
        console.error('Error al copiar: ', err);
        alert('Error al copiar');
    });
}
</script>
@endsection
