<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'profile_image',
        'bio',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
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
        return $this->hasMany(FilmMatch::class, 'friend_id');
    }

    // Método para obtener todos los matches (iniciados y recibidos)
    public function allMatches()
    {
        return FilmMatch::where('user_id_1', $this->id)
            ->orWhere('friend_id', $this->id);
    }

    /**
     * Get all movie likes by the user.
     */
    public function movieLikes()
    {
        return $this->hasMany(MovieLike::class);
    }

    /**
     * Get all notifications for the user.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get the friends of the user.
     */
    public function friends()
    {
        return $this->belongsToMany(User::class, 'friends', 'user_id', 'friend_id')
                    ->withPivot('status')
                    ->withTimestamps();
    }

    /**
     * Get the users who have added this user as a friend.
     */
    public function friendOf()
    {
        return $this->belongsToMany(User::class, 'friends', 'friend_id', 'user_id')
                    ->withPivot('status')
                    ->withTimestamps();
    }
}
