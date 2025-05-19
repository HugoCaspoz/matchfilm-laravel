<?php

use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\FriendController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WatchlistController;
use Illuminate\Support\Facades\Route;

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
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Películas - acciones de usuario
    Route::post('/movies/{id}/rate', [MovieController::class, 'rate'])->name('movies.rate');
    Route::post('/movies/{id}/watchlist', [MovieController::class, 'addToWatchlist'])->name('movies.add_to_watchlist');
    
    // Likes y dislikes
    Route::post('/movies/{id}/like', [MovieController::class, 'like'])->name('movies.like');
    Route::post('/movies/{id}/dislike', [MovieController::class, 'dislike'])->name('movies.dislike');
    
    // Favoritos
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::get('/favorites/search', [FavoriteController::class, 'search'])->name('favorites.search');
    Route::post('/favorites/{id}', [FavoriteController::class, 'toggleFavorite'])->name('favorites.toggle');
    
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
    
    // Amigos (pareja)
    Route::get('/friends', [FriendController::class, 'index'])->name('friends.index');
    Route::get('/friends/search', [FriendController::class, 'search'])->name('friends.search');
    Route::post('/friends/request', [FriendController::class, 'sendRequest'])->name('friends.request');
    Route::delete('/friends/remove/{id}', [FriendController::class, 'removeFriend'])->name('friends.remove');
    Route::get('/friends/matches/{id}', [FriendController::class, 'getMatches'])->name('friends.matches');
    Route::post('/friends/accept/{id}', [FriendController::class, 'acceptRequest'])->name('friends.accept');
    Route::post('/friends/reject/{id}', [FriendController::class, 'rejectRequest'])->name('friends.reject');
    
    // Notificaciones
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read/{id}', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read.all');
    Route::get('/notifications/count', [NotificationController::class, 'getUnreadCount'])->name('notifications.count');
    // Añadir la ruta para la invitación de película
    Route::post('/notifications/movie-invitation', [NotificationController::class, 'sendMovieInvitation'])->name('notifications.movie_invitation');
});

// Rutas para servir archivos CSS y JS
Route::get('/css/{filename}', [App\Http\Controllers\AssetController::class, 'serveCSS'])->name('serve.css');
Route::get('/js/{filename}', [App\Http\Controllers\AssetController::class, 'serveJS'])->name('serve.js');

// Rutas de autenticación (Laravel ya las proporciona si usas Breeze o Jetstream)
require __DIR__.'/auth.php';
