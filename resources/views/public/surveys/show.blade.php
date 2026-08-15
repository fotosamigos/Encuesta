@extends('layouts.app')
@section('title', $survey->title)

@section('content')
<div class="max-w-3xl mx-auto">
    @if($survey->cover_image_path || $survey->cover_image_url)
        <img src="{{ $survey->cover_image_path ? asset('storage/'.$survey->cover_image_path) : $survey->cover_image_url }}"
             class="w-full h-56 object-cover rounded-xl mb-6">
    @endif

    <h1 class="text-2xl font-bold text-blue-900">{{ $survey->title }}</h1>
    @if($survey->description)
        <p class="text-slate-500 mt-2 mb-6">{{ $survey->description }}</p>
    @endif

    @if($alreadyResponded)
        <div class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-4 text-blue-800 text-sm text-center">
            Ya registramos tu respuesta a esta encuesta desde este dispositivo. ¡Gracias por participar!
        </div>
    @else
        @include('surveys._form', ['action' => route('surveys.store', $survey)])
    @endif
</div>
@endsection
