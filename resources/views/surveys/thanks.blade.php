@extends('layouts.app')

@section('title', 'Gracias por participar - ' . $survey->title)

@section('meta_description', 'Resultados de la encuesta: ' . $survey->title)

@section('og_image_full', url('images/default-survey-preview.jpg'))

@section('og_title', 'Resultados - ' . $survey->title)
@section('og_description', 'Mira los resultados de esta encuesta. ' . ($survey->description ?? ''))

@section('content') 
@php
    // Paleta de colores por defecto (fallback)
    $defaultColors = [
        ['#1e40af', '#3b82f6'], // Azul profesional
        ['#047857', '#10b981'], // Verde profesional
        ['#374151', '#6b7280'], // Gris oscuro
        ['#0891b2', '#06b6d4'], // Cyan profesional
        ['#1e3a8a', '#2563eb'], // Azul oscuro
        ['#065f46', '#059669'], // Verde oscuro
        ['#1f2937', '#4b5563'], // Negro/Gris
        ['#0e7490', '#0891b2'], // Teal profesional
    ];
@endphp

<div class="min-vh-100 position-relative thanks-container" style="background: linear-gradient(145deg, #000000 0%, #1a1a1a 50%, #0d0d0d 100%);">
    <!-- Efecto de estrellas y formas geométricas - Amarillo y Negro -->
    <div class="position-absolute w-100 h-100" style="overflow: hidden; z-index: 0; top: 0; left: 0;">
        <div class="star-shape" style="position: absolute; top: 10%; right: 15%; width: 350px; height: 350px; background: radial-gradient(circle, rgba(253, 215, 26, 0.2) 0%, transparent 70%); clip-path: polygon(50% 0%, 61% 35%, 98% 35%, 68% 57%, 79% 91%, 50% 70%, 21% 91%, 32% 57%, 2% 35%, 39% 35%);"></div>
        <div class="hex-shape" style="position: absolute; bottom: 15%; left: 10%; width: 400px; height: 400px; background: linear-gradient(135deg, rgba(253, 215, 26, 0.15), transparent); clip-path: polygon(25% 5%, 75% 5%, 100% 50%, 75% 95%, 25% 95%, 0% 50%);"></div>
        <div class="circle-shape" style="position: absolute; top: 50%; left: 50%; width: 300px; height: 300px; background: rgba(253, 215, 26, 0.08); border-radius: 50%; transform: translate(-50%, -50%);"></div>
    </div>

    <div class="container position-relative" style="z-index: 1; padding: 0;">
        <!-- Tarjeta única con todo el contenido -->
        <div class="row justify-content-center g-0">
            <div class="col-12 col-lg-10 col-xl-9">
                <div class="card border-0 overflow-hidden shadow-lg success-card" style="background: #000000; box-shadow: 0 25px 70px rgba(253, 215, 26, 0.4), 0 0 100px rgba(253, 215, 26, 0.2); border: 3px solid #fdd71a;">
                    <!-- Banner de la encuesta (si existe) -->
                    @if($survey->banner)
                        <div class="banner-wrapper" style="aspect-ratio: 856/200;">
                            <img src="{{ asset('storage/' . $survey->banner) }}"
                                 alt="Banner de {{ $survey->title }}"
                                 class="banner-img">
                        </div>
                    @endif

                    <div class="card-body thanks-card-body">
                        <!-- Título de la encuesta debajo del banner -->
                        @if($survey->banner)
                            <div class="text-center thanks-survey-title">
                                <h3 class="fw-bold mb-2" style="color: #fdd71a;">
                                    <i class="bi bi-star-fill"></i> {{ $survey->title }}
                                </h3>
                                @if($survey->description)
                                    <p class="mb-0" style="color: #ffffff;">{{ $survey->description }}</p>
                                @endif
                            </div>
                        @endif

                        <!-- Sección de éxito -->
                        <div class="text-center thanks-success-section">
                            <!-- Ícono de éxito animado -->
                            <div class="mb-4 success-animation">
                                <i class="bi bi-patch-check-fill" style="font-size: 4rem; color: #fdd71a; filter: drop-shadow(0 0 20px rgba(253, 215, 26, 0.7));"></i>
                            </div>

                            <!-- Mensaje principal -->
                            <h1 class="thanks-main-title">¡VOTO REGISTRADO!</h1>
                            <p class="thanks-subtitle">Tu participación ha sido confirmada exitosamente</p>

                            @if(!$survey->banner)
                                <hr class="my-4" style="border-color: #fdd71a; opacity: 0.3;">
                                <h5 class="fw-semibold mb-2" style="color: #fdd71a;">
                                    <i class="bi bi-star-fill"></i> {{ $survey->title }}
                                </h5>
                                @if($survey->description)
                                    <p class="small mb-0" style="color: #ffffff;">{{ $survey->description }}</p>
                                @endif
                            @endif
                        </div>

                        @if($showResults)
                        <!-- Resultados en Tiempo Real -->
                        <div class="results-section">
                            <h3 class="fw-bold mb-4 text-center" style="color: #fdd71a;">
                                <i class="bi bi-graph-up-arrow"></i> RESULTADOS EN VIVO
                            </h3>

                        @foreach($statistics as $index => $stat)
                            <div class="question-block {{ $loop->last ? '' : 'mb-5 pb-5 border-bottom' }}">
                                <!-- Pregunta -->
                                <div class="d-flex align-items-start gap-3 mb-5">
                                    <span class="badge bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; font-size: 1.2rem; flex-shrink: 0;">
                                        {{ $index + 1 }}
                                    </span>
                                    <div class="flex-grow-1">
                                        <h5 class="fw-bold text-dark mb-0">{{ $stat['question'] }}</h5>
                                    </div>
                                </div>

                                <!-- Gráfico de pastel - ARRIBA, ANCHO COMPLETO -->
                                <div class="row mb-5">
                                    <div class="col-12">
                                        <div class="chart-container-web p-5 bg-white rounded-4 shadow-sm" style="position: relative; height: 600px;">
                                            <canvas id="chart-{{ $index }}"></canvas>
                                        </div>
                                    </div>
                                </div>

                                <!-- Opciones con barras de progreso - ABAJO EN 2 COLUMNAS -->
                                <div class="row">
                                    <div class="col-12">
                                        <div class="options-container row row-cols-1 row-cols-md-2 g-4">
                                    @foreach($stat['options'] as $optIndex => $option)
                                        @php
                                            // Usar color personalizado o fallback a colores por defecto
                                            $optionColor = $option['color'] ?? null;
                                            $gradientStart = $optionColor ?? $defaultColors[$optIndex % count($defaultColors)][0];
                                            $gradientEnd = $optionColor ?? $defaultColors[$optIndex % count($defaultColors)][1];

                                            // Ajustar segundo color del gradiente (más claro)
                                            if ($optionColor) {
                                                // Crear versión más clara del color para el gradiente
                                                $gradientEnd = $optionColor;
                                            }
                                        @endphp
                                        <div class="col">
                                            <div class="option-item fade-in p-4 rounded-3 h-100 bg-white shadow-sm"
                                                 style="animation-delay: {{ $optIndex * 0.1 }}s; border-left: 5px solid {{ $optionColor ?? $defaultColors[$optIndex % count($defaultColors)][0] }};">
                                                <div class="d-flex justify-content-between align-items-center mb-3 gap-3">
                                                    <span class="fw-semibold text-dark d-flex align-items-center gap-2">
                                                        <span class="d-inline-block rounded-circle" style="width: 14px; height: 14px; background-color: {{ $optionColor ?? $defaultColors[$optIndex % count($defaultColors)][0] }}; flex-shrink: 0;"></span>
                                                        <span>{{ $option['text'] }}</span>
                                                    </span>
                                                    <span class="fw-bold text-primary" style="font-size: 1.5rem; flex-shrink: 0;">
                                                        <span class="percentage-counter" data-target="{{ $option['percentage'] }}">0</span>%
                                                    </span>
                                                </div>

                                                <!-- Barra de progreso animada con color personalizado -->
                                                <div class="progress shadow-sm" style="height: 30px; border-radius: 15px; background: rgba(0,0,0,0.05);">
                                                    <div class="progress-bar progress-bar-animated progress-bar-striped position-relative overflow-visible"
                                                         role="progressbar"
                                                         style="width: 0%;
                                                                background: linear-gradient(90deg, {{ $gradientStart }} 0%, {{ $gradientEnd }} 100%);
                                                                border-radius: 15px;
                                                                transition: width 1.5s ease-out {{ $optIndex * 0.2 }}s;"
                                                         data-width="{{ $option['percentage'] }}"
                                                         aria-valuenow="{{ $option['percentage'] }}"
                                                         aria-valuemin="0"
                                                         aria-valuemax="100">

                                                        @if($option['percentage'] > 0)
                                                            <!-- Efecto de brillo -->
                                                            <span class="shine-effect"></span>
                                                        @endif
                                                    </div>
                                                </div>

                                                <!-- Contador de votos válidos y no válidos -->
                                                <div class="mt-2 d-flex justify-content-between align-items-center">
                                                    <small class="text-muted">
                                                        <i class="bi bi-check-circle-fill text-success"></i>
                                                        <strong>{{ $option['votes'] }}</strong> votos válidos
                                                    </small>
                                                    @if(isset($option['invalid_votes']) && $option['invalid_votes'] > 0)
                                                        <small class="text-muted">
                                                            <i class="bi bi-x-circle-fill text-danger"></i>
                                                            <strong>{{ $option['invalid_votes'] }}</strong> no válidos
                                                        </small>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                            <!-- Información de privacidad -->
                            <div class="alert alert-info border-0 shadow-sm mt-5" role="alert" style="background: linear-gradient(135deg, rgba(13, 110, 253, 0.08) 0%, rgba(13, 202, 240, 0.08) 100%);">
                                <div class="d-flex align-items-start gap-3">
                                    <i class="bi bi-shield-check-fill text-info" style="font-size: 1.5rem;"></i>
                                    <div>
                                        <h6 class="fw-bold mb-2">Tu privacidad es importante</h6>
                                        <p class="mb-0 small">
                                            Tu voto es completamente anónimo. No almacenamos ningún dato personal.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @else
                        <!-- Mensaje cuando no se muestran resultados -->
                        <div class="alert alert-success border-0 shadow-sm" role="alert" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(5, 150, 105, 0.1) 100%);">
                            <div class="d-flex align-items-start gap-3">
                                <i class="bi bi-info-circle-fill text-success" style="font-size: 1.5rem;"></i>
                                <div>
                                    <h6 class="fw-bold mb-2">Voto registrado exitosamente</h6>
                                    <p class="mb-0">
                                        Tu voto ha sido registrado correctamente. 
                                    </p>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Footer -->
                    <div class="card-footer text-center py-3 border-0" style="background: #fdd71a; border-top: 3px solid #000000;">
                        <small style="color: #000000; font-weight: 600;">
                            <i class="bi bi-calendar-check-fill"></i>
                            Registrado: {{ now()->format('d/m/Y H:i') }}
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mensaje adicional -->
        <div class="text-center mt-4">
            <p style="color: #fdd71a;">
                <i class="bi bi-lightning-fill"></i> Actualizando resultados automáticamente
            </p>
        </div>
    </div>
</div>

<style>
/* Estilos del banner */
.banner-wrapper {
    width: 100%;
    height: auto;
    overflow: hidden;
    line-height: 0;
    margin: 0;
    padding: 0;
    border-bottom: 4px solid #fdd71a;
    animation: fadeIn 0.8s ease-out;
}

.banner-img {
    width: 100%;
    height: auto;
    display: block;
    opacity: 1;
    filter: brightness(1.1) contrast(1.1);
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

/* Animación del ícono de éxito */
.success-animation {
    animation: successPop 0.6s ease-out;
}

@keyframes successPop {
    0% {
        transform: scale(0) rotate(-180deg);
        opacity: 0;
    }
    50% {
        transform: scale(1.2) rotate(10deg);
    }
    100% {
        transform: scale(1) rotate(0deg);
        opacity: 1;
    }
}

/* Animación de las cards */
.success-card {
    animation: slideDown 0.6s ease-out;
}

.stats-card {
    animation: slideUp 0.8s ease-out 0.3s both;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Animación de entrada para opciones */
.fade-in {
    animation: fadeInScale 0.5s ease-out both;
}

@keyframes fadeInScale {
    from {
        opacity: 0;
        transform: translateX(-20px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateX(0) scale(1);
    }
}

/* Estadísticas de participación */
.participation-stats {
    animation: fadeIn 0.8s ease-out 0.3s both;
}

.stat-item {
    transition: all 0.3s ease;
    padding: 10px;
    border-radius: 8px;
}

.stat-item:hover {
    background: rgba(255, 255, 255, 0.5);
    transform: scale(1.05);
}

/* Transiciones suaves para elementos */
.results-section {
    animation: fadeInUp 0.7s ease-out 0.4s both;
}

/* Efecto de brillo en las barras */
.shine-effect {
    position: absolute;
    top: 0;
    left: -100%;
    width: 50%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
    animation: shine 2s infinite;
}

@keyframes shine {
    0% {
        left: -100%;
    }
    100% {
        left: 200%;
    }
}

/* Hover effects para las opciones */
.option-item {
    transition: all 0.3s ease;
    border: 1px solid rgba(0, 0, 0, 0.05);
}

.option-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12) !important;
    border-color: rgba(13, 110, 253, 0.2);
}

.progress {
    overflow: visible;
}

.progress-bar {
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

/* Animación de la pregunta */
.question-block {
    animation: fadeInUp 0.6s ease-out both;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Estilos para el contenedor del gráfico - ANCHO COMPLETO */
.chart-container-web {
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    width: 100%;
}

.chart-container-web:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15) !important;
}

.chart-container-web canvas {
    max-width: 100% !important;
    width: 100% !important;
    height: 100% !important;
}

/* Responsive para tablets */
@media (max-width: 992px) {
    .chart-container-web {
        height: 500px !important;
        padding: 1.5rem !important;
    }
}

/* Estilos responsivos personalizados */
.thanks-container {
    padding: 2rem 0;
}

.thanks-card-body {
    padding: 2rem 2.5rem;
}

.thanks-survey-title {
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 2px solid rgba(253, 215, 26, 0.3);
}

.thanks-success-section {
    margin-bottom: 2.5rem;
    padding-bottom: 2rem;
    border-bottom: 2px solid rgba(253, 215, 26, 0.3);
}

.thanks-main-title {
    font-size: 2.25rem;
    font-weight: bold;
    color: #fdd71a;
    margin-bottom: 1rem;
    text-transform: uppercase;
    letter-spacing: 2px;
    text-shadow: 0 0 30px rgba(253, 215, 26, 0.6);
}

.thanks-subtitle {
    font-size: 1.25rem;
    color: #ffffff;
    margin-bottom: 0;
}

/* Responsive para móviles */
@media (max-width: 768px) {
    /* Ajustes generales */
    .thanks-container {
        padding: 1.5rem 0 !important;
    }

    .thanks-card-body {
        padding: 1.5rem !important;
    }

    .thanks-survey-title {
        margin-bottom: 1.5rem !important;
        padding-bottom: 1rem !important;
    }

    .thanks-survey-title h3 {
        font-size: 1.25rem !important;
    }

    .thanks-success-section {
        margin-bottom: 2rem !important;
        padding-bottom: 1.5rem !important;
    }

    /* Tipografía móvil */
    .thanks-main-title {
        font-size: 1.75rem !important;
    }

    .thanks-subtitle {
        font-size: 1rem !important;
    }

    h3 {
        font-size: 1.3rem !important;
    }

    h5 {
        font-size: 1.1rem !important;
    }

    h4 {
        font-size: 1.2rem !important;
    }

    /* Ícono de éxito */
    .success-animation i {
        font-size: 3rem !important;
    }

    /* Estadísticas de participación en móvil */
    .participation-stats {
        padding: 1rem !important;
        margin-bottom: 1.5rem !important;
    }

    .stat-item {
        padding: 0.5rem !important;
    }

    .stat-item i {
        font-size: 1.2rem !important;
    }

    .stat-item h4 {
        font-size: 1rem !important;
        margin-top: 0.5rem !important;
    }

    .stat-item small {
        font-size: 0.75rem !important;
    }

    /* Título de resultados */
    .results-section h3 {
        margin-bottom: 2rem !important;
    }

    /* Pregunta móvil (excepto la última) */
    .question-block:not(:last-child) {
        margin-bottom: 3rem !important;
        padding-bottom: 2rem !important;
    }

    /* Última pregunta sin espaciado extra */
    .question-block:last-child {
        margin-bottom: 0 !important;
        padding-bottom: 0 !important;
    }

    .question-block .badge {
        width: 40px !important;
        height: 40px !important;
        font-size: 1.1rem !important;
    }

    .question-block h5 {
        font-size: 1.1rem !important;
        line-height: 1.5 !important;
    }

    /* Gráfico móvil - ANCHO COMPLETO */
    .chart-container-web {
        height: 500px !important;
        margin-bottom: 3rem !important;
        padding: 2rem 1.5rem !important;
        background: rgba(255, 255, 255, 0.98);
        border-radius: 20px;
    }

    /* Opciones móviles */
    .option-item {
        padding: 1.5rem !important;
        margin-bottom: 0 !important;
    }

    .option-item .d-flex {
        flex-direction: column !important;
        align-items: flex-start !important;
        gap: 0.75rem !important;
    }

    .option-item .fw-semibold {
        font-size: 1rem !important;
    }

    .option-item .fw-bold {
        font-size: 1.75rem !important;
        align-self: flex-end !important;
    }

    /* Barras de progreso móvil */
    .progress {
        height: 28px !important;
        border-radius: 14px !important;
    }

    /* Gap entre columnas móvil */
    .options-container.g-4 {
        gap: 1rem !important;
    }

    /* Alert de privacidad */
    .alert {
        padding: 1.5rem !important;
        margin-top: 3rem !important;
    }

    .alert h6 {
        font-size: 1rem !important;
    }

    .alert p {
        font-size: 0.9rem !important;
    }

    .alert i.bi-shield-check-fill {
        font-size: 1.5rem !important;
    }

    /* Footer */
    .card-footer {
        padding: 1rem !important;
    }

    .card-footer small {
        font-size: 0.85rem !important;
    }

    /* Mensaje adicional */
    .text-center.mt-4 p {
        font-size: 0.9rem !important;
    }
}

/* Responsive para móviles pequeños */
@media (max-width: 576px) {
    /* Ajustes extremos para pantallas muy pequeñas */
    .thanks-container {
        padding: 1rem 0 !important;
    }

    .thanks-card-body {
        padding: 1rem !important;
    }

    .thanks-survey-title {
        margin-bottom: 1rem !important;
        padding-bottom: 0.75rem !important;
    }

    .thanks-survey-title h3 {
        font-size: 1.1rem !important;
    }

    .thanks-success-section {
        margin-bottom: 1.5rem !important;
        padding-bottom: 1rem !important;
    }

    .thanks-main-title {
        font-size: 1.5rem !important;
    }

    .thanks-subtitle {
        font-size: 0.9rem !important;
    }

    .chart-container-web {
        height: 450px !important;
        padding: 1.5rem 1rem !important;
    }

    .question-block .d-flex {
        gap: 0.75rem !important;
    }

    .question-block .badge {
        width: 38px !important;
        height: 38px !important;
        font-size: 1rem !important;
    }

    .option-item {
        padding: 1.25rem !important;
    }

    .option-item .fw-semibold {
        font-size: 0.95rem !important;
    }

    .option-item .fw-bold {
        font-size: 1.5rem !important;
    }

    .progress {
        height: 24px !important;
        border-radius: 12px !important;
    }
}

/* Partículas de confetti (opcional) */
.confetti {
    position: fixed;
    width: 10px;
    height: 10px;
    background: #f0f;
    position: absolute;
    animation: confetti-fall 3s linear infinite;
}

@keyframes confetti-fall {
    to {
        transform: translateY(100vh) rotate(360deg);
    }
}
</style>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Vibración de celebración en dispositivos móviles
    if (navigator.vibrate) {
        navigator.vibrate([100, 50, 100, 50, 200]);
    }

    // Animación de los porcentajes
    const percentageCounters = document.querySelectorAll('.percentage-counter');
    percentageCounters.forEach((element, index) => {
        const target = parseFloat(element.getAttribute('data-target'));
        const duration = 1500;
        const increment = target / (duration / 16);
        let current = 0;

        const updatePercentage = () => {
            current += increment;
            if (current < target) {
                element.textContent = current.toFixed(1);
                requestAnimationFrame(updatePercentage);
            } else {
                element.textContent = target.toFixed(1);
            }
        };

        setTimeout(updatePercentage, 800 + (index * 100));
    });

    // Animación de las barras de progreso
    setTimeout(() => {
        const progressBars = document.querySelectorAll('.progress-bar');
        progressBars.forEach((bar, index) => {
            const targetWidth = bar.getAttribute('data-width');
            setTimeout(() => {
                bar.style.width = targetWidth + '%';
            }, index * 200);
        });
    }, 600);

    // Crear efecto de confetti con colores institucionales de Colombia
    function createConfetti() {
        const colors = ['#FCD116', '#003893', '#CE1126', '#1e3a8a', '#047857', '#3b82f6']; // Amarillo, Azul, Rojo Colombia + institucionales
        const confettiCount = 40; // Menos confetti para efecto más sobrio

        for (let i = 0; i < confettiCount; i++) {
            setTimeout(() => {
                const confetti = document.createElement('div');
                confetti.style.position = 'fixed';
                confetti.style.width = Math.random() * 10 + 5 + 'px';
                confetti.style.height = confetti.style.width;
                confetti.style.background = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.left = Math.random() * 100 + '%';
                confetti.style.top = '-10px';
                confetti.style.borderRadius = Math.random() > 0.5 ? '50%' : '0';
                confetti.style.opacity = Math.random();
                confetti.style.zIndex = '9999';
                confetti.style.pointerEvents = 'none';

                document.body.appendChild(confetti);

                const duration = Math.random() * 3 + 2;
                const rotation = Math.random() * 360;

                confetti.animate([
                    { transform: 'translateY(0) rotate(0deg)', opacity: 1 },
                    { transform: `translateY(${window.innerHeight + 10}px) rotate(${rotation}deg)`, opacity: 0 }
                ], {
                    duration: duration * 1000,
                    easing: 'cubic-bezier(0.25, 0.46, 0.45, 0.94)'
                });

                setTimeout(() => {
                    confetti.remove();
                }, duration * 1000);
            }, i * 30);
        }
    }

    // Lanzar confetti después de un pequeño delay
    setTimeout(createConfetti, 400);

    // Añadir delays escalonados a las preguntas
    const questionBlocks = document.querySelectorAll('.question-block');
    questionBlocks.forEach((block, index) => {
        block.style.animationDelay = (0.4 + index * 0.2) + 's';
    });

    // Crear gráficos de pastel para cada pregunta - MEJORADO PARA WEB
    @if($showResults)
    const chartData = @json($statistics);

    // Colores vibrantes y profesionales para WEB
    const defaultChartColors = [
        '#3b82f6', // Azul vibrante
        '#10b981', // Verde esmeralda
        '#f59e0b', // Naranja dorado
        '#ef4444', // Rojo coral
        '#8b5cf6', // Púrpura
        '#ec4899', // Rosa
        '#06b6d4', // Cyan
        '#84cc16', // Lima
    ];

    chartData.forEach((question, index) => {
        const ctx = document.getElementById(`chart-${index}`);
        if (!ctx) return;

        const labels = question.options.map(opt => opt.text);
        const data = question.options.map(opt => opt.percentage);
        // Usar colores personalizados o colores por defecto vibrantes
        const colors = question.options.map((opt, idx) =>
            opt.color || defaultChartColors[idx % defaultChartColors.length]
        );

        // Debug: Mostrar cuántas opciones hay
        console.log(`Pregunta ${index + 1}: ${labels.length} opciones`, labels);

        // Detectar si es móvil o desktop
        const isMobile = window.innerWidth <= 768;
        const isSmallMobile = window.innerWidth <= 576;
        const isDesktop = window.innerWidth > 992;

        new Chart(ctx, {
            type: 'doughnut', // Cambio a doughnut para verse más moderno
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: colors,
                    borderColor: '#ffffff',
                    borderWidth: isDesktop ? 4 : 3, // Bordes más gruesos en desktop
                    hoverOffset: isDesktop ? 25 : 15, // Efecto hover más pronunciado en desktop
                    hoverBorderWidth: isDesktop ? 5 : 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false, // Cambiar a false para controlar altura manualmente
                layout: {
                    padding: isDesktop ? 30 : 15
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                        align: 'start', // Alinear a la izquierda
                        maxHeight: 400, // Altura máxima para la leyenda
                        fullSize: true, // Ocupar todo el ancho disponible
                        labels: {
                            padding: isDesktop ? 18 : 12,
                            font: {
                                size: isDesktop ? 14 : (isMobile ? 13 : 12),
                                family: "'Inter', system-ui, -apple-system, sans-serif",
                                weight: '600'
                            },
                            color: '#1f2937',
                            usePointStyle: true,
                            pointStyle: 'circle',
                            boxWidth: isDesktop ? 12 : 10,
                            boxHeight: isDesktop ? 12 : 10,
                            textAlign: 'left', // Texto alineado a la izquierda
                            filter: function(item, chart) {
                                // NO filtrar nada - mostrar TODAS las etiquetas
                                return true;
                            },
                            // Generar etiquetas personalizadas con solo porcentaje
                            generateLabels: function(chart) {
                                const data = chart.data;
                                if (data.labels.length && data.datasets.length) {
                                    const dataset = data.datasets[0];
                                    const total = dataset.data.reduce((a, b) => a + b, 0);

                                    // IMPORTANTE: Retornar TODAS las etiquetas sin límite
                                    return data.labels.map((label, i) => {
                                        const meta = chart.getDatasetMeta(0);
                                        const style = meta.controller.getStyle(i);
                                        const value = dataset.data[i];
                                        const percentage = ((value / total) * 100).toFixed(1);

                                        // Truncar texto en móvil
                                        let displayLabel = label;
                                        if (isSmallMobile && label.length > 20) {
                                            displayLabel = label.substring(0, 20) + '...';
                                        } else if (isMobile && label.length > 25) {
                                            displayLabel = label.substring(0, 25) + '...';
                                        }

                                        // Solo mostrar nombre y porcentaje
                                        return {
                                            text: `${displayLabel}: ${percentage}%`,
                                            fillStyle: style.backgroundColor,
                                            strokeStyle: style.borderColor,
                                            lineWidth: style.borderWidth,
                                            hidden: !chart.getDataVisibility(i),
                                            index: i
                                        };
                                    });
                                }
                                return [];
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.95)',
                        padding: isDesktop ? 16 : 12,
                        titleFont: {
                            size: isDesktop ? 16 : 14,
                            weight: 'bold',
                            family: "'Inter', system-ui, sans-serif"
                        },
                        bodyFont: {
                            size: isDesktop ? 15 : 13,
                            family: "'Inter', system-ui, sans-serif"
                        },
                        cornerRadius: 12,
                        displayColors: true,
                        boxWidth: isDesktop ? 15 : 12,
                        boxHeight: isDesktop ? 15 : 12,
                        boxPadding: 8,
                        borderColor: 'rgba(255, 255, 255, 0.2)',
                        borderWidth: 2,
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                // Solo mostrar porcentaje en el tooltip
                                return ` ${percentage}%`;
                            },
                            title: function(context) {
                                // Mostrar el nombre completo en el título
                                return context[0].label || '';
                            }
                        }
                    }
                },
                animation: {
                    animateRotate: true,
                    animateScale: true,
                    duration: isDesktop ? 2000 : 1500, // Animación más lenta y suave en desktop
                    delay: 400 + (index * 200),
                    easing: 'easeInOutQuart' // Easing más suave
                },
                // Efecto 3D sutil - PASTEL MÁS GRANDE Y CENTRADO
                cutout: isDesktop ? '40%' : '35%', // Agujero del donut más pequeño = pastel más grande
                radius: '70%', // Radio optimizado para ancho completo
            }
        });
    });
    @endif
});
</script>
@endsection

