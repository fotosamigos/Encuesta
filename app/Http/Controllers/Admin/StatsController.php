<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Response;
use App\Models\Survey;
use App\Models\User;
use Illuminate\View\View;

class StatsController extends Controller
{
    /** Panel general de estadísticas: todas las encuestas. */
    public function index(): View
    {
        $totals = [
            'surveys' => Survey::count(),
            'active_surveys' => Survey::where('is_active', true)->count(),
            'responses' => Response::count(),
            'encuestadores' => User::where('role', 'encuestador')->count(),
            'encuestadores_activos' => User::where('role', 'encuestador')->where('is_active', true)->count(),
        ];

        $responsesBySurvey = Survey::withCount('responses')
            ->orderByDesc('responses_count')
            ->take(10)
            ->get(['id', 'title']);

        $responsesByEncuestador = User::where('role', 'encuestador')
            ->withCount('responses')
            ->orderByDesc('responses_count')
            ->get(['id', 'name', 'color']);

        return view('admin.stats', compact('totals', 'responsesBySurvey', 'responsesByEncuestador'));
    }
}
