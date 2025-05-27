<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TmdbService
{
    protected $apiKey;
    protected $baseUrl = 'https://api.themoviedb.org/3';

    public function __construct()
    {
        // Intentar obtener la API key de diferentes fuentes
        $this->apiKey = config('services.tmdb.key') 
                     ?? env('TMDB_API_KEY') 
                     ?? 'eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOiI1Y2EzMWVkZGFiNjE0OGVhNWM1ODY1YWQ5NWZmMWQ4MSIsInN1YiI6IjY1ZTRlNDcyMjBlNmE1MDE2MzUxZjQzOCIsInNjb3BlcyI6WyJhcGlfcmVhZCJdLCJ2ZXJzaW9uIjoxfQ.6IRKLCdBV7SK2KvzvVrlIPar4DjLApqE4RboCW99658';
        
        if (!$this->apiKey) {
            Log::error('TMDB API Key no configurada');
        }
    }

    public function getPopularMovies($page = 1)
    {
        try {
            $cacheKey = 'popular_movies_page_' . $page;

            return Cache::remember($cacheKey, 60 * 60, function () use ($page) {
                Log::info('Obteniendo películas populares de TMDB', ['page' => $page]);

                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json',
                ])->timeout(10)->get($this->baseUrl . '/movie/popular', [
                    'language' => 'es-ES',
                    'page' => $page,
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    Log::info('Respuesta exitosa de TMDB popular movies', [
                        'total_results' => $data['total_results'] ?? 0,
                        'results_count' => count($data['results'] ?? [])
                    ]);
                    return $data;
                } else {
                    Log::error('Error en respuesta de TMDB popular movies', [
                        'status' => $response->status(),
                        'body' => $response->body()
                    ]);
                    return $this->getFallbackMoviesData($page);
                }
            });
        } catch (\Exception $e) {
            Log::error('Excepción al obtener películas populares: ' . $e->getMessage());
            return $this->getFallbackMoviesData($page);
        }
    }

    public function getMovie($id)
    {
        try {
            $cacheKey = 'movie_' . $id;

            return Cache::remember($cacheKey, 60 * 60 * 24, function () use ($id) {
                Log::info('Obteniendo película específica de TMDB', ['movie_id' => $id]);

                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json',
                ])->timeout(10)->get($this->baseUrl . '/movie/' . $id, [
                    'language' => 'es-ES',
                    'append_to_response' => 'credits',
                ]);

                if ($response->successful()) {
                    return $response->json();
                } else {
                    Log::error('Error al obtener película específica', [
                        'movie_id' => $id,
                        'status' => $response->status()
                    ]);
                    return null;
                }
            });
        } catch (\Exception $e) {
            Log::error('Excepción al obtener película específica: ' . $e->getMessage());
            return null;
        }
    }

    public function searchMovies($query)
    {
        try {
            $cacheKey = 'search_movies_' . md5($query);

            return Cache::remember($cacheKey, 60 * 30, function () use ($query) {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json',
                ])->timeout(10)->get($this->baseUrl . '/search/movie', [
                    'query' => $query,
                    'language' => 'es-ES',
                    'include_adult' => false,
                    'page' => 1,
                ]);

                if ($response->successful()) {
                    return $response->json();
                } else {
                    Log::error('Error en búsqueda de películas', [
                        'query' => $query,
                        'status' => $response->status()
                    ]);
                    return ['results' => []];
                }
            });
        } catch (\Exception $e) {
            Log::error('Excepción en búsqueda de películas: ' . $e->getMessage());
            return ['results' => []];
        }
    }

    public function getMoviesByGenre($genreId, $page = 1)
    {
        try {
            $cacheKey = 'movies_genre_' . $genreId . '_page_' . $page;

            return Cache::remember($cacheKey, 60 * 60, function () use ($genreId, $page) {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json',
                ])->timeout(10)->get($this->baseUrl . '/discover/movie', [
                    'with_genres' => $genreId,
                    'language' => 'es-ES',
                    'page' => $page,
                    'sort_by' => 'popularity.desc',
                    'include_adult' => false,
                ]);

                if ($response->successful()) {
                    return $response->json();
                } else {
                    Log::error('Error al obtener películas por género', [
                        'genre_id' => $genreId,
                        'status' => $response->status()
                    ]);
                    return $this->getFallbackMoviesData($page);
                }
            });
        } catch (\Exception $e) {
            Log::error('Excepción al obtener películas por género: ' . $e->getMessage());
            return $this->getFallbackMoviesData($page);
        }
    }

    public function getGenres()
    {
        try {
            $cacheKey = 'movie_genres';

            return Cache::remember($cacheKey, 60 * 60 * 24 * 7, function () {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json',
                ])->timeout(10)->get($this->baseUrl . '/genre/movie/list', [
                    'language' => 'es-ES',
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    return $data['genres'] ?? [];
                } else {
                    Log::error('Error al obtener géneros', [
                        'status' => $response->status()
                    ]);
                    return $this->getFallbackGenres();
                }
            });
        } catch (\Exception $e) {
            Log::error('Excepción al obtener géneros: ' . $e->getMessage());
            return $this->getFallbackGenres();
        }
    }

    private function getFallbackMoviesData($page = 1)
    {
        return [
            'results' => [
                [
                    'id' => 1,
                    'title' => 'Película de Ejemplo 1',
                    'overview' => 'Esta es una película de ejemplo que se muestra cuando hay problemas con la API de TMDB.',
                    'poster_path' => null,
                    'vote_average' => 7.5,
                    'release_date' => '2024-01-01'
                ],
                [
                    'id' => 2,
                    'title' => 'Película de Ejemplo 2',
                    'overview' => 'Segunda película de ejemplo con una descripción más detallada para probar la interfaz.',
                    'poster_path' => null,
                    'vote_average' => 6.8,
                    'release_date' => '2024-02-01'
                ],
                [
                    'id' => 3,
                    'title' => 'Película de Ejemplo 3',
                    'overview' => 'Tercera película de ejemplo para asegurar que hay suficiente contenido.',
                    'poster_path' => null,
                    'vote_average' => 8.2,
                    'release_date' => '2024-03-01'
                ]
            ],
            'page' => $page,
            'total_pages' => 1,
            'total_results' => 3
        ];
    }

    private function getFallbackGenres()
    {
        return [
            ['id' => 28, 'name' => 'Acción'],
            ['id' => 12, 'name' => 'Aventura'],
            ['id' => 16, 'name' => 'Animación'],
            ['id' => 35, 'name' => 'Comedia'],
            ['id' => 80, 'name' => 'Crimen'],
            ['id' => 18, 'name' => 'Drama'],
            ['id' => 14, 'name' => 'Fantasía'],
            ['id' => 27, 'name' => 'Terror'],
            ['id' => 10749, 'name' => 'Romance'],
            ['id' => 878, 'name' => 'Ciencia ficción']
        ];
    }

    public function getPosterUrl($posterPath, $size = 'w500')
    {
        if (empty($posterPath)) {
            return null;
        }
        return "https://image.tmdb.org/t/p/{$size}{$posterPath}";
    }

    public function getBackdropUrl($backdropPath, $size = 'w1280')
    {
        if (empty($backdropPath)) {
            return null;
        }
        return "https://image.tmdb.org/t/p/{$size}{$backdropPath}";
    }
}
