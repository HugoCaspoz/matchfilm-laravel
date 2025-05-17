<?php

namespace App\Http\Controllers;

use App\Models\Match;
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
        $matches = Match::where('user_id', Auth::id())
                        ->with('friend')
                        ->orderBy('created_at', 'desc')
                        ->get();

        return view('matches.index', compact('matches'));
    }

    public function show($id)
    {
        $match = Match::where('id', $id)
                      ->where('user_id', Auth::id())
                      ->with('friend')
                      ->firstOrFail();

        return view('matches.show', compact('match'));
    }
}
