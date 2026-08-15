<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Services\SurveyResponseService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SurveyController extends Controller
{
    /** Encuestas públicas y activas, visibles para cualquier visitante. */
    public function index(): View
    {
        $surveys = Survey::where('is_public', true)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->latest()
            ->get();

        return view('public.surveys.index', compact('surveys'));
    }

    public function show(Survey $survey): View
    {
        abort_unless($survey->isVisibleTo(auth()->user()), 404);
        abort_unless($survey->isActiveNow(), 404, 'Esta encuesta ya no está disponible.');

        $survey->load('questions.options');

        // Evita mostrar el formulario de nuevo si esta misma sesión ya respondió
        // (no es infalible —alguien puede borrar cookies—, pero frena el caso normal
        // de doble clic / recargar la página / volver atrás).
        $alreadyResponded = session()->has("responded_survey_{$survey->id}");

        return view('public.surveys.show', compact('survey', 'alreadyResponded'));
    }

    public function store(Request $request, Survey $survey, SurveyResponseService $service): RedirectResponse
    {
        abort_unless($survey->isVisibleTo(auth()->user()), 404);
        abort_unless($survey->isActiveNow(), 404);

        if (session()->has("responded_survey_{$survey->id}")) {
            return redirect()->route('surveys.show', $survey)
                ->with('status', 'Ya registramos tu respuesta a esta encuesta. ¡Gracias!');
        }

        $service->submit(
            survey: $survey,
            answersInput: $request->input('answers', []),
            respondent: null,
            lat: $request->float('latitude') ?: null,
            lng: $request->float('longitude') ?: null,
        );

        session()->put("responded_survey_{$survey->id}", true);

        return redirect()->route('surveys.show', $survey)
            ->with('status', '¡Gracias! Tu respuesta fue registrada correctamente.');
    }
}
