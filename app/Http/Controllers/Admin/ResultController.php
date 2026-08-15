<?php

namespace App\Http\Controllers\Admin;

use App\Exports\SurveyResultsExport;
use App\Http\Controllers\Concerns\HandlesSurveyResults;
use App\Http\Controllers\Controller;
use App\Models\Survey;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ResultController extends Controller
{
    use HandlesSurveyResults;

    public function show(Survey $survey): View
    {
        return view('results.show', [
            'survey' => $survey,
            'stats' => $this->buildStats($survey),
            'mapDataUrl' => route('admin.surveys.results.map-data', $survey),
            'exportUrl' => route('admin.surveys.results.export', $survey),
            'backUrl' => route('admin.surveys.index'),
            'canEdit' => true,
        ]);
    }

    public function mapData(Survey $survey): JsonResponse
    {
        return $this->buildMapData($survey);
    }

    public function export(Survey $survey): BinaryFileResponse
    {
        $filename = 'resultados-' . str($survey->title)->slug() . '.xlsx';
        return Excel::download(new SurveyResultsExport($survey), $filename);
    }
}
