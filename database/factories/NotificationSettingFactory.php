<?php

namespace Database\Factories;

use App\Models\NotificationSetting;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationSetting>
 */
class NotificationSettingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'meditation_reminders' => true,
            'comment_alerts' => true,
            'subscription_alerts' => true,
            'post_react_alerts' => true,
        ];
    }
}
