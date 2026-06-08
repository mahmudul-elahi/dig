<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'type', 'otp_hash', 'expires_at'])]
class Otp extends Model
{
    use HasFactory;

    protected $attributes = [
        'type' => 'email_verification',
    ];

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
