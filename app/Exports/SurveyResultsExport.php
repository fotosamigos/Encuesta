<?php

namespace App\Exports;

use App\Models\Survey;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SurveyResultsExport implements FromCollection, WithHeadings, WithStyles
{
    private array $questionColumns = [];

    public function __construct(private Survey $survey)
    {
        $this->survey->loadMissing('questions.options');
        foreach ($this->survey->questions as $q) {
            $this->questionColumns[] = $q;
        }
    }

    public function headings(): array
    {
        $headings = ['# Respuesta', 'Tipo de respondiente', 'Encuestador', 'Fecha (hora Perú)', 'Latitud', 'Longitud', 'País ubicación'];
        foreach ($this->questionColumns as $q) {
            $headings[] = $q->title;
        }
        return $headings;
    }

    public function collection(): Collection
    {
        $responses = $this->survey->responses()
            ->with(['respondent', 'answers.selectedOptions'])
            ->orderBy('submitted_at')
            ->get();

        return $responses->map(function ($response, $index) {
            $row = [
                $index + 1,
                $response->respondent_type === 'encuestador' ? 'Encuestador' : 'Persona natural',
                $response->respondent?->name ?? '—',
                $response->submitted_at->setTimezone('America/Lima')->format('d/m/Y H:i'),
                $response->latitude ?? '—',
                $response->longitude ?? '—',
                $response->location_country ?? '—',
            ];

            foreach ($this->questionColumns as $q) {
                $answer = $response->answers->firstWhere('question_id', $q->id);

                if (!$answer) {
                    $row[] = '';
                    continue;
                }

                if ($answer->selectedOptions->isNotEmpty()) {
                    $row[] = $answer->selectedOptions->map(function ($o) {
                        return $o->option_group ? "[{$o->option_group}] {$o->label}" : $o->label;
                    })->implode('; ');
                } else {
                    $row[] = $answer->answer_text;
                }
            }

            return $row;
        });
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
