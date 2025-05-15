<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TmdbService;

class HomeController extends Controller
{
    protected $tmdbService;

    public function __construct(TmdbService $tmdbService = null)
    {
        $this->tmdbService = $tmdbService ?? new TmdbService();
    }

    public function index()
    {
        // Obtener películas populares para mostrar en la página de inicio
        $popularMovies = $this->tmdbService->getPopularMovies();

        return view('welcome');
    }
}
