<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selamat Datang di ServiceKU</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f3f4f6; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .card { background: #fff; border-radius: 12px; padding: 40px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .logo { text-align: center; font-size: 28px; font-weight: bold; color: #4F46E5; margin-bottom: 20px; }
        h1 { color: #111827; font-size: 22px; margin-bottom: 16px; }
        p { color: #6B7280; line-height: 1.6; margin-bottom: 12px; font-size: 15px; }
        .info-box { background: #EEF2FF; border: 1px solid #E0E7FF; border-radius: 8px; padding: 16px; margin: 20px 0; }
        .info-box p { margin: 4px 0; font-size: 14px; }
        .info-box strong { color: #111827; }
        .btn { display: inline-block; background: #4F46E5; color: #fff; padding: 12px 32px; border-radius: 8px; text-decoration: none; font-weight: 600; margin-top: 16px; }
        .footer { text-align: center; margin-top: 30px; color: #9CA3AF; font-size: 12px; }
        hr { border: none; border-top: 1px solid #E5E7EB; margin: 24px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="logo">ServiceKU</div>
            <h1>Selamat Datang, {{ $tenant->tenant_name }}! 🎉</h1>
            <p>Terima kasih telah mendaftar di <strong>ServiceKU</strong> — platform manajemen servis HP & laptop terpercaya.</p>

            <div class="info-box">
                <p><strong>Nama Toko:</strong> {{ $tenant->tenant_name }}</p>
                <p><strong>Email:</strong> {{ $tenant->email }}</p>
                <p><strong>Password:</strong> <code>{{ $password }}</code></p>
                <p style="color:#DC2626; font-size:12px; margin-top:8px;">⚠️ Segera ganti password setelah login pertama.</p>
            </div>

            @if ($loginUrl)
                <div style="text-align: center;">
                    <a href="{{ $loginUrl }}" class="btn">Masuk ke Aplikasi</a>
                </div>
            @endif

            <hr>

            <p style="font-size: 13px;">Butuh bantuan? Balas email ini atau hubungi tim support kami.</p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} ServiceKU. All rights reserved.</p>
            <p>Platform Manajemen Servis Terpercaya</p>
        </div>
    </div>
</body>
</html>
