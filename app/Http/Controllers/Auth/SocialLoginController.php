<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SocialLoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Laravel\Socialite\Facades\Socialite;

#[Group('Authentication')]
class SocialLoginController extends Controller
{
    #[Endpoint(title: 'Facebook/Google', description: "Login with Facebook or Google. The 'provider' field should be either 'facebook' or 'google'.s")]
    public function __invoke(SocialLoginRequest $request): JsonResponse
    {
        $provider = $request->input('provider');

        $socialUser = Socialite::driver($provider)
            ->stateless()
            ->userFromToken($request->input('id_token'));

        $user = User::where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        if ($user === null) {
            $names = $this->splitName($socialUser->getName());

            $user = User::create([
                'first_name' => $names[0],
                'last_name' => $names[1],
                'email' => $socialUser->getEmail(),
                'email_verified_at' => now(),
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
            ]);
        }

        $token = $user->createToken('social-login')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => (new UserResource($user))->resolve(),
        ]);
    }

    /**
     * @return array{0: string, 1: string|null}
     */
    private function splitName(?string $fullName): array
    {
        if ($fullName === null || $fullName === '') {
            return ['User', null];
        }

        $parts = explode(' ', $fullName, 2);

        return [$parts[0], $parts[1] ?? null];
    }
}
