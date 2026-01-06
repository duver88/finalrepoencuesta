<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Survey;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Vote;

echo "╔════════════════════════════════════════════════╗\n";
echo "║   ESTADÍSTICAS DE LA BASE DE DATOS            ║\n";
echo "╚════════════════════════════════════════════════╝\n\n";

// Usuarios
echo "👤 USUARIOS:\n";
echo "   Total: " . User::count() . "\n";
echo "   Admins: " . User::where('is_admin', true)->count() . "\n\n";

// Encuestas
echo "📊 ENCUESTAS:\n";
echo "   Total: " . Survey::count() . "\n";
echo "   Activas: " . Survey::where('is_active', true)->count() . "\n";
echo "   Inactivas: " . Survey::where('is_active', false)->count() . "\n\n";

// Preguntas y Opciones
echo "❓ PREGUNTAS:\n";
echo "   Total: " . Question::count() . "\n";
echo "   Opciones: " . QuestionOption::count() . "\n\n";

// Votos
echo "🗳️  VOTOS:\n";
echo "   Total: " . Vote::count() . "\n";
echo "   Votantes únicos (IP): " . Vote::distinct('ip_address')->count() . "\n";
echo "   Votantes únicos (Fingerprint): " . Vote::whereNotNull('fingerprint')->distinct('fingerprint')->count() . "\n\n";

// Detalle de encuestas
echo "📋 DETALLE POR ENCUESTA:\n";
echo "   " . str_repeat("─", 70) . "\n";

$surveys = Survey::withCount('votes')->get();

if ($surveys->count() > 0) {
    foreach($surveys as $survey) {
        $uniqueVoters = Vote::where('survey_id', $survey->id)
            ->distinct('ip_address')
            ->count();

        $status = $survey->is_active ? '✅ Activa' : '❌ Inactiva';

        echo "   " . $survey->title . "\n";
        echo "   Estado: " . $status . "\n";
        echo "   Preguntas: " . $survey->questions->count() . "\n";
        echo "   Votos: " . $survey->votes_count . "\n";
        echo "   Votantes únicos: " . $uniqueVoters . "\n";
        echo "   Link: http://127.0.0.1:8000/survey/" . $survey->slug . "\n";
        echo "   " . str_repeat("─", 70) . "\n";
    }
} else {
    echo "   No hay encuestas creadas aún.\n";
    echo "   " . str_repeat("─", 70) . "\n";
}

echo "\n";
echo "💡 TIP: Ejecuta este script con: php database-stats.php\n";
