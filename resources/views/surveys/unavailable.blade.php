@extends('layouts.app')

@section('title', 'Encuesta No Disponible')

@section('content')
<div class="min-vh-100 d-flex align-items-center" style="background: #1a1a1a; padding: 3rem 0;">
    <div class="container" style="max-width: 740px;">

        <!-- Card de mensaje -->
        <div class="message-card">
            <div style="text-align: center; padding: 64px 24px;">
                <!-- Icono -->
                <div style="margin-bottom: 32px;">
                    <svg width="80" height="80" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="display: inline-block;">
                        <circle cx="12" cy="12" r="10" stroke="#fdd71a" stroke-width="2"/>
                        <path d="M15 9L9 15M9 9L15 15" stroke="#fdd71a" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>

                <!-- Título -->
                <h1 style="font-size: 28px; font-weight: 400; color: #ffffff; margin: 0 0 16px 0; line-height: 1.3;">
                    No se puede acceder a la encuesta
                </h1>

                <!-- Descripción -->
                <p style="font-size: 14px; color: #9aa0a6; margin: 0 0 24px 0; line-height: 1.6;">
                    Esta encuesta ha alcanzado su límite de participantes o ya no está disponible.
                </p>

                <!-- Divider -->
                <div style="width: 100%; height: 1px; background: #3c4043; margin: 32px 0;"></div>

                <!-- Info adicional -->
                <div style="font-size: 12px; color: #9aa0a6;">
                    <i class="bi bi-info-circle"></i> Si crees que esto es un error, contacta al administrador
                </div>
            </div>
        </div>

    </div>
</div>

<style>
.message-card {
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

/* Responsive */
@media (max-width: 768px) {
    .container {
        padding-left: 16px !important;
        padding-right: 16px !important;
    }

    .message-card > div {
        padding: 48px 20px !important;
    }

    .message-card h1 {
        font-size: 24px !important;
    }

    .message-card p {
        font-size: 13px !important;
    }

    .message-card svg {
        width: 64px !important;
        height: 64px !important;
    }
}

@media (max-width: 480px) {
    .container {
        padding-left: 12px !important;
        padding-right: 12px !important;
    }

    .message-card > div {
        padding: 40px 16px !important;
    }

    .message-card h1 {
        font-size: 20px !important;
        line-height: 1.3 !important;
    }

    .message-card svg {
        width: 56px !important;
        height: 56px !important;
    }
}
</style>
@endsection
