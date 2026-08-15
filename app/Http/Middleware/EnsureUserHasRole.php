<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Uso en rutas: ->middleware('role:admin') o ->middleware('role:encuestador')
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if (!$user || $user->role !== $role) {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        if ($role === 'encuestador' && !$user->is_active) {
            auth()->logout();
            return redirect()->route('login')
                ->withErrors(['email' => 'Tu cuenta de encuestador ha sido deshabilitada. Contacta al administrador.']);
        }

        return $next($request);
    }
}
