@extends('layouts.admin')

@section('title', 'Crear Grupo de Encuestas')

@section('content')
<div class="container-fluid px-0">
    <div class="mb-4">
        <h1 class="h2 fw-bold mb-1" style="color: #1e293b;">
            <i class="bi bi-collection"></i> Nuevo Grupo de Encuestas
        </h1>
        <p class="text-muted mb-0">Crea un grupo para restringir votaciones múltiples</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="modern-card">
                <form action="{{ url('admin/survey-groups') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label for="name" class="form-label fw-semibold" style="color: #1e293b;">
                            Nombre del Grupo <span class="text-danger">*</span>
                        </label>
                        <input 
                            type="text" 
                            class="form-control @error('name') is-invalid @enderror" 
                            id="name" 
                            name="name" 
                            value="{{ old('name') }}" 
                            required
                            placeholder="Ej: Encuestas de Favorabilidad 2024"
                        >
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="description" class="form-label fw-semibold" style="color: #1e293b;">
                            Descripción
                        </label>
                        <textarea 
                            class="form-control @error('description') is-invalid @enderror" 
                            id="description" 
                            name="description" 
                            rows="3"
                            placeholder="Describe el propósito de este grupo..."
                        >{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input 
                                class="form-check-input" 
                                type="checkbox" 
                                id="restrict_voting" 
                                name="restrict_voting" 
                                value="1"
                                {{ old('restrict_voting', true) ? 'checked' : '' }}
                            >
                            <label class="form-check-label fw-semibold" for="restrict_voting" style="color: #1e293b;">
                                Restringir votación
                            </label>
                        </div>
                        <small class="text-muted">
                            Si está activo, un usuario solo podrá votar en UNA encuesta de este grupo. Las demás quedarán bloqueadas para ese usuario.
                        </small>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-gradient-primary">
                            <i class="bi bi-save"></i> Crear Grupo
                        </button>
                        <a href="{{ url('admin/survey-groups') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="alert alert-info">
                <h6 class="alert-heading"><i class="bi bi-info-circle"></i> ¿Cómo funciona?</h6>
                <p class="small mb-2">Los grupos de encuestas te permiten:</p>
                <ul class="small mb-0">
                    <li>Agrupar encuestas relacionadas</li>
                    <li>Restringir que un usuario vote en múltiples encuestas del grupo</li>
                    <li>Útil para encuestas duplicadas o variantes de la misma encuesta</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
