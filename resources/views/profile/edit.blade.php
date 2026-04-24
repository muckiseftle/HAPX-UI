@extends('layouts.app')

@section('header', 'Profil & Sicherheit')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <!-- Passwort ändern -->
    <div class="bg-white shadow-xl rounded-2xl p-8 border border-gray-100">
        <div class="flex items-center mb-6">
            <div class="bg-blue-100 p-3 rounded-xl mr-4">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-800">Passwort ändern</h2>
                <p class="text-sm text-gray-500">Sorgen Sie für ein starkes Passwort</p>
            </div>
        </div>

        <form action="{{ route('profile.password') }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Aktuelles Passwort</label>
                    <input type="password" name="current_password" class="w-full border-gray-200 rounded-xl p-3 border focus:ring-blue-500" required>
                    @error('current_password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div></div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Neues Passwort</label>
                    <input type="password" name="password" class="w-full border-gray-200 rounded-xl p-3 border focus:ring-blue-500" required>
                    @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Passwort bestätigen</label>
                    <input type="password" name="password_confirmation" class="w-full border-gray-200 rounded-xl p-3 border focus:ring-blue-500" required>
                </div>
            </div>

            <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-xl font-bold hover:bg-blue-700 transition">
                Passwort speichern
            </button>
        </form>
    </div>

    <!-- 2FA Verwaltung -->
    <div class="bg-white shadow-xl rounded-2xl p-8 border border-gray-100">
        <div class="flex items-center mb-6">
            <div class="bg-indigo-100 p-3 rounded-xl mr-4">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04currM12 21.355r1"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21a9 9 0 009-9H3a9 9 0 009 9z"></path></svg>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-800">Zwei-Faktor-Authentifizierung (2FA)</h2>
                <p class="text-sm text-gray-500">Zusätzliche Sicherheitsebene für Ihren Account</p>
            </div>
        </div>

        @if($user->two_factor_enabled)
            <div class="bg-green-50 border border-green-100 rounded-2xl p-6 flex items-center justify-between">
                <div class="flex items-center">
                    <div class="bg-green-100 p-2 rounded-full mr-4 text-green-600">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-green-800">2FA ist aktiv</h3>
                        <p class="text-sm text-green-700">Ihr Account ist durch OTP geschützt.</p>
                    </div>
                </div>
                <form action="{{ route('profile.2fa.disable') }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-red-100 text-red-600 px-4 py-2 rounded-lg text-sm font-bold hover:bg-red-200 transition">
                        Deaktivieren
                    </button>
                </form>
            </div>
        @else
            <div class="space-y-6">
                <div class="bg-gray-50 rounded-2xl p-6">
                    <h3 class="font-bold mb-4">Einrichtung</h3>
                    <ol class="list-decimal list-inside text-sm text-gray-600 space-y-2 mb-6">
                        <li>Installieren Sie eine Authenticator-App (z.B. Google Authenticator oder Authy).</li>
                        <li>Scannen Sie den QR-Code unten oder geben Sie den geheimen Schlüssel manuell ein.</li>
                        <li>Geben Sie den generierten 6-stelligen Code zur Bestätigung ein.</li>
                    </ol>

                    <div class="flex flex-col md:flex-row items-center space-y-6 md:space-y-0 md:space-x-8">
                        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                            {!! $qrCodeSvg !!}
                        </div>
                        <div class="flex-1">
                            <p class="text-xs font-bold text-gray-400 uppercase mb-1">Geheimer Schlüssel</p>
                            <code class="bg-white border border-gray-200 px-4 py-2 rounded-lg text-indigo-600 font-mono block w-full text-center md:text-left">
                                {{ $secret }}
                            </code>
                            
                            <form action="{{ route('profile.2fa.enable') }}" method="POST" class="mt-6 space-y-4">
                                @csrf
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Bestätigungscode (OTP)</label>
                                    <input type="text" name="otp" placeholder="123456" class="w-full border-gray-200 rounded-xl p-3 border focus:ring-indigo-500" required>
                                </div>
                                <button type="submit" class="w-full bg-indigo-600 text-white px-6 py-3 rounded-xl font-bold hover:bg-indigo-700 transition">
                                    2FA jetzt aktivieren
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
