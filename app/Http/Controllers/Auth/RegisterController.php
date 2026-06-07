<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Services\EmailOtpService;
use App\Http\Responses\MessageResponse;

#[Group('Authentication')]
class RegisterController extends Controller
{
    #[Endpoint(title: 'Register', description: 'Create a new user account. No token is created on registration.')]
    public function __invoke(RegisterRequest $request): JsonResponse
    {
        $user = DB::transaction(function () use ($request): User {
            return User::create($request->validated());
        });

        // Send OTP for email verification instead of firing the Registered event
        app(\App\Services\OtpService::class)->sendFor($user);

        // Return a message-only response indicating the OTP was sent
        return (new MessageResponse('Verification OTP sent.', Response::HTTP_CREATED))->toResponse(request());
    }
}
