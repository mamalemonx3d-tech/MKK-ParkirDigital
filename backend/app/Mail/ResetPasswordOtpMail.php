<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $otp;
    public string $name;

    public function __construct(string $otp, string $name = 'Petugas')
    {
        $this->otp = $otp;
        $this->name = $name;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Kode Verifikasi OTP Reset Password - Secure Parking',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reset_password_otp',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
