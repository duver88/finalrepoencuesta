@extends('layouts.admin')

@section('title', 'Gestión de Tokens - ' . $survey->title)

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1" style="color: #1e293b;">
                <i class="bi bi-key-fill" style="color: #667eea;"></i> Gestión de Tokens
            </h1>
            <p class="text-muted mb-0">{{ $survey->title }}</p>
        </div>
        <a href="{{ route('admin.surveys.show', $survey) }}" class="btn" style="background: #f1f5f9; color: #64748b; border: none; padding: 0.75rem 1.5rem; border-radius: 12px; font-weight: 500;">
            <i class="bi bi-arrow-left"></i> Volver a la encuesta
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Estadísticas Rápidas -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon stat-primary">
                    <i class="bi bi-key-fill"></i>
                </div>
                <div>
                    <p class="text-muted mb-1 small fw-medium text-uppercase" style="letter-spacing: 0.5px;">Total Tokens</p>
                    <h3 class="mb-0 fw-bold" style="color: #1e293b;">{{ number_format($stats['total']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="--primary-gradient: var(--warning-gradient);">
                <div class="stat-icon stat-warning">
                    <i class="bi bi-clock-fill"></i>
                </div>
                <div>
                    <p class="text-muted mb-1 small fw-medium text-uppercase" style="letter-spacing: 0.5px;">Pendientes</p>
                    <h3 class="mb-0 fw-bold" style="color: #1e293b;">{{ number_format($stats['pending']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="--primary-gradient: var(--success-gradient);">
                <div class="stat-icon stat-success">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div>
                    <p class="text-muted mb-1 small fw-medium text-uppercase" style="letter-spacing: 0.5px;">Usados</p>
                    <h3 class="mb-0 fw-bold" style="color: #1e293b;">{{ number_format($stats['used']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="--primary-gradient: var(--danger-gradient);">
                <div class="stat-icon stat-danger">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <div>
                    <p class="text-muted mb-1 small fw-medium text-uppercase" style="letter-spacing: 0.5px;">Intentos Múltiples</p>
                    <h3 class="mb-0 fw-bold" style="color: #1e293b;">{{ number_format($stats['multiple_attempts']) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- URLs para Facebook Ads -->
    <div class="row g-3 mb-4">
        <div class="col-md-12">
            <div class="modern-card" style="border-left: 4px solid #11998e;">
                <h5 class="mb-3 fw-bold" style="color: #1e293b;">
                    <i class="bi bi-facebook" style="color: #11998e;"></i> URLs para Facebook Ads (Generación Automática)
                </h5>
                <div>
                    <p class="text-muted mb-3">
                        <i class="bi bi-lightbulb-fill text-warning"></i> <strong>Recomendado:</strong> Usa estas URLs en tus anuncios de Facebook. Cada persona que entre obtendrá automáticamente un token único.
                    </p>

                    <!-- URL Básica -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-link-45deg"></i> URL Básica (tracking orgánico)
                        </label>
                        <div class="input-group">
                            @php
                                $baseUrl = $survey->survey_group_id && $survey->group && $survey->group->slug
                                    ? url('/t/' . $survey->group->slug . '/' . $survey->public_slug)
                                    : url('/t/' . $survey->public_slug);
                            @endphp
                            <input type="text" class="form-control" readonly
                                   value="{{ $baseUrl }}"
                                   id="url-basic">
                            <button class="btn btn-outline-success" type="button"
                                    onclick="copyToClipboard('{{ $baseUrl }}', 'url-basic-btn')">
                                <i class="bi bi-clipboard"></i> <span id="url-basic-btn">Copiar</span>
                            </button>
                        </div>
                        @if($survey->survey_group_id && $survey->group)
                            <small class="text-muted"><i class="bi bi-collection"></i> Grupo: <strong>{{ $survey->group->name }}</strong></small>
                        @endif
                    </div>

                    <!-- URL para Facebook Ads -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-facebook"></i> URL para Facebook Ads
                        </label>
                        <div class="input-group">
                            @php
                                $facebookUrl = $baseUrl . '?source=facebook-ads';
                            @endphp
                            <input type="text" class="form-control" readonly
                                   value="{{ $facebookUrl }}"
                                   id="url-facebook">
                            <button class="btn btn-outline-primary" type="button"
                                    onclick="copyToClipboard('{{ $facebookUrl }}', 'url-facebook-btn')">
                                <i class="bi bi-clipboard"></i> <span id="url-facebook-btn">Copiar</span>
                            </button>
                        </div>
                    </div>

                    <!-- URL para Instagram Ads -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-instagram"></i> URL para Instagram Ads
                        </label>
                        <div class="input-group">
                            @php
                                $instagramUrl = $baseUrl . '?source=instagram-ads';
                            @endphp
                            <input type="text" class="form-control" readonly
                                   value="{{ $instagramUrl }}"
                                   id="url-instagram">
                            <button class="btn btn-outline-danger" type="button"
                                    onclick="copyToClipboard('{{ $instagramUrl }}', 'url-instagram-btn')">
                                <i class="bi bi-clipboard"></i> <span id="url-instagram-btn">Copiar</span>
                            </button>
                        </div>
                    </div>

                    <!-- URL con Campaña Personalizada -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-tag-fill"></i> URL con Campaña Personalizada
                        </label>
                        <div class="input-group">
                            @php
                                $campaignUrl = $baseUrl . '?source=facebook-ads&campaign_id=mi-campana-2025';
                            @endphp
                            <input type="text" class="form-control" readonly
                                   value="{{ $campaignUrl }}"
                                   id="url-campaign">
                            <button class="btn btn-outline-info" type="button"
                                    onclick="copyToClipboard('{{ $campaignUrl }}', 'url-campaign-btn')">
                                <i class="bi bi-clipboard"></i> <span id="url-campaign-btn">Copiar</span>
                            </button>
                        </div>
                        <small class="text-muted">Personaliza <code>campaign_id=</code> con el nombre de tu campaña</small>
                    </div>

                    <div class="alert alert-info mb-0" style="background: linear-gradient(135deg, rgba(79, 172, 254, 0.1) 0%, rgba(0, 242, 254, 0.1) 100%); border-left: 4px solid #4facfe; border-radius: 12px;">
                        <i class="bi bi-info-circle-fill" style="color: #4facfe;"></i>
                        <strong>¿Cómo funciona?</strong>
                        <ul class="mb-0 mt-2">
                            <li>Cada visitante que entre con estos links obtiene un <strong>token único automático</strong></li>
                            <li>No necesitas pre-generar tokens manualmente</li>
                            <li>Los tokens se rastrean por <code>source</code> y <code>campaign_id</code> en Analytics</li>
                            <li>Puedes ver cuántos votos vienen de cada fuente en la sección de Analíticas</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Acciones -->
    <div class="row g-3 mb-4">
        <div class="col-md-12">
            <div class="modern-card">
                <h5 class="mb-3 fw-bold" style="color: #1e293b;">
                    <i class="bi bi-gear-fill" style="color: #667eea;"></i> Acciones de Tokens
                </h5>
                <div>
                    <div class="row g-3">
                        <!-- Generar Tokens -->
                        <div class="col-md-4">
                            <button type="button" class="btn btn-gradient-primary w-100" data-bs-toggle="modal" data-bs-target="#generateTokensModal">
                                <i class="bi bi-plus-circle"></i> Generar Tokens Manualmente
                            </button>
                            <small class="text-muted d-block mt-2">Para QR codes, emails, etc.</small>
                        </div>

                        <!-- Exportar Tokens -->
                        <div class="col-md-4">
                            <a href="{{ route('admin.surveys.tokens.export', $survey) }}" class="btn w-100" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 12px; font-weight: 600; box-shadow: 0 4px 12px rgba(17, 153, 142, 0.3);">
                                <i class="bi bi-download"></i> Exportar Tokens Pendientes
                            </a>
                            <small class="text-muted d-block mt-2">Descarga archivo .txt con URLs</small>
                        </div>

                        <!-- Ver Analíticas -->
                        <div class="col-md-4">
                            <a href="{{ route('admin.surveys.tokens.analytics', $survey) }}" class="btn w-100" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 12px; font-weight: 600; box-shadow: 0 4px 12px rgba(79, 172, 254, 0.3);">
                                <i class="bi bi-bar-chart"></i> Ver Analíticas
                            </a>
                            <small class="text-muted d-block mt-2">Tokens por fuente y estado</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Eliminación Masiva -->
    <div class="row g-3 mb-4">
        <div class="col-md-12">
            <div class="modern-card" style="border-left: 4px solid #ee0979;">
                <h6 class="mb-3 fw-bold" style="color: #1e293b;">
                    <i class="bi bi-trash-fill" style="color: #ee0979;"></i> Eliminación Masiva
                </h6>
                <div>
                    <p class="text-muted mb-3">Eliminar tokens por estado. Esta acción no se puede deshacer.</p>
                    <div class="row g-2">
                        <div class="col-md-4">
                            <form action="{{ route('admin.surveys.tokens.bulk-delete', $survey) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar TODOS los tokens pendientes?')">
                                @csrf
                                <input type="hidden" name="status" value="pending">
                                <button type="submit" class="btn btn-outline-warning w-100">
                                    <i class="bi bi-trash"></i> Eliminar Pendientes ({{ number_format($stats['pending']) }})
                                </button>
                            </form>
                        </div>
                        <div class="col-md-4">
                            <form action="{{ route('admin.surveys.tokens.bulk-delete', $survey) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar TODOS los tokens usados?')">
                                @csrf
                                <input type="hidden" name="status" value="used">
                                <button type="submit" class="btn btn-outline-success w-100">
                                    <i class="bi bi-trash"></i> Eliminar Usados ({{ number_format($stats['used']) }})
                                </button>
                            </form>
                        </div>
                        <div class="col-md-4">
                            <form action="{{ route('admin.surveys.tokens.bulk-delete', $survey) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar TODOS los tokens expirados?')">
                                @csrf
                                <input type="hidden" name="status" value="expired">
                                <button type="submit" class="btn btn-outline-danger w-100">
                                    <i class="bi bi-trash"></i> Eliminar Expirados ({{ number_format($stats['expired']) }})
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="modern-card mb-4">
        <h5 class="mb-3 fw-bold" style="color: #1e293b;">
            <i class="bi bi-funnel-fill" style="color: #667eea;"></i> Filtros
        </h5>
        <form method="GET" action="{{ route('admin.surveys.tokens.index', $survey) }}" id="filterForm">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="status" class="form-label fw-semibold">Estado</label>
                    <select name="status" id="status" class="form-select" onchange="document.getElementById('filterForm').submit()">
                        <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>Todos</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendientes</option>
                        <option value="used" {{ request('status') == 'used' ? 'selected' : '' }}>Usados</option>
                        <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expirados</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="source" class="form-label fw-semibold">Fuente</label>
                    <select name="source" id="source" class="form-select" onchange="document.getElementById('filterForm').submit()">
                        <option value="all" {{ request('source') == 'all' ? 'selected' : '' }}>Todas</option>
                        @foreach($sources as $source)
                            <option value="{{ $source }}" {{ request('source') == $source ? 'selected' : '' }}>{{ $source }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="multiple_attempts" class="form-label fw-semibold">Intentos Múltiples</label>
                    <select name="multiple_attempts" id="multiple_attempts" class="form-select" onchange="document.getElementById('filterForm').submit()">
                        <option value="0" {{ request('multiple_attempts') == '0' ? 'selected' : '' }}>Todos</option>
                        <option value="1" {{ request('multiple_attempts') == '1' ? 'selected' : '' }}>Solo con intentos múltiples</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="question_option" class="form-label fw-semibold">Filtrar por Respuesta</label>
                    <select name="question_option" id="question_option" class="form-select" onchange="document.getElementById('filterForm').submit()">
                        <option value="all" {{ request('question_option') == 'all' || !request('question_option') ? 'selected' : '' }}>Todas las respuestas</option>
                        @foreach($questions as $question)
                            <optgroup label="{{ $question->question_text }}">
                                @foreach($question->options as $option)
                                    <option value="{{ $option->id }}" {{ request('question_option') == $option->id ? 'selected' : '' }}>
                                        {{ $option->option_text }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="sort_by" class="form-label fw-semibold">Ordenar por</label>
                    <select name="sort_by" id="sort_by" class="form-select" onchange="document.getElementById('filterForm').submit()">
                        <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Fecha creación</option>
                        <option value="used_at" {{ request('sort_by') == 'used_at' ? 'selected' : '' }}>Fecha uso</option>
                        <option value="vote_attempts" {{ request('sort_by') == 'vote_attempts' ? 'selected' : '' }}>Intentos de voto</option>
                        <option value="last_attempt_at" {{ request('sort_by') == 'last_attempt_at' ? 'selected' : '' }}>Último intento</option>
                    </select>
                </div>
            </div>
            <div class="mt-3">
                <a href="{{ route('admin.surveys.tokens.index', $survey) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Limpiar filtros
                </a>
            </div>
        </form>
    </div>

    <!-- Tabla de Tokens -->
    <div class="modern-card">
        <h5 class="mb-4 fw-bold" style="color: #1e293b;">
            <i class="bi bi-list-ul" style="color: #667eea;"></i> Lista de Tokens
            <span class="badge bg-secondary ms-2">{{ $tokens->total() }} resultados</span>
        </h5>
        <div>
            @if($tokens->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                            <tr>
                                <th style="width: 300px; color: #475569; font-weight: 600; font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1rem;">Token</th>
                                <th style="color: #475569; font-weight: 600; font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1rem;">Fuente</th>
                                <th style="color: #475569; font-weight: 600; font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1rem;">Campaign ID</th>
                                <th style="color: #475569; font-weight: 600; font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1rem;">Estado</th>
                                <th style="color: #475569; font-weight: 600; font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1rem;">Intentos</th>
                                <th style="color: #475569; font-weight: 600; font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1rem;">Usado</th>
                                <th style="color: #475569; font-weight: 600; font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1rem;">Creado</th>
                                <th class="text-center" style="color: #475569; font-weight: 600; font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1rem;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tokens as $token)
                                <tr>
                                    <td>
                                        <code class="text-muted small">{{ Str::limit($token->token, 20) }}</code>
                                        @if($token->status === 'pending')
                                            @php
                                                $individualTokenUrl = $survey->survey_group_id && $survey->group && $survey->group->slug
                                                    ? url('/t/' . $survey->group->slug . '/' . $survey->public_slug . '?token=' . $token->token)
                                                    : url('/t/' . $survey->public_slug . '?token=' . $token->token);
                                            @endphp
                                            <button class="btn btn-sm btn-link p-0 ms-2" onclick="copyToClipboard('{{ $individualTokenUrl }}')" title="Copiar URL">
                                                <i class="bi bi-clipboard"></i>
                                            </button>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $token->source }}</span>
                                    </td>
                                    <td>
                                        @if($token->campaign_id)
                                            <span class="badge bg-info">{{ $token->campaign_id }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
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
                                    </td>
                                    <td>
                                        @if($token->vote_attempts > 1)
                                            <span class="badge bg-danger" style="font-size: 0.875rem; padding: 0.5rem 0.75rem;">
                                                <i class="bi bi-exclamation-triangle-fill"></i> {{ $token->vote_attempts }} intentos
                                            </span>
                                        @elseif($token->vote_attempts == 1)
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle"></i> {{ $token->vote_attempts }}
                                            </span>
                                        @else
                                            <span class="text-muted">{{ $token->vote_attempts }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($token->used_at)
                                            <small class="text-muted">{{ $token->used_at->format('d/m/Y H:i') }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $token->created_at->format('d/m/Y H:i') }}</small>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.surveys.tokens.show', [$survey, $token]) }}" class="btn btn-sm btn-outline-primary" title="Ver detalles">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <form action="{{ route('admin.surveys.tokens.destroy', [$survey, $token]) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este token?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                                    <i class="bi bi-trash"></i>
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
                <div class="text-center py-5">
                    <i class="bi bi-key text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-3">No hay tokens generados aún.</p>
                </div>
            @endif
        </div>
        @if($tokens->hasPages())
            <div class="card-footer">
                {{ $tokens->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal: Generar Tokens -->
<div class="modal fade" id="generateTokensModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.surveys.tokens.generate', $survey) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle"></i> Generar Tokens
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Cantidad de Tokens *</label>
                        <input type="number" class="form-control" id="quantity" name="quantity"
                               min="1" max="1000000" value="100" required>
                        <small class="text-muted">Máximo: 1,000,000 tokens</small>
                    </div>

                    <div class="mb-3">
                        <label for="source" class="form-label">Fuente *</label>
                        <input type="text" class="form-control" id="source" name="source"
                               placeholder="Ej: facebook-ads, whatsapp, email" required>
                        <small class="text-muted">Identifica el origen de estos tokens</small>
                    </div>

                    <div class="mb-3">
                        <label for="campaign_id" class="form-label">Campaign ID (Opcional)</label>
                        <input type="text" class="form-control" id="campaign_id" name="campaign_id"
                               placeholder="Ej: campaña-2024-Q1">
                        <small class="text-muted">ID de la campaña para analíticas</small>
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>Nota:</strong> Los tokens se generarán de forma masiva y podrás exportarlos luego.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Generar Tokens
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function copyToClipboard(text, buttonId) {
    navigator.clipboard.writeText(text).then(function() {
        if (buttonId) {
            const btnSpan = document.getElementById(buttonId);
            const originalText = btnSpan.textContent;
            btnSpan.textContent = '¡Copiado!';
            setTimeout(() => {
                btnSpan.textContent = originalText;
            }, 2000);
        } else {
            alert('URL copiada al portapapeles!');
        }
    }, function(err) {
        console.error('Error al copiar: ', err);
        alert('Error al copiar la URL');
    });
}
</script>
@endsection
