<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant\User;
use App\Models\Tenant\ActivityLog;
use App\Models\Tenant\LoginHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleLoginController extends Controller
{
    public function redirect()
    {
        if (!config('services.google.client_id')) {
            return redirect()->route('login')->with('error', 'Login Google belum dikonfigurasi.');
        }

        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Login Google gagal: ' . $e->getMessage());
        }

        $user = User::where('google_id', $googleUser->id)
            ->orWhere('email', $googleUser->email)
            ->first();

        if ($user) {
            $user->update([
                'google_id' => $googleUser->id,
                'google_avatar' => $googleUser->avatar ?? $user->google_avatar,
            ]);

            ActivityLog::log('auth', 'Login dengan Google: ' . $user->name);
            LoginHistory::record($user->id, true);

            Auth::login($user);
            return redirect()->intended(route('dashboard'));
        }

        $domain = explode('@', $googleUser->email)[1] ?? '';
        if (!tenancy()->initialized) {
            return redirect()->route('login')->with('error', 'Akun Google tidak terdaftar di toko ini.');
        }

        return redirect()->route('login')->with('error', 'Tidak ada akun yang terhubung dengan Google ini. Hubungi owner untuk menghubungkan akun.');
    }

    public function link(string $googleId)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        try {
            $googleUser = Socialite::driver('google')->userFromToken($googleId);
            $user->update([
                'google_id' => $googleUser->id,
                'google_avatar' => $googleUser->avatar,
            ]);

            ActivityLog::log('auth', 'Akun Google terhubung: ' . $googleUser->email);

            return back()->with('success', 'Akun Google berhasil ditautkan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menautkan akun Google.');
        }
    }

    public function unlink()
    {
        $user = Auth::user();
        $user->update([
            'google_id' => null,
            'google_avatar' => null,
        ]);

        ActivityLog::log('auth', 'Akun Google dilepaskan');

        return back()->with('success', 'Akun Google berhasil dilepaskan.');
    }
}
