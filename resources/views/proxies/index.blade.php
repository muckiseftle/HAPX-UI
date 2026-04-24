@extends('layouts.app')

@section('header', 'Proxy Hosts')

@section('content')
<div class="flex justify-end mb-4">
    <a href="{{ route('proxies.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg shadow hover:bg-blue-700 transition">
        + Neuen Host anlegen
    </a>
</div>

<div class="bg-white shadow overflow-hidden sm:rounded-md">
    <ul class="divide-y divide-gray-200">
        @forelse ($hosts as $host)
            <li>
                <div class="px-4 py-4 sm:px-6 hover:bg-gray-50 flex items-center justify-between">
                    <div class="flex flex-col">
                        <span class="text-lg font-bold text-gray-800">{{ $host->hostname ?? $host->name }}</span>
                        <span class="text-sm text-gray-500 uppercase">{{ $host->mode }} | {{ $host->listen_address }}:{{ $host->listen_port }}</span>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $host->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $host->is_active ? 'Aktiv' : 'Inaktiv' }}
                        </span>
                        
                        <div class="flex items-center space-x-3">
                            <a href="{{ route('proxies.edit', $host) }}" class="text-blue-600 hover:text-blue-900 font-medium">Bearbeiten</a>
                            
                            <form action="{{ route('proxies.destroy', $host) }}" method="POST" onsubmit="return confirm('Sicher?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">Löschen</button>
                            </form>
                        </div>
                    </div>
                </div>
            </li>
        @empty
            <li class="px-4 py-8 text-center text-gray-500">
                Noch keine Proxy Hosts vorhanden.
            </li>
        @endforelse
    </ul>
</div>
@endsection
