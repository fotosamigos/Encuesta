@php $isEdit = isset($encuestador); @endphp
<form method="POST" action="{{ $isEdit ? route('admin.encuestadores.update', $encuestador) : route('admin.encuestadores.store') }}" class="bg-white rounded-xl border border-slate-200 p-5 space-y-4 max-w-xl">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Nombre</label>
        <input type="text" name="name" value="{{ old('name', $encuestador->name ?? '') }}" required class="w-full rounded-lg border-slate-300 text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Correo</label>
        <input type="email" name="email" value="{{ old('email', $encuestador->email ?? '') }}" required class="w-full rounded-lg border-slate-300 text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">{{ $isEdit ? 'Nueva contraseña (dejar en blanco para no cambiar)' : 'Contraseña' }}</label>
        <input type="password" name="password" class="w-full rounded-lg border-slate-300 text-sm" {{ $isEdit ? '' : 'required' }}>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-2">Color en el mapa</label>
        <div class="flex flex-wrap gap-2 mb-2">
            @foreach($palette as $hex => $label)
                <label class="cursor-pointer">
                    <input type="radio" name="color_radio" value="{{ $hex }}" class="hidden palette-option" {{ old('color', $encuestador->color ?? '') === $hex ? 'checked' : '' }}>
                    <span class="w-7 h-7 rounded-full inline-block border-2 border-transparent" style="background:{{ $hex }}" title="{{ $label }}"></span>
                </label>
            @endforeach
        </div>
        <label class="block text-xs text-slate-500 mb-1">...o color hex personalizado</label>
        <input type="text" name="color" id="color-hex" value="{{ old('color', $encuestador->color ?? '') }}" placeholder="#2563EB" class="w-40 rounded-lg border-slate-300 text-sm">
    </div>

    <button type="submit" class="bg-blue-900 hover:bg-blue-800 text-white px-6 py-2.5 rounded-lg text-sm font-medium">
        {{ $isEdit ? 'Guardar cambios' : 'Crear cuenta' }}
    </button>
</form>

<script>
document.querySelectorAll('.palette-option').forEach(function (radio) {
    radio.addEventListener('change', function () {
        document.getElementById('color-hex').value = this.value;
    });
});
</script>
