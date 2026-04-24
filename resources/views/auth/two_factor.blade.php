<!DOCTYPE html>
<html lang="de" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>2FA Verifizierung | HAPX-UI</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full">
    <div class="min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <div class="flex justify-center">
                <div class="bg-indigo-600 p-3 rounded-2xl shadow-lg">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04currM12 21.355r1"></path>
                    </svg>
                </div>
            </div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">2FA Verifizierung</h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Bitte geben Sie den 6-stelligen Code aus Ihrer App ein.
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-4 shadow-2xl border border-gray-100 sm:rounded-2xl sm:px-10">
                <form class="space-y-6" action="{{ route('login.2fa') }}" method="POST">
                    @csrf
                    <div>
                        <label for="otp" class="block text-sm font-semibold text-gray-700">Authenticator Code</label>
                        <div class="mt-1">
                            <input id="otp" name="otp" type="text" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code" required autofocus
                                class="appearance-none block w-full px-3 py-3 border border-gray-300 rounded-xl shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-center text-2xl tracking-widest"
                                placeholder="000000">
                        </div>
                        @error('otp')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-150">
                            Verifizieren & Anmelden
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
