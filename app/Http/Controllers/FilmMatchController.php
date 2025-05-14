<?php

namespace App\Http\Controllers;

use App\Models\FilmMatch;
use App\Models\Movie;
use App\Services\MovieService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FilmMatchController extends Controller
{
    public function __construct(
        protected MovieService $movieService
    ) {}

    public function index()
    {
        $user = Auth::user();
        $matches = $user->filmMatches()
                        ->with('movie')
                        ->orderBy('match_score', 'desc')
                        ->paginate(12);
        
        return view('matches.index', compact('matches'));
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'movie_id' => 'required|integer',
            'liked' => 'boolean',
            'watched' => 'boolean',
        ]);
        
        $user = Auth::user();
        $movie = $this->movieService->getMovie($validated['movie_id']);
        
        if (!$movie) {
            return back()->with('error', 'Película no encontrada');
        }
        
        // Calcular match_score basado en preferencias del usuario
        $matchScore = $this->calculateMatchScore($user, $movie);
        
        // Crear o actualizar el match
        FilmMatch::updateOrCreate(
            ['user_id' => $user->id, 'movie_id' => $movie->id],
            [
                'match_score' => $matchScore,
                'liked' => $validated['liked'] ?? null,
                'watched' => $validated['watched'] ?? false,
            ]
        );
        
        return back()->with('success', 'Preferencia guardada correctamente');
    }
    
    private function calculateMatchScore($user, $movie)
    {
        // Implementar algoritmo de cálculo de match
        // Basado en preferencias del usuario y características de la película
        $score = 50.0; // Valor por defecto
        
        // Obtener preferencias del usuario
        $preferences = $user->preferences;
        
        if ($preferences) {
            // Comparar géneros favoritos
            if ($preferences->favorite_genres && is_array($movie->genres)) {
                $matchingGenres = array_intersect($preferences->favorite_genres, $movie->genres);
                if (count($matchingGenres) > 0) {
                    $score += count($matchingGenres) * 10;
                }
            }
            
            // Comparar director
            if ($preferences->favorite_directors && $movie->director) {
                if (in_array($movie->director, $preferences->favorite_directors)) {
                    $score += 15;
                }
            }
            
            // Comparar año
            if ($preferences->min_year && $preferences->max_year && $movie->release_year) {
                if ($movie->release_year >= $preferences->min_year && $movie->release_year <= $preferences->max_year) {
                    $score += 10;
                }
            }
            
            // Comparar rating
            if ($preferences->min_rating && $movie->rating) {
                if ($movie->rating >= $preferences->min_rating) {
                    $score += 10;
                }
            }
        }
        
        // Limitar score a 100
        return min(100, $score);
    }
}