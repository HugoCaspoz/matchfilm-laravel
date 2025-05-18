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
        // Obtener amigos aceptados (limitado a 1 para el concepto de "pareja")
        $friends = DB::table('friends')
                    ->where('user_id', Auth::id())
                    ->where('status', 'accepted')
                    ->join('users', 'users.id', '=', 'friends.friend_id')
                    ->select('users.*', 'friends.id as friendship_id')
                    ->limit(1) // Limitamos a 1 para el concepto de "pareja"
                    ->get();

        // Solicitudes pendientes enviadas
        $sentRequests = DB::table('friends')
                        ->where('user_id', Auth::id())
                        ->where('status', 'pending')
                        ->join('users', 'users.id', '=', 'friends.friend_id')
                        ->select('users.*', 'friends.id as friendship_id')
                        ->get();

        // Solicitudes pendientes recibidas
        $receivedRequests = DB::table('friends')
                            ->where('friend_id', Auth::id())
                            ->where('status', 'pending')
                            ->join('users', 'users.id', '=', 'friends.user_id')
                            ->select('users.*', 'friends.id as friendship_id')
                            ->get();

        return view('friends.index', compact('friends', 'sentRequests', 'receivedRequests'));
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        $results = [];

        if ($query) {
            // Primero intentamos buscar por username
            $results = User::where(function($q) use ($query) {
                            $q->where('username', 'like', "%{$query}%")
                              ->orWhere('name', 'like', "%{$query}%");
                        })
                        ->where('id', '!=', Auth::id())
                        ->get();
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
                    'message' => 'Ya existe una solicitud de amistad con este usuario.'
                ], 400);
            }

            // Verificar si el usuario ya tiene una pareja
            $existingPartner = Friend::where('user_id', $userId)
                                    ->where('status', 'accepted')
                                    ->first();

            if ($existingPartner) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya tienes una pareja, no puedes agregar más.'
                ], 400);
            }

            // Crear solicitud
            $friendship = Friend::create([
                'user_id' => $userId,
                'friend_id' => $friendId,
                'status' => 'pending'
            ]);

            // Crear notificación
            Notification::create([
                'user_id' => $friendId,
                'from_user_id' => $userId,
                'type' => 'friend_request',
                'message' => Auth::user()->username . ' te ha enviado una solicitud de amistad.',
                'read' => false,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Solicitud de amistad enviada correctamente.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al enviar solicitud de amistad: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al enviar la solicitud de amistad: ' . $e->getMessage()
            ], 500);
        }
    }

    public function acceptRequest($id)
    {
        try {
            $friendRequest = Friend::where('id', $id)
                            ->where('friend_id', Auth::id())
                            ->where('status', 'pending')
                            ->firstOrFail();

            // Verificar si el usuario ya tiene una pareja
            $existingPartner = Friend::where('user_id', Auth::id())
                                    ->where('status', 'accepted')
                                    ->first();

            if ($existingPartner) {
                return redirect()->back()->with('error', 'Ya tienes una pareja, no puedes aceptar más solicitudes.');
            }

            // Actualizar estado
            $friendRequest->status = 'accepted';
            $friendRequest->save();

            // Crear relación recíproca
            Friend::create([
                'user_id' => Auth::id(),
                'friend_id' => $friendRequest->user_id,
                'status' => 'accepted',
            ]);

            // Crear notificación
            Notification::create([
                'user_id' => $friendRequest->user_id,
                'from_user_id' => Auth::id(),
                'type' => 'friend_accepted',
                'message' => Auth::user()->username . ' ha aceptado tu solicitud de amistad.',
                'read' => false,
            ]);

            return redirect()->back()->with('success', 'Solicitud de amistad aceptada.');
        } catch (\Exception $e) {
            Log::error('Error al aceptar solicitud de amistad: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al aceptar la solicitud de amistad.');
        }
    }

    public function rejectRequest($id)
    {
        try {
            $friendRequest = Friend::where('id', $id)
                            ->where('friend_id', Auth::id())
                            ->where('status', 'pending')
                            ->firstOrFail();

            // Actualizar estado
            $friendRequest->status = 'rejected';
            $friendRequest->save();

            return redirect()->back()->with('success', 'Solicitud de amistad rechazada.');
        } catch (\Exception $e) {
            Log::error('Error al rechazar solicitud de amistad: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al rechazar la solicitud de amistad.');
        }
    }

    public function removeFriend($id)
    {
        try {
            // Eliminar relación en ambas direcciones
            Friend::where(function($query) use ($id) {
                    $query->where('user_id', Auth::id())
                          ->where('friend_id', $id);
                })
                ->orWhere(function($query) use ($id) {
                    $query->where('user_id', $id)
                          ->where('friend_id', Auth::id());
                })
                ->delete();

            return redirect()->back()->with('success', 'Amigo eliminado.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar amigo: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al eliminar el amigo.');
        }
    }
}
