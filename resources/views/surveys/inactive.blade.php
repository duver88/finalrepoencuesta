@extends('layouts.app')

@section('title', 'Encuesta No Disponible')

@section('content')
<div class="min-vh-100 d-flex align-items-center position-relative" style="background: linear-gradient(135deg, #fff9e6 0%, #e6f2ff 50%, #ffe6e6 100%);">
    <!-- Efecto difuminado de fondo - Colores de Colombia -->
    <div class="position-absolute w-100 h-100" style="overflow: hidden; z-index: 0;">
        <div class="blur-circle" style="position: absolute; top: -10%; left: -5%; width: 600px; height: 600px; background: radial-gradient(circle, rgba(255, 209, 0, 0.2) 0%, transparent 70%); filter: blur(60px);"></div>
        <div class="blur-circle" style="position: absolute; bottom: -15%; right: -5%; width: 550px; height: 550px; background: radial-gradient(circle, rgba(206, 17, 38, 0.15) 0%, transparent 70%); filter: blur(60px);"></div>
        <div class="blur-circle" style="position: absolute; top: 30%; right: 10%; width: 500px; height: 500px; background: radial-gradient(circle, rgba(0, 56, 168, 0.15) 0%, transparent 70%); filter: blur(50px);"></div>
    </div>

    <div class="container py-5 position-relative" style="z-index: 1;">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card border-0 rounded-4 overflow-hidden" style="backdrop-filter: blur(10px); background: rgba(255, 255, 255, 0.95); box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);">
                    <div class="card-body text-center p-5">
                        <!-- Ícono de advertencia animado -->
                        <div class="mb-4 inactive-animation">
                            <i class="bi bi-pause-circle-fill text-warning" style="font-size: 6rem;"></i>
                        </div>

                        <!-- Mensaje principal -->
                        <h1 class="display-5 fw-bold text-dark mb-3">Encuesta No Disponible</h1>
                        <p class="lead text-muted mb-4">Esta encuesta no está activa en este momento.</p>

                        <hr class="my-4">

                        <!-- Información de la encuesta -->
                        <div class="row g-4 mb-4">
                            <div class="col-12">
                                <div class="p-4 bg-light rounded-3">
                                    <h5 class="fw-semibold text-dark mb-3">
                                        <i class="bi bi-clipboard-data text-warning"></i> {{ $survey->title }}
                                    </h5>
                                    @if($survey->description)
                                        <p class="text-muted small mb-0">{{ $survey->description }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Información adicional -->
                        <div class="alert alert-warning border-0 shadow-sm" role="alert">
                            <i class="bi bi-info-circle-fill"></i>
                            <strong>¿Por qué no está disponible?</strong>
                            <p class="mb-0 mt-2 small">
                                El administrador ha pausado temporalmente esta encuesta.
                                Puede estar en revisión o haber finalizado su período de votación.
                            </p>
                        </div>

                        <!-- Mensaje para contactar -->
                        <div class="mt-4 p-3 bg-info bg-opacity-10 rounded-3">
                            <p class="mb-0 text-dark">
                                <i class="bi bi-envelope-fill text-info"></i>
                                Si necesitas más información, contacta al administrador de la encuesta.
                            </p>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="card-footer bg-light text-center py-3">
                        <small class="text-muted">
                            <i class="bi bi-clipboard-data"></i> Sistema de Encuestas
                        </small>
                    </div>
                </div>

                <!-- Mensaje adicional -->
                <div class="text-center mt-4">
                    <p class="text-muted">
                        <i class="bi bi-arrow-left-circle"></i> Puedes cerrar esta ventana
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Animación del ícono de pausa */
.inactive-animation {
    animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% {
        transform: scale(1);
        opacity: 1;
    }
    50% {
        transform: scale(1.05);
        opacity: 0.8;
    }
}

/* Animación del card */
.card {
    animation: slideUp 0.5s ease-out;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive */
@media (max-width: 768px) {
    .display-5 {
        font-size: 2rem;
    }

    .lead {
        font-size: 1rem;
    }

    .inactive-animation i {
        font-size: 4rem !important;
    }
}
</style>
@endsection
