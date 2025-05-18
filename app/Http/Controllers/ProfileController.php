<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show()
    {
        $user = Auth::user();
        return view('profile.show', compact('user'));
    }

    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
        ]);

        // Preparar los datos para actualizar
        $dataToUpdate = [
            'name' => $validated['name'],
            'username' => $validated['username'],
        ];

        // Actualizar la contraseña si se proporciona
        if ($request->filled('password')) {
            $request->validate([
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);

            $dataToUpdate['password'] = Hash::make($request->password);
        }

        // Actualizar el usuario usando DB
        DB::table('users')
            ->where('id', $user->id)
            ->update($dataToUpdate);

        return redirect()->route('profile.show')->with('success', 'Perfil actualizado correctamente.');
    }

    public function destroy(Request $request)
    {
        $user = Auth::user();

        // Verificar la contraseña
        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'La contraseña proporcionada no coincide con nuestros registros.']);
        }

        // Eliminar la imagen de perfil si existe
        if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
            Storage::disk('public')->delete($user->profile_image);
        }

        // Guardar el ID del usuario
        $userId = $user->id;

        // Cerrar sesión antes de eliminar
        Auth::logout();

        // Eliminar el usuario usando DB
        DB::table('users')->where('id', $userId)->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
