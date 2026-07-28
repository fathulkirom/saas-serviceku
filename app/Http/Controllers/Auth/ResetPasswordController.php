<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    /**
     * Tampilkan form lupa password.
     */
    public function showLinkRequestForm()
    {
        return inertia('Auth/ForgotPassword');
    }

    /**
     * Kirim link reset password ke email.
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
        ]);

        // Cari user di tenant database
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->with('error', 'Maaf, email tersebut belum terdaftar di sistem kami.');
        }

        // Buat token
        $token = Str::random(60);

        // Simpan ke password_reset_tokens
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            ['email' => $user->email, 'token' => Hash::make($token), 'created_at' => now()]
        );

        // Kirim notifikasi
        try {
            $user->notify(new ResetPasswordNotification($token));
        } catch (\Exception $e) {
            // Jika mail tidak dikonfigurasi, log token untuk testing
            logger()->info('Password reset token for ' . $user->email . ': ' . $token);
        }

        return back()->with('success', 'Link reset password telah dikirim ke email Anda.');
    }

    /**
     * Tampilkan form reset password.
     */
    public function showResetForm(Request $request, string $token)
    {
        return inertia('Auth/ResetPassword', [
            'token' => $token,
            'email' => $request->email ?? '',
        ]);
    }

    /**
     * Proses reset password.
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|string|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Cari token di database
        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$record || !Hash::check($request->token, $record->token)) {
            return back()->withErrors(['email' => 'Token reset password tidak valid atau sudah kadaluarsa.']);
        }

        // Cek kadaluarsa (token berlaku 60 menit)
        if ($record->created_at && now()->diffInMinutes($record->created_at) > 60) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return back()->withErrors(['email' => 'Token reset password sudah kadaluarsa. Silakan minta ulang.']);
        }

        // Update password user
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return back()->withErrors(['email' => 'Email tidak ditemukan.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Hapus token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('success', 'Password berhasil direset. Silakan login dengan password baru.');
    }
}
