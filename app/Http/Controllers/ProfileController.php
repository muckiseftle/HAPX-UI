<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FALaravel\Facade as Google2FA;
use App\Models\User;
use App\Services\TwoFactorService;

class ProfileController extends Controller
{
    public function edit(TwoFactorService $tfService)
    {
        $user = Auth::user();
        $qrCodeSvg = null;
        $secret = null;

        if (!$user->two_factor_enabled) {
            $secret = session('new_2fa_secret') ?: Google2FA::generateSecretKey();
            session(['new_2fa_secret' => $secret]);
            
            $google2fa_url = Google2FA::getQRCodeUrl(
                'HAPX-UI',
                $user->email,
                $secret
            );
            
            $qrCodeSvg = $tfService->getQrCodeSvg($google2fa_url);
        }

        return view('profile.edit', compact('user', 'qrCodeSvg', 'secret'));
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'min:8', 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Passwort erfolgreich geändert.');
    }

    public function enable2fa(Request $request)
    {
        $user = Auth::user();
        $secret = session('new_2fa_secret');
        $otp = $request->otp;

        if (Google2FA::verifyKey($secret, $otp)) {
            $user->google2fa_secret = encrypt($secret);
            $user->two_factor_enabled = true;
            $user->save();

            session()->forget('new_2fa_secret');
            return back()->with('success', '2FA wurde erfolgreich aktiviert.');
        }

        return back()->with('error', 'Ungültiger OTP Code. Bitte erneut versuchen.');
    }

    public function disable2fa(Request $request)
    {
        $user = Auth::user();
        $user->google2fa_secret = null;
        $user->two_factor_enabled = false;
        $user->save();

        return back()->with('success', '2FA wurde deaktiviert.');
    }
}
