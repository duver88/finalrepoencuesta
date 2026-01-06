@extends('layouts.admin')

@section('title', 'Tokens Sospechosos - ' . $survey->title)

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="mb-4 d-flex justify-content-between align-items-start">
        <div>
            <h1 class="h2 fw-bold mb-1" style="color: #1e293b;">
                <i class="bi bi-shield-exclamation text-warning"></i> Análisis de Tokens Sospechosos
            </h1>
            <p class="text-muted mb-0">{{ $survey->title }}</p>
            <small class="text-muted">Generado: {{ now()->format('d/m/Y H:i:s') }}</small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.surveys.suspicious-tokens.export', $survey) }}" class="btn btn-outline-success">
                <i class="bi bi-file-earmark-spreadsheet"></i> Exportar CSV Sospechosos
            </a>
            <a href="{{ route('admin.surveys.suspicious-tokens.export-clean', $survey) }}" class="btn btn-success">
                <i class="bi bi-file-earmark-check"></i> Exportar CSV Limpio (Sin Sospechosos)
            </a>
            <a href="{{ route('admin.surveys.report.export-excel', $survey) }}" class="btn btn-outline-primary">
                <i class="bi bi-file-earmark-excel"></i> Exportar Todos los Votos
            </a>
            <a href="{{ route('admin.surveys.report', $survey) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Volver al Reporte
            </a>
        </div>
    </div>

    <!-- Resumen -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <h3 class="h5 fw-bold mb-3" style="color: #1e293b;">
                <i class="bi bi-info-circle-fill text-primary"></i> Resumen de Análisis
            </h3>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon stat-primary">
                    <i class="bi bi-key-fill"></i>
                </div>
                <div>
                    <p class="text-muted mb-1 small fw-medium text-uppercase" style="letter-spacing: 0.5px;">Total Tokens</p>
                    <h3 class="mb-0 fw-bold" style="color: #1e293b;">{{ number_format($suspiciousData['summary']['total_tokens']) }}</h3>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card" style="--primary-gradient: var(--danger-gradient);">
                <div class="stat-icon stat-danger">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <div>
                    <p class="text-muted mb-1 small fw-medium text-uppercase" style="letter-spacing: 0.5px;">Tokens Sospechosos</p>
                    <h3 class="mb-0 fw-bold" style="color: #1e293b;">{{ number_format($suspiciousData['summary']['total_suspicious']) }}</h3>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card" style="--primary-gradient: var(--warning-gradient);">
                <div class="stat-icon stat-warning">
                    <i class="bi bi-clock-fill"></i>
                </div>
                <div>
                    <p class="text-muted mb-1 small fw-medium text-uppercase" style="letter-spacing: 0.5px;">Mismo Minuto</p>
                    <h3 class="mb-0 fw-bold" style="color: #1e293b;">{{ number_format($suspiciousData['summary']['same_minute_count']) }}</h3>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card" style="--primary-gradient: var(--info-gradient);">
                <div class="stat-icon stat-info">
                    <i class="bi bi-stopwatch-fill"></i>
                </div>
                <div>
                    <p class="text-muted mb-1 small fw-medium text-uppercase" style="letter-spacing: 0.5px;">1-2 Min Diff</p>
                    <h3 class="mb-0 fw-bold" style="color: #1e293b;">{{ number_format($suspiciousData['summary']['one_minute_count'] + $suspiciousData['summary']['two_minute_count']) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Descripción del Análisis -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="fw-bold mb-3"><i class="bi bi-lightbulb-fill text-warning"></i> Criterios de Detección</h5>
            <div class="row">
                <div class="col-md-4">
                    <div class="alert alert-warning mb-0">
                        <strong><i class="bi bi-clock"></i> Mismo Minuto:</strong>
                        <p class="mb-0 small">Múltiples tokens usados en el mismo minuto (ej: 13:01 y 13:01)</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="alert alert-info mb-0">
                        <strong><i class="bi bi-stopwatch"></i> 1 Minuto de Diferencia:</strong>
                        <p class="mb-0 small">Tokens con 1 minuto exacto de diferencia (ej: 13:01 y 13:02)</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="alert alert-secondary mb-0">
                        <strong><i class="bi bi-stopwatch"></i> 2 Minutos de Diferencia:</strong>
                        <p class="mb-0 small">Tokens con 2 minutos exactos de diferencia (ej: 13:01 y 13:03)</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tokens en el Mismo Minuto -->
    @if(count($suspiciousData['same_minute']) > 0)
    <div class="card mb-4">
        <div class="card-header bg-warning bg-opacity-10">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-clock-fill text-warning"></i>
                Tokens Usados en el Mismo Minuto ({{ count($suspiciousData['same_minute']) }} grupos)
            </h5>
        </div>
        <div class="card-body">
            @foreach($suspiciousData['same_minute'] as $index => $group)
            <div class="mb-4 p-3 border rounded bg-light">
                <h6 class="fw-bold text-danger">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    Grupo {{ $index + 1 }}: {{ $group['timestamp'] }} ({{ $group['count'] }} tokens)
                </h6>
                <div class="table-responsive mt-3">
                    <table class="table table-sm table-bordered table-hover">
                        <thead class="table-warning">
                            <tr>
                                <th>Token</th>
                                <th>Hora Exacta</th>
                                <th>Fingerprint</th>
                                <th>Pregunta 1</th>
                                <th>Respuesta 1</th>
                                <th>Pregunta 2</th>
                                <th>Respuesta 2</th>
                                <th>Estado</th>
                                <th>Fraud Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($group['tokens'] as $tokenData)
                            <tr>
                                <td><code class="small">{{ substr($tokenData['token'], 0, 12) }}...</code></td>
                                <td class="small">{{ $tokenData['used_at'] }}</td>
                                <td class="small">{{ substr($tokenData['fingerprint'], 0, 10) }}...</td>
                                <td class="small">{{ $tokenData['votes'][0]['question'] ?? 'N/A' }}</td>
                                <td class="small fw-bold">{{ $tokenData['votes'][0]['answer'] ?? 'N/A' }}</td>
                                <td class="small">{{ $tokenData['votes'][1]['question'] ?? 'N/A' }}</td>
                                <td class="small fw-bold">{{ $tokenData['votes'][1]['answer'] ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-{{ $tokenData['votes'][0]['status'] == 'approved' ? 'success' : ($tokenData['votes'][0]['status'] == 'pending_review' ? 'warning' : 'danger') }}">
                                        {{ ucfirst($tokenData['votes'][0]['status'] ?? 'N/A') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $tokenData['votes'][0]['fraud_score'] >= 60 ? 'danger' : 'secondary' }}">
                                        {{ $tokenData['votes'][0]['fraud_score'] ?? 0 }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @else
    <div class="alert alert-success">
        <i class="bi bi-check-circle-fill"></i> No se encontraron tokens usados en el mismo minuto.
    </div>
    @endif

    <!-- Tokens con 1 Minuto de Diferencia -->
    @if(count($suspiciousData['one_minute_diff']) > 0)
    <div class="card mb-4">
        <div class="card-header bg-info bg-opacity-10">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-stopwatch-fill text-info"></i>
                Tokens con 1 Minuto de Diferencia ({{ count($suspiciousData['one_minute_diff']) }} grupos)
            </h5>
        </div>
        <div class="card-body">
            @foreach($suspiciousData['one_minute_diff'] as $index => $group)
            <div class="mb-4 p-3 border rounded bg-light">
                <h6 class="fw-bold text-info">
                    <i class="bi bi-arrow-left-right"></i>
                    Grupo {{ $index + 1 }}: {{ $group['timestamp_1'] }} ↔ {{ $group['timestamp_2'] }} ({{ $group['total_count'] }} tokens)
                </h6>

                <div class="mt-3">
                    <strong class="text-muted">Tokens en {{ $group['timestamp_1'] }}:</strong>
                    <div class="table-responsive mt-2">
                        <table class="table table-sm table-bordered table-hover">
                            <thead class="table-info">
                                <tr>
                                    <th>Token</th>
                                    <th>Hora Exacta</th>
                                    <th>Fingerprint</th>
                                    <th>Respuesta 1</th>
                                    <th>Respuesta 2</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($group['tokens_at_time_1'] as $tokenData)
                                <tr>
                                    <td><code class="small">{{ substr($tokenData['token'], 0, 12) }}...</code></td>
                                    <td class="small">{{ $tokenData['used_at'] }}</td>
                                    <td class="small">{{ substr($tokenData['fingerprint'], 0, 10) }}...</td>
                                    <td class="small fw-bold">{{ $tokenData['votes'][0]['answer'] ?? 'N/A' }}</td>
                                    <td class="small fw-bold">{{ $tokenData['votes'][1]['answer'] ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $tokenData['votes'][0]['status'] == 'approved' ? 'success' : ($tokenData['votes'][0]['status'] == 'pending_review' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($tokenData['votes'][0]['status'] ?? 'N/A') }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-3">
                    <strong class="text-muted">Tokens en {{ $group['timestamp_2'] }}:</strong>
                    <div class="table-responsive mt-2">
                        <table class="table table-sm table-bordered table-hover">
                            <thead class="table-info">
                                <tr>
                                    <th>Token</th>
                                    <th>Hora Exacta</th>
                                    <th>Fingerprint</th>
                                    <th>Respuesta 1</th>
                                    <th>Respuesta 2</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($group['tokens_at_time_2'] as $tokenData)
                                <tr>
                                    <td><code class="small">{{ substr($tokenData['token'], 0, 12) }}...</code></td>
                                    <td class="small">{{ $tokenData['used_at'] }}</td>
                                    <td class="small">{{ substr($tokenData['fingerprint'], 0, 10) }}...</td>
                                    <td class="small fw-bold">{{ $tokenData['votes'][0]['answer'] ?? 'N/A' }}</td>
                                    <td class="small fw-bold">{{ $tokenData['votes'][1]['answer'] ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $tokenData['votes'][0]['status'] == 'approved' ? 'success' : ($tokenData['votes'][0]['status'] == 'pending_review' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($tokenData['votes'][0]['status'] ?? 'N/A') }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @else
    <div class="alert alert-success">
        <i class="bi bi-check-circle-fill"></i> No se encontraron tokens con 1 minuto de diferencia.
    </div>
    @endif

    <!-- Tokens con 2 Minutos de Diferencia -->
    @if(count($suspiciousData['two_minute_diff']) > 0)
    <div class="card mb-4">
        <div class="card-header bg-secondary bg-opacity-10">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-stopwatch-fill text-secondary"></i>
                Tokens con 2 Minutos de Diferencia ({{ count($suspiciousData['two_minute_diff']) }} grupos)
            </h5>
        </div>
        <div class="card-body">
            @foreach($suspiciousData['two_minute_diff'] as $index => $group)
            <div class="mb-4 p-3 border rounded bg-light">
                <h6 class="fw-bold text-secondary">
                    <i class="bi bi-arrow-left-right"></i>
                    Grupo {{ $index + 1 }}: {{ $group['timestamp_1'] }} ↔ {{ $group['timestamp_2'] }} ({{ $group['total_count'] }} tokens)
                </h6>

                <div class="mt-3">
                    <strong class="text-muted">Tokens en {{ $group['timestamp_1'] }}:</strong>
                    <div class="table-responsive mt-2">
                        <table class="table table-sm table-bordered table-hover">
                            <thead class="table-secondary">
                                <tr>
                                    <th>Token</th>
                                    <th>Hora Exacta</th>
                                    <th>Fingerprint</th>
                                    <th>Respuesta 1</th>
                                    <th>Respuesta 2</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($group['tokens_at_time_1'] as $tokenData)
                                <tr>
                                    <td><code class="small">{{ substr($tokenData['token'], 0, 12) }}...</code></td>
                                    <td class="small">{{ $tokenData['used_at'] }}</td>
                                    <td class="small">{{ substr($tokenData['fingerprint'], 0, 10) }}...</td>
                                    <td class="small fw-bold">{{ $tokenData['votes'][0]['answer'] ?? 'N/A' }}</td>
                                    <td class="small fw-bold">{{ $tokenData['votes'][1]['answer'] ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $tokenData['votes'][0]['status'] == 'approved' ? 'success' : ($tokenData['votes'][0]['status'] == 'pending_review' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($tokenData['votes'][0]['status'] ?? 'N/A') }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-3">
                    <strong class="text-muted">Tokens en {{ $group['timestamp_2'] }}:</strong>
                    <div class="table-responsive mt-2">
                        <table class="table table-sm table-bordered table-hover">
                            <thead class="table-secondary">
                                <tr>
                                    <th>Token</th>
                                    <th>Hora Exacta</th>
                                    <th>Fingerprint</th>
                                    <th>Respuesta 1</th>
                                    <th>Respuesta 2</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($group['tokens_at_time_2'] as $tokenData)
                                <tr>
                                    <td><code class="small">{{ substr($tokenData['token'], 0, 12) }}...</code></td>
                                    <td class="small">{{ $tokenData['used_at'] }}</td>
                                    <td class="small">{{ substr($tokenData['fingerprint'], 0, 10) }}...</td>
                                    <td class="small fw-bold">{{ $tokenData['votes'][0]['answer'] ?? 'N/A' }}</td>
                                    <td class="small fw-bold">{{ $tokenData['votes'][1]['answer'] ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $tokenData['votes'][0]['status'] == 'approved' ? 'success' : ($tokenData['votes'][0]['status'] == 'pending_review' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($tokenData['votes'][0]['status'] ?? 'N/A') }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @else
    <div class="alert alert-success">
        <i class="bi bi-check-circle-fill"></i> No se encontraron tokens con 2 minutos de diferencia.
    </div>
    @endif
</div>

<style>
.stat-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    border: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.stat-icon.stat-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.stat-icon.stat-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
}

.stat-icon.stat-warning {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
}

.stat-icon.stat-danger {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
}

.stat-icon.stat-info {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: white;
}
</style>
@endsection
