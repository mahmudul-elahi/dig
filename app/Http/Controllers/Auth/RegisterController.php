<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Responses\MessageResponse;
use App\Models\NotificationSetting;
use App\Models\User;
use App\Services\OtpService;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

#[Group('Authentication')]
class RegisterController extends Controller
{
    #[Endpoint(title: 'Register', description: 'Create a new user account. No token is created on registration.')]
    public function __invoke(RegisterRequest $request): JsonResponse
    {
        $user = DB::transaction(function () use ($request): User {
            $user = User::create($request->validated());

            NotificationSetting::create(['user_id' => $user->id]);

            return $user;
        });

        app(OtpService::class)->sendFor($user);

        return (new MessageResponse('Verification OTP sent.', Response::HTTP_CREATED))->toResponse(request());
    }
}
