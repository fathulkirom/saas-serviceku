<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Kode OTP ServiceKU</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f3f4f6; margin: 0; padding: 0; }
        .container { max-width: 500px; margin: 0 auto; padding: 20px; }
        .card { background: #fff; border-radius: 12px; padding: 40px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); text-align: center; }
        .logo { font-size: 28px; font-weight: bold; color: #4F46E5; margin-bottom: 20px; }
        h1 { color: #111827; font-size: 20px; margin-bottom: 8px; }
        p { color: #6B7280; font-size: 14px; line-height: 1.6; }
        .otp-box { background: #EEF2FF; border: 2px dashed #4F46E5; border-radius: 12px; padding: 20px; margin: 24px 0; }
        .otp-code { font-size: 36px; font-weight: bold; color: #4F46E5; letter-spacing: 8px; font-family: monospace; }
        .footer { margin-top: 24px; color: #9CA3AF; font-size: 11px; }
        hr { border: none; border-top: 1px solid #E5E7EB; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="logo">ServiceKU</div>
            <h1>Verifikasi Email Anda</h1>
            <p>Masukkan kode OTP berikut untuk melanjutkan pendaftaran toko Anda.</p>
            <div class="otp-box">
                <div class="otp-code">{{ $otp }}</div>
            </div>
            <p style="color: #DC2626; font-size: 12px;">Kode berlaku selama 15 menit.</p>
            <hr>
            <p style="font-size: 12px;">Jika Anda tidak mendaftar di ServiceKU, abaikan email ini.</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} ServiceKU. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
