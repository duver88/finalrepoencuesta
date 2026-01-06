@extends('layouts.admin')

@section('title', 'Gestión de Encuestas')

@section('content')
<div class="container-fluid px-0">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h2 fw-bold mb-1" style="color: #1e293b;">
                <i class="bi bi-clipboard-data"></i> Gestión de Encuestas
            </h1>
            <p class="text-muted mb-0">Administra todas tus encuestas</p>
        </div>
        <a href="{{ route('admin.surveys.create') }}" class="btn btn-gradient-primary">
            <i class="bi bi-plus-circle"></i> Nueva Encuesta
        </a>
    </div>

    <div class="modern-card">
        @if($surveys->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                        <tr>
                            <th style="color: #475569; font-weight: 600; font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1rem;">Encuesta</th>
                            <th style="color: #475569; font-weight: 600; font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1rem;">Estado</th>
                            <th style="color: #475569; font-weight: 600; font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1rem;">Votos</th>
                            <th style="color: #475569; font-weight: 600; font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1rem;">Link Público</th>
                            <th class="text-end" style="color: #475569; font-weight: 600; font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1rem;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($surveys as $survey)
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 1rem;">
                                    <div class="fw-semibold" style="color: #1e293b; font-size: 0.9375rem;">{{ $survey->title }}</div>
                                    <small class="text-muted">
                                        <i class="bi bi-question-circle"></i> {{ $survey->questions->count() }} preguntas
                                    </small>
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
                                        <i class="bi bi-graph-up"></i> {{ number_format($survey->votes_count) }}
                                    </span>
                                </td>
                                <td style="padding: 1rem;">
                                    <a href="{{ url('/t/' . $survey->public_slug) }}" target="_blank"
                                       class="btn btn-sm"
                                       style="background: linear-gradient(135deg, rgba(79, 172, 254, 0.15) 0%, rgba(0, 242, 254, 0.15) 100%); color: #4facfe; border: 1px solid rgba(79, 172, 254, 0.3); padding: 0.5rem 0.875rem; border-radius: 8px; font-weight: 500; transition: all 0.2s;">
                                        <i class="bi bi-link-45deg"></i> Ver Encuesta
                                    </a>
                                </td>
                                <td class="text-end" style="padding: 1rem;">
                                    <div class="d-inline-flex gap-1">
                                        <a href="{{ route('admin.surveys.show', $survey) }}"
                                           class="btn btn-sm"
                                           style="background: #f1f5f9; color: #64748b; border: none; padding: 0.5rem 0.75rem; border-radius: 8px; transition: all 0.2s;"
                                           onmouseover="this.style.background='#e2e8f0'"
                                           onmouseout="this.style.background='#f1f5f9'"
                                           title="Resultados">
                                            <i class="bi bi-bar-chart"></i>
                                        </a>
                                        <a href="{{ route('admin.surveys.report', $survey) }}"
                                           class="btn btn-sm"
                                           style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; border: none; padding: 0.5rem 0.75rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(240, 147, 251, 0.3); transition: all 0.2s;"
                                           onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 12px rgba(240, 147, 251, 0.4)'"
                                           onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(240, 147, 251, 0.3)'"
                                           title="Reporte">
                                            <i class="bi bi-file-text"></i>
                                        </a>
                                        <a href="{{ route('admin.surveys.tokens.index', $survey) }}"
                                           class="btn btn-sm"
                                           style="background: #f1f5f9; color: #64748b; border: none; padding: 0.5rem 0.75rem; border-radius: 8px; transition: all 0.2s;"
                                           onmouseover="this.style.background='#e2e8f0'"
                                           onmouseout="this.style.background='#f1f5f9'"
                                           title="Tokens">
                                            <i class="bi bi-key"></i>
                                        </a>
                                        <a href="{{ route('admin.surveys.edit', $survey) }}"
                                           class="btn btn-sm"
                                           style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 0.5rem 0.75rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3); transition: all 0.2s;"
                                           onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 12px rgba(102, 126, 234, 0.4)'"
                                           onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(102, 126, 234, 0.3)'"
                                           title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.surveys.duplicate', $survey) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit"
                                                    class="btn btn-sm"
                                                    style="background: linear-gradient(135deg, rgba(255, 184, 0, 0.15) 0%, rgba(255, 143, 0, 0.15) 100%); color: #ff8f00; border: 1px solid rgba(255, 143, 0, 0.3); padding: 0.5rem 0.75rem; border-radius: 8px; transition: all 0.2s;"
                                                    title="Duplicar"
                                                    onclick="return confirm('¿Deseas duplicar esta encuesta? Se creará una copia con todas las preguntas y opciones.');">
                                                <i class="bi bi-files"></i>
                                            </button>
                                        </form>
                                        @if($survey->is_active)
                                            <form method="POST" action="{{ route('admin.surveys.unpublish', $survey) }}" class="d-inline">
                                                @csrf
                                                <button type="submit"
                                                        class="btn btn-sm"
                                                        style="background: linear-gradient(135deg, rgba(240, 147, 251, 0.15) 0%, rgba(245, 87, 108, 0.15) 100%); color: #f093fb; border: 1px solid rgba(240, 147, 251, 0.3); padding: 0.5rem 0.75rem; border-radius: 8px; transition: all 0.2s;"
                                                        title="Despublicar">
                                                    <i class="bi bi-eye-slash"></i>
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('admin.surveys.publish', $survey) }}" class="d-inline">
                                                @csrf
                                                <button type="submit"
                                                        class="btn btn-sm"
                                                        style="background: linear-gradient(135deg, rgba(17, 153, 142, 0.15) 0%, rgba(56, 239, 125, 0.15) 100%); color: #11998e; border: 1px solid rgba(17, 153, 142, 0.3); padding: 0.5rem 0.75rem; border-radius: 8px; transition: all 0.2s;"
                                                        title="Publicar">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('admin.surveys.destroy', $survey) }}"
                                              class="d-inline" onsubmit="return confirm('¿Estás seguro de eliminar esta encuesta?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="btn btn-sm"
                                                    style="background: linear-gradient(135deg, rgba(238, 9, 121, 0.15) 0%, rgba(255, 106, 0, 0.15) 100%); color: #ee0979; border: 1px solid rgba(238, 9, 121, 0.3); padding: 0.5rem 0.75rem; border-radius: 8px; transition: all 0.2s;"
                                                    title="Eliminar">
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
