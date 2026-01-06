<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Models\Vote;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Solo contar votos válidos (con token o manuales)
        $surveys = Survey::withCount(['votes' => function ($query) {
            $query->valid();
        }])->latest()->get();

        $totalSurveys = Survey::count();
        $activeSurveys = Survey::where('is_active', true)->count();
        $totalVotes = Vote::valid()->count();
        $uniqueVoters = Vote::valid()->distinct('fingerprint')->count();

        return view('admin.dashboard', compact(
            'surveys',
            'totalSurveys',
            'activeSurveys',
            'totalVotes',
            'uniqueVoters'
        ));
    }
}