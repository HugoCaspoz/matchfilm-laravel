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
                    'message' => 'Ya existe una relación de amistad o solicitud pendiente con este usuario.'
                ], 400);
            }

            // Crear solicitud de amistad con estado 'pending'
            $friendship = Friend::create([
                'user_id' => $userId,
                'friend_id' => $friendId,
                'status' => 'pending' // Cambiado a 'pending' para requerir aceptación
            ]);

            // Crear notificación
            Notification::create([
                'user_id' => $friendId,
                'from_user_id' => $userId,
                'type' => 'friend_request',
                'message' => Auth::user()->username . ' te ha enviado una solicitud de amistad.',
                'read' => false,
                'data' => json_encode([
                    'friendship_id' => $friendship->id
                ]),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Solicitud de amistad enviada correctamente.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al enviar solicitud de amistad: ' . $e->getMessage());
        
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar solicitud de amistad: ' . $e->getMessage()
            ], 500);
        }
    }

    // Añadir métodos para aceptar y rechazar solicitudes de amistad
    public function acceptRequest($id)
    {
        try {
            $friendship = Friend::findOrFail($id);
        
            // Verificar que el usuario actual sea el destinatario de la solicitud
            if ($friendship->friend_id != Auth::id()) {
                return redirect()->back()->with('error', 'No tienes permiso para aceptar esta solicitud.');
            }
        
            // Verificar que la solicitud esté pendiente
            if ($friendship->status != 'pending') {
                return redirect()->back()->with('error', 'Esta solicitud ya ha sido procesada.');
            }
        
            // Aceptar la solicitud
            $friendship->status = 'accepted';
            $friendship->save();
        
            // Crear notificación para el remitente
            Notification::create([
                'user_id' => $friendship->user_id,
                'from_user_id' => Auth::id(),
                'type' => 'friend_accepted',
                'message' => Auth::user()->username . ' ha aceptado tu solicitud de amistad.',
                'read' => false,
                'data' => json_encode([]),
            ]);
        
            return redirect()->back()->with('success', 'Solicitud de amistad aceptada correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al aceptar solicitud de amistad: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al aceptar la solicitud de amistad.');
        }
    }

    public function rejectRequest($id)
    {
        try {
            $friendship = Friend::findOrFail($id);
        
            // Verificar que el usuario actual sea el destinatario de la solicitud
            if ($friendship->friend_id != Auth::id()) {
                return redirect()->back()->with('error', 'No tienes permiso para rechazar esta solicitud.');
            }
        
            // Verificar que la solicitud esté pendiente
            if ($friendship->status != 'pending') {
                return redirect()->back()->with('error', 'Esta solicitud ya ha sido procesada.');
            }
        
            // Rechazar la solicitud
            $friendship->status = 'rejected';
            $friendship->save();
        
            // Opcional: Crear notificación para el remitente
            Notification::create([
                'user_id' => $friendship->user_id,
                'from_user_id' => Auth::id(),
                'type' => 'friend_rejected',
                'message' => Auth::user()->username . ' ha rechazado tu solicitud de amistad.',
                'read' => false,
                'data' => json_encode([]),
            ]);
        
            return redirect()->back()->with('success', 'Solicitud de amistad rechazada.');
        } catch (\Exception $e) {
            Log::error('Error al rechazar solicitud de amistad: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al rechazar la solicitud de amistad.');
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

            return redirect()->back()->with('success', 'Amigo eliminado correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar amigo: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al eliminar el amigo.');
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
