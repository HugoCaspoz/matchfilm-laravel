<?php

namespace App\Http\Controllers;

use App\Models\FilmMatch;
use App\Models\Friend;
use App\Models\MovieLike;
use App\Models\User;
use App\Services\TmdbService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MatchController extends Controller
{
    protected $tmdbService;

    public function __construct(TmdbService $tmdbService)
    {
        $this->middleware('auth');
        $this->tmdbService = $tmdbService;
    }

    public function index()
    {
        $user = Auth::user();
        
        // Obtener los amigos del usuario
        $friends = Friend::where(function($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere('friend_id', $user->id);
            })
            ->where('status', 'accepted')
            ->get()
            ->map(function($friendship) use ($user) {
                // Determinar cuál es el amigo (el otro usuario en la relación)
                $friendId = $friendship->user_id == $user->id ? $friendship->friend_id : $friendship->user_id;
                return User::find($friendId);
            });
        
        // Si no hay amigos, no puede haber matches
        if ($friends->isEmpty()) {
            return view('matches.index', [
                'friends' => $friends,
                'selectedFriend' => null,
                'matches' => []
            ]);
        }
        
        // Obtener el amigo seleccionado (por defecto el primero)
        $selectedFriendId = request('friend_id', $friends->first()->id);
        $selectedFriend = $friends->firstWhere('id', $selectedFriendId);
        
        // Obtener los matches (películas que ambos han dado like)
        $matches = $this->getMatchesWithFriend($user->id, $selectedFriendId);
        
        return view('matches.index', [
            'friends' => $friends,
            'selectedFriend' => $selectedFriend,
            'matches' => $matches
        ]);
    }
    
    /**
     * Obtener las películas que ambos usuarios han dado like
     */
    private function getMatchesWithFriend($userId, $friendId)
    {
        // Obtener IDs de películas que ambos usuarios han dado like
        $matchedMovieIds = DB::table('movie_likes as ml1')
            ->join('movie_likes as ml2', 'ml1.tmdb_id', '=', 'ml2.tmdb_id')
            ->where('ml1.user_id', $userId)
            ->where('ml2.user_id', $friendId)
            ->where('ml1.liked', true)
            ->where('ml2.liked', true)
            ->select('ml1.tmdb_id')
            ->pluck('tmdb_id')
            ->toArray();
        
        // Obtener detalles de cada película
        $matches = [];
        foreach ($matchedMovieIds as $movieId) {
            $movieDetails = $this->tmdbService->getMovie($movieId);
            if ($movieDetails) {
                // Verificar si ya existe un registro de match en la base de datos
                $existingMatch = FilmMatch::where(function($query) use ($userId, $friendId, $movieId) {
                    $query->where('user_id_1', $userId)
                        ->where('friend_id', $friendId)
                        ->where('tmdb_id', $movieId);
                })
                ->orWhere(function($query) use ($userId, $friendId, $movieId) {
                    $query->where('user_id_1', $friendId)
                        ->where('friend_id', $userId)
                        ->where('tmdb_id', $movieId);
                })
                ->first();
                
                // Si no existe, crear el registro de match
                if (!$existingMatch) {
                    FilmMatch::create([
                        'user_id_1' => $userId,
                        'friend_id' => $friendId,
                        'tmdb_id' => $movieId,
                        'movie_title' => $movieDetails['title'] ?? 'Película sin título',
                        'movie_poster' => $movieDetails['poster_path'] ? 'https://image.tmdb.org/t/p/w500' . $movieDetails['poster_path'] : null,
                        'status' => 'pending',
                        'matched_at' => now()
                    ]);
                }
                
                $matches[] = $movieDetails;
            }
        }
        
        return $matches;
    }
}
