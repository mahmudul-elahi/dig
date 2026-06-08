<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ProfileUpdateRequest;
use App\Http\Resources\UserResource;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;

#[Group('Profile')]
class ProfileController extends Controller
{
    #[Endpoint(title: 'Me', description: "Get the authenticated user's information.")]
    public function show(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    #[Endpoint(title: 'Update Profile', description: "Update the authenticated user's name and/or email.")]
    public function update(ProfileUpdateRequest $request): UserResource
    {
        $user = $request->user();
        $user->fill($request->validated());

        if (! $user->isDirty('email') || ! ($user instanceof MustVerifyEmail)) {
            $user->save();

            return new UserResource($user);
        }

        $user->email_verified_at = null;
        $user->save();
        $user->sendEmailVerificationNotification();

        return new UserResource($user);
    }
}
