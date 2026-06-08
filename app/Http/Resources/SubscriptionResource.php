<?php

namespace App\Http\Resources;

class SubscriptionResource extends ApiResource
{
    public $attributes = [
        'revenuecat_event_type',
        'product_id',
        'transaction_id',
        'store',
        'period_type',
        'purchased_at',
        'expiration_at',
        'auto_resume_at',
        'original_payload',
        'created_at',
        'updated_at',
    ];

    public $relationships = [
        'user' => UserResource::class,
    ];
}
