<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Survey;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class QuestionController extends Controller
{
    public function index(Survey $survey): View
    {
        $survey->load('questions.options');

        return view('admin.surveys.builder', compact('survey'));
    }

    public function store(Request $request, Survey $survey): RedirectResponse
    {
        $this->saveQuestion($request, $survey);

        return back()->with('status', 'Pregunta agregada.');
    }

    public function update(Request $request, Survey $survey, Question $question): RedirectResponse
    {
        $this->saveQuestion($request, $survey, $question);

        return back()->with('status', 'Pregunta actualizada.');
    }

    public function destroy(Survey $survey, Question $question): RedirectResponse
    {
        foreach ($question->options as $option) {
            if ($option->image_path) {
                Storage::disk('public')->delete($option->image_path);
            }
        }
        $question->delete();

        return back()->with('status', 'Pregunta eliminada.');
    }

    /** Reordena preguntas vía drag & drop (recibe array de IDs en orden). */
    public function reorder(Request $request, Survey $survey): RedirectResponse
    {
        $ids = (array) $request->input('order', []);
        foreach ($ids as $index => $id) {
            Question::where('id', $id)->where('survey_id', $survey->id)->update(['order' => $index]);
        }

        return response()->json(['ok' => true]);
    }

    private function saveQuestion(Request $request, Survey $survey, ?Question $question = null): void
    {
        $validated = $request->validate([
            'title' => 'required|string|max:500',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:short_text,paragraph,single_choice,multiple_choice,dual_choice,linear_scale,date,time',
            'is_required' => 'sometimes|boolean',
            'ask_location' => 'sometimes|boolean',
            'min_select' => 'nullable|integer|min:1',
            'max_select' => 'nullable|integer|min:1',
            'scale_min' => 'nullable|integer|min:0',
            'scale_max' => 'nullable|integer|min:1',
            'scale_min_label' => 'nullable|string|max:100',
            'scale_max_label' => 'nullable|string|max:100',
            'options' => 'nullable|array',
            'options.*.label' => 'nullable|string|max:255',
            'options.*.image_url' => 'nullable|string|max:2000',
            'options.*.group' => 'nullable|in:principal,secundaria',
            'options.*.image' => 'nullable|image|max:4096',
        ]);

        DB::transaction(function () use ($request, $survey, $question, $validated) {
            $payload = [
                'survey_id' => $survey->id,
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'type' => $validated['type'],
                'is_required' => $request->boolean('is_required'),
                'ask_location' => $request->boolean('ask_location'),
                'min_select' => $validated['min_select'] ?? null,
                'max_select' => $validated['max_select'] ?? null,
                'scale_min' => $validated['scale_min'] ?? null,
                'scale_max' => $validated['scale_max'] ?? null,
                'scale_min_label' => $validated['scale_min_label'] ?? null,
                'scale_max_label' => $validated['scale_max_label'] ?? null,
            ];

            if ($question) {
                $question->update($payload);
            } else {
                $payload['order'] = $survey->questions()->max('order') + 1;
                $question = Question::create($payload);
            }

            // Reemplazamos por completo las opciones cada vez que se guarda (simple y predecible)
            if (in_array($validated['type'], ['single_choice', 'multiple_choice', 'dual_choice'])) {
                foreach ($question->options as $old) {
                    if ($old->image_path) {
                        Storage::disk('public')->delete($old->image_path);
                    }
                }
                $question->options()->delete();

                foreach ($request->input('options', []) as $i => $optionData) {
                    $imagePath = null;
                    if ($request->hasFile("options.$i.image")) {
                        $imagePath = $request->file("options.$i.image")->store('options', 'public');
                    }

                    QuestionOption::create([
                        'question_id' => $question->id,
                        'label' => $optionData['label'] ?? ('Opción ' . ($i + 1)),
                        'image_path' => $imagePath,
                        'image_url' => $optionData['image_url'] ?? null,
                        'order' => $i,
                        'option_group' => $validated['type'] === 'dual_choice' ? ($optionData['group'] ?? null) : null,
                    ]);
                }
            } else {
                $question->options()->delete();
            }
        });
    }
}
