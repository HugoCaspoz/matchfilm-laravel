<?php

namespace App\Http\Controllers;

use App\Models\Match;
use App\Models\MovieLike;
use App\Models\Notification;
use App\Services\TmdbService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MovieController extends Controller
{
    protected $tmdbService;

    public function __construct(TmdbService $tmdbService)
    {
        $this->tmdbService = $tmdbService;
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $moviesData = $this->tmdbService->getPopularMovies($request->page ?? 1);

        // Asegurarse de que estamos pasando 'results' a la vista
        $movies = $moviesData['results'] ?? [];

        return view('movies.index', compact('movies'));
    }

    public function show($id)
    {
        $movie = $this->tmdbService->getMovie($id);

        $userRating = null;
        $inWatchlist = false;

        if (Auth::check()) {
            $userRating = MovieLike::where('user_id', Auth::id())
                                ->where('tmdb_id', $id)
                                ->first();
        }

        return view('movies.show', compact('movie', 'userRating'));
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        $results = [];

        if ($query) {
            $searchData = $this->tmdbService->searchMovies($query);
            $results = $searchData['results'] ?? [];
        }

        return view('movies.search', compact('results', 'query'));
    }

    public function like(Request $request, $id)
    {
        $user = Auth::user();

        // Registrar el like
        $movieLike = MovieLike::updateOrCreate(
            [
                'user_id' => $user->id,
                'tmdb_id' => $id,
            ],
            [
                'liked' => true,
            ]
        );

        // Verificar si hay match con algún amigo
        $match = $this->checkForMatch($user->id, $id);

        return response()->json([
            'success' => true,
            'match' => $match
        ]);
    }

    public function dislike(Request $request, $id)
    {
        $user = Auth::user();

        // Registrar el dislike
        MovieLike::updateOrCreate(
            [
                'user_id' => $user->id,
                'tmdb_id' => $id,
            ],
            [
                'liked' => false,
            ]
        );

        return response()->json([
            'success' => true
        ]);
    }

    protected function checkForMatch($userId, $tmdbId)
    {
        // Obtener amigos del usuario
        $user = Auth::user();
        $friends = $user->friends()->pluck('users.id')->toArray();

        // Buscar si algún amigo ha dado like a la misma película
        $friendLikes = MovieLike::where('tmdb_id', $tmdbId)
                                ->where('liked', true)
                                ->whereIn('user_id', $friends)
                                ->with('user')
                                ->get();

        if ($friendLikes->isNotEmpty()) {
            // Hay match con al menos un amigo
            $friendLike = $friendLikes->first();
            $friendId = $friendLike->user_id;

            // Obtener detalles de la película
            $movie = $this->tmdbService->getMovie($tmdbId);

            // Crear registro de match
            $match = Match::create([
                'user_id' => $userId,
                'friend_id' => $friendId,
                'tmdb_id' => $tmdbId,
                'movie_title' => $movie['title'] ?? 'Película sin título',
                'movie_poster' => $movie['poster_path'] ? 'https://image.tmdb.org/t/p/w500' . $movie['poster_path'] : null,
            ]);

            // Crear match recíproco
            Match::create([
                'user_id' => $friendId,
                'friend_id' => $userId,
                'tmdb_id' => $tmdbId,
                'movie_title' => $movie['title'] ?? 'Película sin título',
                'movie_poster' => $movie['poster_path'] ? 'https://image.tmdb.org/t/p/w500' . $movie['poster_path'] : null,
            ]);

            // Crear notificación para el amigo
            Notification::create([
                'user_id' => $friendId,
                'from_user_id' => $userId,
                'type' => 'match',
                'message' => 'Tienes un nuevo match para ver ' . ($movie['title'] ?? 'una película'),
                'read' => false,
                'data' => [
                    'tmdb_id' => $tmdbId,
                    'movie_title' => $movie['title'] ?? 'Película sin título',
                    'movie_poster' => $movie['poster_path'] ? 'https://image.tmdb.org/t/p/w500' . $movie['poster_path'] : null,
                ],
            ]);

            return [
                'user' => $friendLike->user,
                'movie' => $movie
            ];
        }

        return null;
    }

    public function byGenre($genreId)
    {
        $moviesData = $this->tmdbService->getMoviesByGenre($genreId);
        $movies = $moviesData['results'] ?? [];
        $genres = $this->tmdbService->getGenres();

        // Buscar el género actual en la lista de géneros
        $currentGenre = null;
        foreach ($genres as $genre) {
            if ($genre['id'] == $genreId) {
                $currentGenre = $genre;
                break;
            }
        }

        return view('movies.by_genre', compact('movies', 'currentGenre', 'genres'));
    }
}
