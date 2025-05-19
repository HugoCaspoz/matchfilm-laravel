<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $notifications = Notification::where('user_id', Auth::id())
                                    ->with('fromUser')
                                    ->orderBy('created_at', 'desc')
                                    ->paginate(20);
        
        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead($id)
    {
        $notification = Notification::where('id', $id)
                                   ->where('user_id', Auth::id())
                                   ->firstOrFail();
        
        $notification->read = true;
        $notification->save();
        
        return redirect()->back();
    }

    public function markAllAsRead()
    {
        Notification::where('user_id', Auth::id())
                   ->where('read', false)
                   ->update(['read' => true]);
        
        return redirect()->back();
    }

    public function getUnreadCount()
    {
        $count = Notification::where('user_id', Auth::id())
                            ->where('read', false)
                            ->count();
        
        return response()->json(['count' => $count]);
    }

    // Añadir el método para manejar invitaciones de películas
    public function sendMovieInvitation(Request $request)
    {
        try {
            $validated = $request->validate([
                'friend_id' => 'required|exists:users,id',
                'movie_id' => 'required',
                'movie_title' => 'required|string',
                'watch_date' => 'required|date',
                'message' => 'nullable|string',
            ]);

            $user = Auth::user();
            
            // Crear la notificación para el amigo
            $notification = new Notification();
            $notification->user_id = $validated['friend_id'];
            $notification->from_user_id = $user->id;
            $notification->type = 'movie_invitation';
            $notification->message = $user->name . ' te ha invitado a ver una película';
            $notification->read = false;
            $notification->data = [
                'movie_id' => $validated['movie_id'],
                'movie_title' => $validated['movie_title'],
                'watch_date' => $validated['watch_date'],
                'message' => $validated['message'] ?? '',
                // Obtener el poster de la película desde TMDB
                'movie_poster' => app(App\Services\TmdbService::class)->getMovie($validated['movie_id'])['poster_path'] 
                    ? 'https://image.tmdb.org/t/p/w500' . app(App\Services\TmdbService::class)->getMovie($validated['movie_id'])['poster_path'] 
                    : null,
            ];
            $notification->save();

            return response()->json([
                'success' => true,
                'message' => 'Invitación enviada correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar la invitación: ' . $e->getMessage()
            ], 500);
        }
    }
}
