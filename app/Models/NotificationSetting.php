<?php

namespace App\Models;

use Database\Factories\NotificationSettingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'meditation_reminders',
    'comment_alerts',
    'subscription_alerts',
    'post_react_alerts',
])]
class NotificationSetting extends Model
{
    /** @use HasFactory<NotificationSettingFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'meditation_reminders' => 'boolean',
            'comment_alerts' => 'boolean',
            'subscription_alerts' => 'boolean',
            'post_react_alerts' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
