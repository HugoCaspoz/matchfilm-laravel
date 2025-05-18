<?php

namespace App\Http\Controllers;

use App\Models\FilmMatch;
use App\Models\MovieLike;
use App\Models\Notification;
use App\Models\User;
use App\Services\TmdbService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MovieController extends Controller
{
    protected $tmdbService;

    public function __construct(TmdbService $tmdbService)
    {
        $this->tmdbService = $tmdbService;
        $this->middleware('auth')->except(['index', 'show', 'search', 'byGenre']);
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
        try {
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
        } catch (\Exception $e) {
            Log::error('Error en like: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el like: ' . $e->getMessage()
            ], 500);
        }
    }

    public function dislike(Request $request, $id)
    {
        try {
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
        } catch (\Exception $e) {
            Log::error('Error en dislike: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el dislike: ' . $e->getMessage()
            ], 500);
        }
    }

    protected function checkForMatch($userId, $tmdbId)
    {
        try {
            // Obtener amigos del usuario
            $user = Auth::user();

            // Como no tenemos una relación de amigos directa, usamos la tabla friends
            // Esto debe adaptarse según cómo manejes las amistades en tu aplicación
            $friends = DB::table('friends')
                        ->where('user_id', $userId)
                        ->where('status', 'accepted')
                        ->pluck('friend_id')
                        ->toArray();

            // Buscar si algún amigo ha dado like a la misma película
            $friendLikes = MovieLike::where('tmdb_id', $tmdbId)
                                    ->where('liked', true)
                                    ->whereIn('user_id', $friends)
                                    ->get();

            if ($friendLikes->isNotEmpty()) {
                // Hay match con al menos un amigo
                $friendLike = $friendLikes->first();
                $friendId = $friendLike->user_id;

                // Obtener detalles de la película
                $movie = $this->tmdbService->getMovie($tmdbId);

                // Crear registro de match
                $match = FilmMatch::create([
                    'user_id_1' => $userId,
                    'friend_id' => $friendId,
                    'tmdb_id' => $tmdbId,
                    'movie_title' => $movie['title'] ?? 'Película sin título',
                    'movie_poster' => $movie['poster_path'] ? 'https://image.tmdb.org/t/p/w500' . $movie['poster_path'] : null,
                    'matched_at' => now(),
                    'status' => 'pending'
                ]);

                // Crear match recíproco
                FilmMatch::create([
                    'user_id_1' => $friendId,
                    'friend_id' => $userId,
                    'tmdb_id' => $tmdbId,
                    'movie_title' => $movie['title'] ?? 'Película sin título',
                    'movie_poster' => $movie['poster_path'] ? 'https://image.tmdb.org/t/p/w500' . $movie['poster_path'] : null,
                    'matched_at' => now(),
                    'status' => 'pending'
                ]);

                // Crear notificación para el amigo
                Notification::create([
                    'user_id' => $friendId,
                    'from_user_id' => $userId,
                    'type' => 'match',
                    'message' => 'Tienes un nuevo match para ver ' . ($movie['title'] ?? 'una película'),
                    'read' => false,
                    'data' => json_encode([
                        'tmdb_id' => $tmdbId,
                        'movie_title' => $movie['title'] ?? 'Película sin título',
                        'movie_poster' => $movie['poster_path'] ? 'https://image.tmdb.org/t/p/w500' . $movie['poster_path'] : null,
                    ]),
                ]);

                // Obtener el usuario amigo para devolverlo en la respuesta
                $friendUser = User::find($friendId);

                return [
                    'user' => $friendUser,
                    'movie' => $movie
                ];
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Error en checkForMatch: ' . $e->getMessage());
            return null;
        }
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
