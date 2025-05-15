<?php

namespace App\Http\Controllers;

use App\Models\Match;
use App\Models\User;
use App\Services\TmdbService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MatchController extends Controller
{
    protected $tmdbService;

    public function __construct(TmdbService $tmdbService)
    {
        $this->tmdbService = $tmdbService;
        $this->middleware('auth');
    }

    public function index()
    {
        // Obtener matches donde el usuario actual es user_id_1 o user_id_2
        $pendingMatches = FilmMatch::where(function($query) {
                $query->where('user_id_1', Auth::id())
                      ->orWhere('user_id_2', Auth::id());
            })
            ->where('status', 'pending')
            ->with(['userOne', 'userTwo'])
            ->get();
            
        $acceptedMatches = FilmMatch::where(function($query) {
                $query->where('user_id_1', Auth::id())
                      ->orWhere('user_id_2', Auth::id());
            })
            ->where('status', 'accepted')
            ->with(['userOne', 'userTwo'])
            ->get();
            
        // Obtener detalles de las películas
        $pendingMatches = $pendingMatches->map(function($match) {
            $match->movie = $this->tmdbService->getMovie($match->tmdb_id);
            return $match;
        });
        
        $acceptedMatches = $acceptedMatches->map(function($match) {
            $match->movie = $this->tmdbService->getMovie($match->tmdb_id);
            return $match;
        });
        
        // Encontrar posibles matches basados en valoraciones similares
        $potentialMatches = $this->findPotentialMatches();
        
        return view('matches.index', compact('pendingMatches', 'acceptedMatches', 'potentialMatches'));
    }
    
    public function accept($id)
    {
        $match = FilmMatch::findOrFail($id);
        
        // Verificar que el usuario actual es parte del match
        if ($match->user_id_1 != Auth::id() && $match->user_id_2 != Auth::id()) {
            return redirect()->route('matches.index')->with('error', 'No tienes permiso para aceptar este match.');
        }
        
        $match->status = 'accepted';
        $match->save();
        
        return redirect()->route('matches.index')->with('success', 'Match aceptado correctamente.');
    }
    
    public function reject($id)
    {
        $match = FilmMatch::findOrFail($id);
        
        // Verificar que el usuario actual es parte del match
        if ($match->user_id_1 != Auth::id() && $match->user_id_2 != Auth::id()) {
            return redirect()->route('matches.index')->with('error', 'No tienes permiso para rechazar este match.');
        }
        
        $match->status = 'rejected';
        $match->save();
        
        return redirect()->route('matches.index')->with('success', 'Match rechazado correctamente.');
    }
    
    public function create(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'tmdb_id' => 'required|integer',
        ]);
        
        // Verificar que no existe ya un match para estos usuarios y película
        $existingMatch = FilmMatch::where(function($query) use ($request) {
                $query->where(function($q) use ($request) {
                    $q->where('user_id_1', Auth::id())
                      ->where('user_id_2', $request->user_id);
                })->orWhere(function($q) use ($request) {
                    $q->where('user_id_1', $request->user_id)
                      ->where('user_id_2', Auth::id());
                });
            })
            ->where('tmdb_id', $request->tmdb_id)
            ->first();
            
        if ($existingMatch) {
            return redirect()->back()->with('error', 'Ya existe un match para esta película con este usuario.');
        }
        
        // Crear el nuevo match
        FilmMatch::create([
            'user_id_1' => Auth::id(),
            'user_id_2' => $request->user_id,
            'tmdb_id' => $request->tmdb_id,
            'status' => 'pending',
        ]);
        
        return redirect()->back()->with('success', 'Solicitud de match enviada correctamente.');
    }
    
    private function findPotentialMatches()
    {
        // Encontrar usuarios con valoraciones similares
        $currentUserRatings = DB::table('ratings')
            ->where('user_id', Auth::id())
            ->where('rating', '>=', 4) // Solo películas bien valoradas
            ->pluck('tmdb_id');
            
        if ($currentUserRatings->isEmpty()) {
            return collect();
        }
        
        $potentialMatches = DB::table('ratings as r1')
            ->join('ratings as r2', 'r1.tmdb_id', '=', 'r2.tmdb_id')
            ->join('users', 'r2.user_id', '=', 'users.id')
            ->where('r1.user_id', Auth::id())
            ->where('r2.user_id', '!=', Auth::id())
            ->where('r1.rating', '>=', 4)
            ->where('r2.rating', '>=', 4)
            ->select('r2.user_id', 'users.username', DB::raw('COUNT(*) as common_movies'))
            ->groupBy('r2.user_id', 'users.username')
            ->having('common_movies', '>=', 2) // Al menos 2 películas en común
            ->orderByDesc('common_movies')
            ->limit(5)
            ->get();
            
        // Obtener películas en común para cada usuario potencial
        $result = [];
        foreach ($potentialMatches as $match) {
            $commonMovies = DB::table('ratings as r1')
                ->join('ratings as r2', 'r1.tmdb_id', '=', 'r2.tmdb_id')
                ->where('r1.user_id', Auth::id())
                ->where('r2.user_id', $match->user_id)
                ->where('r1.rating', '>=', 4)
                ->where('r2.rating', '>=', 4)
                ->select('r1.tmdb_id')
                ->limit(3)
                ->pluck('tmdb_id');
                
            $movies = [];
            foreach ($commonMovies as $tmdbId) {
                $movies[] = $this->tmdbService->getMovie($tmdbId);
            }
            
            $result[] = [
                'user_id' => $match->user_id,
                'username' => $match->username,
                'common_count' => $match->common_movies,
                'common_movies' => $movies,
            ];
        }
        
        return collect($result);
    }
}