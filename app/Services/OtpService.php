<?php

namespace App\Services;

use App\Models\Otp;
use App\Models\User;
use App\Notifications\SendOtpVerification;
use App\Notifications\SendPasswordResetOtp;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

class OtpService
{
    protected int $otpLength = 5;

    protected int $ttlMinutes = 10;

    /**
     * Create and send an OTP for the given user.
     * Returns the plain OTP in case callers need it (e.g. tests).
     *
     * @param string $type either 'email_verification' or 'forgot_password'
     */
    public function sendFor(User $user, string $type = 'email_verification'): string
    {
        $otp = (string) random_int(10000, 99999);

        Otp::create([
            'user_id' => $user->id,
            'type' => $type,
            'otp_hash' => Hash::make($otp),
            'expires_at' => Carbon::now()->addMinutes($this->ttlMinutes),
        ]);

        if ($type === 'forgot_password') {
            $user->notify(new SendPasswordResetOtp($otp));
        } else {
            $user->notify(new SendOtpVerification($otp));
        }

        return $otp;
    }
}
