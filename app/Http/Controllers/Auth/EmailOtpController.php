<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use App\Http\Responses\MessageResponse;

class EmailOtpController extends Controller
{

    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'digits:5'],
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

        Otp::where('user_id', $user->id)->delete();

        return (new MessageResponse('Email verified.'))->toResponse(request());
    }

    public function resend(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $user = User::where('email', $request->email)->firstOrFail();

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified.'], 409);
        }

        // throttle or rate-limit resend as desired - omitted for brevity

        app(\App\Services\OtpService::class)->sendFor($user);

        return response()->json(['message' => 'Verification OTP sent.']);
    }
}
