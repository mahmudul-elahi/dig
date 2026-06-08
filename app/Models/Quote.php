<?php

namespace App\Models;

use Database\Factories\QuoteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'quote', 'source', 'status', 'reactions_count'])]
class Quote extends Model
{
    /** @use HasFactory<QuoteFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'reactions_count' => 'integer',
            'is_liked' => 'boolean',
        ];
    }

    public function scopeCreatedAtDate(Builder $query, string $date): void
    {
        $query->whereDate('created_at', $date);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function quoteLikes(): HasMany
    {
        return $this->hasMany(QuoteLike::class);
    }

    public function isLikedBy(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        return $this->quoteLikes()
            ->where('user_id', $user->getKey())
            ->exists();
    }
}
