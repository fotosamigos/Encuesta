@extends('layouts.app')
@section('title', 'Nueva encuesta')
@section('content')
<h1 class="text-2xl font-bold text-blue-900 mb-6">Nueva encuesta</h1>
@include('admin.surveys._form', ['accessMap' => collect()])
@endsection
