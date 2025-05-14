<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'tmdb_id', 'title', 'description', 'poster_url', 'backdrop_url', 
        'release_year', 'rating', 'genres', 'director', 'duration', 'last_synced_at'
    ];
    
    protected $casts = [
        'genres' => 'array',
        'release_year' => 'integer',
        'rating' => 'float',
        'duration' => 'integer',
        'last_synced_at' => 'datetime',
    ];
    
    public function users()
    {
        return $this->belongsToMany(User::class, 'film_matches')
                    ->withPivot('match_score', 'liked', 'watched')
                    ->withTimestamps();
    }
    
    public function filmMatches()
    {
        return $this->hasMany(FilmMatch::class);
    }
    
    public function shouldRefresh()
    {
        return !$this->last_synced_at || $this->last_synced_at->diffInDays(now()) > 7;
    }
}