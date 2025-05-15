<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\WatchlistController;
use App\Http\Controllers\MessageController;

// Rutas públicas
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/movies', [MovieController::class, 'index'])->name('movies.index');
Route::get('/movies/search', [MovieController::class, 'search'])->name('movies.search');
Route::get('/movies/genre/{id}', [MovieController::class, 'byGenre'])->name('movies.by_genre');
Route::get('/movies/{id}', [MovieController::class, 'show'])->name('movies.show');

// Rutas protegidas (requieren autenticación)
Route::middleware(['auth'])->group(function () {
    // Perfil
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    
    // Películas - acciones de usuario
    Route::post('/movies/{id}/rate', [MovieController::class, 'rate'])->name('movies.rate');
    Route::post('/movies/{id}/watchlist', [MovieController::class, 'addToWatchlist'])->name('movies.add_to_watchlist');
    
    // Listas de películas
    Route::resource('watchlists', WatchlistController::class);
    Route::post('/watchlists/{watchlist}/remove/{tmdb_id}', [WatchlistController::class, 'removeMovie'])->name('watchlists.remove_movie');
    
    // Matches
    Route::get('/matches', [MatchController::class, 'index'])->name('matches.index');
    Route::post('/matches/{id}/accept', [MatchController::class, 'accept'])->name('matches.accept');
    Route::post('/matches/{id}/reject', [MatchController::class, 'reject'])->name('matches.reject');
    
    // Mensajes
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/{user}', [MessageController::class, 'show'])->name('messages.show');
    Route::post('/messages/{user}', [MessageController::class, 'store'])->name('messages.store');
});

// Rutas de autenticación (Laravel ya las proporciona si usas Breeze o Jetstream)
require __DIR__.'/auth.php';