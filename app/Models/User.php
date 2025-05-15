<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'username',
        'email',
        'password',
        'profile_image',
        'bio',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Relaciones
    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function watchlists()
    {
        return $this->hasMany(Watchlist::class);
    }

    public function genrePreferences()
    {
        return $this->hasMany(UserGenrePreference::class);
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    // Relaciones para matches
    public function initiatedMatches()
    {
        return $this->hasMany(FilmMatch::class, 'user_id_1');
    }

    public function receivedMatches()
    {
        return $this->hasMany(FilmMatch::class, 'user_id_2');
    }

    // Método para obtener todos los matches (iniciados y recibidos)
    public function allMatches()
    {
        return FilmMatch::where('user_id_1', $this->id)
            ->orWhere('user_id_2', $this->id);
    }
}