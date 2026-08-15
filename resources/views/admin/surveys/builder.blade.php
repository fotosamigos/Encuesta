@extends('layouts.app')
@section('title', 'Preguntas — ' . $survey->title)

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-blue-900">{{ $survey->title }}</h1>
        <p class="text-sm text-slate-500">Constructor de preguntas</p>
    </div>
    <a href="{{ route('admin.surveys.edit', $survey) }}" class="text-blue-700 text-sm hover:underline">← Editar datos de la encuesta</a>
</div>

{{-- Preguntas existentes --}}
<div class="space-y-4 mb-10">
    @forelse($survey->questions as $question)
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs uppercase text-blue-700 font-semibold">{{ str_replace('_',' ', $question->type) }}</span>
                    <h3 class="font-medium text-slate-800">{{ $question->title }} @if($question->is_required)<span class="text-red-500">*</span>@endif</h3>
                    @if($question->ask_location)
                        <span class="text-xs text-amber-600">📍 Pide ubicación</span>
                    @endif
                </div>
                <div class="flex gap-3 text-sm">
                    <button type="button" onclick="document.getElementById('edit-{{ $question->id }}').classList.toggle('hidden')" class="text-blue-700 hover:underline">Editar</button>
                    <form action="{{ route('admin.surveys.questions.destroy', [$survey, $question]) }}" method="POST" onsubmit="return confirm('¿Eliminar pregunta?')">
                        @csrf @method('DELETE')
                        <button class="text-red-600 hover:underline">Eliminar</button>
                    </form>
                </div>
            </div>

            @if($question->hasOptions())
                <ul class="mt-3 text-sm text-slate-500 list-disc list-inside">
                    @foreach($question->options as $opt)
                        <li>{{ $opt->option_group ? "[{$opt->option_group}] " : '' }}{{ $opt->label }}</li>
                    @endforeach
                </ul>
            @endif

            {{-- Formulario de edición (oculto por defecto) --}}
            <div id="edit-{{ $question->id }}" class="hidden mt-4 border-t border-slate-100 pt-4">
                @include('admin.surveys._question-form', ['survey' => $survey, 'question' => $question])
            </div>
        </div>
    @empty
        <p class="text-slate-400 text-sm">Aún no hay preguntas. Agrega la primera abajo.</p>
    @endforelse
</div>

{{-- Nueva pregunta --}}
<div class="bg-white rounded-xl border-2 border-dashed border-blue-200 p-5">
    <h2 class="font-semibold text-slate-800 mb-4">Agregar nueva pregunta</h2>
    @include('admin.surveys._question-form', ['survey' => $survey, 'question' => null])
</div>

<div class="mt-8">
    <a href="{{ route('admin.surveys.results.show', $survey) }}" class="text-blue-700 text-sm hover:underline">Ver resultados de esta encuesta →</a>
</div>
@endsection
