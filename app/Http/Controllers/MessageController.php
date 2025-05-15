<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // Obtener conversaciones únicas del usuario actual
        $conversations = Message::where('sender_id', Auth::id())
            ->orWhere('receiver_id', Auth::id())
            ->orderBy('sent_at', 'desc')
            ->get()
            ->map(function ($message) {
                // Determinar el otro usuario en la conversación
                $otherUserId = $message->sender_id == Auth::id() 
                    ? $message->receiver_id 
                    : $message->sender_id;
                
                return [
                    'user_id' => $otherUserId,
                    'last_message' => $message,
                ];
            })
            ->unique('user_id')
            ->values();
        
        // Obtener información de los usuarios
        $userIds = $conversations->pluck('user_id');
        $users = User::whereIn('id', $userIds)->get()->keyBy('id');
        
        // Contar mensajes no leídos
        $unreadCounts = Message::where('receiver_id', Auth::id())
            ->where('is_read', false)
            ->selectRaw('sender_id, COUNT(*) as count')
            ->groupBy('sender_id')
            ->pluck('count', 'sender_id');
        
        return view('messages.index', compact('conversations', 'users', 'unreadCounts'));
    }

    public function show(User $user)
    {
        // Obtener mensajes entre el usuario actual y el usuario seleccionado
        $messages = Message::where(function ($query) use ($user) {
                $query->where('sender_id', Auth::id())
                    ->where('receiver_id', $user->id);
            })
            ->orWhere(function ($query) use ($user) {
                $query->where('sender_id', $user->id)
                    ->where('receiver_id', Auth::id());
            })
            ->orderBy('sent_at', 'asc')
            ->get();
        
        // Marcar mensajes como leídos
        Message::where('sender_id', $user->id)
            ->where('receiver_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);
        
        return view('messages.show', compact('user', 'messages'));
    }

    public function store(Request $request, User $user)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);
        
        // Crear nuevo mensaje
        Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $user->id,
            'message' => $request->message,
            'is_read' => false,
        ]);
        
        return redirect()->route('messages.show', $user)->with('success', 'Mensaje enviado correctamente.');
    }
}