<?php

namespace App\Http\Resources;

class QuoteResource extends ApiResource
{
    public $attributes = [
        'user_id',
        'quote',
        'source',
        'status',
        'reactions_count',
        'created_at',
        'updated_at',
    ];
}
