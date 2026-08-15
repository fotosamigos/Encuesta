<?php

use App\Http\Controllers\Admin\EncuestadorController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\ResultController as AdminResultController;
use App\Http\Controllers\Admin\StatsController;
use App\Http\Controllers\Admin\SurveyController as AdminSurveyController;
use App\Http\Controllers\Encuestador\DashboardController;
use App\Http\Controllers\Encuestador\ResultController as EncuestadorResultController;
use App\Http\Controllers\Encuestador\SurveyResponseController;
use App\Http\Controllers\Public\SurveyController as PublicSurveyController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Público (persona natural, sin cuenta)
|--------------------------------------------------------------------------
*/
Route::get('/', [PublicSurveyController::class, 'index'])->name('surveys.index');
Route::get('/encuestas/{survey}', [PublicSurveyController::class, 'show'])->name('surveys.show');
Route::post('/encuestas/{survey}', [PublicSurveyController::class, 'store'])
    ->middleware('throttle:6,1') // máx. 6 envíos por minuto por IP — freno anti-spam/bots
    ->name('surveys.store');

/*
|--------------------------------------------------------------------------
| Autenticación (Breeze) — perfil disponible para admin y encuestador
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    // Breeze redirige aquí tras el login; de aquí despachamos según el rol.
    Route::get('/dashboard', function () {
        $user = auth()->user();

        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        if (!$user->is_active) {
            auth()->logout();
            return redirect()->route('login')->withErrors([
                'email' => 'Tu cuenta de encuestador ha sido deshabilitada. Contacta al administrador.',
            ]);
        }

        return redirect()->route('encuestador.dashboard');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Administrador
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/', [StatsController::class, 'index'])->name('dashboard');
    Route::get('/estadisticas', [StatsController::class, 'index'])->name('stats');

    Route::resource('surveys', AdminSurveyController::class)->except(['show']);

    Route::get('surveys/{survey}/preguntas', [QuestionController::class, 'index'])->name('surveys.questions.index');
    Route::post('surveys/{survey}/preguntas', [QuestionController::class, 'store'])->name('surveys.questions.store');
    Route::put('surveys/{survey}/preguntas/{question}', [QuestionController::class, 'update'])->name('surveys.questions.update');
    Route::delete('surveys/{survey}/preguntas/{question}', [QuestionController::class, 'destroy'])->name('surveys.questions.destroy');
    Route::post('surveys/{survey}/preguntas/reordenar', [QuestionController::class, 'reorder'])->name('surveys.questions.reorder');

    Route::get('surveys/{survey}/resultados', [AdminResultController::class, 'show'])->name('surveys.results.show');
    Route::get('surveys/{survey}/resultados/mapa', [AdminResultController::class, 'mapData'])->name('surveys.results.map-data');
    Route::get('surveys/{survey}/resultados/exportar', [AdminResultController::class, 'export'])->name('surveys.results.export');

    Route::resource('encuestadores', EncuestadorController::class)->except(['show']);
    Route::patch('encuestadores/{encuestador}/toggle', [EncuestadorController::class, 'toggle'])->name('encuestadores.toggle');
});

/*
|--------------------------------------------------------------------------
| Encuestador
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:encuestador'])->prefix('encuestador')->name('encuestador.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('surveys/{survey}', [SurveyResponseController::class, 'show'])->name('surveys.show');
    Route::post('surveys/{survey}', [SurveyResponseController::class, 'store'])
        ->middleware('throttle:20,1')
        ->name('surveys.store');

    Route::get('surveys/{survey}/resultados', [EncuestadorResultController::class, 'show'])->name('surveys.results.show');
    Route::get('surveys/{survey}/resultados/mapa', [EncuestadorResultController::class, 'mapData'])->name('surveys.results.map-data');
    Route::get('surveys/{survey}/resultados/exportar', [EncuestadorResultController::class, 'export'])->name('surveys.results.export');
});

require __DIR__.'/auth.php';
