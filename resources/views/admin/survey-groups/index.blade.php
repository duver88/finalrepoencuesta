@extends('layouts.admin')

@section('title', 'Grupos de Encuestas')

@section('content')
<div class="container-fluid px-0">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h2 fw-bold mb-1" style="color: #1e293b;">
                <i class="bi bi-collection"></i> Grupos de Encuestas
            </h1>
            <p class="text-muted mb-0">Agrupa encuestas para restringir que un usuario vote en múltiples</p>
        </div>
        <a href="{{ route('admin.survey-groups.create') }}" class="btn btn-gradient-primary">
            <i class="bi bi-plus-circle"></i> Nuevo Grupo
        </a>
    </div>

    <div class="modern-card">
        @if($groups->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                        <tr>
                            <th style="color: #475569; font-weight: 600; font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1rem;">Grupo</th>
                            <th style="color: #475569; font-weight: 600; font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1rem;">Encuestas</th>
                            <th style="color: #475569; font-weight: 600; font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1rem;">Restricción</th>
                            <th class="text-end" style="color: #475569; font-weight: 600; font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1rem;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($groups as $group)
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 1rem;">
                                    <div class="fw-semibold" style="color: #1e293b; font-size: 0.9375rem;">{{ $group->name }}</div>
                                    @if($group->description)
                                        <small class="text-muted">{{ Str::limit($group->description, 60) }}</small>
                                    @endif
                                </td>
                                <td style="padding: 1rem;">
                                    <span class="badge bg-primary">{{ $group->surveys_count }} encuestas</span>
                                </td>
                                <td style="padding: 1rem;">
                                    @if($group->restrict_voting)
                                        <span class="badge" style="background: rgba(239, 68, 68, 0.15); color: #dc2626; border: 1px solid rgba(239, 68, 68, 0.3);">
                                            <i class="bi bi-shield-lock-fill"></i> Activa
                                        </span>
                                    @else
                                        <span class="badge" style="background: #f1f5f9; color: #64748b;">
                                            <i class="bi bi-unlock-fill"></i> Desactivada
                                        </span>
                                    @endif
                                </td>
                                <td style="padding: 1rem;" class="text-end">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ url('admin/survey-groups/' . $group->id) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-eye"></i> Ver
                                        </a>
                                        <a href="{{ route('admin.survey-groups.report', $group) }}" class="btn btn-primary btn-sm">
                                            <i class="bi bi-file-text"></i> Reporte
                                        </a>
                                        <a href="{{ url('admin/survey-groups/' . $group->id . '/edit') }}" class="btn btn-outline-secondary btn-sm">
                                            <i class="bi bi-pencil"></i> Editar
                                        </a>
                                        <form action="{{ url('admin/survey-groups/' . $group->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este grupo? Las encuestas NO se eliminarán.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm">
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

            <div class="p-3">
                {{ $groups->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-collection" style="font-size: 3rem; color: #cbd5e1;"></i>
                <p class="text-muted mt-3">No hay grupos creados todavía.</p>
                <a href="{{ route('admin.survey-groups.create') }}" class="btn btn-gradient-primary mt-2">
                    <i class="bi bi-plus-circle"></i> Crear Primer Grupo
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
