<?php

namespace App\Http\Controllers\Encuestador;

use App\Exports\SurveyResultsExport;
use App\Http\Controllers\Concerns\HandlesSurveyResults;
use App\Http\Controllers\Controller;
use App\Models\Survey;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ResultController extends Controller
{
    use HandlesSurveyResults;

    public function show(Request $request, Survey $survey): View
    {
        $this->authorizeResults($request, $survey);

        return view('results.show', [
            'survey' => $survey,
            'stats' => $this->buildStats($survey),
            'mapDataUrl' => route('encuestador.surveys.results.map-data', $survey),
            'exportUrl' => route('encuestador.surveys.results.export', $survey),
            'backUrl' => route('encuestador.dashboard'),
            'canEdit' => false,
        ]);
    }

    public function mapData(Request $request, Survey $survey): JsonResponse
    {
        $this->authorizeResults($request, $survey);
        return $this->buildMapData($survey);
    }

    public function export(Request $request, Survey $survey): BinaryFileResponse
    {
        $this->authorizeResults($request, $survey);
        $filename = 'resultados-' . str($survey->title)->slug() . '.xlsx';
        return Excel::download(new SurveyResultsExport($survey), $filename);
    }

    private function authorizeResults(Request $request, Survey $survey): void
    {
        $hasAccess = $survey->authorizedEncuestadores()
            ->wherePivot('user_id', $request->user()->id)
            ->wherePivot('can_view_results', true)
            ->exists();

        abort_unless($hasAccess, 403, 'No tienes autorización para ver los resultados de esta encuesta.');
    }
}
