<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    use HasFactory;

    // types: 'email_verification', 'forgot_password'
    protected $attributes = [
        'type' => 'email_verification',
    ];

    protected $fillable = ['user_id', 'type', 'otp_hash', 'expires_at'];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
