<?php

namespace AppHttpControllers;

use AppModelsUser;
use AppMailResetPasswordOtpMail;
use IlluminateHttpRequest;
use IlluminateSupportFacadesCache;
use IlluminateSupportFacadesHash;
use IlluminateSupportFacadesLog;
use IlluminateSupportFacadesMail;
use IlluminateSupportStr;

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

        // Kirim OTP via Google Mailer / Gmail SMTP
        try {
            Mail::to($user->email)->send(new ResetPasswordOtpMail($otp, $user->name));
            Log::info("OTP reset password berhasil dikirim ke email: {$email}");

            return response()->json([
                'success' => true,
                'message' => "Kode OTP 6-digit telah dikirimkan ke email resmi Anda ({$email}). Silakan periksa inbox atau folder spam.",
                'dev_otp' => config('app.debug') ? $otp : null,
            ]);
        } catch (\Throwable $e) {
            Log::error("Gagal mengirim email OTP ke {$email}: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim email OTP. Pastikan koneksi internet aktif atau hubungi Super Admin.',
            ], 500);
        }
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
