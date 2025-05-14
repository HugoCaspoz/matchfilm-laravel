<?php

namespace App\Services;

use App\Models\Movie;
use Illuminate\Support\Facades\Log;

class MovieService
{
    public function __construct(
        protected TmdbService $tmdbService
    ) {}

    public function getMovie($tmdbId)
    {
        // Primero busca en la base de datos local
        $movie = Movie::where('tmdb_id', $tmdbId)->first();
        
        // Si no existe o está desactualizado (más de 7 días)
        if (!$movie || $movie->last_synced_at->diffInDays(now()) > 7) {
            // Busca en la API de TMDB
            try {
                $tmdbMovie = $this->tmdbService->getMovie($tmdbId);
                
                if ($tmdbMovie) {
                    // Si no existe, crea un nuevo registro
                    if (!$movie) {
                        $movie = new Movie();
                        $movie->tmdb_id = $tmdbId;
                    }
                    
                    // Obtener géneros
                    $genres = [];
                    if (isset($tmdbMovie['genres'])) {
                        foreach ($tmdbMovie['genres'] as $genre) {
                            $genres[] = $genre['name'];
                        }
                    }
                    
                    // Actualiza los datos
                    $movie->fill([
                        'title' => $tmdbMovie['title'],
                        'description' => $tmdbMovie['overview'] ?? null,
                        'poster_url' => $this->tmdbService->getPosterUrl($tmdbMovie['poster_path'] ?? null),
                        'backdrop_url' => $this->tmdbService->getBackdropUrl($tmdbMovie['backdrop_path'] ?? null),
                        'release_year' => isset($tmdbMovie['release_date']) ? substr($tmdbMovie['release_date'], 0, 4) : null,
                        'rating' => $tmdbMovie['vote_average'] ?? null,
                        'genres' => $genres,
                        'director' => isset($tmdbMovie['credits']) ? $this->tmdbService->getDirector($tmdbMovie['credits']) : null,
                        'duration' => $tmdbMovie['runtime'] ?? null,
                        'last_synced_at' => now(),
                    ]);
                    
                    $movie->save();
                }
            } catch (\Exception $e) {
                Log::error('Error fetching movie from TMDB: ' . $e->getMessage());
                if (!$movie) return null;
            }
        }
        
        return $movie;
    }
    
    public function searchMovies($query, $page = 1)
    {
        $results = $this->tmdbService->searchMovies($query, $page);
        
        // Opcional: guardar películas encontradas en la base de datos
        if (isset($results['results']) && count($results['results']) > 0) {
            foreach ($results['results'] as $result) {
                // Guardar solo información básica
                $this->saveBasicMovieInfo($result);
            }
        }
        
        return $results;
    }
    
    public function getPopularMovies($page = 1)
    {
        $results = $this->tmdbService->getPopularMovies($page);
        
        // Opcional: guardar películas populares en la base de datos
        if (isset($results['results']) && count($results['results']) > 0) {
            foreach ($results['results'] as $result) {
                $this->saveBasicMovieInfo($result);
            }
        }
        
        return $results;
    }
    
    protected function saveBasicMovieInfo($movieData)
    {
        if (!isset($movieData['id'])) {
            return null;
        }
        
        $movie = Movie::firstOrNew(['tmdb_id' => $movieData['id']]);
        
        // Solo actualizar si es nuevo o está desactualizado
        if (!$movie->exists || $movie->shouldRefresh()) {
            $movie->fill([
                'title' => $movieData['title'],
                'description' => $movieData['overview'] ?? null,
                'poster_url' => $this->tmdbService->getPosterUrl($movieData['poster_path'] ?? null),
                'backdrop_url' => $this->tmdbService->getBackdropUrl($movieData['backdrop_path'] ?? null),
                'release_year' => isset($movieData['release_date']) ? substr($movieData['release_date'], 0, 4) : null,
                'rating' => $movieData['vote_average'] ?? null,
                'last_synced_at' => now(),
            ]);
            
            $movie->save();
        }
        
        return $movie;
    }
}