<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateNotificationRequest;
use App\Http\Resources\NotificationSettingResource;
use App\Models\NotificationSetting;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;

#[Group('User Notification Settings')]
class NotificationController extends Controller
{
    #[Endpoint(title: 'Show Notification Settings', description: 'Retrieve the notification settings for the authenticated user.')]
    public function show(): NotificationSettingResource
    {
        $settings = request()->user()->notificationSetting;

        if ($settings === null) {
            $settings = NotificationSetting::create(['user_id' => request()->user()->id]);
        }

        return new NotificationSettingResource($settings);
    }

    #[Endpoint(title: 'Update Notification Settings', description: 'Update the notification settings for the authenticated user.')]
    public function update(UpdateNotificationRequest $request): NotificationSettingResource
    {
        $settings = $request->user()->notificationSetting;

        if ($settings === null) {
            $settings = NotificationSetting::create(['user_id' => $request->user()->id]);
        }

        $settings->update($request->validated());

        return new NotificationSettingResource($settings->fresh());
    }
}
