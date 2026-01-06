@extends('layouts.admin')

@section('title', 'Dashboard - Admin')

@section('content')
<div class="container-fluid px-0">
    <div class="mb-4">
        <h1 class="h2 fw-bold mb-1" style="color: #1e293b;">Dashboard</h1>
        <p class="text-muted">Resumen general del sistema de encuestas</p>
    </div>

    <!-- Estadísticas Modernas -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon stat-primary">
                    <i class="bi bi-clipboard-data"></i>
                </div>
                <div>
                    <p class="text-muted mb-1 small fw-medium text-uppercase" style="letter-spacing: 0.5px;">Total Encuestas</p>
                    <h3 class="mb-0 fw-bold" style="color: #1e293b;">{{ number_format($totalSurveys) }}</h3>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card" style="--primary-gradient: var(--success-gradient);">
                <div class="stat-icon stat-success">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div>
                    <p class="text-muted mb-1 small fw-medium text-uppercase" style="letter-spacing: 0.5px;">Encuestas Activas</p>
                    <h3 class="mb-0 fw-bold" style="color: #1e293b;">{{ number_format($activeSurveys) }}</h3>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card" style="--primary-gradient: var(--warning-gradient);">
                <div class="stat-icon stat-warning">
                    <i class="bi bi-chat-dots-fill"></i>
                </div>
                <div>
                    <p class="text-muted mb-1 small fw-medium text-uppercase" style="letter-spacing: 0.5px;">Total Votos</p>
                    <h3 class="mb-0 fw-bold" style="color: #1e293b;">{{ number_format($totalVotes) }}</h3>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card" style="--primary-gradient: var(--info-gradient);">
                <div class="stat-icon" style="background: linear-gradient(135deg, rgba(79, 172, 254, 0.1) 0%, rgba(0, 242, 254, 0.1) 100%); color: #4facfe;">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div>
                    <p class="text-muted mb-1 small fw-medium text-uppercase" style="letter-spacing: 0.5px;">Votantes Únicos</p>
                    <h3 class="mb-0 fw-bold" style="color: #1e293b;">{{ number_format($uniqueVoters) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Listado de encuestas -->
    <div class="modern-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="mb-1 fw-bold" style="color: #1e293b;">
                    <i class="bi bi-list-ul"></i> Encuestas Recientes
                </h5>
                <p class="text-muted small mb-0">Gestiona y visualiza tus encuestas</p>
            </div>
            <a href="{{ route('admin.surveys.create') }}" class="btn btn-gradient-primary">
                <i class="bi bi-plus-circle"></i> Nueva Encuesta
            </a>
        </div>

        @if($surveys->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                        <tr>
                            <th style="color: #475569; font-weight: 600; font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1rem;">Encuesta</th>
                            <th style="color: #475569; font-weight: 600; font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1rem;">Estado</th>
                            <th style="color: #475569; font-weight: 600; font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1rem;">Votos</th>
                            <th style="color: #475569; font-weight: 600; font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1rem;">Preguntas</th>
                            <th class="text-end" style="color: #475569; font-weight: 600; font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1rem;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($surveys as $survey)
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 1rem;">
                                    <div>
                                        <strong style="color: #1e293b; font-size: 0.9375rem;">{{ $survey->title }}</strong>
                                        @if($survey->description)
                                            <br><small class="text-muted">{{ Str::limit($survey->description, 60) }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td style="padding: 1rem;">
                                    @if($survey->is_active)
                                        <span class="badge" style="background: linear-gradient(135deg, rgba(17, 153, 142, 0.15) 0%, rgba(56, 239, 125, 0.15) 100%); color: #11998e; border: 1px solid rgba(17, 153, 142, 0.3); padding: 0.5rem 0.875rem; font-weight: 600;">
                                            <i class="bi bi-check-circle-fill"></i> Activa
                                        </span>
                                    @else
                                        <span class="badge" style="background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; padding: 0.5rem 0.875rem; font-weight: 600;">
                                            <i class="bi bi-x-circle-fill"></i> Inactiva
                                        </span>
                                    @endif
                                </td>
                                <td style="padding: 1rem;">
                                    <span class="badge" style="background: linear-gradient(135deg, rgba(240, 147, 251, 0.15) 0%, rgba(245, 87, 108, 0.15) 100%); color: #f093fb; border: 1px solid rgba(240, 147, 251, 0.3); padding: 0.5rem 0.875rem; font-weight: 600;">
                                        {{ number_format($survey->votes_count) }}
                                    </span>
                                </td>
                                <td style="padding: 1rem;">
                                    <span class="badge" style="background: linear-gradient(135deg, rgba(102, 126, 234, 0.15) 0%, rgba(118, 75, 162, 0.15) 100%); color: #667eea; border: 1px solid rgba(102, 126, 234, 0.3); padding: 0.5rem 0.875rem; font-weight: 600;">
                                        {{ $survey->questions->count() }}
                                    </span>
                                </td>
                                <td class="text-end" style="padding: 1rem;">
                                    <div class="d-inline-flex gap-2">
                                        <a href="{{ route('admin.surveys.show', $survey) }}"
                                           class="btn btn-sm"
                                           style="background: #f1f5f9; color: #64748b; border: none; padding: 0.5rem 1rem; border-radius: 8px; font-weight: 500; transition: all 0.2s;"
                                           onmouseover="this.style.background='#e2e8f0'; this.style.color='#334155';"
                                           onmouseout="this.style.background='#f1f5f9'; this.style.color='#64748b';"
                                           title="Ver Resultados">
                                            <i class="bi bi-graph-up"></i>
                                        </a>
                                        <a href="{{ route('admin.surveys.edit', $survey) }}"
                                           class="btn btn-sm"
                                           style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 0.5rem 1rem; border-radius: 8px; font-weight: 500; box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3); transition: all 0.2s;"
                                           onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 12px rgba(102, 126, 234, 0.4)';"
                                           onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(102, 126, 234, 0.3)';"
                                           title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-5">
                <div class="mb-4">
                    <div style="width: 80px; height: 80px; background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%); border-radius: 20px; display: inline-flex; align-items: center; justify-content: center;">
                        <i class="bi bi-clipboard-x" style="font-size: 2.5rem; color: #667eea;"></i>
                    </div>
                </div>
                <h5 class="fw-bold mb-2" style="color: #1e293b;">No hay encuestas</h5>
                <p class="text-muted mb-4">Comienza creando una nueva encuesta para empezar a recopilar respuestas.</p>
                <a href="{{ route('admin.surveys.create') }}" class="btn btn-gradient-primary">
                    <i class="bi bi-plus-circle"></i> Crear Primera Encuesta
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
