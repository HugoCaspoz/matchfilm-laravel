<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FilmMatch extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id', 'movie_id', 'match_score', 'liked', 'watched'
    ];
    
    protected $casts = [
        'match_score' => 'float',
        'liked' => 'boolean',
        'watched' => 'boolean',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function movie()
    {
        return $this->belongsTo(Movie::class);
    }
}