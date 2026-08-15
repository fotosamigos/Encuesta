<?php

namespace App\Services;

use App\Models\Answer;
use App\Models\Question;
use App\Models\Response;
use App\Models\Survey;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SurveyResponseService
{
    public function __construct(private GeoLocationService $geo)
    {
    }

    /**
     * @param Survey $survey
     * @param array $answersInput  Formato: [question_id => valor, ...] según tipo de pregunta:
     *      short_text/paragraph: string
     *      date: 'YYYY-MM-DD'   time: 'HH:MM'
     *      linear_scale: int
     *      single_choice: question_option_id (int)
     *      multiple_choice: [question_option_id, ...]
     *      dual_choice: ['principal' => question_option_id, 'secundaria' => question_option_id]
     * @param User|null $respondent  null = persona natural (anónimo)
     * @param float|null $lat
     * @param float|null $lng
     */
    public function submit(Survey $survey, array $answersInput, ?User $respondent, ?float $lat, ?float $lng): Response
    {
        $questions = $survey->questions()->with('options')->get();

        $this->validate($questions, $answersInput);

        return DB::transaction(function () use ($survey, $questions, $answersInput, $respondent, $lat, $lng) {

            $locationData = null;
            if ($lat !== null && $lng !== null) {
                $locationData = $this->geo->resolve($lat, $lng);
            }

            $sequenceNumber = null;
            if ($respondent) {
                $sequenceNumber = (int) Response::where('survey_id', $survey->id)
                    ->where('respondent_id', $respondent->id)
                    ->max('encuestador_sequence_number') + 1;
            }

            $response = Response::create([
                'survey_id' => $survey->id,
                'respondent_type' => $respondent ? Response::RESPONDENT_ENCUESTADOR : Response::RESPONDENT_NATURAL,
                'respondent_id' => $respondent?->id,
                'latitude' => $lat,
                'longitude' => $lng,
                'location_country' => $locationData['country'] ?? null,
                'location_timezone' => $locationData['timezone'] ?? null,
                'encuestador_sequence_number' => $sequenceNumber,
                'submitted_at' => now(),
            ]);

            foreach ($questions as $question) {
                $value = $answersInput[$question->id] ?? null;

                if ($value === null || $value === '' || $value === []) {
                    continue; // pregunta opcional sin responder
                }

                $answer = Answer::create([
                    'response_id' => $response->id,
                    'question_id' => $question->id,
                    'answer_text' => $question->hasOptions() ? null : (is_array($value) ? json_encode($value) : $value),
                ]);

                if ($question->type === Question::TYPE_SINGLE_CHOICE) {
                    $answer->selectedOptions()->attach((int) $value);
                }

                if ($question->type === Question::TYPE_MULTIPLE_CHOICE) {
                    $answer->selectedOptions()->attach(array_map('intval', (array) $value));
                }

                if ($question->type === Question::TYPE_DUAL_CHOICE) {
                    $ids = array_filter([
                        (int) ($value['principal'] ?? 0),
                        (int) ($value['secundaria'] ?? 0),
                    ]);
                    if ($ids) {
                        $answer->selectedOptions()->attach($ids);
                    }
                }
            }

            return $response;
        });
    }

    /**
     * @param \Illuminate\Support\Collection<int, Question> $questions
     */
    private function validate($questions, array $answersInput): void
    {
        $errors = [];

        foreach ($questions as $question) {
            $value = $answersInput[$question->id] ?? null;
            $isEmpty = $value === null || $value === '' || $value === [];

            if ($question->is_required && $isEmpty) {
                $errors["answers.{$question->id}"] = "La pregunta \"{$question->title}\" es obligatoria.";
                continue;
            }

            if ($isEmpty) {
                continue;
            }

            switch ($question->type) {
                case Question::TYPE_LINEAR_SCALE:
                    if (!is_numeric($value) || $value < $question->scale_min || $value > $question->scale_max) {
                        $errors["answers.{$question->id}"] = "Valor fuera de rango en \"{$question->title}\".";
                    }
                    break;

                case Question::TYPE_SINGLE_CHOICE:
                    if (!$question->options->contains('id', (int) $value)) {
                        $errors["answers.{$question->id}"] = "Opción inválida en \"{$question->title}\".";
                    }
                    break;

                case Question::TYPE_MULTIPLE_CHOICE:
                    $values = (array) $value;
                    $count = count($values);
                    $min = $question->min_select ?? 1;
                    $max = $question->max_select ?? count($question->options);

                    if ($count < $min || $count > $max) {
                        $errors["answers.{$question->id}"] = $min === $max
                            ? "Debes marcar exactamente {$min} opciones en \"{$question->title}\"."
                            : "Debes marcar entre {$min} y {$max} opciones en \"{$question->title}\".";
                    }

                    $validIds = $question->options->pluck('id')->all();
                    foreach ($values as $v) {
                        if (!in_array((int) $v, $validIds, true)) {
                            $errors["answers.{$question->id}"] = "Opción inválida en \"{$question->title}\".";
                            break;
                        }
                    }
                    break;

                case Question::TYPE_DUAL_CHOICE:
                    $principalIds = $question->principalOptions()->pluck('id')->all();
                    $secundariaIds = $question->secundariaOptions()->pluck('id')->all();
                    $p = (int) ($value['principal'] ?? 0);
                    $s = (int) ($value['secundaria'] ?? 0);

                    if ($principalIds && !in_array($p, $principalIds, true)) {
                        $errors["answers.{$question->id}.principal"] = "Elige una opción principal válida en \"{$question->title}\".";
                    }
                    if ($secundariaIds && !in_array($s, $secundariaIds, true)) {
                        $errors["answers.{$question->id}.secundaria"] = "Elige una opción secundaria válida en \"{$question->title}\".";
                    }
                    break;

                case Question::TYPE_DATE:
                    if (!strtotime($value)) {
                        $errors["answers.{$question->id}"] = "Fecha inválida en \"{$question->title}\".";
                    }
                    break;
            }
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
    }
}
