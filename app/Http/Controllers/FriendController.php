<?php

namespace App\Http\Controllers;

use App\Models\Friend;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FriendController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();

        // Amigos aceptados
        $friends = $user->friends()->wherePivot('status', 'accepted')->get();

        // Solicitudes pendientes enviadas
        $sentRequests = Friend::where('user_id', $user->id)
                              ->where('status', 'pending')
                              ->with('friend')
                              ->get();

        // Solicitudes pendientes recibidas
        $receivedRequests = Friend::where('friend_id', $user->id)
                                 ->where('status', 'pending')
                                 ->with('user')
                                 ->get();

        return view('friends.index', compact('friends', 'sentRequests', 'receivedRequests'));
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        $results = [];

        if ($query) {
            $results = User::where('name', 'like', "%{$query}%")
                          ->where('id', '!=', Auth::id())
                          ->get();
        }

        return view('friends.search', compact('results', 'query'));
    }

    public function sendRequest(Request $request)
    {
        $request->validate([
            'friend_id' => 'required|exists:users,id',
        ]);

        $userId = Auth::id();
        $friendId = $request->friend_id;

        // Verificar que no exista ya una solicitud
        $existingRequest = Friend::where(function($query) use ($userId, $friendId) {
                                $query->where('user_id', $userId)
                                      ->where('friend_id', $friendId);
                            })
                            ->orWhere(function($query) use ($userId, $friendId) {
                                $query->where('user_id', $friendId)
                                      ->where('friend_id', $userId);
                            })
                            ->first();

        if ($existingRequest) {
            return redirect()->back()->with('error', 'Ya existe una solicitud de amistad con este usuario.');
        }

        // Crear solicitud
        Friend::create([
            'user_id' => $userId,
            'friend_id' => $friendId,
            'status' => 'pending',
        ]);

        // Crear notificación
        Notification::create([
            'user_id' => $friendId,
            'from_user_id' => $userId,
            'type' => 'friend_request',
            'message' => Auth::user()->name . ' te ha enviado una solicitud de amistad.',
            'read' => false,
        ]);

        return redirect()->back()->with('success', 'Solicitud de amistad enviada.');
    }

    public function acceptRequest($id)
    {
        $friendRequest = Friend::where('id', $id)
                              ->where('friend_id', Auth::id())
                              ->where('status', 'pending')
                              ->firstOrFail();

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
            'message' => Auth::user()->name . ' ha aceptado tu solicitud de amistad.',
            'read' => false,
        ]);

        return redirect()->back()->with('success', 'Solicitud de amistad aceptada.');
    }

    public function rejectRequest($id)
    {
        $friendRequest = Friend::where('id', $id)
                              ->where('friend_id', Auth::id())
                              ->where('status', 'pending')
                              ->firstOrFail();

        // Actualizar estado
        $friendRequest->status = 'rejected';
        $friendRequest->save();

        return redirect()->back()->with('success', 'Solicitud de amistad rechazada.');
    }

    public function removeFriend($id)
    {
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
    }
}
