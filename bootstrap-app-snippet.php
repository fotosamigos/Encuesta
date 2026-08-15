<?php
/*
|--------------------------------------------------------------------------
| ESTO NO ES UN ARCHIVO PARA COPIAR TAL CUAL — es una guía de qué agregar
| a tu bootstrap/app.php (Laravel 11/12). Abre tu bootstrap/app.php y
| busca el bloque ->withMiddleware(function (Middleware $middleware) { ... })
| Dentro de ese callback, agrega el alias 'role':
|--------------------------------------------------------------------------
*/

->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'role' => \App\Http\Middleware\EnsureUserHasRole::class,
    ]);
})

/*
|--------------------------------------------------------------------------
| Tu bootstrap/app.php completo debería quedar más o menos así:
|--------------------------------------------------------------------------
*/

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureUserHasRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
