<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HAPX-UI Laravel</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <span class="text-xl font-bold text-blue-600">HAPX-UI</span>
                    </div>
                    <div class="hidden sm:-my-px sm:ml-6 sm:flex sm:space-x-8">
                        <a href="{{ route('proxies.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('proxies.*') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500' }} text-sm font-medium leading-5 focus:outline-none transition duration-150 ease-in-out">
                            Proxy Hosts
                        </a>
                        <a href="{{ route('certificates.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('certificates.*') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500' }} text-sm font-medium leading-5 focus:outline-none transition duration-150 ease-in-out">
                            Zertifikate
                        </a>
                        <a href="{{ route('monitoring.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('monitoring.*') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500' }} text-sm font-medium leading-5 focus:outline-none transition duration-150 ease-in-out">
                            Performance
                        </a>
                        <a href="{{ route('profile.edit') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('profile.*') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500' }} text-sm font-medium leading-5 focus:outline-none transition duration-150 ease-in-out">
                            Profil
                        </a>
                    </div>
                </div>
                <div class="flex items-center">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm font-medium text-gray-500 hover:text-gray-700 transition">
                            Abmelden
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <div class="py-10">
        <header>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h1 class="text-3xl font-bold leading-tight text-gray-900">
                    @yield('header')
                </h1>
            </div>
        </header>
        <main>
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-8">
                @if (session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                        {{ session('error') }}
                    </div>
                @endif
                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
