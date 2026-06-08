<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class UserResource extends JsonApiResource
{
    protected bool $usesRequestQueryString = false;

    /**
     * The resource's attributes.
     */
    public $attributes = [
        'first_name',
        'last_name',
        'email',
        'email_verified_at',
        'role',
        'is_premium',
        'ends_at',
        'created_at',
        'updated_at',
    ];
}
