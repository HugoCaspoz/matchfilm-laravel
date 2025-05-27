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
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
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
            'username' => [
                'required', 
                'string', 
                'min:5',           // Mínimo 5 caracteres
                'max:50',          // Máximo 50 caracteres
                Rule::unique('users')->ignore($user->id),
                'regex:/^[a-zA-Z0-9_]+$/' // Solo letras, números y guiones bajos
            ],
        ], [
            // Mensajes personalizados
            'username.required' => 'El username es obligatorio.',
            'username.min' => 'El username debe tener al menos 5 caracteres.',
            'username.max' => 'El username no puede tener más de 50 caracteres.',
            'username.unique' => 'Este username ya está en uso.',
            'username.regex' => 'El username solo puede contener letras, números y guiones bajos.',
            'name.required' => 'El nombre es obligatorio.',
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
            ], [
                'password.required' => 'La contraseña es obligatoria.',
                'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
                'password.confirmed' => 'La confirmación de contraseña no coincide.',
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
        $userId = $user->id;

        // Verificar la contraseña
        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'La contraseña proporcionada no coincide con nuestros registros.']);
        }

        Log::info('Iniciando eliminación de cuenta', ['user_id' => $userId]);

        try {
            // Eliminar la imagen de perfil si existe
            if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
                Storage::disk('public')->delete($user->profile_image);
            }

            // Cerrar sesión antes de eliminar
            Auth::logout();

            // Eliminar relaciones y el usuario usando transacción
            DB::transaction(function() use ($userId) {
                $this->deleteUserRelatedDataSafely($userId);
                
                // Eliminar el usuario
                DB::table('users')->where('id', $userId)->delete();
            });

            Log::info('Cuenta eliminada exitosamente', ['user_id' => $userId]);

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/')->with('success', 'Cuenta eliminada correctamente.');

        } catch (\Exception $e) {
            Log::error('Error al eliminar cuenta', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('profile.edit')->with('error', 'Hubo un error al eliminar tu cuenta. Por favor, inténtalo de nuevo.');
        }
    }

    /**
     * Eliminar datos relacionados del usuario de forma segura
     */
    private function deleteUserRelatedDataSafely($userId)
    {
        $tablesToClean = [
            'friends' => ['user_id', 'friend_id'],
            'notifications' => ['user_id', 'from_user_id'],
            'film_matches' => ['user_id_1', 'friend_id'],
            'movie_likes' => ['user_id'],
            'ratings' => ['user_id'],
            'watchlists' => ['user_id'],
        ];

        foreach ($tablesToClean as $table => $columns) {
            try {
                // Verificar si la tabla existe
                if (Schema::hasTable($table)) {
                    $query = DB::table($table);
                    
                    // Construir la consulta WHERE para múltiples columnas
                    $query->where(function($q) use ($columns, $userId, $table) {
                        foreach ($columns as $column) {
                            if (Schema::hasColumn($table, $column)) {
                                $q->orWhere($column, $userId);
                            }
                        }
                    });

                    $deletedCount = $query->delete();
                    
                    Log::info("Eliminados registros de tabla {$table}", [
                        'user_id' => $userId,
                        'deleted_count' => $deletedCount
                    ]);
                } else {
                    Log::warning("Tabla {$table} no existe, saltando...", ['user_id' => $userId]);
                }
            } catch (\Exception $e) {
                Log::warning("Error al limpiar tabla {$table}", [
                    'user_id' => $userId,
                    'error' => $e->getMessage()
                ]);
                continue;
            }
        }
    }
}
