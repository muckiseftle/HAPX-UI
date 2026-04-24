@extends('layouts.app')

@section('header', 'Zertifikatsverwaltung')

@section('content')
<div class="space-y-8">
    <!-- Header mit Actions -->
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800">Übersicht</h2>
        <div class="flex space-x-2">
            <a href="{{ route('certificates.sync') }}" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition text-sm flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                Sync Filesystem
            </a>
        </div>
    </div>

    <!-- Zertifikats-Liste -->
    <div class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-100">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Domain</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Typ / Methode</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Ablaufdatum</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aktionen</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse ($certificates as $cert)
                        <tr class="hover:bg-gray-50 transition duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $cert->domain }}</div>
                                <div class="text-xs text-gray-400">{{ basename($cert->path) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $cert->type === 'letsencrypt' ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $cert->type === 'letsencrypt' ? 'Let\'s Encrypt' : 'Self-Signed' }}
                                </span>
                                @if($cert->type === 'letsencrypt')
                                    <span class="ml-1 text-[10px] text-gray-400 uppercase tracking-tighter">
                                        ({{ $cert->validation_method }})
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($cert->status === 'active')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <span class="w-2 h-2 mr-1.5 bg-green-500 rounded-full"></span>
                                        Aktiv
                                    </span>
                                @elseif($cert->status === 'pending')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <span class="w-2 h-2 mr-1.5 bg-yellow-500 rounded-full animate-pulse"></span>
                                        Ausstehend
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <span class="w-2 h-2 mr-1.5 bg-red-500 rounded-full"></span>
                                        Fehlgeschlagen
                                    </span>
                                @endif
                                
                                @if($cert->error_message)
                                    <div class="text-[10px] text-red-500 mt-1 max-w-xs truncate" title="{{ $cert->error_message }}">
                                        {{ $cert->error_message }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($cert->expires_at)
                                    <span class="{{ $cert->isNearingExpiry() ? 'text-orange-600 font-bold' : '' }}">
                                        {{ $cert->expires_at->format('d.m.Y') }}
                                    </span>
                                    <div class="text-[10px] text-gray-400">
                                        {{ $cert->expires_at->diffForHumans() }}
                                    </div>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                @if($cert->type === 'letsencrypt')
                                    <form action="{{ route('certificates.renew', $cert) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-indigo-600 hover:text-indigo-900" title="Erneuern">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                        </button>
                                    </form>
                                @endif
                                <form action="{{ route('certificates.destroy') }}" method="POST" class="inline" onsubmit="return confirm('Zertifikat wirklich löschen?')">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="id" value="{{ $cert->id }}">
                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                Keine Zertifikate gefunden. Erstellen Sie ein neues Zertifikat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Erstellen Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Let's Encrypt (Modern) -->
        <div class="bg-white shadow-xl rounded-2xl p-8 border border-gray-100">
            <div class="flex items-center mb-6">
                <div class="bg-indigo-100 p-3 rounded-xl mr-4">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04currM12 21.355r1"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21a9 9 0 009-9H3a9 9 0 009 9z"></path></svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Let's Encrypt</h2>
                    <p class="text-sm text-gray-500">Zertifikat beantragen</p>
                </div>
            </div>

            <form action="{{ route('certificates.letsencrypt.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Domain</label>
                    <input type="text" name="domain" placeholder="example.com oder *.example.com" class="w-full border-gray-200 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-3 border" required>
                </div>

                <div class="flex p-1 bg-gray-100 rounded-xl mb-4">
                    <button type="button" onclick="setMethod('http')" id="btn-http" class="flex-1 py-2 text-sm font-bold rounded-lg transition bg-white shadow text-indigo-600">
                        HTTP-01 (Einfach)
                    </button>
                    <button type="button" onclick="setMethod('dns')" id="btn-dns" class="flex-1 py-2 text-sm font-bold rounded-lg transition text-gray-500 hover:text-gray-700">
                        DNS-01 (Wildcard/API)
                    </button>
                </div>
                <input type="hidden" name="validation_method" id="validation_method" value="http">

                <div id="dns_section" class="hidden space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">DNS Anbieter</label>
                        <select name="dns_provider" id="dns_provider" onchange="updateDnsFields()" class="w-full border-gray-200 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-3 border">
                            <option value="dns_cf">Cloudflare</option>
                            <option value="dns_hetzner">Hetzner</option>
                            <option value="dns_dgon">DigitalOcean</option>
                            <option value="dns_doapi">Domain-Offensive (do.de)</option>
                            <option value="dns_ali">Alibaba Cloud</option>
                        </select>
                    </div>

                    <div id="dns_creds" class="space-y-4 bg-gray-50 p-4 rounded-xl">
                        <!-- Wird per JS befüllt -->
                    </div>
                </div>

                <div id="http_info" class="text-xs text-gray-500 bg-blue-50 p-4 rounded-xl border border-blue-100 leading-relaxed">
                    <strong>Hinweis:</strong> Bei der HTTP-Validierung muss Port 80 der Domain bereits auf diesen Server zeigen. HAProxy leitet die Anfrage automatisch intern weiter.
                </div>

                <button type="submit" class="w-full bg-indigo-600 text-white px-6 py-3 rounded-xl font-bold shadow-lg hover:bg-indigo-700 transition transform hover:-translate-y-0.5">
                    Zertifikat beantragen
                </button>
            </form>
        </div>

        <!-- Self-Signed -->
        <div class="bg-white shadow-xl rounded-2xl p-8 border border-gray-100">
            <div class="flex items-center mb-6">
                <div class="bg-gray-100 p-3 rounded-xl mr-4">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Self-Signed</h2>
                    <p class="text-sm text-gray-500">Für interne Testzwecke</p>
                </div>
            </div>

            <form action="{{ route('certificates.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Common Name (CN)</label>
                    <input type="text" name="domain" placeholder="localhost oder dev.local" class="w-full border-gray-200 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm p-3 border" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Gültigkeit (Tage)</label>
                    <input type="number" name="days" value="3650" class="w-full border-gray-200 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm p-3 border">
                </div>
                <button type="submit" class="w-full bg-gray-800 text-white px-6 py-3 rounded-xl font-bold shadow-lg hover:bg-gray-900 transition transform hover:-translate-y-0.5">
                    Generieren
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function setMethod(method) {
    document.getElementById('validation_method').value = method;
    const dnsSection = document.getElementById('dns_section');
    const httpInfo = document.getElementById('http_info');
    const btnHttp = document.getElementById('btn-http');
    const btnDns = document.getElementById('btn-dns');

    if (method === 'dns') {
        dnsSection.classList.remove('hidden');
        httpInfo.classList.add('hidden');
        btnDns.classList.add('bg-white', 'shadow', 'text-indigo-600');
        btnDns.classList.remove('text-gray-500');
        btnHttp.classList.remove('bg-white', 'shadow', 'text-indigo-600');
        btnHttp.classList.add('text-gray-500');
        updateDnsFields();
    } else {
        dnsSection.classList.add('hidden');
        httpInfo.classList.remove('hidden');
        btnHttp.classList.add('bg-white', 'shadow', 'text-indigo-600');
        btnHttp.classList.remove('text-gray-500');
        btnDns.classList.remove('bg-white', 'shadow', 'text-indigo-600');
        btnDns.classList.add('text-gray-500');
    }
}

function updateDnsFields() {
    const provider = document.getElementById('dns_provider').value;
    const credsDiv = document.getElementById('dns_creds');
    let html = '';

    if (provider === 'dns_cf') {
        html = `
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">CF_Token (API Token)</label>
                <input type="password" name="dns_credentials[CF_Token]" class="w-full border-gray-200 rounded-lg text-sm p-2 border" required>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">CF_Account_ID</label>
                <input type="text" name="dns_credentials[CF_Account_ID]" class="w-full border-gray-200 rounded-lg text-sm p-2 border">
            </div>
        `;
    } else if (provider === 'dns_hetzner') {
        html = `
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">HETZNER_Token</label>
                <input type="password" name="dns_credentials[HETZNER_Token]" class="w-full border-gray-200 rounded-lg text-sm p-2 border" required>
            </div>
        `;
    } else if (provider === 'dns_dgon') {
        html = `
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">DO_API_KEY (DigitalOcean Token)</label>
                <input type="password" name="dns_credentials[DO_API_KEY]" class="w-full border-gray-200 rounded-lg text-sm p-2 border" required>
            </div>
        `;
    } else if (provider === 'dns_doapi') {
        html = `
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">DO_LETOKEN (do.de API Token)</label>
                <input type="password" name="dns_credentials[DO_LETOKEN]" class="w-full border-gray-200 rounded-lg text-sm p-2 border" required>
            </div>
        `;
    } else if (provider === 'dns_ali') {
        html = `
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Ali_Key</label>
                <input type="text" name="dns_credentials[Ali_Key]" class="w-full border-gray-200 rounded-lg text-sm p-2 border" required>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Ali_Secret</label>
                <input type="password" name="dns_credentials[Ali_Secret]" class="w-full border-gray-200 rounded-lg text-sm p-2 border" required>
            </div>
        `;
    }

    credsDiv.innerHTML = html;
}

document.addEventListener('DOMContentLoaded', () => {
    // Initial state is HTTP
});
</script>
@endsection
