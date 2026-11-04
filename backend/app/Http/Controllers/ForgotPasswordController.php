<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Mail\ResetPasswordOtpMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    public function checkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = strtolower(trim($request->email));
        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Email tidak terdaftar dalam sistem.'
            ], 404);
        }

        $otp = (string) random_int(100000, 999999);

        // Simpan OTP ke cache selama 5 menit
        Cache::put('forgot_password_otp_' . $email, Hash::make($otp), now()->addMinutes(5));

        $emailSent = false;

        // 1. Kirim OTP via Google Mailer / SMTP
        try {
            Mail::to($user->email)->send(new ResetPasswordOtpMail($otp, $user->name));
            $emailSent = true;
            Log::info("OTP reset password berhasil dikirim ke email {$email}");
        } catch (\Throwable $e) {
            Log::error("Gagal mengirim email OTP ke {$email}: " . $e->getMessage());
        }

        // 2. Kirim juga via WhatsApp jika nomor terdaftar & Fonnte aktif
        $waSent = false;
        $nomor = trim($user->no_hp ?? '');
        $fonnteToken = config('services.fonnte.token');

        if ($nomor && $fonnteToken) {
            if (str_starts_with($nomor, '0')) {
                $nomor = '62' . substr($nomor, 1);
            }

            $message = "🅿️ SECURE PARKING SYSTEM\n\n" .
                "Kode OTP untuk reset password {$user->name} adalah:\n\n" .
                "*{$otp}*\n\n" .
                "Kode OTP berlaku selama 5 menit.\n" .
                "Jangan berikan kode ini kepada siapapun.";

            $fonnteUrl = config('services.fonnte.url', 'https://api.fonnte.com/send');

            try {
                $response = Http::withHeaders([
                    'Authorization' => $fonnteToken,
                ])->timeout(15)->post($fonnteUrl, [
                    'target' => $nomor,
                    'message' => $message,
                    'countryCode' => '62',
                ]);

                if ($response->successful()) {
                    $waSent = true;
                }
            } catch (\Throwable $e) {
                Log::warning('WhatsApp OTP notice: ' . $e->getMessage());
            }
        }

        // Response dinamis
        if ($emailSent && $waSent) {
            $msg = "Kode OTP berhasil dikirim ke Email ({$email}) dan WhatsApp Anda.";
        } elseif ($emailSent) {
            $msg = "Kode OTP berhasil dikirim ke Email Anda ({$email}). Periksa inbox atau folder spam.";
        } elseif ($waSent) {
            $msg = "Kode OTP berhasil dikirim ke WhatsApp Anda.";
        } else {
            $msg = "Kode OTP berhasil dibuat (Mode Pengujian aktif).";
        }

        return response()->json([
            'success' => true,
            'message' => $msg,
            'dev_otp' => config('app.debug') ? $otp : null,
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|digits:6',
        ]);

        $email = strtolower(trim($request->email));
        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Email tidak terdaftar.'
            ], 404);
        }

        $storedOtp = Cache::get('forgot_password_otp_' . $email);

        if (!$storedOtp) {
            return response()->json([
                'success' => false,
                'message' => 'OTP sudah kadaluarsa. Silakan minta OTP baru.'
            ], 400);
        }

        if (!Hash::check($request->otp, $storedOtp)) {
            return response()->json([
                'success' => false,
                'message' => 'OTP salah.'
            ], 400);
        }

        Cache::forget('forgot_password_otp_' . $email);

        $resetToken = Str::random(64);
        Cache::put('forgot_password_verified_' . $email, $resetToken, now()->addMinutes(10));

        return response()->json([
            'success' => true,
            'message' => 'OTP benar. Silakan buat password baru.',
            'reset_token' => $resetToken,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'reset_token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $email = strtolower(trim($request->email));
        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Email tidak terdaftar.'
            ], 404);
        }

        $storedToken = Cache::get('forgot_password_verified_' . $email);

        if (!$storedToken || !hash_equals($storedToken, $request->reset_token)) {
            return response()->json([
                'success' => false,
                'message' => 'Akses reset password tidak valid atau sudah kadaluarsa.'
            ], 400);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        Cache::forget('forgot_password_verified_' . $email);

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diubah. Silakan login dengan password baru.',
        ]);
    }
}
