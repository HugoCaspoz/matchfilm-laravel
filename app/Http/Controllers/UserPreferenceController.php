<?php

namespace App\Http\Controllers;

use App\Models\UserPreference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserPreferenceController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        $preferences = $user->preferences ?? new UserPreference();
        
        // Obtener géneros populares para mostrar en el formulario
        $popularGenres = [
            'Acción', 'Aventura', 'Animación', 'Comedia', 'Crimen', 
            'Documental', 'Drama', 'Familia', 'Fantasía', 'Historia', 
            'Terror', 'Música', 'Misterio', 'Romance', 'Ciencia ficción', 
            'Película de TV', 'Suspense', 'Bélica', 'Western'
        ];
        
        return view('preferences.edit', compact('preferences', 'popularGenres'));
    }
    
    public function update(Request $request)
    {
        $validated = $request->validate([
            'favorite_genres' => 'nullable|array',
            'favorite_directors' => 'nullable|string',
            'preferred_duration' => 'nullable|integer|min:0|max:300',
            'min_year' => 'nullable|integer|min:1900|max:' . date('Y'),
            'max_year' => 'nullable|integer|min:1900|max:' . date('Y'),
            'min_rating' => 'nullable|numeric|min:0|max:10',
        ]);
        
        // Convertir directores de string a array
        if (isset($validated['favorite_directors'])) {
            $validated['favorite_directors'] = array_map('trim', explode(',', $validated['favorite_directors']));
        }
        
        $user = Auth::user();
        
        // Crear o actualizar preferencias
        UserPreference::updateOrCreate(
            ['user_id' => $user->id],
            $validated
        );
        
        return redirect()->route('preferences.edit')->with('success', 'Preferencias actualizadas correctamente');
    }
}