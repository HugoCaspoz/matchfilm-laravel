<?php

namespace App\Services;

use App\Models\TmdbFilm;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TmdbService
{
    protected $apiKey;
    protected $baseUrl = 'https://api.themoviedb.org/3';

    public function __construct()
    {
        $this->apiKey = config('services.tmdb.key', env('TMDB_API_KEY'));
    }

    public function getMovie($tmdbId)
    {
        // Primero intentamos obtener de la base de datos
        $cachedMovie = TmdbFilm::find($tmdbId);
        
        if ($cachedMovie && $cachedMovie->cache_expires_at > now()) {
            return $cachedMovie;
        }

        // Si no hay API key configurada, devolver datos de ejemplo
        if (empty($this->apiKey)) {
            return (object)[
                'tmdb_id' => $tmdbId,
                'title' => 'Película ' . $tmdbId,
                'poster_path' => null,
                'overview' => 'Descripción de ejemplo para la película ' . $tmdbId
            ];
        }

        // Si no está en caché o expiró, obtenemos de la API
        try {
            $response = Http::get("{$this->baseUrl}/movie/{$tmdbId}", [
                'api_key' => $this->apiKey,
                'language' => 'es-ES',
            ]);

            if ($response->successful()) {
                $movieData = $response->json();
                
                // Actualizamos o creamos en la base de datos
                TmdbFilm::updateOrCreate(
                    ['tmdb_id' => $tmdbId],
                    [
                        'title' => $movieData['title'],
                        'poster_path' => $movieData['poster_path'] ?? null,
                        'cache_expires_at' => now()->addDays(1), // Caché por 1 día
                    ]
                );

                return TmdbFilm::find($tmdbId);
            }
        } catch (\Exception $e) {
            Log::error('Error fetching movie from TMDB: ' . $e->getMessage());
        }

        return null;
    }

    public function getPopularMovies(int $page = 1)
    {
        // No usar caché para evitar el error
        try {
            $response = Http::get("{$this->baseUrl}/movie/popular", [
                'api_key' => $this->apiKey,
                'page' => $page,
                'language' => 'es-ES',
            ]);
            
            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::error('Error fetching popular movies from TMDB: ' . $e->getMessage());
        }
        
        // Datos de ejemplo en caso de error
        return [
            'results' => [
                [
                    'id' => 1,
                    'title' => 'Película de ejemplo 1',
                    'overview' => 'Esta es una película de ejemplo para cuando hay un error en la API.',
                    'poster_path' => null
                ],
                [
                    'id' => 2,
                    'title' => 'Película de ejemplo 2',
                    'overview' => 'Esta es otra película de ejemplo.',
                    'poster_path' => null
                ]
            ],
            'page' => $page,
            'total_pages' => 1
        ];
    }

    public function searchMovies(string $query, int $page = 1)
    {
        // No usar caché para evitar el error
        try {
            $response = Http::get("{$this->baseUrl}/search/movie", [
                'api_key' => $this->apiKey,
                'query' => $query,
                'page' => $page,
                'language' => 'es-ES',
            ]);
            
            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::error('Error searching movies from TMDB: ' . $e->getMessage());
        }
        
        return ['results' => []];
    }

    public function getGenres()
    {
        // No usar caché para evitar el error
        try {
            $response = Http::get("{$this->baseUrl}/genre/movie/list", [
                'api_key' => $this->apiKey,
                'language' => 'es-ES',
            ]);
            
            if ($response->successful()) {
                return $response->json()['genres'];
            }
        } catch (\Exception $e) {
            Log::error('Error fetching genres from TMDB: ' . $e->getMessage());
        }
        
        return [];
    }
}