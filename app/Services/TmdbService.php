<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class TmdbService
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = env('TMDB_API_KEY', 'eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOiI1Y2EzMWVkZGFiNjE0OGVhNWM1ODY1YWQ5NWZmMWQ4MSIsInN1YiI6IjY1ZTRlNDcyMjBlNmE1MDE2MzUxZjQzOCIsInNjb3BlcyI6WyJhcGlfcmVhZCJdLCJ2ZXJzaW9uIjoxfQ.6IRKLCdBV7SK2KvzvVrlIPar4DjLApqE4RboCW99658');
        $this->baseUrl = 'https://api.themoviedb.org/3';
    }

    public function getPopularMovies($page = 1)
    {
        $cacheKey = 'popular_movies_page_' . $page;

        return Cache::remember($cacheKey, 60 * 60, function () use ($page) {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ])->get($this->baseUrl . '/movie/popular', [
                'language' => 'es-ES',
                'page' => $page,
            ]);

            return $response->json();
        });
    }

    public function getMovie($id)
    {
        $cacheKey = 'movie_' . $id;

        return Cache::remember($cacheKey, 60 * 60 * 24, function () use ($id) {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ])->get($this->baseUrl . '/movie/' . $id, [
                'language' => 'es-ES',
            ]);

            return $response->json();
        });
    }

    public function searchMovies($query)
    {
        $cacheKey = 'search_movies_' . md5($query);

        return Cache::remember($cacheKey, 60 * 30, function () use ($query) {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ])->get($this->baseUrl . '/search/movie', [
                'query' => $query,
                'language' => 'es-ES',
                'include_adult' => false,
                'page' => 1,
            ]);

            return $response->json();
        });
    }

    public function getMoviesByGenre($genreId, $page = 1)
    {
        $cacheKey = 'movies_genre_' . $genreId . '_page_' . $page;

        return Cache::remember($cacheKey, 60 * 60, function () use ($genreId, $page) {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ])->get($this->baseUrl . '/discover/movie', [
                'with_genres' => $genreId,
                'language' => 'es-ES',
                'page' => $page,
            ]);

            return $response->json();
        });
    }

    public function getGenres()
    {
        $cacheKey = 'movie_genres';

        return Cache::remember($cacheKey, 60 * 60 * 24 * 7, function () {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ])->get($this->baseUrl . '/genre/movie/list', [
                'language' => 'es-ES',
            ]);

            $data = $response->json();
            return $data['genres'] ?? [];
        });
    }
}
