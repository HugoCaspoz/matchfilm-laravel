<?php

namespace App\Http\Controllers;

use App\Models\Friend;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FriendController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // Obtener amigos aceptados
        $friends = DB::table('friends')
                    ->where('user_id', Auth::id())
                    ->where('status', 'accepted')
                    ->join('users', 'users.id', '=', 'friends.friend_id')
                    ->select('users.*', 'friends.id as friendship_id')
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
        $existingRequest = DB::table('friends')
                            ->where(function($query) use ($userId, $friendId) {
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
        DB::table('friends')->insert([
            'user_id' => $userId,
            'friend_id' => $friendId,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
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
        $friendRequest = DB::table('friends')
                        ->where('id', $id)
                        ->where('friend_id', Auth::id())
                        ->where('status', 'pending')
                        ->first();

        if (!$friendRequest) {
            return redirect()->back()->with('error', 'Solicitud de amistad no encontrada.');
        }

        // Actualizar estado
        DB::table('friends')
            ->where('id', $id)
            ->update([
                'status' => 'accepted',
                'updated_at' => now()
            ]);

        // Crear relación recíproca
        DB::table('friends')->insert([
            'user_id' => Auth::id(),
            'friend_id' => $friendRequest->user_id,
            'status' => 'accepted',
            'created_at' => now(),
            'updated_at' => now(),
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
        $friendRequest = DB::table('friends')
                        ->where('id', $id)
                        ->where('friend_id', Auth::id())
                        ->where('status', 'pending')
                        ->first();

        if (!$friendRequest) {
            return redirect()->back()->with('error', 'Solicitud de amistad no encontrada.');
        }

        // Actualizar estado
        DB::table('friends')
            ->where('id', $id)
            ->update([
                'status' => 'rejected',
                'updated_at' => now()
            ]);

        return redirect()->back()->with('success', 'Solicitud de amistad rechazada.');
    }

    public function removeFriend($id)
    {
        // Eliminar relación en ambas direcciones
        DB::table('friends')
            ->where(function($query) use ($id) {
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
