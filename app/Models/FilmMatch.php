<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FilmMatch extends Model
{
    use HasFactory;

    protected $table = 'matches'; // Mantiene el nombre de la tabla como 'matches'
    
    protected $fillable = [
        'user_id_1',
        'user_id_2',
        'tmdb_id',
        'status',
        'matched_at'
    ];

    protected $casts = [
        'matched_at' => 'datetime',
    ];

    // Relaciones
    public function userOne()
    {
        return $this->belongsTo(User::class, 'user_id_1');
    }

    public function userTwo()
    {
        return $this->belongsTo(User::class, 'user_id_2');
    }

    // Método para obtener el otro usuario del match
    public function getOtherUser($userId)
    {
        return $this->user_id_1 == $userId ? $this->userTwo : $this->userOne;
    }
}