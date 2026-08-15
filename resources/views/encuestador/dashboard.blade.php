@extends('layouts.app')
@section('title', 'Mi panel')

@section('content')
<h1 class="text-2xl font-bold text-blue-900 mb-6">Bienvenido, {{ auth()->user()->name }}</h1>

<div class="grid lg:grid-cols-2 gap-8">
    <div>
        <h2 class="font-semibold text-slate-800 mb-3">Encuestas públicas</h2>
        <div class="space-y-3">
            @forelse($publicSurveys as $s)
                <a href="{{ route('encuestador.surveys.show', $s) }}" class="block bg-white rounded-xl border border-slate-200 p-4 hover:border-blue-400">
                    <p class="font-medium text-slate-800">{{ $s->title }}</p>
                    <p class="text-xs text-slate-500 line-clamp-1">{{ $s->description }}</p>
                </a>
            @empty
                <p class="text-sm text-slate-400">No hay encuestas públicas activas.</p>
            @endforelse
        </div>
    </div>

    <div>
        <h2 class="font-semibold text-slate-800 mb-3">Encuestas especiales asignadas a ti</h2>
        <div class="space-y-3">
            @forelse($specialSurveys as $s)
                <a href="{{ route('encuestador.surveys.show', $s) }}" class="block bg-white rounded-xl border border-amber-200 bg-amber-50 p-4 hover:border-amber-400">
                    <p class="font-medium text-slate-800">{{ $s->title }}</p>
                    <p class="text-xs text-slate-500 line-clamp-1">{{ $s->description }}</p>
                </a>
            @empty
                <p class="text-sm text-slate-400">No tienes encuestas especiales asignadas.</p>
            @endforelse
        </div>
    </div>
</div>

<div class="mt-10">
    <h2 class="font-semibold text-slate-800 mb-3">Resultados que puedes consultar</h2>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
        @forelse($resultSurveys as $s)
            <a href="{{ route('encuestador.surveys.results.show', $s) }}" class="block bg-white rounded-xl border border-slate-200 p-4 hover:border-blue-400">
                <p class="font-medium text-slate-800">{{ $s->title }}</p>
                <p class="text-xs text-blue-600">Ver resultados y mapa →</p>
            </a>
        @empty
            <p class="text-sm text-slate-400">Aún no tienes resultados autorizados para ver.</p>
        @endforelse
    </div>
</div>
@endsection
