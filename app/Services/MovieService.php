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
        $this->apiKey = config('services.tmdb.key', env('TMDB_API_KEY'));
    }

    public function getMovie($tmdbId)
    {
        try {
            $response = Http::get("{$this->baseUrl}/movie/{$tmdbId}", [
                'api_key' => $this->apiKey,
                'language' => 'es-ES',
                'append_to_response' => 'credits',
            ]);

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::error('Error fetching movie from TMDB: ' . $e->getMessage());
        }

        return null;
    }

    public function getPopularMovies(int $page = 1)
    {
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

    public function getMoviesByGenre($genreId, int $page = 1)
    {
        try {
            $response = Http::get("{$this->baseUrl}/discover/movie", [
                'api_key' => $this->apiKey,
                'with_genres' => $genreId,
                'page' => $page,
                'language' => 'es-ES',
                'sort_by' => 'popularity.desc',
                'include_adult' => false,
            ]);

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::error('Error fetching movies by genre from TMDB: ' . $e->getMessage());
        }

        return [
            'results' => [
                [
                    'id' => 1,
                    'title' => 'Película de ejemplo por género',
                    'overview' => 'Esta es una película de ejemplo para cuando hay un error en la API.',
                    'poster_path' => null
                ]
            ],
            'page' => $page,
            'total_pages' => 1
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

    public function getDirector($credits)
    {
        if (!isset($credits['crew']) || !is_array($credits['crew'])) {
            return null;
        }

        foreach ($credits['crew'] as $crewMember) {
            if (isset($crewMember['job']) && $crewMember['job'] === 'Director') {
                return $crewMember['name'] ?? null;
            }
        }

        return null;
    }
}
