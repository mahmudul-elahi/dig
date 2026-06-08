<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class SocialLoginRequest extends FormRequest
{
    private const ALLOWED_PROVIDERS = ['google', 'apple'];

    public function rules(): array
    {
        return [
            'provider' => ['required', 'string', 'in:' . implode(',', self::ALLOWED_PROVIDERS)],
            'id_token' => ['required', 'string'],
        ];
    }
}
