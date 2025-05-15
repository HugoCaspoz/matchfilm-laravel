<?php

namespace App\Http\Controllers;

use App\Models\Watchlist;
use App\Services\TmdbService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WatchlistController extends Controller
{
    protected $tmdbService;

    public function __construct(TmdbService $tmdbService)
    {
        $this->tmdbService = $tmdbService;
        $this->middleware('auth');
    }

    public function index()
    {
        $watchlists = Watchlist::where('user_id', Auth::id())
            ->withCount('films')
            ->get();
            
        return view('watchlists.index', compact('watchlists'));
    }

    public function show(Watchlist $watchlist)
    {
        // Verificar que el usuario actual es el propietario o la lista es pública
        if ($watchlist->user_id != Auth::id() && !$watchlist->is_public) {
            return redirect()->route('watchlists.index')
                ->with('error', 'No tienes permiso para ver esta lista.');
        }
        
        // Obtener IDs de películas en la lista
        $filmIds = $watchlist->films()->pluck('tmdb_id');
        
        // Obtener detalles de cada película
        $films = [];
        foreach ($filmIds as $id) {
            $film = $this->tmdbService->getMovie($id);
            if ($film) {
                $films[] = $film;
            }
        }
        
        return view('watchlists.show', compact('watchlist', 'films'));
    }

    public function create()
    {
        return view('watchlists.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'is_public' => 'boolean',
        ]);
        
        Watchlist::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'is_public' => $request->has('is_public'),
        ]);
        
        return redirect()->route('watchlists.index')
            ->with('success', 'Lista creada correctamente.');
    }

    public function edit(Watchlist $watchlist)
    {
        // Verificar que el usuario actual es el propietario
        if ($watchlist->user_id != Auth::id()) {
            return redirect()->route('watchlists.index')
                ->with('error', 'No tienes permiso para editar esta lista.');
        }
        
        return view('watchlists.edit', compact('watchlist'));
    }

    public function update(Request $request, Watchlist $watchlist)
    {
        // Verificar que el usuario actual es el propietario
        if ($watchlist->user_id != Auth::id()) {
            return redirect()->route('watchlists.index')
                ->with('error', 'No tienes permiso para editar esta lista.');
        }
        
        $request->validate([
            'name' => 'required|string|max:100',
            'is_public' => 'boolean',
        ]);
        
        $watchlist->update([
            'name' => $request->name,
            'is_public' => $request->has('is_public'),
        ]);
        
        return redirect()->route('watchlists.show', $watchlist)
            ->with('success', 'Lista actualizada correctamente.');
    }

    public function destroy(Watchlist $watchlist)
    {
        // Verificar que el usuario actual es el propietario
        if ($watchlist->user_id != Auth::id()) {
            return redirect()->route('watchlists.index')
                ->with('error', 'No tienes permiso para eliminar esta lista.');
        }
        
        $watchlist->delete();
        
        return redirect()->route('watchlists.index')
            ->with('success', 'Lista eliminada correctamente.');
    }

    public function removeMovie(Watchlist $watchlist, $tmdbId)
    {
        // Verificar que el usuario actual es el propietario
        if ($watchlist->user_id != Auth::id()) {
            return redirect()->route('watchlists.index')
                ->with('error', 'No tienes permiso para modificar esta lista.');
        }
        
        $watchlist->films()->detach($tmdbId);
        
        return redirect()->route('watchlists.show', $watchlist)
            ->with('success', 'Película eliminada de la lista.');
    }
}