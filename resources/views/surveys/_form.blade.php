{{-- Se incluye desde public/surveys/show.blade.php y encuestador/surveys/show.blade.php --}}
{{-- Espera la variable $survey y $action (URL del form) --}}

@php $needsLocation = $survey->questions->contains('ask_location', true); @endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" id="survey-form" class="space-y-6">
    @csrf
    <input type="hidden" name="latitude" id="latitude">
    <input type="hidden" name="longitude" id="longitude">

    @if($needsLocation)
        <div id="location-notice" class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">
            Esta encuesta solicita tu ubicación para algunas preguntas. Te pediremos permiso al enviar tus respuestas.
        </div>
    @endif

    @foreach($survey->questions as $question)
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
            <div class="flex items-start justify-between gap-2">
                <label class="block font-medium text-slate-800">
                    {{ $loop->iteration }}. {{ $question->title }}
                    @if($question->is_required) <span class="text-red-500">*</span> @endif
                </label>
            </div>
            @if($question->description)
                <p class="text-sm text-slate-500 mt-1">{{ $question->description }}</p>
            @endif

            <div class="mt-4">
                @switch($question->type)

                    @case('short_text')
                        <input type="text" name="answers[{{ $question->id }}]"
                               class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                               {{ $question->is_required ? 'required' : '' }}>
                        @break

                    @case('paragraph')
                        <textarea name="answers[{{ $question->id }}]" rows="4"
                                  class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                                  {{ $question->is_required ? 'required' : '' }}></textarea>
                        @break

                    @case('date')
                        <input type="date" name="answers[{{ $question->id }}]"
                               class="rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                               {{ $question->is_required ? 'required' : '' }}>
                        @break

                    @case('time')
                        <input type="time" name="answers[{{ $question->id }}]"
                               class="rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                               {{ $question->is_required ? 'required' : '' }}>
                        @break

                    @case('linear_scale')
                        <div class="flex items-center gap-3">
                            <span class="text-xs text-slate-500">{{ $question->scale_min_label ?? $question->scale_min }}</span>
                            @for($i = $question->scale_min; $i <= $question->scale_max; $i++)
                                <label class="flex flex-col items-center gap-1 text-xs text-slate-500">
                                    <input type="radio" name="answers[{{ $question->id }}]" value="{{ $i }}"
                                           {{ $question->is_required ? 'required' : '' }}>
                                    {{ $i }}
                                </label>
                            @endfor
                            <span class="text-xs text-slate-500">{{ $question->scale_max_label ?? $question->scale_max }}</span>
                        </div>
                        @break

                    @case('single_choice')
                        <div class="grid sm:grid-cols-2 gap-3">
                            @foreach($question->options as $option)
                                <label class="flex items-center gap-3 border border-slate-200 rounded-lg p-3 cursor-pointer hover:border-blue-400">
                                    <input type="radio" name="answers[{{ $question->id }}]" value="{{ $option->id }}"
                                           {{ $question->is_required ? 'required' : '' }}>
                                    @if($option->image)
                                        <img src="{{ $option->image }}" class="w-14 h-14 object-cover rounded-md">
                                    @endif
                                    <span>{{ $option->label }}</span>
                                </label>
                            @endforeach
                        </div>
                        @break

                    @case('multiple_choice')
                        @php
                            $min = $question->min_select ?? 1;
                            $max = $question->max_select ?? $question->options->count();
                        @endphp
                        <p class="text-xs text-slate-500 mb-2">
                            {{ $min === $max ? "Selecciona exactamente {$min} opción(es)." : "Selecciona entre {$min} y {$max} opciones." }}
                        </p>
                        <div class="grid sm:grid-cols-2 gap-3">
                            @foreach($question->options as $option)
                                <label class="flex items-center gap-3 border border-slate-200 rounded-lg p-3 cursor-pointer hover:border-blue-400">
                                    <input type="checkbox" name="answers[{{ $question->id }}][]" value="{{ $option->id }}"
                                           class="multi-choice" data-question="{{ $question->id }}" data-max="{{ $max }}">
                                    @if($option->image)
                                        <img src="{{ $option->image }}" class="w-14 h-14 object-cover rounded-md">
                                    @endif
                                    <span>{{ $option->label }}</span>
                                </label>
                            @endforeach
                        </div>
                        @break

                    @case('dual_choice')
                        <div class="grid sm:grid-cols-2 gap-6">
                            <div>
                                <p class="text-xs font-semibold text-slate-500 mb-2">Opción principal</p>
                                <div class="space-y-2">
                                    @foreach($question->principalOptions as $option)
                                        <label class="flex items-center gap-3 border border-slate-200 rounded-lg p-3 cursor-pointer hover:border-blue-400">
                                            <input type="radio" name="answers[{{ $question->id }}][principal]" value="{{ $option->id }}">
                                            @if($option->image)
                                                <img src="{{ $option->image }}" class="w-12 h-12 object-cover rounded-md">
                                            @endif
                                            <span>{{ $option->label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-slate-500 mb-2">Opción secundaria</p>
                                <div class="space-y-2">
                                    @foreach($question->secundariaOptions as $option)
                                        <label class="flex items-center gap-3 border border-slate-200 rounded-lg p-3 cursor-pointer hover:border-blue-400">
                                            <input type="radio" name="answers[{{ $question->id }}][secundaria]" value="{{ $option->id }}">
                                            @if($option->image)
                                                <img src="{{ $option->image }}" class="w-12 h-12 object-cover rounded-md">
                                            @endif
                                            <span>{{ $option->label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @break
                @endswitch
            </div>
        </div>
    @endforeach

    <button type="submit" class="w-full sm:w-auto bg-blue-900 hover:bg-blue-800 text-white font-medium px-6 py-3 rounded-lg transition">
        Enviar respuestas
    </button>
</form>

<script>
document.querySelectorAll('.multi-choice').forEach(function (box) {
    box.addEventListener('change', function () {
        var qid = this.dataset.question;
        var max = parseInt(this.dataset.max, 10);
        var checked = document.querySelectorAll('.multi-choice[data-question="' + qid + '"]:checked');
        if (checked.length > max) {
            this.checked = false;
            alert('Solo puedes seleccionar hasta ' + max + ' opciones en esta pregunta.');
        }
    });
});

@if($needsLocation)
document.getElementById('survey-form').addEventListener('submit', function (e) {
    if (document.getElementById('latitude').value) return; // ya se obtuvo
    e.preventDefault();
    var form = this;
    if (!navigator.geolocation) { form.submit(); return; }

    navigator.geolocation.getCurrentPosition(function (pos) {
        document.getElementById('latitude').value = pos.coords.latitude;
        document.getElementById('longitude').value = pos.coords.longitude;
        form.submit();
    }, function () {
        form.submit(); // el usuario negó el permiso; se envía sin ubicación
    }, { timeout: 8000 });
});
@endif
</script>
