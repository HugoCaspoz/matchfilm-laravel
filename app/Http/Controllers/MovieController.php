<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Services\MovieService;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    public function __construct(
        protected MovieService $movieService
    ) {}

    public function index(Request $request)
    {
        $query = $request->input('q');
        
        if ($query) {
            // Buscar películas
            $results = $this->movieService->searchMovies($query);
            $movies = $results['results'] ?? [];
            return view('movies.index', compact('movies', 'query'));
        } else {
            // Mostrar películas populares
            $results = $this->movieService->getPopularMovies();
            $movies = $results['results'] ?? [];
            return view('movies.index', compact('movies'));
        }
    }

    public function show($id)
    {
        $movie = $this->movieService->getMovie($id);
        
        if (!$movie) {
            abort(404);
        }
        
        return view('movies.show', compact('movie'));
    }
}