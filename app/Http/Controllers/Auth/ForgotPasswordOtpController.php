<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ForgotPasswordOtpController extends Controller
{
    public function send(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $user = User::where('email', $request->email)->firstOrFail();

        app(\App\Services\EmailOtpService::class)->sendFor($user, 'forgot_password');

        return response()->json(['message' => 'Password reset OTP sent.']);
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
            return response()->json(['message' => 'Invalid or expired OTP.'], 422);
        }

        // OTP is valid; allow client to proceed to reset password. We can return a short-lived token or just 200.
        // For now return a simple success message.
        return response()->json(['message' => 'OTP validated. Proceed to reset password.']);
    }
}
