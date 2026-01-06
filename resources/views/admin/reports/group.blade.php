@extends('layouts.admin')

@section('title', 'Reporte de Grupo - ' . $group->name)

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="mb-4 d-flex justify-content-between align-items-start">
        <div>
            <h1 class="h2 fw-bold mb-1" style="color: #1e293b;">Reporte de Grupo</h1>
            <p class="text-muted mb-0">{{ $group->name }}</p>
            <small class="text-muted">Generado: {{ now()->format('d/m/Y H:i:s') }}</small>
            @if($group->restrict_voting)
                <span class="badge bg-warning ms-2">Votación Restringida</span>
            @endif
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.survey-groups.report.export-excel', $group) }}" class="btn btn-success">
                <i class="bi bi-file-earmark-excel"></i> Exportar Excel (Todos)
            </a>
            <a href="{{ route('admin.survey-groups.report.export-excel-valid', $group) }}" class="btn btn-outline-success">
                <i class="bi bi-file-earmark-excel"></i> Excel (Solo Válidos)
            </a>
            <a href="{{ route('admin.survey-groups.suspicious-tokens', $group) }}" class="btn btn-warning">
                <i class="bi bi-shield-exclamation"></i> Tokens Sospechosos
            </a>
            <a href="{{ route('admin.survey-groups.report.export-csv', $group) }}" class="btn btn-outline-success">
                <i class="bi bi-file-earmark-spreadsheet"></i> CSV Reporte
            </a>
            <a href="{{ route('admin.survey-groups.report.export-pdf', $group) }}" class="btn btn-outline-danger">
                <i class="bi bi-file-earmark-pdf"></i> PDF
            </a>
            <a href="{{ route('admin.survey-groups.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <!-- Estadísticas Generales del Grupo -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <h3 class="h5 fw-bold mb-3" style="color: #1e293b;">
                <i class="bi bi-bar-chart-fill text-primary"></i> Estadísticas Generales del Grupo
            </h3>
        </div>

        <div class="col-12 col-md-6 col-xl-2-4">
            <div class="stat-card">
                <div class="stat-icon stat-primary">
                    <i class="bi bi-collection-fill"></i>
                </div>
                <div>
                    <p class="text-muted mb-1 small fw-medium text-uppercase" style="letter-spacing: 0.5px;">Encuestas</p>
                    <h3 class="mb-0 fw-bold" style="color: #1e293b;">{{ number_format($report['basic_stats']['total_surveys']) }}</h3>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-2-4">
            <div class="stat-card" style="--primary-gradient: var(--info-gradient);">
                <div class="stat-icon stat-info">
                    <i class="bi bi-eye-fill"></i>
                </div>
                <div>
                    <p class="text-muted mb-1 small fw-medium text-uppercase" style="letter-spacing: 0.5px;">Vistas Totales</p>
                    <h3 class="mb-0 fw-bold" style="color: #1e293b;">{{ number_format($report['basic_stats']['total_views']) }}</h3>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-2-4">
            <div class="stat-card" style="--primary-gradient: var(--success-gradient);">
                <div class="stat-icon stat-success">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div>
                    <p class="text-muted mb-1 small fw-medium text-uppercase" style="letter-spacing: 0.5px;">Votos Válidos</p>
                    <h3 class="mb-0 fw-bold" style="color: #1e293b;">{{ number_format($report['basic_stats']['valid_votes']) }}</h3>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-2-4">
            <div class="stat-card" style="--primary-gradient: var(--warning-gradient);">
                <div class="stat-icon stat-warning">
                    <i class="bi bi-send-fill"></i>
                </div>
                <div>
                    <p class="text-muted mb-1 small fw-medium text-uppercase" style="letter-spacing: 0.5px;">Votos Enviados</p>
                    <h3 class="mb-0 fw-bold" style="color: #1e293b;">{{ number_format($report['basic_stats']['total_votes_submitted']) }}</h3>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-2-4">
            <div class="stat-card" style="--primary-gradient: var(--secondary-gradient);">
                <div class="stat-icon stat-secondary">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div>
                    <p class="text-muted mb-1 small fw-medium text-uppercase" style="letter-spacing: 0.5px;">Votantes Únicos</p>
                    <h3 class="mb-0 fw-bold" style="color: #1e293b;">{{ number_format($report['basic_stats']['unique_voters']) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Métricas de Conversión -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <h3 class="h5 fw-bold mb-3" style="color: #1e293b;">
                <i class="bi bi-graph-up text-success"></i> Métricas de Conversión del Grupo
            </h3>
        </div>

        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-arrow-right-circle text-primary me-2"></i>
                        <span class="text-muted small">Vistas → Votos</span>
                    </div>
                    <h2 class="mb-0 fw-bold" style="color: #1e293b;">{{ $report['conversion_metrics']['view_to_vote_rate'] }}%</h2>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        <span class="text-muted small">Votos → Aprobados</span>
                    </div>
                    <h2 class="mb-0 fw-bold" style="color: #1e293b;">{{ $report['conversion_metrics']['vote_approval_rate'] }}%</h2>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-trophy text-warning me-2"></i>
                        <span class="text-muted small">Conversión Total</span>
                    </div>
                    <h2 class="mb-0 fw-bold" style="color: #1e293b;">{{ $report['conversion_metrics']['complete_conversion_rate'] }}%</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Votos No Contados -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <h3 class="h5 fw-bold mb-3" style="color: #1e293b;">
                <i class="bi bi-exclamation-triangle text-danger"></i> Votos No Contados
            </h3>
        </div>

        <div class="col-12 col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-clock text-warning me-2"></i>
                        <span class="text-muted small">Pendientes Revisión</span>
                    </div>
                    <h3 class="mb-0 fw-bold" style="color: #1e293b;">{{ number_format($report['basic_stats']['pending_review']) }}</h3>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-x-circle text-danger me-2"></i>
                        <span class="text-muted small">Rechazados</span>
                    </div>
                    <h3 class="mb-0 fw-bold" style="color: #1e293b;">{{ number_format($report['basic_stats']['rejected_votes']) }}</h3>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-shield-x text-secondary me-2"></i>
                        <span class="text-muted small">Sin Token Válido</span>
                    </div>
                    <h3 class="mb-0 fw-bold" style="color: #1e293b;">{{ number_format($report['basic_stats']['not_counted_votes']) }}</h3>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-bug text-danger me-2"></i>
                        <span class="text-muted small">Duplicados/Fraudulentos</span>
                    </div>
                    <h3 class="mb-0 fw-bold" style="color: #1e293b;">{{ number_format($report['basic_stats']['duplicate_or_fraudulent']) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas por Encuesta Individual -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <h3 class="h5 fw-bold mb-3" style="color: #1e293b;">
                <i class="bi bi-list-check text-primary"></i> Desempeño por Encuesta
            </h3>
        </div>

        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th style="color: #1e293b;">Encuesta</th>
                                    <th class="text-center" style="color: #1e293b;">Vistas</th>
                                    <th class="text-center" style="color: #1e293b;">Votos Válidos</th>
                                    <th class="text-center" style="color: #1e293b;">Conversión</th>
                                    <th class="text-center" style="color: #1e293b;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($report['per_survey_stats'] as $surveyStat)
                                <tr>
                                    <td>
                                        <div class="fw-medium" style="color: #1e293b;">{{ $surveyStat['survey_title'] }}</div>
                                        <small class="text-muted">{{ $surveyStat['survey_slug'] }}</small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info">{{ number_format($surveyStat['views']) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success">{{ number_format($surveyStat['valid_votes']) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <strong style="color: #1e293b;">{{ $surveyStat['conversion_rate'] }}%</strong>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.surveys.report', $surveyStat['survey_id']) }}"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-file-text"></i> Ver Reporte
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Resultados Agregados por Pregunta -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <h3 class="h5 fw-bold mb-3" style="color: #1e293b;">
                <i class="bi bi-question-circle text-info"></i> Resultados Agregados por Pregunta
            </h3>
            <p class="text-muted small">Suma de votos de todas las encuestas del grupo</p>
        </div>

        @foreach($report['question_stats'] as $questionStat)
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h4 class="h6 fw-bold mb-3" style="color: #1e293b;">{{ $questionStat['question_text'] }}</h4>
                    <p class="text-muted small mb-3">Total de votos agregados: {{ number_format($questionStat['total_votes']) }}</p>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th style="color: #1e293b;">Opción</th>
                                    <th class="text-center" style="color: #1e293b;">Votos Totales</th>
                                    <th class="text-center" style="color: #1e293b;">Porcentaje</th>
                                    <th style="color: #1e293b;">Distribución</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($questionStat['options'] as $option)
                                <tr>
                                    <td class="fw-medium">{{ $option['option_text'] }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-primary">{{ number_format($option['votes']) }}</span>
                                    </td>
                                    <td class="text-center fw-bold" style="color: #1e293b;">{{ $option['percentage'] }}%</td>
                                    <td>
                                        <div class="progress" style="height: 25px;">
                                            <div class="progress-bar bg-primary" role="progressbar"
                                                 style="width: {{ $option['percentage'] }}%;"
                                                 aria-valuenow="{{ $option['percentage'] }}"
                                                 aria-valuemin="0"
                                                 aria-valuemax="100">
                                                {{ $option['percentage'] }}%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Análisis de Fraude Agregado -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <h3 class="h5 fw-bold mb-3" style="color: #1e293b;">
                <i class="bi bi-shield-check text-danger"></i> Análisis de Fraude del Grupo
            </h3>
        </div>

        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-speedometer2 text-warning" style="font-size: 2rem;"></i>
                    <p class="text-muted small mt-2 mb-1">Score Promedio</p>
                    <h3 class="mb-0 fw-bold" style="color: #1e293b;">{{ $report['fraud_stats']['average_fraud_score'] }}</h3>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-exclamation-triangle text-danger" style="font-size: 2rem;"></i>
                    <p class="text-muted small mt-2 mb-1">Votos Alto Riesgo</p>
                    <h3 class="mb-0 fw-bold" style="color: #1e293b;">{{ number_format($report['fraud_stats']['high_risk_count']) }}</h3>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-percent text-info" style="font-size: 2rem;"></i>
                    <p class="text-muted small mt-2 mb-1">% Alto Riesgo</p>
                    <h3 class="mb-0 fw-bold" style="color: #1e293b;">{{ $report['fraud_stats']['high_risk_percentage'] }}%</h3>
                </div>
            </div>
        </div>

        @if(!empty($report['fraud_stats']['fraud_reasons_distribution']))
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="h6 fw-bold mb-3" style="color: #1e293b;">Razones de Fraude Detectadas en el Grupo</h5>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th style="color: #1e293b;">Razón</th>
                                    <th class="text-end" style="color: #1e293b;">Cantidad</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($report['fraud_stats']['fraud_reasons_distribution'] as $reason => $count)
                                <tr>
                                    <td>{{ $reason }}</td>
                                    <td class="text-end">
                                        <span class="badge bg-warning">{{ number_format($count) }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Tokens con Intentos Duplicados del Grupo -->
        @if($report['fraud_stats']['duplicate_token_stats']['total_tokens_with_duplicates'] > 0)
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="h6 fw-bold mb-3" style="color: #1e293b;">
                        <i class="bi bi-arrow-repeat text-danger"></i> Tokens con Intentos Duplicados (Todas las Encuestas)
                    </h5>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="alert alert-warning mb-0">
                                <strong>{{ number_format($report['fraud_stats']['duplicate_token_stats']['total_tokens_with_duplicates']) }}</strong> tokens
                                con múltiples intentos de voto
                                <br>
                                <strong>{{ number_format($report['fraud_stats']['duplicate_token_stats']['total_duplicate_attempts']) }}</strong> intentos
                                totales registrados en todo el grupo
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="small fw-bold mb-2">Distribución por Intentos:</h6>
                            @foreach($report['fraud_stats']['duplicate_token_stats']['attempts_by_count'] as $attempts => $count)
                                <span class="badge bg-secondary me-1">{{ $attempts }} intentos: {{ $count }} tokens</span>
                            @endforeach
                        </div>
                    </div>

                    @if(!empty($report['fraud_stats']['duplicate_token_stats']['top_duplicate_tokens']))
                    <h6 class="small fw-bold mb-2">Top 10 Tokens con Más Intentos:</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th style="color: #1e293b;">Token</th>
                                    <th class="text-center" style="color: #1e293b;">Intentos</th>
                                    <th class="text-center" style="color: #1e293b;">Estado</th>
                                    <th class="text-center" style="color: #1e293b;">Encuesta</th>
                                    <th style="color: #1e293b;">Último Intento</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($report['fraud_stats']['duplicate_token_stats']['top_duplicate_tokens'] as $tokenData)
                                <tr>
                                    <td>
                                        <code class="text-muted small" title="{{ $tokenData['full_token'] }}">
                                            {{ $tokenData['token'] }}
                                        </code>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-danger">{{ $tokenData['attempts'] }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($tokenData['status'] === 'used')
                                            <span class="badge bg-success">Usado</span>
                                        @elseif($tokenData['status'] === 'pending')
                                            <span class="badge bg-secondary">Pendiente</span>
                                        @elseif($tokenData['status'] === 'reserved')
                                            <span class="badge bg-warning">Reservado</span>
                                        @else
                                            <span class="badge bg-dark">{{ $tokenData['status'] }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <small class="text-muted">#{{ $tokenData['survey_id'] }}</small>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $tokenData['last_attempt_at'] ?? 'N/A' }}</small>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Estadísticas de Tokens Agregadas -->
    <div class="row g-4">
        <div class="col-12">
            <h3 class="h5 fw-bold mb-3" style="color: #1e293b;">
                <i class="bi bi-key text-primary"></i> Estadísticas de Tokens del Grupo
            </h3>
        </div>

        <div class="col-12 col-md-6 col-xl-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-collection text-primary"></i>
                    <p class="text-muted small mt-2 mb-1">Total</p>
                    <h4 class="mb-0 fw-bold" style="color: #1e293b;">{{ number_format($report['token_stats']['total_tokens']) }}</h4>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-hourglass text-secondary"></i>
                    <p class="text-muted small mt-2 mb-1">Pendientes</p>
                    <h4 class="mb-0 fw-bold" style="color: #1e293b;">{{ number_format($report['token_stats']['pending_tokens']) }}</h4>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-clock text-warning"></i>
                    <p class="text-muted small mt-2 mb-1">Reservados</p>
                    <h4 class="mb-0 fw-bold" style="color: #1e293b;">{{ number_format($report['token_stats']['reserved_tokens']) }}</h4>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-check-circle text-success"></i>
                    <p class="text-muted small mt-2 mb-1">Usados</p>
                    <h4 class="mb-0 fw-bold" style="color: #1e293b;">{{ number_format($report['token_stats']['used_tokens']) }}</h4>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-x-circle text-danger"></i>
                    <p class="text-muted small mt-2 mb-1">Expirados</p>
                    <h4 class="mb-0 fw-bold" style="color: #1e293b;">{{ number_format($report['token_stats']['expired_tokens']) }}</h4>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-percent text-info"></i>
                    <p class="text-muted small mt-2 mb-1">Tasa de Uso</p>
                    <h4 class="mb-0 fw-bold" style="color: #1e293b;">{{ $report['token_stats']['usage_rate'] }}%</h4>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
