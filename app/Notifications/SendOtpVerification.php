<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendOtpVerification extends Notification
{
    use Queueable;

    public string $otp;

    public function __construct(string $otp)
    {
        $this->otp = $otp;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your email verification code')
            ->line('Use the following one-time verification code to confirm your email address:')
            ->line("\n" . $this->otp . "\n")
            ->line('This code will expire in 10 minutes.');
    }
}
