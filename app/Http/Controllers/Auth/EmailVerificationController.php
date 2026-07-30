<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant\User;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;

class EmailVerificationController extends Controller
{
    /**
     * Tampilkan halaman pemberitahuan verifikasi.
     */
    public function notice()
    {
        if (!\App\Services\FeatureFlagService::isEnabled('email_verification')) {
            return redirect()->intended(route('dashboard'));
        }

        $user = auth()->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard'));
        }

        return inertia('Auth/VerifyEmail');
    }

    /**
     * Verifikasi email pengguna.
     */
    public function verify(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            abort(403, 'Link verifikasi tidak valid.');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('dashboard')->with('info', 'Email sudah terverifikasi.');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect()->route('dashboard')->with('success', 'Email berhasil diverifikasi!');
    }

    /**
     * Kirim ulang link verifikasi.
     */
    public function resend(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('dashboard')->with('info', 'Email sudah terverifikasi.');
        }

        $user->sendEmailVerificationNotification();

        return back()->with('success', 'Link verifikasi telah dikirim ulang ke email Anda.');
    }
}
