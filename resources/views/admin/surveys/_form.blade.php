{{-- Compartido por create.blade.php y edit.blade.php --}}
@php
    $isEdit = $survey->exists;
    $accessMap = $accessMap ?? collect();
@endphp

<form method="POST" action="{{ $isEdit ? route('admin.surveys.update', $survey) : route('admin.surveys.store') }}"
      enctype="multipart/form-data" class="space-y-6">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="bg-white rounded-xl border border-slate-200 p-5 space-y-4">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Título</label>
            <input type="text" name="title" value="{{ old('title', $survey->title) }}" required
                   class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Descripción</label>
            <textarea name="description" rows="3"
                      class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500">{{ old('description', $survey->description) }}</textarea>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Imagen de portada (archivo)</label>
                <input type="file" name="cover_image" accept="image/*" class="w-full text-sm">
                @if($survey->cover_image_path)
                    <img src="{{ asset('storage/'.$survey->cover_image_path) }}" class="w-32 h-20 object-cover rounded-md mt-2">
                @endif
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">...o URL de imagen</label>
                <input type="text" name="cover_image_url" value="{{ old('cover_image_url', $survey->cover_image_url) }}"
                       class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500">
            </div>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Fecha/hora de inicio (opcional)</label>
                <input type="datetime-local" name="starts_at"
                       value="{{ old('starts_at', optional($survey->starts_at)->format('Y-m-d\TH:i')) }}"
                       class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Fecha/hora de cierre (opcional)</label>
                <input type="datetime-local" name="ends_at"
                       value="{{ old('ends_at', optional($survey->ends_at)->format('Y-m-d\TH:i')) }}"
                       class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500">
            </div>
        </div>

        <div class="flex items-center gap-6">
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $survey->is_active) ? 'checked' : '' }}>
                Encuesta activa (publicada)
            </label>
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_public" value="1" {{ old('is_public', $survey->is_public) ? 'checked' : '' }}>
                Visible para cualquier cuenta (si se desmarca, es "especial": solo encuestadores autorizados)
            </label>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <h2 class="font-semibold text-slate-800 mb-1">Acceso de encuestadores</h2>
        <p class="text-xs text-slate-500 mb-4">
            Marca qué encuestadores pueden responder esta encuesta (necesario si es "especial") y/o ver sus resultados en tiempo real.
        </p>

        @if($encuestadores->isEmpty())
            <p class="text-sm text-slate-400">Aún no has creado cuentas de encuestador.</p>
        @else
            <table class="w-full text-sm">
                <thead class="text-xs text-slate-500 uppercase">
                    <tr>
                        <th class="text-left py-2">Encuestador</th>
                        <th class="text-center py-2">Puede responder</th>
                        <th class="text-center py-2">Puede ver resultados</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($encuestadores as $enc)
                        @php $pivot = $accessMap[$enc->id]->pivot ?? null; @endphp
                        <tr>
                            <td class="py-2 flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full inline-block" style="background:{{ $enc->color ?? '#94a3b8' }}"></span>
                                {{ $enc->name }}
                            </td>
                            <td class="text-center">
                                <input type="checkbox" name="can_respond[]" value="{{ $enc->id }}" {{ $pivot?->can_respond ? 'checked' : '' }}>
                            </td>
                            <td class="text-center">
                                <input type="checkbox" name="can_view_results[]" value="{{ $enc->id }}" {{ $pivot?->can_view_results ? 'checked' : '' }}>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <button type="submit" class="bg-blue-900 hover:bg-blue-800 text-white px-6 py-2.5 rounded-lg text-sm font-medium">
        {{ $isEdit ? 'Guardar cambios' : 'Crear encuesta y continuar a preguntas' }}
    </button>
</form>
