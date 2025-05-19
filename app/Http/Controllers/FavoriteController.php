<?php

namespace App\Http\Controllers;

use App\Models\MovieLike;
use App\Services\TmdbService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    protected $tmdbService;

    public function __construct(TmdbService $tmdbService)
    {
        $this->middleware('auth');
        $this->tmdbService = $tmdbService;
    }

    /**
     * Mostrar la lista de películas favoritas del usuario.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Obtener los IDs de las películas que le gustan al usuario
        $likedMovies = MovieLike::where('user_id', $user->id)
                          ->where('liked', true)
                          ->orderBy('created_at', 'desc')
                          ->get();
    
        // Obtener los IDs de las películas que no le gustan al usuario
        $dislikedMovies = MovieLike::where('user_id', $user->id)
                             ->where('liked', false)
                             ->orderBy('created_at', 'desc')
                             ->get();
    
        // Obtener los detalles de cada película desde TMDB
        $likedMoviesDetails = [];
        foreach ($likedMovies as $like) {
            $movieDetails = $this->tmdbService->getMovie($like->tmdb_id);
            if ($movieDetails) {
                // Añadir la fecha de like a los detalles de la película
                $movieDetails['liked_at'] = $like->created_at;
                $movieDetails['user_liked'] = true;
                $likedMoviesDetails[] = $movieDetails;
            }
        }
    
        $dislikedMoviesDetails = [];
        foreach ($dislikedMovies as $dislike) {
            $movieDetails = $this->tmdbService->getMovie($dislike->tmdb_id);
            if ($movieDetails) {
                // Añadir la fecha de dislike a los detalles de la película
                $movieDetails['liked_at'] = $dislike->created_at;
                $movieDetails['user_liked'] = false;
                $dislikedMoviesDetails[] = $movieDetails;
            }
        }
    
        return view('favorites.index', [
            'likedMovies' => $likedMoviesDetails,
            'dislikedMovies' => $dislikedMoviesDetails
        ]);
    }

    /**
     * Buscar películas para marcar como favoritas.
     */
    public function search(Request $request)
    {
        $query = $request->input('query');
        $results = [];
        
        if ($query) {
            $searchData = $this->tmdbService->searchMovies($query);
            $results = $searchData['results'] ?? [];
            
            // Si el usuario está autenticado, marcar las películas que ya le gustan
            if (Auth::check()) {
                $userId = Auth::id();
                
                // Obtener IDs de películas que el usuario ya ha valorado
                $likedMovieIds = MovieLike::where('user_id', $userId)
                    ->where('liked', true)
                    ->pluck('tmdb_id')
                    ->toArray();
                
                // Marcar las películas que ya le gustan al usuario
                foreach ($results as &$movie) {
                    $movie['user_liked'] = in_array($movie['id'], $likedMovieIds);
                }
            }
        }
        
        return view('favorites.search', compact('results', 'query'));
    }

    /**
     * Marcar o desmarcar una película como favorita.
     */
    public function toggleFavorite(Request $request, $id)
    {
        try {
            $user = Auth::user();
            $action = $request->input('action', 'like'); // 'like' o 'unlike'
            
            if ($action === 'like') {
                // Marcar como favorita
                MovieLike::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'tmdb_id' => $id,
                    ],
                    [
                        'liked' => true,
                    ]
                );
                
                $message = 'Película añadida a favoritos';
            } else {
                // Quitar de favoritos
                MovieLike::where('user_id', $user->id)
                        ->where('tmdb_id', $id)
                        ->delete();
                
                $message = 'Película eliminada de favoritos';
            }
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message
                ]);
            }
            
            return redirect()->back()->with('success', $message);
            
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al procesar la solicitud: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Error al procesar la solicitud: ' . $e->getMessage());
        }
    }
}
