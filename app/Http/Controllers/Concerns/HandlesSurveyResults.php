<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Question;
use App\Models\Survey;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

trait HandlesSurveyResults
{
    /**
     * Estadísticas agregadas por pregunta: para preguntas de opciones,
     * conteo por opción; para escala, promedio; para texto, solo el total respondido.
     */
    protected function buildStats(Survey $survey): array
    {
        $survey->loadMissing('questions.options');
        $totalResponses = $survey->responses()->count();

        $stats = [];
        foreach ($survey->questions as $question) {
            $entry = [
                'question_id' => $question->id,
                'title' => $question->title,
                'type' => $question->type,
                'answered_count' => $question->answers()->count(),
            ];

            if ($question->hasOptions()) {
                $entry['options'] = $question->options->map(function ($opt) {
                    $count = DB::table('answer_options')
                        ->where('question_option_id', $opt->id)
                        ->count();
                    return [
                        'label' => $opt->label,
                        'group' => $opt->option_group,
                        'count' => $count,
                    ];
                })->values();
            }

            if ($question->type === Question::TYPE_LINEAR_SCALE) {
                $entry['average'] = round(
                    (float) $question->answers()->avg(DB::raw('CAST(answer_text AS DECIMAL(5,2))')),
                    2
                );
            }

            $stats[] = $entry;
        }

        return [
            'total_responses' => $totalResponses,
            'questions' => $stats,
        ];
    }

    /**
     * Puntos para el mapa Leaflet. Cada respuesta con ubicación se agrupa
     * por encuestador (color propio) y numerada de forma correlativa 1,2,3...
     * dentro de ESTA encuesta. Las respuestas de persona natural van en gris,
     * sin número de encuestador.
     */
    protected function buildMapData(Survey $survey): JsonResponse
    {
        $responses = $survey->responses()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->with('respondent')
            ->orderBy('submitted_at')
            ->get();

        $points = $responses->map(function ($r) {
            return [
                'id' => $r->id,
                'lat' => (float) $r->latitude,
                'lng' => (float) $r->longitude,
                'respondent_type' => $r->respondent_type,
                'encuestador_name' => $r->respondent?->name,
                'color' => $r->respondent?->color ?? '#6B7280', // gris por defecto (persona natural)
                'sequence' => $r->encuestador_sequence_number,
                'submitted_at' => $r->submitted_at->setTimezone('America/Lima')->format('d/m/Y g:ia'),
                'dual_time_label' => $r->dual_time_label,
                'country' => $r->location_country,
            ];
        });

        return response()->json(['points' => $points]);
    }
}
