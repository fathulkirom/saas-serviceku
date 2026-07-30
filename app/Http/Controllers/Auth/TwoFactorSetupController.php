<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorSetupController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Cek status 2FA user.
     */
    public function status()
    {
        $user = Auth::user();

        return response()->json([
            'enabled' => $user->hasTwoFactorEnabled(),
            'recovery_codes' => $user->hasTwoFactorEnabled() ? $user->twoFactorRecoveryCodes() : [],
        ]);
    }

    /**
     * Enable 2FA: generate secret & QR code.
     */
    public function enable()
    {
        if (!\App\Services\FeatureFlagService::isEnabled('two_factor_auth')) {
            return response()->json(['errors' => ['message' => ['Fitur 2FA sedang dinonaktifkan oleh admin.']]], 403);
        }

        $user = Auth::user();
        $google2fa = new Google2FA();

        $secret = $google2fa->generateSecretKey();
        $user->forceFill(['two_factor_secret' => $secret])->save();

        $qrCode = $google2fa->getQRCodeInline(
            config('app.name'),
            $user->email,
            $secret
        );

        return response()->json([
            'qr_code' => $qrCode,
            'secret' => $secret,
        ]);
    }

    /**
     * Confirm 2FA dengan kode dari authenticator.
     */
    public function confirm(Request $request)
    {
        $request->validate(['code' => 'required|string|size:6']);

        $user = Auth::user();
        $google2fa = new Google2FA();

        if (!$google2fa->verifyKey($user->two_factor_secret, $request->code)) {
            return response()->json(['errors' => ['code' => ['Kode tidak valid.']]], 422);
        }

        $user->forceFill(['two_factor_confirmed_at' => now()])->save();
        $user->regenerateTwoFactorRecoveryCodes();

        return response()->json([
            'recovery_codes' => $user->twoFactorRecoveryCodes(),
        ]);
    }

    /**
     * Disable 2FA.
     */
    public function disable()
    {
        $user = Auth::user();

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        return response()->json(['message' => '2FA dinonaktifkan.']);
    }

    /**
     * Regenerate recovery codes.
     */
    public function regenerateCodes()
    {
        $user = Auth::user();

        if (!$user->hasTwoFactorEnabled()) {
            return response()->json(['errors' => ['message' => ['2FA tidak aktif.']]], 422);
        }

        $user->regenerateTwoFactorRecoveryCodes();

        return response()->json([
            'recovery_codes' => $user->twoFactorRecoveryCodes(),
        ]);
    }
}
