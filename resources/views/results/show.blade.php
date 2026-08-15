@extends('layouts.app')
@section('title', 'Resultados — ' . $survey->title)

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-blue-900">{{ $survey->title }}</h1>
        <p class="text-sm text-slate-500">{{ $stats['total_responses'] }} respuesta(s) en total</p>
    </div>
    <div class="flex gap-3">
        <a href="{{ $backUrl }}" class="text-blue-700 text-sm hover:underline self-center">← Volver</a>
        <a href="{{ $exportUrl }}" class="bg-green-700 hover:bg-green-800 text-white px-4 py-2 rounded-lg text-sm">
            ⬇ Exportar a Excel
        </a>
    </div>
</div>

{{-- Mapa --}}
<div class="bg-white rounded-xl border border-slate-200 p-5 mb-8">
    <div class="flex items-center justify-between mb-3">
        <h2 class="font-semibold text-slate-800">Mapa de respuestas</h2>
        <button id="toggle-map" type="button" class="text-blue-700 text-sm hover:underline">Mostrar / ocultar mapa</button>
    </div>
    <div id="results-map" style="height: 480px; display:none;" class="rounded-lg overflow-hidden border border-slate-200"></div>
    <p class="text-xs text-slate-400 mt-2">
        Solo se muestran respuestas donde la persona brindó su ubicación. El número dentro de cada punto de color indica el orden
        de esa encuesta para ese encuestador (persona natural se muestra en gris, sin número).
    </p>
</div>

{{-- Estadísticas por pregunta --}}
<div class="space-y-6">
    @foreach($stats['questions'] as $q)
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h3 class="font-medium text-slate-800">{{ $q['title'] }}</h3>
            <p class="text-xs text-slate-400 mb-3">{{ $q['answered_count'] }} respuesta(s)</p>

            @if(isset($q['options']))
                <div class="space-y-2">
                    @foreach($q['options'] as $opt)
                        @php
                            $pct = $stats['total_responses'] > 0 ? round(($opt['count'] / $stats['total_responses']) * 100) : 0;
                        @endphp
                        <div>
                            <div class="flex justify-between text-xs text-slate-500 mb-1">
                                <span>{{ $opt['group'] ? "[{$opt['group']}] " : '' }}{{ $opt['label'] }}</span>
                                <span>{{ $opt['count'] }} ({{ $pct }}%)</span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-2">
                                <div class="bg-blue-700 h-2 rounded-full" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @elseif(isset($q['average']))
                <p class="text-sm text-slate-600">Promedio: <span class="font-semibold">{{ $q['average'] }}</span></p>
            @else
                <p class="text-xs text-slate-400">Pregunta de texto libre — revisa el Excel exportado para el detalle.</p>
            @endif
        </div>
    @endforeach
</div>
@endsection

@push('scripts')
<script>
document.getElementById('toggle-map').addEventListener('click', function () {
    var el = document.getElementById('results-map');
    var wasHidden = el.style.display === 'none';
    el.style.display = wasHidden ? 'block' : 'none';
    if (wasHidden && !window.__mapInitialized) {
        initResultsMap();
        window.__mapInitialized = true;
    }
});

function initResultsMap() {
    var map = L.map('results-map').setView([-9.19, -75.02], 5); // centro aproximado de Perú

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19,
    }).addTo(map);

    fetch('{{ $mapDataUrl }}')
        .then(function (r) { return r.json(); })
        .then(function (data) {
            var points = data.points;
            if (!points.length) return;

            var bounds = [];
            points.forEach(function (p) {
                var icon = L.divIcon({
                    className: '',
                    html: '<div style="background:' + p.color + ';color:white;border-radius:9999px;width:26px;height:26px;' +
                          'display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;' +
                          'border:2px solid white;box-shadow:0 1px 3px rgba(0,0,0,.4)">' + (p.sequence ?? '') + '</div>',
                    iconSize: [26, 26],
                });

                var popup = '<div style="font-size:12px">' +
                    (p.encuestador_name ? '<strong>' + p.encuestador_name + '</strong><br>' : '<strong>Persona natural</strong><br>') +
                    p.dual_time_label +
                    (p.country ? '<br>País: ' + p.country : '') +
                    '</div>';

                L.marker([p.lat, p.lng], { icon: icon }).addTo(map).bindPopup(popup);
                bounds.push([p.lat, p.lng]);
            });

            map.fitBounds(bounds, { padding: [30, 30] });
        });
}
</script>
@endpush
