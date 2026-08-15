<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class EncuestadorController extends Controller
{
    // Paleta fija sugerida (el admin también puede elegir un hex libre desde el formulario).
    public const COLOR_PALETTE = [
        '#2563EB' => 'Azul', '#DC2626' => 'Rojo', '#16A34A' => 'Verde',
        '#D97706' => 'Ámbar', '#7C3AED' => 'Violeta', '#DB2777' => 'Rosa',
        '#0891B2' => 'Cian', '#65A30D' => 'Lima', '#EA580C' => 'Naranja',
        '#4338CA' => 'Índigo',
    ];

    public function index(): View
    {
        $encuestadores = User::where('role', 'encuestador')
            ->withCount('responses')
            ->orderBy('name')
            ->get();

        return view('admin.encuestadores.index', compact('encuestadores'));
    }

    public function create(): View
    {
        return view('admin.encuestadores.create', ['palette' => self::COLOR_PALETTE]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', Password::defaults()],
            'color' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'encuestador',
            'color' => $data['color'] ?? null,
            'is_active' => true,
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.encuestadores.index')->with('status', 'Cuenta de encuestador creada.');
    }

    public function edit(User $encuestador): View
    {
        abort_unless($encuestador->role === 'encuestador', 404);

        return view('admin.encuestadores.edit', [
            'encuestador' => $encuestador,
            'palette' => self::COLOR_PALETTE,
        ]);
    }

    public function update(Request $request, User $encuestador): RedirectResponse
    {
        abort_unless($encuestador->role === 'encuestador', 404);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $encuestador->id,
            'password' => ['nullable', Password::defaults()],
            'color' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $encuestador->name = $data['name'];
        $encuestador->email = $data['email'];
        $encuestador->color = $data['color'] ?? null;
        if (!empty($data['password'])) {
            $encuestador->password = Hash::make($data['password']);
        }
        $encuestador->save();

        return redirect()->route('admin.encuestadores.index')->with('status', 'Cuenta actualizada.');
    }

    /** Habilita / deshabilita la cuenta (no la borra). */
    public function toggle(User $encuestador): RedirectResponse
    {
        abort_unless($encuestador->role === 'encuestador', 404);

        $encuestador->update(['is_active' => !$encuestador->is_active]);

        return back()->with('status', $encuestador->is_active ? 'Cuenta habilitada.' : 'Cuenta deshabilitada.');
    }

    public function destroy(User $encuestador): RedirectResponse
    {
        abort_unless($encuestador->role === 'encuestador', 404);
        $encuestador->delete();

        return redirect()->route('admin.encuestadores.index')->with('status', 'Cuenta eliminada.');
    }
}
