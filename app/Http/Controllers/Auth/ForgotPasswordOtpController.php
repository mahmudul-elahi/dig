<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Responses\MessageResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as PasswordRules;

#[Group('Authentication')]
class ForgotPasswordOtpController extends Controller
{
    #[Endpoint(title: 'Send Password Reset OTP', description: 'Send a 5-digit OTP to the user\'s email for password reset.')]
    public function send(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $user = User::where('email', $request->email)->firstOrFail();

        app(\App\Services\OtpService::class)->sendFor($user, 'forgot_password');

        return (new MessageResponse('Password reset OTP sent.'))->toResponse(request());
    }

    #[Endpoint(title: 'Verify Password Reset OTP', description: 'Verify the password reset OTP sent to the user\'s email.')]
    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'digits:5'],
        ]);

        $user = User::where('email', $request->email)->firstOrFail();

        $record = \App\Models\Otp::where('user_id', $user->id)
            ->ofType('forgot_password')
            ->latest()
            ->first();

        if (! $record || $record->isExpired() || ! \Illuminate\Support\Facades\Hash::check($request->otp, $record->otp_hash)) {
            return (new MessageResponse('Invalid or expired OTP.', 422))->toResponse(request());
        }

        return (new MessageResponse('OTP validated. Proceed to reset password.'))->toResponse(request());
    }

    #[Endpoint(title: 'Reset Password', description: 'Reset the user\'s password using a verified OTP. Requires email, otp, password, and password_confirmation.')]
    public function reset(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'digits:5'],
            'password' => ['required', 'confirmed', PasswordRules::defaults()],
        ]);

        $user = User::where('email', $request->email)->firstOrFail();

        $record = \App\Models\Otp::where('user_id', $user->id)
            ->ofType('forgot_password')
            ->latest()
            ->first();

        if (! $record || $record->isExpired() || ! Hash::check($request->otp, $record->otp_hash)) {
            return (new MessageResponse('Invalid or expired OTP.', 422))->toResponse(request());
        }

        $user->password = Hash::make($request->password);
        $user->save();

        \App\Models\Otp::where('user_id', $user->id)->ofType('forgot_password')->delete();

        if (method_exists($user, 'tokens')) {
            $user->tokens()->delete();
        }

        return (new MessageResponse('Password reset successfully.'))->toResponse(request());
    }
}
