@extends('layouts.app')
@section('title', 'Encuestas disponibles')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-blue-900">Encuestas disponibles</h1>
    <p class="text-slate-500 mt-1">Participa respondiendo cualquiera de las siguientes encuestas.</p>
</div>

@if($surveys->isEmpty())
    <div class="bg-white rounded-xl border border-slate-200 p-10 text-center text-slate-500">
        No hay encuestas disponibles en este momento.
    </div>
@else
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($surveys as $survey)
            <a href="{{ route('surveys.show', $survey) }}" class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden hover:shadow-md transition">
                @if($survey->cover_image_path || $survey->cover_image_url)
                    <img src="{{ $survey->cover_image_path ? asset('storage/'.$survey->cover_image_path) : $survey->cover_image_url }}"
                         class="w-full h-40 object-cover">
                @else
                    <div class="w-full h-40 bg-blue-100 flex items-center justify-center text-blue-400">Sin portada</div>
                @endif
                <div class="p-4">
                    <h2 class="font-semibold text-slate-800">{{ $survey->title }}</h2>
                    <p class="text-sm text-slate-500 mt-1 line-clamp-2">{{ $survey->description }}</p>
                </div>
            </a>
        @endforeach
    </div>
@endif
@endsection
