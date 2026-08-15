@extends('layouts.app')
@section('title', 'Estadísticas')

@section('content')
<h1 class="text-2xl font-bold text-blue-900 mb-6">Panel de estadísticas</h1>

<div class="grid sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
    @foreach([
        'Encuestas' => $totals['surveys'],
        'Encuestas activas' => $totals['active_surveys'],
        'Respuestas totales' => $totals['responses'],
        'Encuestadores' => $totals['encuestadores'],
        'Encuestadores activos' => $totals['encuestadores_activos'],
    ] as $label => $value)
        <div class="bg-white rounded-xl border border-slate-200 p-5 text-center shadow-sm">
            <div class="text-3xl font-bold text-blue-900">{{ $value }}</div>
            <div class="text-xs text-slate-500 mt-1">{{ $label }}</div>
        </div>
    @endforeach
</div>

<div class="grid lg:grid-cols-2 gap-6">
    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <h2 class="font-semibold text-slate-800 mb-4">Encuestas con más respuestas</h2>
        <ul class="space-y-2">
            @foreach($responsesBySurvey as $s)
                <li class="flex justify-between text-sm border-b border-slate-100 pb-2">
                    <a href="{{ route('admin.surveys.results.show', $s) }}" class="text-blue-700 hover:underline">{{ $s->title }}</a>
                    <span class="font-medium">{{ $s->responses_count }}</span>
                </li>
            @endforeach
        </ul>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <h2 class="font-semibold text-slate-800 mb-4">Respuestas por encuestador</h2>
        <ul class="space-y-2">
            @foreach($responsesByEncuestador as $e)
                <li class="flex items-center justify-between text-sm border-b border-slate-100 pb-2">
                    <span class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full inline-block" style="background:{{ $e->color ?? '#94a3b8' }}"></span>
                        {{ $e->name }}
                    </span>
                    <span class="font-medium">{{ $e->responses_count }}</span>
                </li>
            @endforeach
        </ul>
    </div>
</div>
@endsection
