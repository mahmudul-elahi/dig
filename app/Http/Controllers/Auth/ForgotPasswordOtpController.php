<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Responses\MessageResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as PasswordRules;

class ForgotPasswordOtpController extends Controller
{
    public function send(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $user = User::where('email', $request->email)->firstOrFail();

        app(\App\Services\OtpService::class)->sendFor($user, 'forgot_password');

        return (new MessageResponse('Password reset OTP sent.'))->toResponse(request());
    }

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

        // OTP is valid; allow client to proceed to reset password. We can return a short-lived token or just 200.
        // For now return a simple success message.
        return (new MessageResponse('OTP validated. Proceed to reset password.'))->toResponse(request());
    }

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

        // Update user's password
        $user->password = Hash::make($request->password);
        $user->save();

        // Remove all forgot_password OTPs for this user
        \App\Models\Otp::where('user_id', $user->id)->ofType('forgot_password')->delete();

        // Remove all personal access tokens for security
        if (method_exists($user, 'tokens')) {
            $user->tokens()->delete();
        }

        return (new MessageResponse('Password reset successfully.'))->toResponse(request());
    }
}
