<div class="space-y-8">
    <!-- Basiskonfiguration -->
    <div class="bg-white shadow-sm border border-gray-100 rounded-2xl p-6 lg:p-8">
        <div class="md:grid md:grid-cols-3 md:gap-8">
            <div>
                <h3 class="text-lg font-bold text-gray-800">1. Basiskonfiguration</h3>
                <p class="mt-2 text-sm text-gray-500 leading-relaxed">Definieren Sie den Namen und den technischen Modus für diesen Proxy.</p>
            </div>
            <div class="mt-5 md:mt-0 md:col-span-2 space-y-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Anzeigename</label>
                    <input type="text" name="name" value="{{ old('name', $proxy->name ?? '') }}" placeholder="z.B. Exchange Verbund" class="block w-full border-gray-200 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-3 border" required>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Proxy-Typ</label>
                        <select name="mode" id="mode-select" class="block w-full border-gray-200 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-3 border">
                            <option value="http" {{ old('mode', $proxy->mode ?? '') == 'http' ? 'selected' : '' }}>Web / Reverse Proxy (HTTP)</option>
                            <option value="tcp" {{ old('mode', $proxy->mode ?? '') == 'tcp' ? 'selected' : '' }}>TLS Stream / Passthrough (TCP)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Load-Balancing</label>
                        <select name="balance_algorithm" class="block w-full border-gray-200 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-3 border">
                            <option value="roundrobin" {{ old('balance_algorithm', $proxy->balance_algorithm ?? '') == 'roundrobin' ? 'selected' : '' }}>Round Robin (Gleichmäßig)</option>
                            <option value="leastconn" {{ old('balance_algorithm', $proxy->balance_algorithm ?? '') == 'leastconn' ? 'selected' : '' }}>Least Connections</option>
                            <option value="source" {{ old('balance_algorithm', $proxy->balance_algorithm ?? '') == 'source' ? 'selected' : '' }}>Source IP (Sticky)</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Domains / Hostnames -->
    <div class="bg-white shadow-sm border border-gray-100 rounded-2xl p-6 lg:p-8">
        <div class="md:grid md:grid-cols-3 md:gap-8">
            <div>
                <h3 class="text-lg font-bold text-gray-800">2. Domains / Hostnames</h3>
                <p class="mt-2 text-sm text-gray-500 leading-relaxed">Fügen Sie eine oder mehrere Domains hinzu, auf die dieser Proxy reagieren soll.</p>
            </div>
            <div class="mt-5 md:mt-0 md:col-span-2">
                <div id="hostnames-container" class="space-y-3">
                    @php
                        $hostnames = old('hostnames', $proxy->hostnames ?? ['']);
                        if(empty($hostnames)) $hostnames = [''];
                    @endphp
                    @foreach($hostnames as $hn)
                        <div class="flex items-center space-x-2 hostname-row">
                            <div class="relative flex-grow">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                                </span>
                                <input type="text" name="hostnames[]" value="{{ $hn }}" placeholder="z.B. app.domain.de" class="block w-full border-gray-200 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm pl-10 p-3 border">
                            </div>
                            <button type="button" onclick="this.closest('.hostname-row').remove()" class="p-3 text-red-400 hover:text-red-600 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>
                    @endforeach
                </div>
                <button type="button" onclick="addHostname()" class="mt-4 inline-flex items-center text-sm font-bold text-indigo-600 hover:text-indigo-800 transition">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Weitere Domain hinzufügen
                </button>
            </div>
        </div>
    </div>

    <!-- Netzwerk & SSL -->
    <div class="bg-white shadow-sm border border-gray-100 rounded-2xl p-6 lg:p-8">
        <div class="md:grid md:grid-cols-3 md:gap-8">
            <div>
                <h3 class="text-lg font-bold text-gray-800">3. Netzwerk & SSL</h3>
                <p class="mt-2 text-sm text-gray-500 leading-relaxed">Eingangs-Port und Verschlüsselung festlegen.</p>
            </div>
            <div class="mt-5 md:mt-0 md:col-span-2 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Listen-Port</label>
                        <input type="number" name="listen_port" value="{{ old('listen_port', $proxy->listen_port ?? 443) }}" class="block w-full border-gray-200 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-3 border" required>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Listen-IP</label>
                        <input type="text" name="listen_address" value="{{ old('listen_address', $proxy->listen_address ?? '*') }}" class="block w-full border-gray-200 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-3 border" required>
                    </div>
                </div>

                <div id="ssl-section" class="pt-6 border-t border-gray-50 space-y-6">
                    <div class="flex items-start p-4 bg-indigo-50/50 rounded-xl border border-indigo-100">
                        <div class="flex items-center h-5">
                            <input name="tls_termination" type="checkbox" value="1" {{ old('tls_termination', $proxy->tls_termination ?? false) ? 'checked' : '' }} class="focus:ring-indigo-500 h-5 w-5 text-indigo-600 border-gray-300 rounded-lg">
                        </div>
                        <div class="ml-3 text-sm">
                            <label class="font-bold text-indigo-900">TLS-Terminierung am HAProxy</label>
                            <p class="text-indigo-700/70 mt-1">Eingehende SSL-Verbindungen hier entschlüsseln (erfordert Zertifikat).</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">SSL-Zertifikat (.pem)</label>
                        <select name="certificate_path" class="block w-full border-gray-200 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-3 border">
                            <option value="">-- Kein Zertifikat --</option>
                            @foreach($certificates as $cert)
                                <option value="{{ $cert->path }}" {{ old('certificate_path', $proxy->certificate_path ?? '') == $cert->path ? 'selected' : '' }}>
                                    {{ $cert->domain }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Backend Nodes -->
    <div class="bg-white shadow-sm border border-gray-100 rounded-2xl p-6 lg:p-8">
        <div class="md:grid md:grid-cols-3 md:gap-8">
            <div>
                <h3 class="text-lg font-bold text-gray-800">4. Backend Ziel-Server</h3>
                <p class="mt-2 text-sm text-gray-500 leading-relaxed">Geben Sie die internen Server an, zu denen die Anfragen geleitet werden sollen.</p>
            </div>
            <div class="mt-5 md:mt-0 md:col-span-2">
                <div id="backends-container" class="space-y-4">
                    @php
                        $backends = old('backends', isset($proxy) ? $proxy->backends->toArray() : [['name' => 'node-1', 'address' => '', 'port' => 443]]);
                    @endphp
                    @foreach($backends as $index => $be)
                        <div class="flex items-center space-x-3 backend-row p-4 bg-gray-50 rounded-2xl border border-gray-100">
                            <input type="text" name="backends[{{$index}}][name]" value="{{ $be['name'] }}" placeholder="Name" class="block w-1/4 border-gray-200 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border" required>
                            <input type="text" name="backends[{{$index}}][address]" value="{{ $be['address'] }}" placeholder="IP (z.B. 10.0.0.5)" class="block w-2/4 border-gray-200 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border" required>
                            <input type="number" name="backends[{{$index}}][port]" value="{{ $be['port'] }}" placeholder="Port" class="block w-1/4 border-gray-200 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border" required>
                            <button type="button" onclick="this.closest('.backend-row').remove()" class="text-red-400 hover:text-red-600 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                    @endforeach
                </div>
                <button type="button" onclick="addBackend()" class="mt-4 inline-flex items-center text-sm font-bold text-indigo-600 hover:text-indigo-800 transition">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Weiteres Backend hinzufügen
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function addHostname() {
        const container = document.getElementById('hostnames-container');
        const div = document.createElement('div');
        div.className = 'flex items-center space-x-2 hostname-row mt-2';
        div.innerHTML = `
            <div class="relative flex-grow">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                </span>
                <input type="text" name="hostnames[]" placeholder="weitere-domain.de" class="block w-full border-gray-200 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm pl-10 p-3 border">
            </div>
            <button type="button" onclick="this.closest('.hostname-row').remove()" class="p-3 text-red-400 hover:text-red-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            </button>
        `;
        container.appendChild(div);
    }

    let backendIndex = {{ count($backends) }};
    function addBackend() {
        const container = document.getElementById('backends-container');
        const div = document.createElement('div');
        div.className = 'flex items-center space-x-3 backend-row p-4 bg-gray-50 rounded-2xl border border-gray-100 mt-3';
        div.innerHTML = `
            <input type="text" name="backends[${backendIndex}][name]" placeholder="Name" class="block w-1/4 border-gray-200 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border" required>
            <input type="text" name="backends[${backendIndex}][address]" placeholder="IP" class="block w-2/4 border-gray-200 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border" required>
            <input type="number" name="backends[${backendIndex}][port]" placeholder="Port" class="block w-1/4 border-gray-200 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border" required>
            <button type="button" onclick="this.closest('.backend-row').remove()" class="text-red-400 hover:text-red-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        `;
        container.appendChild(div);
        backendIndex++;
    }

    const modeSelect = document.getElementById('mode-select');
    const sslSection = document.getElementById('ssl-section');
    function updateVisibility() {
        sslSection.style.display = modeSelect.value === 'tcp' ? 'none' : 'block';
    }
    modeSelect.addEventListener('change', updateVisibility);
    updateVisibility();
</script>
