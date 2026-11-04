<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode OTP Reset Password</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
            margin: 0;
            padding: 30px 15px;
        }
        .container {
            max-width: 520px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }
        .header {
            background-color: #001a4e;
            padding: 24px;
            text-align: center;
        }
        .brand-name {
            color: #ffffff;
            font-size: 20px;
            font-weight: 800;
            letter-spacing: 0.5px;
            margin: 0;
        }
        .brand-subtitle {
            color: #0099ff;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-top: 4px;
        }
        .content {
            padding: 32px 28px;
        }
        .greeting {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 12px;
        }
        .text {
            font-size: 14px;
            line-height: 1.6;
            color: #475569;
            margin-bottom: 24px;
        }
        .otp-box {
            background-color: #f8fafc;
            border: 2px dashed #0099ff;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            margin-bottom: 24px;
        }
        .otp-label {
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 6px;
        }
        .otp-code {
            font-family: 'Courier New', Courier, monospace;
            font-size: 38px;
            font-weight: 900;
            letter-spacing: 10px;
            color: #001a4e;
            display: inline-block;
        }
        .notice {
            background-color: #fffbeb;
            border-left: 4px solid #ff9900;
            padding: 12px 14px;
            border-radius: 6px;
            font-size: 12px;
            line-height: 1.5;
            color: #92400e;
            margin-bottom: 24px;
        }
        .footer {
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 16px;
            text-align: center;
            font-size: 11px;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Brand Header -->
        <div class="header">
            <h1 class="brand-name">SECURE PARKING</h1>
            <div class="brand-subtitle">Sistem Manajemen Operasional Parkir Digital</div>
        </div>

        <!-- Body Content -->
        <div class="content">
            <div class="greeting">Halo, {{ $name }}!</div>
            <div class="text">
                Kami menerima permintaan verifikasi untuk mereset kata sandi akun Anda (Admin / Petugas Gerbang). Gunakan <strong>6-digit Kode OTP</strong> resmi berikut untuk menyelesaikan proses pemulihan kata sandi:
            </div>

            <!-- OTP Display Box -->
            <div class="otp-box">
                <div class="otp-label">Kode Verifikasi OTP Anda</div>
                <div class="otp-code">{{ $otp }}</div>
            </div>

            <!-- Notice & Expiry -->
            <div class="notice">
                &bull; Kode OTP ini hanya berlaku selama <strong>5 menit</strong>.<br>
                &bull; Jangan berikan kode ini kepada siapa pun, termasuk staf operasional lainnya.<br>
                &bull; Jika Anda tidak merasa melakukan permintaan ini, abaikan email ini dan akun Anda tetap aman.
            </div>

            <div class="text" style="margin-bottom: 0; font-size: 13px;">
                Terima kasih atas kerja sama Anda,<br>
                <strong>Tim IT & Operasional Secure Parking</strong>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            &copy; {{ date('Y') }} Secure Parking Digital. Seluruh hak cipta dilindungi.
        </div>
    </div>
</body>
</html>
