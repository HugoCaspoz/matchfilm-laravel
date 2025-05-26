<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\FilmMatch;
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
        
        // Calcular estadísticas usando tus modelos existentes
        $stats = [
            'liked_movies' => $user->movieLikes()->where('liked', true)->count(),
            
            'total_matches' => $user->allMatches()->distinct('tmdb_id')->count(),
            
            'friends_count' => DB::table('friends')
                ->where(function($query) use ($user) {
                    $query->where('user_id', $user->id)
                          ->orWhere('friend_id', $user->id);
                })
                ->where('status', 'accepted')
                ->count()
        ];
        
        // Obtener solicitudes pendientes usando DB directo
        $pendingRequests = DB::table('friends')
            ->join('users', 'friends.user_id', '=', 'users.id')
            ->where('friends.friend_id', $user->id)
            ->where('friends.status', 'pending')
            ->select('friends.*', 'users.name', 'users.username', 'users.email')
            ->get();
        
        // Obtener amigos aceptados usando DB directo
        $acceptedFriends = DB::table('friends')
            ->join('users', function($join) use ($user) {
                $join->on(function($query) use ($user) {
                    $query->where(function($subQuery) use ($user) {
                        $subQuery->where('friends.user_id', $user->id)
                                 ->whereColumn('users.id', 'friends.friend_id');
                    })->orWhere(function($subQuery) use ($user) {
                        $subQuery->where('friends.friend_id', $user->id)
                                 ->whereColumn('users.id', 'friends.user_id');
                    });
                });
            })
            ->where('friends.status', 'accepted')
            ->where(function($query) use ($user) {
                $query->where('friends.user_id', $user->id)
                      ->orWhere('friends.friend_id', $user->id);
            })
            ->select('users.*', 'friends.id as friendship_id')
            ->get();
        
        // Obtener notificaciones usando tu modelo existente
        $notifications = $user->notifications()
                             ->where('read', false)
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

        // Actualizar usando DB para evitar problemas con fillable
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

        // Eliminar relaciones y el usuario usando DB directo
        DB::transaction(function() use ($userId) {
            // Eliminar amistades
            DB::table('friends')->where('user_id', $userId)->orWhere('friend_id', $userId)->delete();
            
            // Eliminar notificaciones
            DB::table('notifications')->where('user_id', $userId)->orWhere('from_user_id', $userId)->delete();
            
            // Eliminar matches
            DB::table('film_matches')->where('user_id_1', $userId)->orWhere('friend_id', $userId)->delete();
            
            // Eliminar likes de películas
            DB::table('movie_likes')->where('user_id', $userId)->delete();
            
            // Eliminar ratings si existen
            DB::table('ratings')->where('user_id', $userId)->delete();
            
            // Eliminar watchlists si existen
            DB::table('watchlists')->where('user_id', $userId)->delete();
            
            // Eliminar el usuario
            DB::table('users')->where('id', $userId)->delete();
        });

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Cuenta eliminada correctamente.');
    }
}