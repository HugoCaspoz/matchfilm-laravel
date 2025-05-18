<?php

namespace App\Http\Controllers;

use App\Models\Friend;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FriendController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // Obtener el usuario actual
        $user = Auth::user();

        // Obtener amigos (pareja) - buscar en ambas direcciones
        $friends = DB::table('friends')
                    ->where(function($query) use ($user) {
                        $query->where('user_id', $user->id)
                              ->orWhere('friend_id', $user->id);
                    })
                    ->where('status', 'accepted')
                    ->join('users', function($join) use ($user) {
                        $join->on('users.id', '=', DB::raw('CASE
                            WHEN friends.user_id = ' . $user->id . ' THEN friends.friend_id
                            ELSE friends.user_id END'));
                    })
                    ->select('users.*', 'friends.id as friendship_id')
                    ->get();

        // Obtener notificaciones
        $notifications = Notification::where('user_id', $user->id)
                            ->where('read', false)
                            ->with('fromUser')
                            ->orderBy('created_at', 'desc')
                            ->get();

        return view('friends.index', compact('friends', 'notifications'));
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        $results = [];

        if ($query) {
            // Buscar por username o name
            $results = User::where(function($q) use ($query) {
                            $q->where('username', 'like', "%{$query}%")
                              ->orWhere('name', 'like', "%{$query}%");
                        })
                        ->where('id', '!=', Auth::id())
                        ->get();

            // Marcar usuarios que ya son amigos
            $user = Auth::user();
            $friendIds = DB::table('friends')
                        ->where(function($query) use ($user) {
                            $query->where('user_id', $user->id)
                                  ->orWhere('friend_id', $user->id);
                        })
                        ->where('status', 'accepted')
                        ->get()
                        ->map(function($friend) use ($user) {
                            return $friend->user_id == $user->id ? $friend->friend_id : $friend->user_id;
                        })
                        ->toArray();

            foreach ($results as $result) {
                $result->is_friend = in_array($result->id, $friendIds);
            }
        }

        return view('friends.search', compact('results', 'query'));
    }

    public function sendRequest(Request $request)
    {
        try {
            // Validar si se envía un ID o un nombre de usuario
            if (is_numeric($request->friend_id)) {
                $friendId = $request->friend_id;
                $friend = User::findOrFail($friendId);
            } else {
                // Buscar por nombre de usuario
                $username = $request->friend_id;
                $friend = User::where('username', $username)->orWhere('name', $username)->firstOrFail();
                $friendId = $friend->id;
            }

            $userId = Auth::id();

            // Verificar si ya existe una relación de amistad
            $existingFriendship = Friend::where(function($query) use ($userId, $friendId) {
                                    $query->where('user_id', $userId)
                                          ->where('friend_id', $friendId);
                                })
                                ->orWhere(function($query) use ($userId, $friendId) {
                                    $query->where('user_id', $friendId)
                                          ->where('friend_id', $userId);
                                })
                                ->first();

            if ($existingFriendship) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe una relación de amistad con este usuario.'
                ], 400);
            }

            // Verificar si el usuario ya tiene una pareja
            $existingPartner = Friend::where(function($query) use ($userId) {
                                    $query->where('user_id', $userId)
                                          ->orWhere('friend_id', $userId);
                                })
                                ->where('status', 'accepted')
                                ->first();

            if ($existingPartner) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya tienes una pareja, no puedes agregar más.'
                ], 400);
            }

            // Crear amistad directamente (sin solicitud pendiente)
            $friendship = Friend::create([
                'user_id' => $userId,
                'friend_id' => $friendId,
                'status' => 'accepted' // Aceptada directamente
            ]);

            // Crear notificación
            Notification::create([
                'user_id' => $friendId,
                'from_user_id' => $userId,
                'type' => 'friend_accepted',
                'message' => Auth::user()->username . ' te ha agregado como pareja.',
                'read' => false,
                'data' => json_encode([]),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pareja agregada correctamente.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al agregar pareja: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al agregar pareja: ' . $e->getMessage()
            ], 500);
        }
    }

    public function removeFriend($id)
    {
        try {
            $user = Auth::user();

            // Eliminar relación en ambas direcciones
            Friend::where(function($query) use ($user, $id) {
                    $query->where(function($q) use ($user, $id) {
                        $q->where('user_id', $user->id)
                          ->where('friend_id', $id);
                    })
                    ->orWhere(function($q) use ($user, $id) {
                        $q->where('user_id', $id)
                          ->where('friend_id', $user->id);
                    });
                })
                ->delete();

            return redirect()->back()->with('success', 'Pareja eliminada correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar pareja: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al eliminar la pareja.');
        }
    }

    // Método para obtener matches de películas con un amigo
    public function getMatches($friendId)
    {
        try {
            $userId = Auth::id();

            // Verificar que sean amigos
            $friendship = Friend::where(function($query) use ($userId, $friendId) {
                            $query->where('user_id', $userId)
                                  ->where('friend_id', $friendId);
                        })
                        ->orWhere(function($query) use ($userId, $friendId) {
                            $query->where('user_id', $friendId)
                                  ->where('friend_id', $userId);
                        })
                        ->where('status', 'accepted')
                        ->first();

            if (!$friendship) {
                return response()->json([
                    'success' => false,
                    'message' => 'No existe una relación de amistad con este usuario.'
                ], 400);
            }

            // Buscar películas que ambos han dado like
            $matches = DB::table('movie_likes as ml1')
                        ->join('movie_likes as ml2', 'ml1.tmdb_id', '=', 'ml2.tmdb_id')
                        ->where('ml1.user_id', $userId)
                        ->where('ml2.user_id', $friendId)
                        ->where('ml1.liked', true)
                        ->where('ml2.liked', true)
                        ->select('ml1.tmdb_id')
                        ->get();

            return response()->json([
                'success' => true,
                'matches' => $matches
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener matches: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener matches: ' . $e->getMessage()
            ], 500);
        }
    }
}
