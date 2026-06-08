<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class NotificationSettingResource extends JsonApiResource
{
    protected bool $usesRequestQueryString = false;

    public $attributes = [
        'meditation_reminders',
        'comment_alerts',
        'subscription_alerts',
        'post_react_alerts',
        'created_at',
        'updated_at',
    ];
}
