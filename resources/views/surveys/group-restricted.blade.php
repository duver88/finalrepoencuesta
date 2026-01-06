<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ya has votado - {{ $survey->title }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .restriction-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 90%;
            padding: 3rem 2rem;
            text-align: center;
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .icon-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }

        .icon-container i {
            font-size: 3rem;
            color: white;
        }

        h1 {
            color: #1e293b;
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .message {
            color: #64748b;
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .survey-info {
            background: #f8fafc;
            border-radius: 12px;
            padding: 1.5rem;
            margin: 2rem 0;
            border-left: 4px solid #667eea;
        }

        .survey-info h3 {
            color: #1e293b;
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .survey-info p {
            color: #64748b;
            font-size: 0.95rem;
            margin: 0;
        }

        .info-box {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            border-radius: 8px;
            padding: 1rem;
            margin: 1.5rem 0;
            text-align: left;
        }

        .info-box strong {
            color: #92400e;
            display: block;
            margin-bottom: 0.5rem;
        }

        .info-box p {
            color: #78350f;
            margin: 0;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="restriction-card">
        <div class="icon-container">
            <i class="bi bi-shield-lock-fill"></i>
        </div>

        <h1>Ya has participado en este grupo de encuestas</h1>

        <p class="message">
            Esta encuesta pertenece al grupo <strong>"{{ $group->name }}"</strong> y ya has votado en otra encuesta de este grupo.
        </p>

        <div class="survey-info">
            <h3><i class="bi bi-check-circle-fill text-success"></i> Encuesta en la que ya votaste:</h3>
            <p>{{ $votedSurvey->title }}</p>
        </div>

        <div class="info-box">
            <strong><i class="bi bi-info-circle"></i> ¿Por qué veo este mensaje?</strong>
            <p>
                Para mantener la integridad de los resultados, solo puedes participar en UNA encuesta de este grupo.
                Tu voto anterior ya fue registrado exitosamente.
            </p>
        </div>

        <p style="color: #94a3b8; font-size: 0.9rem; margin-top: 2rem;">
            Gracias por tu comprensión y participación.
        </p>
    </div>
</body>
</html>
