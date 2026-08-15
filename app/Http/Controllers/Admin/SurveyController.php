<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSurveyRequest;
use App\Models\Survey;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SurveyController extends Controller
{
    public function index(): View
    {
        $surveys = Survey::withCount('responses')
            ->with('creator')
            ->latest()
            ->paginate(15);

        return view('admin.surveys.index', compact('surveys'));
    }

    public function create(): View
    {
        return view('admin.surveys.create', [
            'survey' => new Survey(['is_public' => true, 'is_active' => true]),
            'encuestadores' => User::where('role', 'encuestador')->orderBy('name')->get(),
        ]);
    }

    public function store(StoreSurveyRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('cover_image')) {
            $data['cover_image_path'] = $request->file('cover_image')->store('covers', 'public');
        }

        $survey = Survey::create([
            ...$data,
            'created_by' => $request->user()->id,
        ]);

        $this->syncEncuestadorAccess($survey, $request);

        return redirect()->route('admin.surveys.questions.index', $survey)
            ->with('status', 'Encuesta creada. Ahora agrega las preguntas.');
    }

    public function edit(Survey $survey): View
    {
        return view('admin.surveys.edit', [
            'survey' => $survey,
            'encuestadores' => User::where('role', 'encuestador')->orderBy('name')->get(),
            'accessMap' => $survey->authorizedEncuestadores()->get()->keyBy('id'),
        ]);
    }

    public function update(StoreSurveyRequest $request, Survey $survey): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('cover_image')) {
            if ($survey->cover_image_path) {
                Storage::disk('public')->delete($survey->cover_image_path);
            }
            $data['cover_image_path'] = $request->file('cover_image')->store('covers', 'public');
        }

        $survey->update($data);
        $this->syncEncuestadorAccess($survey, $request);

        return redirect()->route('admin.surveys.edit', $survey)
            ->with('status', 'Encuesta actualizada.');
    }

    public function destroy(Survey $survey): RedirectResponse
    {
        if ($survey->cover_image_path) {
            Storage::disk('public')->delete($survey->cover_image_path);
        }
        $survey->delete();

        return redirect()->route('admin.surveys.index')->with('status', 'Encuesta eliminada.');
    }

    /**
     * Guarda, por cada encuestador marcado en el formulario, sus permisos
     * can_respond / can_view_results para ESTA encuesta puntual.
     */
    private function syncEncuestadorAccess(Survey $survey, Request $request): void
    {
        $respond = (array) $request->input('can_respond', []);
        $viewResults = (array) $request->input('can_view_results', []);

        $userIds = array_unique(array_merge($respond, $viewResults));
        $sync = [];
        foreach ($userIds as $userId) {
            $sync[$userId] = [
                'can_respond' => in_array($userId, $respond),
                'can_view_results' => in_array($userId, $viewResults),
            ];
        }

        $survey->authorizedEncuestadores()->sync($sync);
    }
}
