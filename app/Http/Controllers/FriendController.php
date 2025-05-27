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
            // Validar la entrada
            $request->validate([
                'friend_id' => 'required|string|min:1|max:255'
            ]);

            $userId = Auth::id();
            $friendIdentifier = trim($request->friend_id);

            // Log para debug
            Log::info('Enviando solicitud de amistad', [
                'user_id' => $userId,
                'friend_identifier' => $friendIdentifier
            ]);

            // Buscar el usuario por ID o username
            $friend = null;
            
            if (is_numeric($friendIdentifier)) {
                // Si es numérico, buscar por ID
                $friend = User::find($friendIdentifier);
            } else {
                // Si no es numérico, buscar por username
                $friend = User::where('username', $friendIdentifier)->first();
                
                // Si no se encuentra por username, intentar por name
                if (!$friend) {
                    $friend = User::where('name', $friendIdentifier)->first();
                }
            }

            // Verificar si se encontró el usuario
            if (!$friend) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado. Verifica el nombre de usuario.'
                ], 404);
            }

            // Verificar que no sea el mismo usuario
            if ($friend->id === $userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No puedes enviarte una solicitud a ti mismo.'
                ], 400);
            }

            // Verificar si ya existe una relación de amistad
            $existingFriendship = Friend::where(function($query) use ($userId, $friend) {
                                    $query->where('user_id', $userId)
                                          ->where('friend_id', $friend->id);
                                })
                                ->orWhere(function($query) use ($userId, $friend) {
                                    $query->where('user_id', $friend->id)
                                          ->where('friend_id', $userId);
                                })
                                ->first();

            if ($existingFriendship) {
                $statusMessage = '';
                switch ($existingFriendship->status) {
                    case 'accepted':
                        $statusMessage = 'Ya son amigos.';
                        break;
                    case 'pending':
                        $statusMessage = 'Ya existe una solicitud pendiente.';
                        break;
                    case 'rejected':
                        $statusMessage = 'Existe una solicitud rechazada. Contacta al usuario directamente.';
                        break;
                    default:
                        $statusMessage = 'Ya existe una relación con este usuario.';
                }

                return response()->json([
                    'success' => false,
                    'message' => $statusMessage
                ], 400);
            }

            // Crear solicitud de amistad con estado 'pending'
            $friendship = Friend::create([
                'user_id' => $userId,
                'friend_id' => $friend->id,
                'status' => 'pending'
            ]);

            // Crear notificación - usar array directamente (Laravel lo convertirá automáticamente)
            Notification::create([
                'user_id' => $friend->id,
                'from_user_id' => $userId,
                'type' => 'friend_request',
                'message' => Auth::user()->name . ' te ha enviado una solicitud de pareja.',
                'read' => false,
                'data' => [  // Array directo, el cast se encarga de la conversión
                    'friendship_id' => $friendship->id,
                    'processed' => false
                ],
            ]);

            Log::info('Solicitud de amistad enviada correctamente', [
                'friendship_id' => $friendship->id,
                'from_user' => $userId,
                'to_user' => $friend->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Solicitud de amistad enviada correctamente.'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de entrada inválidos: ' . implode(', ', $e->validator->errors()->all())
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al enviar solicitud de amistad', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'friend_identifier' => $request->friend_id ?? 'no_provided'
            ]);
        
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor. Inténtalo de nuevo.'
            ], 500);
        }
    }

    public function acceptRequest($id)
    {
        try {
            $friendship = Friend::findOrFail($id);
        
            // Verificar que el usuario actual sea el destinatario de la solicitud
            if ($friendship->friend_id != Auth::id()) {
                if (request()->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No tienes permiso para aceptar esta solicitud.'
                    ], 403);
                }
                return redirect()->back()->with('error', 'No tienes permiso para aceptar esta solicitud.');
            }
        
            // Verificar que la solicitud esté pendiente
            if ($friendship->status != 'pending') {
                if (request()->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Esta solicitud ya ha sido procesada.'
                    ], 400);
                }
                return redirect()->back()->with('error', 'Esta solicitud ya ha sido procesada.');
            }
        
            // Aceptar la solicitud
            $friendship->status = 'accepted';
            $friendship->save();

            Log::info('=== INICIO acceptRequest ===', [
                'user_id' => Auth::id(),
                'friendship_id' => $id
            ]);

            // Buscar la notificación - con el cast 'array', data ya es un array
            $notification = Notification::where('user_id', Auth::id())
                                       ->where('type', 'friend_request')
                                       ->get()
                                       ->filter(function($n) use ($id) {
                                           // Como data ya es array gracias al cast, no necesitamos json_decode
                                           return isset($n->data['friendship_id']) && 
                                                  $n->data['friendship_id'] == $id;
                                       })
                                       ->first();

            if ($notification) {
                Log::info('ANTES de actualizar notificación:', [
                    'notification_id' => $notification->id,
                    'read' => $notification->read,
                    'data' => $notification->data,
                    'processed' => $notification->data['processed'] ?? 'NO_EXISTE'
                ]);

                // Actualizar la notificación - trabajar directamente con el array
                $notification->read = true;
                $data = $notification->data; // Ya es array gracias al cast
                $data['processed'] = true;
                $data['action'] = 'accepted';
                $data['processed_at'] = now()->toISOString();
                $notification->data = $data; // Laravel se encarga de la conversión
                $notification->save();

                // Verificar que se guardó correctamente
                $notification->refresh();
                Log::info('DESPUÉS de actualizar notificación:', [
                    'notification_id' => $notification->id,
                    'read' => $notification->read,
                    'data' => $notification->data,
                    'processed' => $notification->data['processed'] ?? 'NO_EXISTE'
                ]);
            } else {
                Log::error('❌ NO SE ENCONTRÓ LA NOTIFICACIÓN', [
                    'user_id' => Auth::id(),
                    'friendship_id' => $id
                ]);
            }

            Log::info('=== FIN acceptRequest ===');
        
            // Crear notificación para el remitente
            Notification::create([
                'user_id' => $friendship->user_id,
                'from_user_id' => Auth::id(),
                'type' => 'friend_accepted',
                'message' => Auth::user()->name . ' ha aceptado tu solicitud de pareja.',
                'read' => false,
                'data' => [  // Array directo
                    'friendship_id' => $friendship->id,
                    'processed' => true
                ],
            ]);

            // Respuesta diferente según el tipo de petición
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Solicitud de amistad aceptada correctamente.'
                ]);
            }
        
            return redirect()->back()->with('success', 'Solicitud de amistad aceptada correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al aceptar solicitud de amistad: ' . $e->getMessage());
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al aceptar la solicitud de amistad.'
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Error al aceptar la solicitud de amistad.');
        }
    }

    public function rejectRequest($id)
    {
        try {
            $friendship = Friend::findOrFail($id);
        
            // Verificar que el usuario actual sea el destinatario de la solicitud
            if ($friendship->friend_id != Auth::id()) {
                if (request()->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No tienes permiso para rechazar esta solicitud.'
                    ], 403);
                }
                return redirect()->back()->with('error', 'No tienes permiso para rechazar esta solicitud.');
            }
        
            // Verificar que la solicitud esté pendiente
            if ($friendship->status != 'pending') {
                if (request()->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Esta solicitud ya ha sido procesada.'
                    ], 400);
                }
                return redirect()->back()->with('error', 'Esta solicitud ya ha sido procesada.');
            }

            Log::info('=== INICIO rejectRequest ===', [
                'user_id' => Auth::id(),
                'friendship_id' => $id
            ]);

            // Buscar la notificación - con el cast 'array', data ya es un array
            $notification = Notification::where('user_id', Auth::id())
                                       ->where('type', 'friend_request')
                                       ->get()
                                       ->filter(function($n) use ($id) {
                                           return isset($n->data['friendship_id']) && 
                                                  $n->data['friendship_id'] == $id;
                                       })
                                       ->first();

            if ($notification) {
                Log::info('Notificación encontrada para rechazar, actualizando...', [
                    'notification_id' => $notification->id,
                    'current_data' => $notification->data
                ]);

                $notification->read = true;
                $data = $notification->data; // Ya es array gracias al cast
                $data['processed'] = true;
                $data['action'] = 'rejected';
                $data['processed_at'] = now()->toISOString();
                $notification->data = $data;
                $notification->save();

                Log::info('✅ Notificación de rechazo actualizada', [
                    'notification_id' => $notification->id,
                    'new_data' => $notification->data
                ]);
            } else {
                Log::error('❌ No se encontró la notificación para rechazar', [
                    'user_id' => Auth::id(),
                    'friendship_id' => $id
                ]);
            }

            Log::info('=== FIN rejectRequest ===');
        
            // Eliminar la solicitud en lugar de marcarla como rechazada
            $friendship->delete();

            // Respuesta diferente según el tipo de petición
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Solicitud de amistad rechazada.'
                ]);
            }
        
            return redirect()->back()->with('success', 'Solicitud de amistad rechazada.');
        } catch (\Exception $e) {
            Log::error('Error al rechazar solicitud de amistad: ' . $e->getMessage());
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al rechazar la solicitud de amistad.'
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Error al rechazar la solicitud de amistad.');
        }
    }

    public function removeFriend($id)
    {
        try {
            $user = Auth::user();
        
            // Eliminar relación en ambas direcciones
            $deleted = Friend::where(function($query) use ($user, $id) {
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

        if ($deleted) {
            // Si es una petición AJAX, devolver JSON
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pareja eliminada correctamente.'
                ]);
            }
            
            // Si es una petición normal, devolver redirect
            return redirect()->back()->with('success', 'Pareja eliminada correctamente.');
        } else {
            // Si es una petición AJAX, devolver JSON de error
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró la relación de amistad.'
                ], 404);
            }
            
            return redirect()->back()->with('error', 'No se encontró la relación de amistad.');
        }
    } catch (\Exception $e) {
        Log::error('Error al eliminar amigo: ' . $e->getMessage());
        
        // Si es una petición AJAX, devolver JSON de error
        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la pareja.'
            ], 500);
        }
        
        return redirect()->back()->with('error', 'Error al eliminar la pareja.');
    }
}

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
