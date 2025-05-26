<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Friend extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'friend_id',
        'status'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who initiated the friendship.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the user who received the friendship request.
     */
    public function friend()
    {
        return $this->belongsTo(User::class, 'friend_id');
    }

    /**
     * Scope for accepted friendships.
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    /**
     * Scope for pending friendships.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}