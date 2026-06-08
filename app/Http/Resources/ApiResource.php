<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\JsonApi\JsonApiResource;

abstract class ApiResource extends JsonApiResource
{
    protected bool $usesRequestQueryString = false;
}
