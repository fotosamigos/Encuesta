@php
    $qid = $question?->id ?? 'new';
    $action = $question
        ? route('admin.surveys.questions.update', [$survey, $question])
        : route('admin.surveys.questions.store', $survey);
    $formId = "question-form-{$qid}";
@endphp

<form id="{{ $formId }}" method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-4">
    @csrf
    @if($question) @method('PUT') @endif

    <div class="grid sm:grid-cols-3 gap-4">
        <div class="sm:col-span-2">
            <label class="block text-xs font-medium text-slate-600 mb-1">Pregunta</label>
            <input type="text" name="title" value="{{ $question->title ?? '' }}" required
                   class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Tipo de pregunta</label>
            <select name="type" class="question-type w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500" data-form="{{ $formId }}">
                @foreach([
                    'short_text' => 'Texto corto',
                    'paragraph' => 'Párrafo',
                    'single_choice' => 'Opción única',
                    'multiple_choice' => 'Opción múltiple',
                    'dual_choice' => 'Principal / Secundaria',
                    'linear_scale' => 'Escala lineal',
                    'date' => 'Fecha',
                    'time' => 'Hora',
                ] as $value => $label)
                    <option value="{{ $value }}" {{ ($question->type ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div>
        <label class="block text-xs font-medium text-slate-600 mb-1">Descripción (opcional)</label>
        <input type="text" name="description" value="{{ $question->description ?? '' }}"
               class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
    </div>

    <div class="flex items-center gap-6">
        <label class="flex items-center gap-2 text-xs">
            <input type="checkbox" name="is_required" value="1" {{ ($question->is_required ?? false) ? 'checked' : '' }}>
            Obligatoria
        </label>
        <label class="flex items-center gap-2 text-xs">
            <input type="checkbox" name="ask_location" value="1" {{ ($question->ask_location ?? false) ? 'checked' : '' }}>
            Pedir ubicación al responder (hora Perú)
        </label>
    </div>

    {{-- Escala lineal --}}
    <div class="section-linear_scale grid sm:grid-cols-4 gap-3">
        <div>
            <label class="block text-xs text-slate-600 mb-1">Mínimo</label>
            <input type="number" name="scale_min" value="{{ $question->scale_min ?? 1 }}" class="w-full rounded-lg border-slate-300 text-sm">
        </div>
        <div>
            <label class="block text-xs text-slate-600 mb-1">Máximo</label>
            <input type="number" name="scale_max" value="{{ $question->scale_max ?? 5 }}" class="w-full rounded-lg border-slate-300 text-sm">
        </div>
        <div>
            <label class="block text-xs text-slate-600 mb-1">Etiqueta mínimo</label>
            <input type="text" name="scale_min_label" value="{{ $question->scale_min_label ?? '' }}" class="w-full rounded-lg border-slate-300 text-sm">
        </div>
        <div>
            <label class="block text-xs text-slate-600 mb-1">Etiqueta máximo</label>
            <input type="text" name="scale_max_label" value="{{ $question->scale_max_label ?? '' }}" class="w-full rounded-lg border-slate-300 text-sm">
        </div>
    </div>

    {{-- Opción múltiple: límites --}}
    <div class="section-multiple_choice grid sm:grid-cols-2 gap-3">
        <div>
            <label class="block text-xs text-slate-600 mb-1">Mínimo de opciones a marcar</label>
            <input type="number" min="1" name="min_select" value="{{ $question->min_select ?? 1 }}" class="w-full rounded-lg border-slate-300 text-sm">
        </div>
        <div>
            <label class="block text-xs text-slate-600 mb-1">Máximo de opciones a marcar</label>
            <input type="number" min="1" name="max_select" value="{{ $question->max_select ?? 1 }}" class="w-full rounded-lg border-slate-300 text-sm">
        </div>
    </div>

    {{-- Opciones (single_choice, multiple_choice, dual_choice) --}}
    <div class="options-section space-y-2" data-form="{{ $formId }}">
        <label class="block text-xs font-medium text-slate-600">Opciones</label>
        <div class="options-list space-y-2">
            @php $i = 0; @endphp
            @foreach(($question->options ?? []) as $opt)
                @include('admin.surveys._option-row', ['index' => $i, 'option' => $opt, 'type' => $question->type])
                @php $i++; @endphp
            @endforeach
        </div>
        <button type="button" class="add-option-btn text-blue-700 text-xs hover:underline" data-form="{{ $formId }}">+ Agregar opción</button>
    </div>

    <button type="submit" class="bg-blue-900 hover:bg-blue-800 text-white px-4 py-2 rounded-lg text-sm">
        {{ $question ? 'Guardar pregunta' : 'Agregar pregunta' }}
    </button>
</form>

<template id="option-row-template-{{ $formId }}">
    @include('admin.surveys._option-row', ['index' => '__INDEX__', 'option' => null, 'type' => null])
</template>

<script>
(function () {
    var formId = "{{ $formId }}";
    var form = document.getElementById(formId);
    var select = form.querySelector('.question-type');
    var optionsSection = form.querySelector('.options-section');
    var optionsList = form.querySelector('.options-list');
    var addBtn = form.querySelector('.add-option-btn');
    var linearSection = form.querySelector('.section-linear_scale');
    var multiSection = form.querySelector('.section-multiple_choice');

    function refresh() {
        var type = select.value;
        var showOptions = ['single_choice', 'multiple_choice', 'dual_choice'].includes(type);
        optionsSection.style.display = showOptions ? '' : 'none';
        linearSection.style.display = type === 'linear_scale' ? '' : 'none';
        multiSection.style.display = type === 'multiple_choice' ? '' : 'none';

        // Mostrar/ocultar selector de grupo (principal/secundaria) en cada fila de opción
        optionsList.querySelectorAll('.option-group-select').forEach(function (el) {
            el.style.display = type === 'dual_choice' ? '' : 'none';
        });
    }

    select.addEventListener('change', refresh);
    refresh();

    addBtn.addEventListener('click', function () {
        var idx = optionsList.querySelectorAll('.option-row').length;
        var tpl = document.getElementById('option-row-template-' + formId).innerHTML;
        var html = tpl.replace(/__INDEX__/g, idx);
        var wrapper = document.createElement('div');
        wrapper.innerHTML = html;
        optionsList.appendChild(wrapper.firstElementChild);
        refresh();
    });

    optionsList.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-option-btn')) {
            e.target.closest('.option-row').remove();
        }
    });
})();
</script>
