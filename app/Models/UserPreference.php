<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPreference extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id', 'favorite_genres', 'favorite_directors', 
        'preferred_duration', 'min_year', 'max_year', 'min_rating'
    ];
    
    protected $casts = [
        'favorite_genres' => 'array',
        'favorite_directors' => 'array',
        'preferred_duration' => 'integer',
        'min_year' => 'integer',
        'max_year' => 'integer',
        'min_rating' => 'float',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}