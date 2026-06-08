<?php

namespace App\Http\Resources;

class UserResource extends ApiResource
{
    public $attributes = [
        'first_name',
        'last_name',
        'email',
        'email_verified_at',
        'role',
        'is_active',
        'is_premium',
        'subscription_type',
        'ends_at',
        'created_at',
        'updated_at',
    ];

    public $relationships = [
        'notificationSetting' => NotificationSettingResource::class,
        'latestSubscription' => SubscriptionResource::class,
        'quotes' => QuoteResource::class,
    ];
}
