<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Sistema de Encuestas')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col font-sans antialiased">

    <nav class="bg-blue-900 text-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <a href="{{ route('surveys.index') }}" class="flex items-center gap-2 font-semibold text-lg tracking-tight">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Sistema de Encuestas
                </a>

                <div class="flex items-center gap-4 text-sm">
                    @auth
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-200">Estadísticas</a>
                            <a href="{{ route('admin.surveys.index') }}" class="hover:text-blue-200">Encuestas</a>
                            <a href="{{ route('admin.encuestadores.index') }}" class="hover:text-blue-200">Encuestadores</a>
                        @else
                            <a href="{{ route('encuestador.dashboard') }}" class="hover:text-blue-200">Mi panel</a>
                        @endif
                        <a href="{{ route('profile.edit') }}" class="hover:text-blue-200">{{ auth()->user()->name }}</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="bg-blue-800 hover:bg-blue-700 px-3 py-1.5 rounded-md transition">Salir</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="bg-blue-800 hover:bg-blue-700 px-3 py-1.5 rounded-md transition">Ingresar</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if (session('status'))
            <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800 text-sm">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800 text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="bg-white border-t border-slate-200 py-6 text-center text-xs text-slate-500">
        Sistema de Encuestas &middot; {{ now()->setTimezone('America/Lima')->format('Y') }}
    </footer>

    @stack('scripts')
</body>
</html>
