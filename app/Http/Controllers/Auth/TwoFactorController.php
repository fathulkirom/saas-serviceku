<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorController extends Controller
{
    /**
     * Tampilkan halaman challenge 2FA.
     */
    public function challenge()
    {
        $userId = session('two_factor_user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $user = User::find($userId);
        if (!$user || !$user->hasTwoFactorEnabled()) {
            session()->forget('two_factor_user_id');
            return redirect()->route('login');
        }

        return inertia('Auth/TwoFactorChallenge', [
            'email' => $user->email,
            'use_backup' => false,
        ]);
    }

    /**
     * Verifikasi kode 2FA (TOTP atau recovery code).
     */
    public function verify(Request $request)
    {
        $userId = session('two_factor_user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $user = User::find($userId);
        if (!$user) {
            session()->forget('two_factor_user_id');
            return redirect()->route('login');
        }

        $request->validate([
            'code' => 'required|string',
        ]);

        $code = str_replace(' ', '', $request->code);
        $valid = false;

        // Coba validasi sebagai TOTP
        $google2fa = new Google2FA();
        if ($user->two_factor_secret && $google2fa->verifyKey($user->two_factor_secret, $code, 2)) {
            $valid = true;
        }

        // Coba validasi sebagai recovery code
        if (!$valid && $user->two_factor_recovery_codes) {
            $recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true) ?? [];
            $foundKey = array_search($code, $recoveryCodes);
            if ($foundKey !== false) {
                unset($recoveryCodes[$foundKey]);
                $user->forceFill([
                    'two_factor_recovery_codes' => encrypt(json_encode(array_values($recoveryCodes))),
                ])->save();
                $valid = true;
            }
        }

        if (!$valid) {
            return back()->withErrors(['code' => 'Kode verifikasi tidak valid.'])->with('use_backup', $request->filled('use_backup'));
        }

        // Login berhasil
        session()->forget('two_factor_user_id');
        Auth::guard('web')->login($user);

        \App\Models\Tenant\LoginHistory::record($user, 'success');
        \App\Models\Tenant\ActivityLog::log('login', "Login 2FA: {$user->name}", $user);

        session()->regenerate();

        return redirect()->intended(route('dashboard'))->with('success', 'Login berhasil.');
    }

    /**
     * Minta kode 2FA dikirim via email (fallback).
     */
    public function sendEmailCode(Request $request)
    {
        $userId = session('two_factor_user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $user = User::find($userId);
        if (!$user) {
            session()->forget('two_factor_user_id');
            return redirect()->route('login');
        }

        // Generate 6-digit code, simpan di session
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        session(['two_factor_email_code' => encrypt($code)]);

        $user->sendTwoFactorCodeNotification($code);

        return back()->with('success', 'Kode verifikasi telah dikirim ke email Anda.');
    }

    /**
     * Verifikasi kode email 2FA.
     */
    public function verifyEmailCode(Request $request)
    {
        $userId = session('two_factor_user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $user = User::find($userId);
        if (!$user) {
            session()->forget('two_factor_user_id');
            return redirect()->route('login');
        }

        $request->validate(['code' => 'required|string']);

        $storedCode = session('two_factor_email_code');
        if (!$storedCode || $request->code !== decrypt($storedCode)) {
            return back()->withErrors(['code' => 'Kode email tidak valid.']);
        }

        // Hapus kode dari session
        session()->forget('two_factor_email_code');

        // Login berhasil
        session()->forget('two_factor_user_id');
        Auth::guard('web')->login($user);

        \App\Models\Tenant\LoginHistory::record($user, 'success');
        \App\Models\Tenant\ActivityLog::log('login', "Login 2FA (email): {$user->name}", $user);

        session()->regenerate();

        return redirect()->intended(route('dashboard'))->with('success', 'Login berhasil.');
    }
}
