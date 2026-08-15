@extends('layouts.app')
@section('title', 'Editar encuesta')
@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-blue-900">Editar encuesta</h1>
    <a href="{{ route('admin.surveys.questions.index', $survey) }}" class="text-blue-700 text-sm hover:underline">
        Editar preguntas →
    </a>
</div>
@include('admin.surveys._form')
@endsection
