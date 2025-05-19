<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FilmMatch extends Model
{
    use HasFactory;

    /**
     * La tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'matches';

    /**
     * Indica si el modelo debe tener timestamps.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id_1',
        'friend_id',
        'tmdb_id',
        'status',
        'matched_at'
    ];

    /**
     * Los atributos que deben convertirse a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'matched_at' => 'datetime',
    ];

    /**
     * Obtener el usuario que inició el match.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id_1');
    }

    /**
     * Obtener el amigo que hizo match con el usuario.
     */
    public function friend()
    {
        return $this->belongsTo(User::class, 'friend_id');
    }
}
