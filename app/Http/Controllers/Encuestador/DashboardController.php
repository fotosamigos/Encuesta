<?php

namespace App\Http\Controllers\Encuestador;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        // Encuestas públicas activas (puede responderlas todas)
        $publicSurveys = Survey::where('is_public', true)
            ->where('is_active', true)
            ->latest()
            ->get();

        // Encuestas "especiales" a las que este encuestador tiene acceso explícito
        $specialSurveys = $user->surveyAccess()
            ->where('is_active', true)
            ->wherePivot('can_respond', true)
            ->get();

        // Encuestas donde puede ver resultados (públicas o especiales, autorizadas)
        $resultSurveys = $user->surveyAccess()
            ->wherePivot('can_view_results', true)
            ->get();

        return view('encuestador.dashboard', compact('publicSurveys', 'specialSurveys', 'resultSurveys'));
    }
}
