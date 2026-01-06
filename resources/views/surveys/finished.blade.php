<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Encuesta Terminada - {{ $survey->title }}</title>

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="Resultados - {{ $survey->title }}">
    <meta property="og:description" content="Los resultados finales de esta encuesta ya están disponibles">
    @if($survey->og_image)
        <meta property="og:image" content="{{ asset('storage/' . $survey->og_image) }}">
    @elseif($survey->banner)
        <meta property="og:image" content="{{ asset('storage/' . $survey->banner) }}">
    @endif

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --accent-color: #7f8c8d;
            --light-gray: #ecf0f1;
            --text-dark: #2c3e50;
            --text-light: #95a5a6;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background:
                radial-gradient(circle at 20% 50%, rgba(236, 240, 241, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(189, 195, 199, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 40% 90%, rgba(149, 165, 166, 0.2) 0%, transparent 50%);
            animation: blur-rotate 30s linear infinite;
            pointer-events: none;
        }

        @keyframes blur-rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .finished-container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 3rem 1rem;
            position: relative;
            z-index: 1;
        }

        .finished-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            margin-bottom: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.8);
        }

        .finished-header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 3.5rem 2rem 3rem;
            text-align: center;
            position: relative;
        }

        .finished-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.15);
            padding: 0.6rem 1.8rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            letter-spacing: 1px;
            margin-bottom: 1.5rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            text-transform: uppercase;
        }

        .finished-title {
            font-size: 2.2rem;
            font-weight: 300;
            margin: 0;
            letter-spacing: -0.5px;
        }

        .finished-subtitle {
            font-size: 1rem;
            margin-top: 1rem;
            opacity: 0.85;
            font-weight: 300;
        }

        .stats-section {
            padding: 3rem 2rem;
            background: transparent;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 2rem 1.5rem;
            text-align: center;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        }

        .stat-icon {
            font-size: 2.2rem;
            margin-bottom: 0.8rem;
            color: var(--primary-color);
            opacity: 0.7;
        }

        .stat-value {
            font-size: 2.8rem;
            font-weight: 200;
            color: var(--text-dark);
            margin: 0.5rem 0;
            letter-spacing: -1px;
        }

        .stat-label {
            font-size: 0.8rem;
            color: var(--text-light);
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-weight: 500;
        }

        .results-section {
            padding: 0 2rem 2rem;
            background: transparent;
        }

        .question-result {
            margin-bottom: 3rem;
            padding: 0;
            background: transparent;
        }

        .question-title {
            font-size: 1.1rem;
            font-weight: 400;
            color: var(--text-dark);
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            letter-spacing: -0.3px;
        }

        .question-title i {
            color: var(--accent-color);
            margin-right: 0.8rem;
            font-size: 1.3rem;
        }

        .winner-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.8rem;
            padding: 1rem 1.5rem;
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            border-radius: 50px;
            font-size: 0.95rem;
            font-weight: 400;
            margin-bottom: 2rem;
            box-shadow: 0 4px 12px rgba(44, 62, 80, 0.2);
        }

        .winner-badge i {
            font-size: 1.2rem;
            color: #f39c12;
        }

        .winner-text {
            font-weight: 300;
            opacity: 0.8;
            margin-right: 0.5rem;
        }

        .winner-option {
            font-weight: 500;
        }

        .options-bars {
            margin-bottom: 2.5rem;
        }

        .option-bar {
            margin-bottom: 1.5rem;
        }

        .option-label {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.6rem;
            font-size: 0.9rem;
        }

        .option-name {
            color: var(--text-dark);
            font-weight: 400;
        }

        .option-percent {
            color: var(--primary-color);
            font-weight: 500;
            font-size: 1rem;
        }

        .progress {
            height: 12px;
            border-radius: 10px;
            background-color: #ecf0f1;
            overflow: hidden;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .progress-bar {
            border-radius: 10px;
            transition: width 1.2s ease;
            height: 100%;
        }

        .chart-container {
            position: relative;
            max-width: 500px;
            height: 400px;
            margin: 0 auto;
            padding: 2rem;
            background: white;
            border-radius: 20px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .finished-footer {
            padding: 2.5rem 2rem;
            text-align: center;
            background: transparent;
        }

        .finished-footer .alert {
            background: white;
            border: 1px solid rgba(0, 0, 0, 0.05);
            color: var(--text-dark);
            font-weight: 400;
            border-radius: 12px;
        }

        .finished-date {
            color: var(--text-light);
            font-size: 0.85rem;
            margin-top: 1.5rem;
            font-weight: 400;
        }

        @media (max-width: 768px) {
            .finished-title {
                font-size: 1.6rem;
            }

            .stat-value {
                font-size: 2.2rem;
            }

            .question-title {
                font-size: 1rem;
            }

            .winner-badge {
                font-size: 0.85rem;
                padding: 0.8rem 1.2rem;
                flex-wrap: wrap;
                justify-content: center;
            }

            .option-label {
                font-size: 0.85rem;
            }

            .option-percent {
                font-size: 0.9rem;
            }

            .chart-container {
                height: 300px;
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="finished-container">
        <div class="finished-card">
            <!-- Header -->
            <div class="finished-header">
                <div class="finished-badge">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    ENCUESTA TERMINADA
                </div>
                <h1 class="finished-title">{{ $survey->title }}</h1>
                @if($survey->description)
                    <p class="finished-subtitle">{{ $survey->description }}</p>
                @endif
            </div>

            <!-- Estadísticas Generales -->
            <div class="stats-section">
                <div class="row g-4 justify-content-center">
                    <div class="col-md-5">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="bi bi-eye-fill"></i>
                            </div>
                            <div class="stat-value">{{ number_format($survey->views_count) }}</div>
                            <div class="stat-label">Visitas Totales</div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="bi bi-bar-chart-fill"></i>
                            </div>
                            <div class="stat-value">{{ number_format($totalVotes) }}</div>
                            <div class="stat-label">Votos Totales</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resultados por Pregunta -->
            <div class="results-section">
                @foreach($statistics as $index => $stat)
                    <div class="question-result">
                        <h3 class="question-title">
                            <i class="bi bi-patch-question-fill"></i>
                            {{ $stat['question'] }}
                        </h3>

                        @php
                            // Encontrar la opción ganadora (mayor porcentaje)
                            $winner = collect($stat['options'])->sortByDesc('percentage')->first();
                        @endphp

                        <!-- Badge del Ganador -->
                        @if($winner && $winner['percentage'] > 0)
                            <div class="text-center">
                                <div class="winner-badge">
                                    <i class="bi bi-trophy-fill"></i>
                                    <span class="winner-text">Ganador:</span>
                                    <span class="winner-option">{{ $winner['text'] }}</span>
                                    <span class="winner-text">·</span>
                                    <span class="winner-option">{{ $winner['percentage'] }}%</span>
                                </div>
                            </div>
                        @endif

                        <!-- Barras de Progreso -->
                        <div class="options-bars">
                            @foreach($stat['options'] as $option)
                                @php
                                    $barColor = $option['color'] ?? '#2c3e50';
                                @endphp
                                <div class="option-bar">
                                    <div class="option-label">
                                        <span class="option-name">{{ $option['text'] }}</span>
                                        <span class="option-percent">{{ $option['percentage'] }}%</span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar"
                                             role="progressbar"
                                             style="width: 0%; background-color: {{ $barColor }};"
                                             data-width="{{ $option['percentage'] }}%"
                                             data-color="{{ $barColor }}"
                                             aria-valuenow="{{ $option['percentage'] }}"
                                             aria-valuemin="0"
                                             aria-valuemax="100">
                                        </div>
                                    </div>
                                    <!-- Contador de votos válidos y no válidos -->
                                    <div class="d-flex justify-content-between align-items-center mt-1" style="font-size: 0.85rem; color: #7f8c8d;">
                                        <span>
                                            <i class="bi bi-check-circle-fill" style="color: #27ae60;"></i>
                                            <strong>{{ $option['votes'] }}</strong> votos válidos
                                        </span>
                                        @if(isset($option['invalid_votes']) && $option['invalid_votes'] > 0)
                                            <span>
                                                <i class="bi bi-x-circle-fill" style="color: #e74c3c;"></i>
                                                <strong>{{ $option['invalid_votes'] }}</strong> no válidos
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Gráfico de pastel -->
                        <div class="chart-container">
                            <canvas id="chart-{{ $index }}"></canvas>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Footer -->
            <div class="finished-footer">
                <div class="alert alert-info mb-0" role="alert">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    <strong>Esta encuesta ha finalizado.</strong> Los resultados mostrados son definitivos.
                </div>
                @if($survey->finished_at)
                    <div class="finished-date">
                        <i class="bi bi-calendar-check me-1"></i>
                        Finalizada el {{ $survey->finished_at->format('d/m/Y') }} a las {{ $survey->finished_at->format('H:i') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Generar gráficos -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const statistics = @json($statistics);

            // Animar las barras de progreso
            setTimeout(() => {
                document.querySelectorAll('.progress-bar').forEach(bar => {
                    const width = bar.getAttribute('data-width');
                    bar.style.width = width;
                });
            }, 300);

            // Paleta de colores neutros como fallback
            const neutralColors = [
                '#2c3e50', // Azul oscuro
                '#95a5a6', // Gris
                '#7f8c8d', // Gris oscuro
                '#bdc3c7', // Gris claro
                '#34495e', // Azul grisáceo
                '#ecf0f1', // Blanco roto
                '#546e7a', // Azul gris
                '#78909c'  // Azul gris claro
            ];

            // Crear un gráfico de pastel para cada pregunta
            statistics.forEach((stat, index) => {
                const ctx = document.getElementById(`chart-${index}`);
                if (!ctx) return;

                const labels = stat.options.map(opt => opt.text);
                const data = stat.options.map(opt => opt.votes);
                const percentages = stat.options.map(opt => opt.percentage);

                // Usar colores personalizados del admin, o neutros como fallback
                const colors = stat.options.map((opt, i) => opt.color || neutralColors[i % neutralColors.length]);

                new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: data,
                            backgroundColor: colors,
                            borderWidth: 0,
                            hoverBorderWidth: 3,
                            hoverBorderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 20,
                                    font: {
                                        size: 12,
                                        weight: '400',
                                        family: "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif"
                                    },
                                    usePointStyle: true,
                                    pointStyle: 'circle',
                                    color: '#2c3e50',
                                    boxWidth: 8,
                                    boxHeight: 8
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(44, 62, 80, 0.95)',
                                padding: 14,
                                cornerRadius: 8,
                                titleFont: {
                                    size: 13,
                                    weight: '400'
                                },
                                bodyFont: {
                                    size: 13,
                                    weight: '300'
                                },
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.parsed || 0;
                                        const percentage = percentages[context.dataIndex];
                                        return `  ${label}: ${percentage}%`;
                                    }
                                }
                            }
                        },
                        animation: {
                            animateRotate: true,
                            animateScale: false,
                            duration: 800,
                            easing: 'easeInOutQuart'
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>
