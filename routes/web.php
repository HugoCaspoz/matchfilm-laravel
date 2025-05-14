<?php

use App\Http\Controllers\MovieController;
use App\Http\Controllers\FilmMatchController;
use App\Http\Controllers\UserPreferenceController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Página de inicio (landing)
Route::get('/', function () {
    return view('welcome');
});

// Rutas que requieren autenticación
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    
    // Películas
    Route::resource('movies', MovieController::class)->only(['index', 'show']);
    
    // Matches
    Route::resource('matches', FilmMatchController::class)->only(['index', 'store', 'destroy']);
    
    // Preferencias
    Route::get('/preferences', [UserPreferenceController::class, 'edit'])->name('preferences.edit');
    Route::put('/preferences', [UserPreferenceController::class, 'update'])->name('preferences.update');
    
    // Perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Incluir rutas de autenticación
require __DIR__.'/auth.php';