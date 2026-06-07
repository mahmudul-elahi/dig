<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Otp;
use App\Notifications\SendOtpVerification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class EmailOtpController extends Controller
{
    protected int $otpLength = 5;
    protected int $ttlMinutes = 10;

    public function send(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $user = User::where('email', $request->email)->firstOrFail();

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified.'], 409);
        }

        app(\App\Services\EmailOtpService::class)->sendFor($user);

        return response()->json(['message' => 'Verification OTP sent.']);
    }

    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'digits:' . $this->otpLength],
        ]);

        $user = User::where('email', $request->email)->firstOrFail();

        $record = Otp::where('user_id', $user->id)
            ->ofType('email_verification')
            ->latest()
            ->first();

        if (! $record || $record->isExpired() || ! Hash::check($request->otp, $record->otp_hash)) {
            return response()->json(['message' => 'Invalid or expired OTP.'], 422);
        }

        $user->markEmailAsVerified();

        // delete all OTPs for the user
        Otp::where('user_id', $user->id)->delete();

        return response()->json(['message' => 'Email verified.']);
    }

    public function resend(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $user = User::where('email', $request->email)->firstOrFail();

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified.'], 409);
        }

        // throttle or rate-limit resend as desired - omitted for brevity

        app(\App\Services\EmailOtpService::class)->sendFor($user);

        return response()->json(['message' => 'Verification OTP sent.']);
    }
}
