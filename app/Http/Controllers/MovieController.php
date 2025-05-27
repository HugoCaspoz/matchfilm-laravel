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
        try {
            $page = $request->get('page', 1);
            
            Log::info('Cargando películas para usuario', [
                'user_id' => Auth::id(),
                'page' => $page
            ]);

            // Si el usuario está autenticado, usar estrategia inteligente
            if (Auth::check()) {
                $movies = $this->getMoviesWithIntelligentStrategy($page);
            } else {
                // Si no está autenticado, obtener películas normalmente
                $moviesData = $this->tmdbService->getPopularMovies($page);
                $movies = $moviesData['results'] ?? [];
            }

            Log::info('Películas finales obtenidas', [
                'count' => count($movies),
                'page' => $page
            ]);

            return view('movies.index', compact('movies'));

        } catch (\Exception $e) {
            Log::error('Error al cargar películas: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);

            // En caso de error, mostrar datos de fallback
            $movies = $this->getFallbackMovies();
            return view('movies.index', compact('movies'));
        }
    }

    private function getMoviesWithIntelligentStrategy($startPage)
    {
        $userId = Auth::id();
        $targetMovieCount = 20; // Objetivo: tener al menos 20 películas
        $maxPagesToFetch = 10; // Máximo 10 páginas para evitar demasiadas consultas
        
        // Obtener IDs de películas que el usuario ya ha valorado
        $ratedMovieIds = MovieLike::where('user_id', $userId)
            ->pluck('tmdb_id')
            ->toArray();

        Log::info('Películas ya valoradas por el usuario', [
            'user_id' => $userId,
            'rated_count' => count($ratedMovieIds)
        ]);

        $allMovies = [];
        $currentPage = $startPage;
        $pagesFetched = 0;

        // Obtener películas de múltiples páginas hasta tener suficientes
        while (count($allMovies) < $targetMovieCount && $pagesFetched < $maxPagesToFetch) {
            try {
                Log::info("Obteniendo página {$currentPage} de películas populares");
                
                $moviesData = $this->tmdbService->getPopularMovies($currentPage);
                
                if (!$moviesData || !isset($moviesData['results'])) {
                    Log::warning("No se obtuvieron datos válidos de la página {$currentPage}");
                    break;
                }

                $pageMovies = $moviesData['results'];
                
                // Filtrar películas que el usuario ya ha valorado
                $filteredMovies = array_filter($pageMovies, function($movie) use ($ratedMovieIds) {
                    return !in_array($movie['id'], $ratedMovieIds);
                });

                Log::info("Página {$currentPage} procesada", [
                    'total_movies' => count($pageMovies),
                    'filtered_movies' => count($filteredMovies),
                    'accumulated_movies' => count($allMovies)
                ]);

                // Agregar las películas filtradas al array principal
                $allMovies = array_merge($allMovies, $filteredMovies);

                // Incrementar contadores
                $currentPage++;
                $pagesFetched++;

                // Si la página no tenía resultados, salir del bucle
                if (empty($pageMovies)) {
                    Log::info("Página {$currentPage} sin resultados, terminando búsqueda");
                    break;
                }

            } catch (\Exception $e) {
                Log::error("Error al obtener página {$currentPage}: " . $e->getMessage());
                break;
            }
        }

        // Eliminar duplicados basándose en el ID (por si acaso)
        $uniqueMovies = [];
        $seenIds = [];
        
        foreach ($allMovies as $movie) {
            if (!in_array($movie['id'], $seenIds)) {
                $uniqueMovies[] = $movie;
                $seenIds[] = $movie['id'];
            }
        }

        // Reindexar el array para evitar problemas con índices no secuenciales
        $finalMovies = array_values($uniqueMovies);

        Log::info('Estrategia inteligente completada', [
            'pages_fetched' => $pagesFetched,
            'total_movies_found' => count($finalMovies),
            'target_was' => $targetMovieCount
        ]);

        return $finalMovies;
    }

    private function getFallbackMovies()
    {
        return [
            [
                'id' => 1,
                'title' => 'Película de Prueba 1',
                'overview' => 'Esta es una película de prueba para verificar que la interfaz funciona correctamente cuando hay problemas con la API.',
                'poster_path' => null,
                'vote_average' => 7.5,
                'release_date' => '2024-01-01'
            ],
            [
                'id' => 2,
                'title' => 'Película de Prueba 2',
                'overview' => 'Segunda película de prueba con una descripción más larga para verificar cómo se muestra el contenido en la interfaz de usuario.',
                'poster_path' => null,
                'vote_average' => 6.8,
                'release_date' => '2024-02-01'
            ],
            [
                'id' => 3,
                'title' => 'Película de Prueba 3',
                'overview' => 'Tercera película de prueba para asegurar que tenemos suficiente contenido para mostrar.',
                'poster_path' => null,
                'vote_average' => 8.2,
                'release_date' => '2024-03-01'
            ]
        ];
    }

    public function show($id)
    {
        try {
            $movie = $this->tmdbService->getMovie($id);

            $userRating = null;
            if (Auth::check()) {
                $userRating = MovieLike::where('user_id', Auth::id())
                                    ->where('tmdb_id', $id)
                                    ->first();
            }

            return view('movies.show', compact('movie', 'userRating'));
        } catch (\Exception $e) {
            Log::error('Error al cargar película específica: ' . $e->getMessage());
            return redirect()->route('movies.index')->with('error', 'No se pudo cargar la película.');
        }
    }

    public function search(Request $request)
    {
        try {
            $query = $request->input('query');
            $results = [];

            if ($query) {
                $searchData = $this->tmdbService->searchMovies($query);
                $results = $searchData['results'] ?? [];

                // Si el usuario está autenticado, filtrar películas que ya han recibido like/dislike
                if (Auth::check()) {
                    $userId = Auth::id();

                    // Obtener IDs de películas que el usuario ya ha valorado
                    $ratedMovieIds = MovieLike::where('user_id', $userId)
                        ->pluck('tmdb_id')
                        ->toArray();

                    // Filtrar las películas para excluir las que ya han sido valoradas
                    $results = array_filter($results, function($movie) use ($ratedMovieIds) {
                        return !in_array($movie['id'], $ratedMovieIds);
                    });

                    // Reindexar el array
                    $results = array_values($results);
                }
            }

            return view('movies.search', compact('results', 'query'));
        } catch (\Exception $e) {
            Log::error('Error en búsqueda de películas: ' . $e->getMessage());
            return view('movies.search', ['results' => [], 'query' => $query]);
        }
    }

    public function like(Request $request, $id)
    {
        try {
            $user = Auth::user();

            Log::info('Usuario dando like', [
                'user_id' => $user->id,
                'movie_id' => $id
            ]);

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

            Log::info('Usuario dando dislike', [
                'user_id' => $user->id,
                'movie_id' => $id
            ]);

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

            $friends = DB::table('friends')
                        ->where(function($query) use ($user) {
                            $query->where('user_id', $user->id)
                                  ->orWhere('friend_id', $user->id);
                        })
                        ->where('status', 'accepted')
                        ->get()
                        ->map(function($friend) use ($user) {
                            return $friend->user_id == $user->id ? $friend->friend_id : $friend->user_id;
                        })
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

                // Crear notificación para el amigo - usar array directamente
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
        try {
            $moviesData = $this->tmdbService->getMoviesByGenre($genreId);
            $movies = $moviesData['results'] ?? [];
            $genres = $this->tmdbService->getGenres();

            // Si el usuario está autenticado, filtrar películas que ya han recibido like/dislike
            if (Auth::check()) {
                $userId = Auth::id();

                // Obtener IDs de películas que el usuario ya ha valorado
                $ratedMovieIds = MovieLike::where('user_id', $userId)
                    ->pluck('tmdb_id')
                    ->toArray();

                // Filtrar las películas para excluir las que ya han sido valoradas
                $movies = array_filter($movies, function($movie) use ($ratedMovieIds) {
                    return !in_array($movie['id'], $ratedMovieIds);
                });

                // Reindexar el array
                $movies = array_values($movies);
            }

            // Buscar el género actual en la lista de géneros
            $currentGenre = null;
            foreach ($genres as $genre) {
                if ($genre['id'] == $genreId) {
                    $currentGenre = $genre;
                    break;
                }
            }

            return view('movies.by_genre', compact('movies', 'currentGenre', 'genres'));
        } catch (\Exception $e) {
            Log::error('Error al cargar películas por género: ' . $e->getMessage());
            return redirect()->route('movies.index')->with('error', 'No se pudieron cargar las películas del género.');
        }
    }
}
