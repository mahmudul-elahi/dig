<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Services\EmailOtpService;
use App\Http\Responses\MessageResponse;
use Illuminate\Http\Response;

#[Group('Authentication')]
class LoginController extends Controller
{
    private const string DUMMY_PASSWORD_HASH = '$2y$12$OoT8jJofGjVA6q9C3/FvNeplQTQg0JAeDakKw2oy4vBvK/eAEksCW';

    #[Endpoint(title: 'Login', description: 'Authenticate a user and return an API token.')]
    public function __invoke(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();
        $passwordHash = $user?->password ?? self::DUMMY_PASSWORD_HASH;
        $passwordMatches = Hash::check($request->password, $passwordHash);

        if (! $user || ! $passwordMatches) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        // if the user's email is not verified, send OTP and instruct verification
        if (! $user->hasVerifiedEmail()) {
            app(EmailOtpService::class)->sendFor($user);

            return (new MessageResponse('Please verify your email. Verification OTP sent.', Response::HTTP_FORBIDDEN))->toResponse(request());
        }

        $token = $user->createToken('auth')->plainTextToken;

        // return the token and the user resource
        return response()->json([
            'token' => $token,
            'user' => (new UserResource($user))->resolve(),
        ]);
    }
}
