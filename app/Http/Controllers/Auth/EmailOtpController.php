<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\EmailVerificationOtp;
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

        $record = EmailVerificationOtp::where('user_id', $user->id)
            ->latest()
            ->first();

        if (! $record || $record->isExpired() || ! Hash::check($request->otp, $record->otp_hash)) {
            return response()->json(['message' => 'Invalid or expired OTP.'], 422);
        }

        $user->markEmailAsVerified();

        // delete all OTPs for the user
        EmailVerificationOtp::where('user_id', $user->id)->delete();

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

    protected function createAndSendOtp(User $user): JsonResponse
    {
        // create and store OTP for the user
        $otp = (string) random_int(10000, 99999);
        $otpHash = Hash::make($otp);

        EmailVerificationOtp::create([
            'user_id' => $user->id,
            'otp_hash' => $otpHash,
            'expires_at' => Carbon::now()->addMinutes($this->ttlMinutes),
        ]);

        $user->notify(new SendOtpVerification($otp));

        return response()->json(['message' => 'Verification OTP sent.']);
    }
}
