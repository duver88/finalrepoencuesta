<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Grupo - {{ $group->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        h1 {
            font-size: 20px;
            color: #1e293b;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        h2 {
            font-size: 16px;
            color: #1e293b;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        h3 {
            font-size: 14px;
            color: #1e293b;
            margin-top: 15px;
            margin-bottom: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th {
            background-color: #667eea;
            color: white;
            padding: 8px;
            text-align: left;
        }
        td {
            padding: 6px;
        }
        .stat-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .stat-item {
            display: table-cell;
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }
        .stat-label {
            font-size: 10px;
            color: #666;
            text-transform: uppercase;
        }
        .stat-value {
            font-size: 16px;
            font-weight: bold;
            color: #1e293b;
        }
        .footer {
            margin-top: 30px;
            font-size: 10px;
            color: #999;
            text-align: center;
        }
    </style>
</head>
<body>
    <h1>Reporte de Grupo: {{ $group->name }}</h1>
    <p><strong>Generado:</strong> {{ now()->format('d/m/Y H:i:s') }}</p>

    <h2>Estadísticas Generales</h2>
    <table>
        <tr>
            <th>Métrica</th>
            <th>Valor</th>
        </tr>
        <tr>
            <td>Total de Encuestas</td>
            <td>{{ number_format($report['basic_stats']['total_surveys']) }}</td>
        </tr>
        <tr>
            <td>Vistas Totales</td>
            <td>{{ number_format($report['basic_stats']['total_views']) }}</td>
        </tr>
        <tr>
            <td>Votos Enviados</td>
            <td>{{ number_format($report['basic_stats']['total_votes_submitted']) }}</td>
        </tr>
        <tr>
            <td>Votos Válidos</td>
            <td>{{ number_format($report['basic_stats']['valid_votes']) }}</td>
        </tr>
        <tr>
            <td>Votos Pendientes Revisión</td>
            <td>{{ number_format($report['basic_stats']['pending_review']) }}</td>
        </tr>
        <tr>
            <td>Votos Rechazados</td>
            <td>{{ number_format($report['basic_stats']['rejected_votes']) }}</td>
        </tr>
        <tr>
            <td>Votos No Contados</td>
            <td>{{ number_format($report['basic_stats']['not_counted_votes']) }}</td>
        </tr>
        <tr>
            <td>Votos Duplicados/Fraudulentos</td>
            <td>{{ number_format($report['basic_stats']['duplicate_or_fraudulent']) }}</td>
        </tr>
        <tr>
            <td>Votantes Únicos</td>
            <td>{{ number_format($report['basic_stats']['unique_voters']) }}</td>
        </tr>
    </table>

    <h2>Métricas de Conversión</h2>
    <table>
        <tr>
            <th>Métrica</th>
            <th>Porcentaje</th>
        </tr>
        <tr>
            <td>Vistas → Votos</td>
            <td>{{ $report['conversion_metrics']['view_to_vote_rate'] }}%</td>
        </tr>
        <tr>
            <td>Votos → Aprobados</td>
            <td>{{ $report['conversion_metrics']['vote_approval_rate'] }}%</td>
        </tr>
        <tr>
            <td>Conversión Completa</td>
            <td>{{ $report['conversion_metrics']['complete_conversion_rate'] }}%</td>
        </tr>
    </table>

    <h2>Estadísticas por Encuesta</h2>
    <table>
        <thead>
            <tr>
                <th>Título</th>
                <th>Vistas</th>
                <th>Votos Válidos</th>
                <th>Tasa de Conversión</th>
            </tr>
        </thead>
        <tbody>
            @foreach($report['per_survey_stats'] as $surveyStat)
            <tr>
                <td>{{ $surveyStat['survey_title'] }}</td>
                <td>{{ number_format($surveyStat['views']) }}</td>
                <td>{{ number_format($surveyStat['valid_votes']) }}</td>
                <td>{{ $surveyStat['conversion_rate'] }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if(count($report['question_stats']) > 0)
    <h2>Estadísticas Agregadas por Pregunta</h2>
    @foreach($report['question_stats'] as $questionStat)
    <h3>{{ $questionStat['question_text'] }}</h3>
    <p><strong>Total de votos:</strong> {{ number_format($questionStat['total_votes']) }}</p>
    <table>
        <thead>
            <tr>
                <th>Opción</th>
                <th>Votos</th>
                <th>Porcentaje</th>
            </tr>
        </thead>
        <tbody>
            @foreach($questionStat['options'] as $option)
            <tr>
                <td>{{ $option['option_text'] }}</td>
                <td>{{ number_format($option['votes']) }}</td>
                <td>{{ $option['percentage'] }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endforeach
    @endif

    <div class="footer">
        <p>Generado con Sombra Política - {{ now()->format('Y') }}</p>
    </div>
</body>
</html>
