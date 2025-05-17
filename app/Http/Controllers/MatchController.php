<?php

namespace App\Http\Controllers;

use App\Models\FilmMatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MatchController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // Usar el método allMatches del modelo User para obtener todos los matches
        $user = Auth::user();
        $matches = $user->allMatches()->with('friend')->orderBy('created_at', 'desc')->get();

        return view('matches.index', compact('matches'));
    }

    public function show($id)
    {
        // Verificar que el match pertenezca al usuario actual (como iniciador o receptor)
        $match = FilmMatch::where('id', $id)
                      ->where(function($query) {
                          $query->where('user_id_1', Auth::id())
                                ->orWhere('friend_id', Auth::id());
                      })
                      ->with('friend')
                      ->firstOrFail();

        return view('matches.show', compact('match'));
    }

    public function accept($id)
    {
        $match = FilmMatch::where('id', $id)
                      ->where('user_id_1', Auth::id())
                      ->firstOrFail();

        $match->status = 'accepted';
        $match->save();

        // También actualizar el match recíproco si existe
        FilmMatch::where('user_id_1', $match->friend_id)
                ->where('friend_id', Auth::id())
                ->where('tmdb_id', $match->tmdb_id)
                ->update(['status' => 'accepted']);

        return redirect()->back()->with('success', 'Match aceptado.');
    }

    public function reject($id)
    {
        $match = FilmMatch::where('id', $id)
                      ->where('user_id_1', Auth::id())
                      ->firstOrFail();

        $match->status = 'rejected';
        $match->save();

        // También actualizar el match recíproco si existe
        FilmMatch::where('user_id_1', $match->friend_id)
                ->where('friend_id', Auth::id())
                ->where('tmdb_id', $match->tmdb_id)
                ->update(['status' => 'rejected']);

        return redirect()->back()->with('success', 'Match rechazado.');
    }
}
