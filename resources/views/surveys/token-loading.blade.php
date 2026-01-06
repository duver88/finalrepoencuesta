@extends('layouts.app')

@section('title', $survey->title)

@section('content')
<div class="min-vh-100 d-flex align-items-center" style="background: #1a1a1a; padding: 3rem 0;">
    <div class="container" style="max-width: 740px;">

        <!-- Card de carga -->
        <div class="loading-card">
            <div style="text-align: center; padding: 64px 24px;">
                <!-- Spinner animado -->
                <div style="margin-bottom: 32px;">
                    <div class="spinner">
                        <svg width="80" height="80" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="12" cy="12" r="10" stroke="#3c4043" stroke-width="2"/>
                            <path d="M12 2 A10 10 0 0 1 22 12" stroke="#fdd71a" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </div>
                </div>

                <!-- Título -->
                <h1 style="font-size: 28px; font-weight: 400; color: #ffffff; margin: 0 0 16px 0; line-height: 1.3;">
                    Cargando encuesta...
                </h1>

                <!-- Descripción -->
                <p style="font-size: 14px; color: #9aa0a6; margin: 0; line-height: 1.6;">
                    Por favor espera un momento
                </p>
            </div>
        </div>

    </div>
</div>

<style>
.loading-card {
    background: #2d2d2d;
    border: 1px solid #3c4043;
    border-radius: 8px;
    border-top: 10px solid #fdd71a;
    box-shadow: 0 1px 2px 0 rgba(60, 64, 67, 0.3), 0 1px 3px 1px rgba(60, 64, 67, 0.15);
    animation: fadeIn 0.4s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.spinner svg {
    animation: spin 1s linear infinite;
    display: inline-block;
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

/* Responsive */
@media (max-width: 768px) {
    .container {
        padding-left: 16px !important;
        padding-right: 16px !important;
    }

    .loading-card > div {
        padding: 48px 20px !important;
    }

    .loading-card h1 {
        font-size: 24px !important;
    }

    .loading-card p {
        font-size: 13px !important;
    }

    .spinner svg {
        width: 64px !important;
        height: 64px !important;
    }
}

@media (max-width: 480px) {
    .container {
        padding-left: 12px !important;
        padding-right: 12px !important;
    }

    .loading-card > div {
        padding: 40px 16px !important;
    }

    .loading-card h1 {
        font-size: 20px !important;
        line-height: 1.3 !important;
    }

    .spinner svg {
        width: 56px !important;
        height: 56px !important;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Esperar el delay configurado (3 segundos)
    setTimeout(function() {
        // Hacer petición al servidor para asignar token
        fetch('{{ route('token.assign', $publicSlug) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.redirect_url) {
                // Redirigir a la encuesta con el token asignado
                window.location.href = data.redirect_url;
            } else {
                // Si no hay tokens disponibles, mostrar página de no disponible
                window.location.href = '{{ route('surveys.show', $publicSlug) }}';
            }
        })
        .catch(error => {
            console.error('Error al asignar token:', error);
            // En caso de error, redirigir sin token
            window.location.href = '{{ route('surveys.show', $publicSlug) }}';
        });
    }, {{ $delay ?? 1000 }});
});
</script>
@endsection
