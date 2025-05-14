<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class TmdbService
{
    protected string $baseUrl = 'https://api.themoviedb.org/3';
    protected string $apiKey;
    protected string $imageBaseUrl = 'https://image.tmdb.org/t/p/';

    public function __construct()
    {
        $this->apiKey = config('services.tmdb.api_key');
    }

    public function getMovie(int $movieId)
    {
        return Cache::remember("tmdb_movie_{$movieId}", now()->addDays(1), function () use ($movieId) {
            $response = Http::get("{$this->baseUrl}/movie/{$movieId}", [
                'api_key' => $this->apiKey,
                'append_to_response' => 'credits,videos,images',
                'language' => 'es-ES', // Puedes cambiar el idioma
            ]);
            
            if ($response->successful()) {
                return $response->json();
            }
            
            return null;
        });
    }

    public function searchMovies(string $query, int $page = 1)
    {
        $cacheKey = "tmdb_search_" . md5($query . '_' . $page);
        
        return Cache::remember($cacheKey, now()->addHours(24), function () use ($query, $page) {
            $response = Http::get("{$this->baseUrl}/search/movie", [
                'api_key' => $this->apiKey,
                'query' => $query,
                'page' => $page,
                'language' => 'es-ES',
            ]);
            
            if ($response->successful()) {
                return $response->json();
            }
            
            return ['results' => []];
        });
    }

    public function getPopularMovies(int $page = 1)
    {
        return Cache::remember("tmdb_popular_page_{$page}", now()->addHours(6), function () use ($page) {
            $response = Http::get("{$this->baseUrl}/movie/popular", [
                'api_key' => $this->apiKey,
                'page' => $page,
                'language' => 'es-ES',
            ]);
            
            if ($response->successful()) {
                return $response->json();
            }
            
            return ['results' => []];
        });
    }

    public function getRecommendations(int $movieId, int $page = 1)
    {
        return Cache::remember("tmdb_recommendations_{$movieId}_page_{$page}", now()->addDays(1), function () use ($movieId, $page) {
            $response = Http::get("{$this->baseUrl}/movie/{$movieId}/recommendations", [
                'api_key' => $this->apiKey,
                'page' => $page,
                'language' => 'es-ES',
            ]);
            
            if ($response->successful()) {
                return $response->json();
            }
            
            return ['results' => []];
        });
    }

    public function getDirector(array $credits)
    {
        if (!isset($credits['crew'])) {
            return null;
        }
        
        foreach ($credits['crew'] as $crewMember) {
            if ($crewMember['job'] === 'Director') {
                return $crewMember['name'];
            }
        }
        
        return null;
    }

    public function getPosterUrl(?string $path, string $size = 'w500')
    {
        if (!$path) {
            return null;
        }
        
        return $this->imageBaseUrl . $size . $path;
    }

    public function getBackdropUrl(?string $path, string $size = 'original')
    {
        if (!$path) {
            return null;
        }
        
        return $this->imageBaseUrl . $size . $path;
    }
}