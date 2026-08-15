@extends('layouts.app')
@section('title', 'Encuestadores')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-blue-900">Cuentas de encuestador</h1>
    <a href="{{ route('admin.encuestadores.create') }}" class="bg-blue-900 hover:bg-blue-800 text-white px-4 py-2 rounded-lg text-sm">
        + Nueva cuenta
    </a>
</div>

<div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
            <tr>
                <th class="text-left px-4 py-3">Nombre</th>
                <th class="text-left px-4 py-3">Correo</th>
                <th class="text-left px-4 py-3">Color</th>
                <th class="text-left px-4 py-3">Respuestas</th>
                <th class="text-left px-4 py-3">Estado</th>
                <th class="text-right px-4 py-3">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach($encuestadores as $enc)
                <tr>
                    <td class="px-4 py-3 font-medium text-slate-800">{{ $enc->name }}</td>
                    <td class="px-4 py-3 text-slate-500">{{ $enc->email }}</td>
                    <td class="px-4 py-3">
                        <span class="w-4 h-4 rounded-full inline-block border border-slate-200" style="background:{{ $enc->color ?? '#94a3b8' }}"></span>
                    </td>
                    <td class="px-4 py-3">{{ $enc->responses_count }}</td>
                    <td class="px-4 py-3">
                        @if($enc->is_active)
                            <span class="text-green-600">Activa</span>
                        @else
                            <span class="text-slate-400">Deshabilitada</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                        <a href="{{ route('admin.encuestadores.edit', $enc) }}" class="text-blue-700 hover:underline">Editar</a>
                        <form action="{{ route('admin.encuestadores.toggle', $enc) }}" method="POST" class="inline">
                            @csrf @method('PATCH')
                            <button class="text-amber-600 hover:underline">{{ $enc->is_active ? 'Deshabilitar' : 'Habilitar' }}</button>
                        </form>
                        <form action="{{ route('admin.encuestadores.destroy', $enc) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar cuenta?')">
                            @csrf @method('DELETE')
                            <button class="text-red-600 hover:underline">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
