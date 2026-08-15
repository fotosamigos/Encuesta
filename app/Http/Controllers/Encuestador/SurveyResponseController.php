<?php

namespace App\Http\Controllers\Encuestador;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Services\SurveyResponseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SurveyResponseController extends Controller
{
    public function show(Request $request, Survey $survey): View
    {
        $this->authorizeAccess($request, $survey);
        $survey->load('questions.options');

        return view('encuestador.surveys.show', compact('survey'));
    }

    public function store(Request $request, Survey $survey, SurveyResponseService $service): RedirectResponse
    {
        $this->authorizeAccess($request, $survey);

        $service->submit(
            survey: $survey,
            answersInput: $request->input('answers', []),
            respondent: $request->user(),
            lat: $request->float('latitude') ?: null,
            lng: $request->float('longitude') ?: null,
        );

        return redirect()->route('encuestador.dashboard')
            ->with('status', '¡Respuesta registrada correctamente!');
    }

    private function authorizeAccess(Request $request, Survey $survey): void
    {
        abort_unless($survey->isActiveNow(), 404);

        if ($survey->is_public) {
            return; // cualquier encuestador puede responder encuestas públicas
        }

        $hasAccess = $survey->authorizedEncuestadores()
            ->wherePivot('user_id', $request->user()->id)
            ->wherePivot('can_respond', true)
            ->exists();

        abort_unless($hasAccess, 403, 'No tienes acceso a esta encuesta especial.');
    }
}
