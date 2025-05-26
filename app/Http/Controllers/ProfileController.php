<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Friend;
use App\Models\Notification;
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
        
        // Calcular estadísticas correctamente
        $stats = [
            'liked_movies' => $user->movieLikes()->where('liked', true)->count(),
            
            'total_matches' => $user->allMatches()->distinct('tmdb_id')->count(),
            
            'friends_count' => Friend::where(function($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhere('friend_id', $user->id);
            })->where('status', 'accepted')->count()
        ];
        
        // Obtener solicitudes pendientes
        $pendingRequests = Friend::where('friend_id', $user->id)
                                ->where('status', 'pending')
                                ->with('user')
                                ->get();
        
        // Obtener amigos aceptados
        $acceptedFriends = Friend::where(function($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->orWhere('friend_id', $user->id);
        })
        ->where('status', 'accepted')
        ->with(['user', 'friend'])
        ->get()
        ->map(function($friendship) use ($user) {
            // Retornar el usuario que NO es el usuario actual
            return $friendship->user_id == $user->id ? $friendship->friend : $friendship->user;
        });
        
        // Obtener notificaciones no leídas
        $notifications = $user->notifications()
                             ->where('read', false)
                             ->with('fromUser')
                             ->orderBy('created_at', 'desc')
                             ->get();
        
        return view('profile.show', compact('user', 'stats', 'pendingRequests', 'acceptedFriends', 'notifications'));
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
            'bio' => ['nullable', 'string', 'max:500'],
        ]);

        // Preparar los datos para actualizar
        $dataToUpdate = [
            'name' => $validated['name'],
            'username' => $validated['username'],
            'bio' => $validated['bio'] ?? null,
        ];

        // Actualizar la contraseña si se proporciona
        if ($request->filled('password')) {
            $request->validate([
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);

            $dataToUpdate['password'] = Hash::make($request->password);
        }

        // Actualizar el usuario
        $user->update($dataToUpdate);

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

        // Eliminar relaciones y el usuario
        DB::transaction(function() use ($userId) {
            // Eliminar amistades
            Friend::where('user_id', $userId)->orWhere('friend_id', $userId)->delete();
            
            // Eliminar notificaciones
            Notification::where('user_id', $userId)->orWhere('from_user_id', $userId)->delete();
            
            // Eliminar matches
            DB::table('film_matches')->where('user_id_1', $userId)->orWhere('friend_id', $userId)->delete();
            
            // Eliminar likes de películas
            DB::table('movie_likes')->where('user_id', $userId)->delete();
            
            // Eliminar el usuario
            User::find($userId)->delete();
        });

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Cuenta eliminada correctamente.');
    }
}