<?php

namespace App\Http\Resources;

class NotificationSettingResource extends ApiResource
{
    public $attributes = [
        'meditation_reminders',
        'comment_alerts',
        'subscription_alerts',
        'post_react_alerts',
        'created_at',
        'updated_at',
    ];

    public $relationships = [
        'user' => UserResource::class,
    ];
}
