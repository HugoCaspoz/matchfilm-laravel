<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use App\Models\TmdbFilm;
use App\Models\Watchlist;
use App\Services\TmdbService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MovieController extends Controller
{
    protected $tmdbService;

    public function __construct(TmdbService $tmdbService)
    {
        $this->tmdbService = $tmdbService;
        $this->middleware('auth')->except(['index', 'show']);
    }

    public function index(Request $request)
    {
        $movies = $this->tmdbService->getPopularMovies($request->page ?? 1);
        return view('movies.index', compact('movies'));
    }

    public function show($id)
    {
        $movie = $this->tmdbService->getMovie($id);
        
        $userRating = null;
        $inWatchlist = false;
        
        if (Auth::check()) {
            $userRating = Rating::where('user_id', Auth::id())
                                ->where('tmdb_id', $id)
                                ->first();
                                
            $inWatchlist = Watchlist::where('user_id', Auth::id())
                                    ->whereHas('films', function($query) use ($id) {
                                        $query->where('tmdb_id', $id);
                                    })
                                    ->exists();
        }
        
        return view('movies.show', compact('movie', 'userRating', 'inWatchlist'));
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        $results = [];
        
        if ($query) {
            $results = $this->tmdbService->searchMovies($query);
        }
        
        return view('movies.search', compact('results', 'query'));
    }

    public function rate(Request $request, $id)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        Rating::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'tmdb_id' => $id,
            ],
            [
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]
        );

        return redirect()->back()->with('success', 'Tu valoración ha sido guardada.');
    }

    public function addToWatchlist(Request $request, $id)
    {
        $request->validate([
            'watchlist_id' => 'required|exists:watchlists,id,user_id,' . Auth::id(),
        ]);

        $watchlist = Watchlist::findOrFail($request->watchlist_id);
        
        // Verificar si la película ya está en la lista
        if (!$watchlist->films()->where('tmdb_id', $id)->exists()) {
            $watchlist->films()->attach($id);
        }

        return redirect()->back()->with('success', 'Película añadida a tu lista.');
    }

    public function byGenre($genreId)
    {
        $movies = $this->tmdbService->getMoviesByGenre($genreId);
        $genres = $this->tmdbService->getGenres();
        $currentGenre = collect($genres)->firstWhere('id', $genreId);
        
        return view('movies.by_genre', compact('movies', 'currentGenre', 'genres'));
    }
}