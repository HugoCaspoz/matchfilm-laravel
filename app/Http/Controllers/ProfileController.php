<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Rating;
use App\Models\UserGenrePreference;
use App\Services\TmdbService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    protected $tmdbService;

    public function __construct(TmdbService $tmdbService)
    {
        $this->tmdbService = $tmdbService;
        $this->middleware('auth');
    }

    public function show()
    {
        $user = Auth::user();
        
        // Obtener valoraciones del usuario
        $ratings = Rating::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
            
        // Obtener detalles de las películas valoradas
        $ratedMovies = [];
        foreach ($ratings as $rating) {
            $movie = $this->tmdbService->getMovie($rating->tmdb_id);
            if ($movie) {
                $ratedMovies[] = [
                    'movie' => $movie,
                    'rating' => $rating,
                ];
            }
        }
        
        // Obtener listas de películas públicas
        $watchlists = $user->watchlists()
            ->where('is_public', true)
            ->withCount('films')
            ->get();
            
        // Obtener preferencias de géneros
        $genrePreferences = UserGenrePreference::where('user_id', $user->id)
            ->orderByDesc('preference_level')
            ->get();
            
        // Obtener información de géneros
        $allGenres = collect($this->tmdbService->getGenres())->keyBy('id');
        
        $genres = $genrePreferences->map(function($preference) use ($allGenres) {
            return [
                'id' => $preference->tmdb_genre_id,
                'name' => $allGenres[$preference->tmdb_genre_id]['name'] ?? 'Desconocido',
                'level' => $preference->preference_level,
            ];
        });
        
        return view('profile.show', compact('user', 'ratedMovies', 'watchlists', 'genres'));
    }

    public function edit()
    {
        $user = Auth::user();
        $allGenres = $this->tmdbService->getGenres();
        
        // Obtener preferencias actuales
        $userGenrePreferences = UserGenrePreference::where('user_id', $user->id)
            ->pluck('preference_level', 'tmdb_genre_id')
            ->toArray();
            
        return view('profile.edit', compact('user', 'allGenres', 'userGenrePreferences'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'username' => ['required', 'string', 'max:50', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'string', 'email', 'max:100', Rule::unique('users')->ignore($user->id)],
            'bio' => ['nullable', 'string', 'max:1000'],
            'profile_image' => ['nullable', 'image', 'max:2048'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'genres' => ['nullable', 'array'],
            'genres.*' => ['nullable', 'integer', 'min:1', 'max:5'],
        ]);
        
        // Actualizar información básica
        $user->username = $request->username;
        $user->email = $request->email;
        $user->bio = $request->bio;
        
        // Actualizar contraseña si se proporcionó
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        
        // Procesar imagen de perfil
        if ($request->hasFile('profile_image')) {
            // Eliminar imagen anterior si existe
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }
            
            // Guardar nueva imagen
            $path = $request->file('profile_image')->store('profile_images', 'public');
            $user->profile_image = $path;
        }
        
        $user->save();
        
        // Actualizar preferencias de géneros
        if ($request->has('genres')) {
            foreach ($request->genres as $genreId => $level) {
                if ($level > 0) {
                    UserGenrePreference::updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'tmdb_genre_id' => $genreId,
                        ],
                        [
                            'preference_level' => $level,
                        ]
                    );
                } else {
                    // Si el nivel es 0, eliminar la preferencia
                    UserGenrePreference::where('user_id', $user->id)
                        ->where('tmdb_genre_id', $genreId)
                        ->delete();
                }
            }
        }
        
        return redirect()->route('profile.show')
            ->with('success', 'Perfil actualizado correctamente.');
    }
}