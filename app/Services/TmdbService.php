<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TmdbService
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = env('TMDB_API_KEY', 'eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOiI1Y2EzMWVkZGFiNjE0OGVhNWM1ODY1YWQ5NWZmMWQ4MSIsInN1YiI6IjY1ZTRlNDcyMjBlNmE1MDE2MzUxZjQzOCIsInNjb3BlcyI6WyJhcGlfcmVhZCJdLCJ2ZXJzaW9uIjoxfQ.6IRKLCdBV7SK2KvzvVrlIPar4DjLApqE4RboCW99658');
        $this->baseUrl = 'https://api.themoviedb.org/3';
        
        if (!$this->apiKey) {
            Log::error('TMDB API Key no configurada');
        }
    }

    public function getPopularMovies($page = 1)
    {
        try {
            $cacheKey = 'popular_movies_page_' . $page;

            return Cache::remember($cacheKey, 60 * 30, function () use ($page) { // Cache por 30 minutos
                Log::info("Obteniendo películas populares de TMDB", ['page' => $page]);

                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json',
                ])->timeout(15)->get($this->baseUrl . '/movie/popular', [
                    'language' => 'es-ES',
                    'page' => $page,
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    Log::info('Respuesta exitosa de TMDB popular movies', [
                        'page' => $page,
                        'total_results' => $data['total_results'] ?? 0,
                        'results_count' => count($data['results'] ?? [])
                    ]);
                    return $data;
                } else {
                    Log::error('Error en respuesta de TMDB popular movies', [
                        'page' => $page,
                        'status' => $response->status(),
                        'body' => $response->body()
                    ]);
                    return null;
                }
            });
        } catch (\Exception $e) {
            Log::error('Excepción al obtener películas populares: ' . $e->getMessage());
            return null;
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
                ]);

                if ($response->successful()) {
                    return $response->json();
                } else {
                    Log::error('Error al obtener películas por género', [
                        'genre_id' => $genreId,
                        'status' => $response->status()
                    ]);
                    return ['results' => []];
                }
            });
        } catch (\Exception $e) {
            Log::error('Excepción al obtener películas por género: ' . $e->getMessage());
            return ['results' => []];
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
                    return [];
                }
            });
        } catch (\Exception $e) {
            Log::error('Excepción al obtener géneros: ' . $e->getMessage());
            return [];
        }
    }
}
