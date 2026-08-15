@extends('layouts.app')
@section('title', 'Encuestas')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-blue-900">Encuestas</h1>
    <a href="{{ route('admin.surveys.create') }}" class="bg-blue-900 hover:bg-blue-800 text-white px-4 py-2 rounded-lg text-sm">
        + Nueva encuesta
    </a>
</div>

<div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
            <tr>
                <th class="text-left px-4 py-3">Título</th>
                <th class="text-left px-4 py-3">Visibilidad</th>
                <th class="text-left px-4 py-3">Estado</th>
                <th class="text-left px-4 py-3">Respuestas</th>
                <th class="text-right px-4 py-3">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach($surveys as $survey)
                <tr>
                    <td class="px-4 py-3 font-medium text-slate-800">{{ $survey->title }}</td>
                    <td class="px-4 py-3">
                        @if($survey->is_public)
                            <span class="bg-green-50 text-green-700 text-xs px-2 py-1 rounded-full">Pública</span>
                        @else
                            <span class="bg-amber-50 text-amber-700 text-xs px-2 py-1 rounded-full">Especial (encuestadores)</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if($survey->is_active)
                            <span class="text-green-600">Activa</span>
                        @else
                            <span class="text-slate-400">Inactiva</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">{{ $survey->responses_count }}</td>
                    <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                        @if($survey->is_public)
                            <button type="button" class="copy-link text-slate-500 hover:underline"
                                    data-url="{{ route('surveys.show', $survey) }}">🔗 Copiar enlace</button>
                        @endif
                        <a href="{{ route('admin.surveys.questions.index', $survey) }}" class="text-blue-700 hover:underline">Preguntas</a>
                        <a href="{{ route('admin.surveys.results.show', $survey) }}" class="text-blue-700 hover:underline">Resultados</a>
                        <a href="{{ route('admin.surveys.edit', $survey) }}" class="text-blue-700 hover:underline">Editar</a>
                        <form action="{{ route('admin.surveys.destroy', $survey) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar esta encuesta?')">
                            @csrf @method('DELETE')
                            <button class="text-red-600 hover:underline">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $surveys->links() }}</div>

<script>
document.querySelectorAll('.copy-link').forEach(function (btn) {
    btn.addEventListener('click', function () {
        navigator.clipboard.writeText(this.dataset.url).then(function () {
            btn.textContent = '✅ Copiado';
            setTimeout(function () { btn.textContent = '🔗 Copiar enlace'; }, 1500);
        });
    });
});
</script>
@endsection
