<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\TmdbService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    protected $tmdbService;

    public function __construct(TmdbService $tmdbService)
    {
        $this->middleware('auth');
        $this->tmdbService = $tmdbService;
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
            
            // Verificar que el friend_id no sea el mismo que el usuario actual
            if ($validated['friend_id'] == $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No puedes enviarte una invitación a ti mismo'
                ], 400);
            }

            // Obtener información de la película una sola vez
            $moviePoster = null;
            try {
                $movieData = $this->tmdbService->getMovie($validated['movie_id']);
                if (isset($movieData['poster_path']) && $movieData['poster_path']) {
                    $moviePoster = 'https://image.tmdb.org/t/p/w500' . $movieData['poster_path'];
                }
            } catch (\Exception $e) {
                Log::warning('Error al obtener datos de la película: ' . $e->getMessage());
                // Continuar sin el poster si hay error
            }
            
            // Crear la notificación para el amigo
            $notification = new Notification();
            $notification->user_id = $validated['friend_id'];
            $notification->from_user_id = $user->id;
            $notification->type = 'movie_invitation';
            $notification->message = $user->name . ' te ha invitado a ver "' . $validated['movie_title'] . '"';
            $notification->read = false;
            $notification->data = json_encode([
                'movie_id' => $validated['movie_id'],
                'movie_title' => $validated['movie_title'],
                'watch_date' => $validated['watch_date'],
                'message' => $validated['message'] ?? '',
                'movie_poster' => $moviePoster,
            ]);
            $notification->save();

            return response()->json([
                'success' => true,
                'message' => 'Invitación enviada correctamente a ' . \App\Models\User::find($validated['friend_id'])->name
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de validación incorrectos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al enviar invitación de película: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor. Por favor, inténtalo de nuevo.'
            ], 500);
        }
    }
}
