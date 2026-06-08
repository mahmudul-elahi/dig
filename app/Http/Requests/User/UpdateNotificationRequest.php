<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNotificationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'meditation_reminders' => ['sometimes', 'boolean'],
            'comment_alerts' => ['sometimes', 'boolean'],
            'subscription_alerts' => ['sometimes', 'boolean'],
            'post_react_alerts' => ['sometimes', 'boolean'],
        ];
    }
}
