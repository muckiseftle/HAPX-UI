<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FALaravel\Facade as Google2FA;
use App\Models\User;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::validate($credentials)) {
            $user = User::where('email', $credentials['email'])->first();

            if ($user->two_factor_enabled) {
                // Store user id in session temporarily
                $request->session()->put('auth.2fa.user_id', $user->id);
                $request->session()->put('auth.2fa.remember', $request->boolean('remember'));

                return redirect()->route('login.2fa');
            }

            if (Auth::attempt($credentials, $request->boolean('remember'))) {
                $request->session()->regenerate();
                return redirect()->intended(route('proxies.index'));
            }
        }

        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
    }

    public function showTwoFactor()
    {
        if (!session()->has('auth.2fa.user_id')) {
            return redirect()->route('login');
        }

        return view('auth.two_factor');
    }

    public function verifyTwoFactor(Request $request)
    {
        $request->validate(['otp' => 'required']);

        $userId = session('auth.2fa.user_id');
        $user = User::findOrFail($userId);

        $secret = decrypt($user->google2fa_secret);

        if (Google2FA::verifyKey($secret, $request->otp)) {
            Auth::login($user, session('auth.2fa.remember', false));
            
            $request->session()->forget(['auth.2fa.user_id', 'auth.2fa.remember']);
            $request->session()->regenerate();

            return redirect()->intended(route('proxies.index'));
        }

        return back()->withErrors(['otp' => 'Ungültiger OTP Code.']);
    }

    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
