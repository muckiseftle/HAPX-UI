@extends('layouts.app')

@section('header', 'Live Performance Dashboard')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="flex justify-between items-center mb-8">
    <div class="flex space-x-2 bg-gray-100 p-1 rounded-lg">
        <a href="?range=live" class="px-4 py-2 rounded-md text-sm font-medium {{ $range === 'live' ? 'bg-white shadow-sm text-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">Live</a>
        <a href="?range=24h" class="px-4 py-2 rounded-md text-sm font-medium {{ $range === '24h' ? 'bg-white shadow-sm text-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">24h</a>
        <a href="?range=7d" class="px-4 py-2 rounded-md text-sm font-medium {{ $range === '7d' ? 'bg-white shadow-sm text-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">7 Tage</a>
    </div>
    <div class="flex items-center space-x-2 text-sm text-gray-500">
        <span class="relative flex h-3 w-3">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
        </span>
        <span id="update-time">Letztes Update: {{ now()->format('H:i:s') }}</span>
    </div>
</div>

<!-- Quick Stats Row -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
    <!-- Connections -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Live Sitzungen</p>
        <div class="flex items-center justify-between">
            <h3 id="stat-conn" class="text-2xl font-bold text-indigo-600">0</h3>
            <div class="p-2 bg-indigo-50 rounded-lg">
                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            </div>
        </div>
    </div>
    <!-- Traffic In -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Traffic In</p>
        <div class="flex items-center justify-between">
            <h3 id="stat-bin" class="text-2xl font-bold text-emerald-600">0 KB/s</h3>
            <div class="p-2 bg-emerald-50 rounded-lg">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path></svg>
            </div>
        </div>
    </div>
    <!-- CPU Usage -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">CPU Auslastung</p>
        <div class="flex items-center justify-between">
            <h3 id="stat-cpu" class="text-2xl font-bold text-blue-600">0%</h3>
            <div class="p-2 bg-blue-50 rounded-lg">
                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            </div>
        </div>
    </div>
    <!-- RAM Usage -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">RAM Nutzung</p>
        <div class="flex items-center justify-between">
            <h3 id="stat-ram" class="text-2xl font-bold text-purple-600">0%</h3>
            <div class="p-2 bg-purple-50 rounded-lg">
                <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>
    </div>
    <!-- Disk Usage -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Disk Nutzung</p>
        <div class="flex items-center justify-between">
            <h3 id="stat-disk" class="text-2xl font-bold text-gray-700">0%</h3>
            <div class="p-2 bg-gray-50 rounded-lg">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-bold text-gray-800 mb-6">Verbindungen</h2>
        <div class="relative h-64">
            <canvas id="connectionsChart"></canvas>
        </div>
    </div>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-bold text-gray-800 mb-6">Bandbreite</h2>
        <div class="relative h-64">
            <canvas id="trafficChart"></canvas>
        </div>
    </div>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-bold text-gray-800 mb-6">System Auslastung (%)</h2>
        <div class="relative h-64">
            <canvas id="systemChart"></canvas>
        </div>
    </div>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-bold text-gray-800 mb-6">Festplattenbelegung (%)</h2>
        <div class="relative h-64">
            <canvas id="diskChart"></canvas>
        </div>
    </div>
</div>

<!-- Active Connections Table -->
<div class="mt-8 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-6 py-5 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
        <h2 class="text-lg font-bold text-gray-800">Aktive Client Verbindungen</h2>
        <span class="text-xs font-bold bg-white border border-gray-200 px-3 py-1 rounded-full text-gray-500 shadow-sm" id="session-count">0 Sessions</span>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-gray-50/30">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Client IP</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Ziel Backend Node</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Aktivität</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-widest text-right">Details</th>
                </tr>
            </thead>
            <tbody id="session-table-body" class="divide-y divide-gray-50">
                <tr>
                    <td colspan="4" class="px-6 py-12 text-center text-gray-400">Initialisiere Echtzeit-Daten...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.color = '#94a3b8';

    const baseOptions = {
        responsive: true,
        maintainAspectRatio: false,
        animation: { duration: 800, easing: 'easeOutQuart' },
        plugins: {
            legend: { display: false },
            tooltip: { backgroundColor: '#1e293b', padding: 12, cornerRadius: 8, displayColors: false }
        },
        scales: {
            y: { beginAtZero: true, grid: { color: '#f1f5f9', drawBorder: false } },
            x: { grid: { display: false }, ticks: { padding: 10, maxRotation: 0 } }
        }
    };

    const connectionsChart = new Chart(document.getElementById('connectionsChart'), {
        type: 'line',
        data: {
            labels: {!! json_encode($labels) !!},
            datasets: [{
                data: {!! json_encode($connections) !!},
                borderColor: '#6366f1',
                borderWidth: 3,
                pointRadius: 0,
                fill: true,
                backgroundColor: (context) => {
                    const gradient = context.chart.ctx.createLinearGradient(0, 0, 0, 400);
                    gradient.addColorStop(0, 'rgba(99, 102, 241, 0.15)');
                    gradient.addColorStop(1, 'rgba(99, 102, 241, 0)');
                    return gradient;
                },
                tension: 0.4
            }]
        },
        options: baseOptions
    });

    const trafficChart = new Chart(document.getElementById('trafficChart'), {
        type: 'line',
        data: {
            labels: {!! json_encode($labels) !!},
            datasets: [
                { label: 'In', data: {!! json_encode($trafficIn) !!}, borderColor: '#10b981', borderWidth: 3, pointRadius: 0, tension: 0.4 },
                { label: 'Out', data: {!! json_encode($trafficOut) !!}, borderColor: '#f59e0b', borderWidth: 3, pointRadius: 0, tension: 0.4 }
            ]
        },
        options: baseOptions
    });

    const systemChart = new Chart(document.getElementById('systemChart'), {
        type: 'line',
        data: {
            labels: {!! json_encode($labels) !!},
            datasets: [
                { label: 'CPU', data: {!! json_encode($cpu) !!}, borderColor: '#3b82f6', borderWidth: 3, pointRadius: 0, tension: 0.4 },
                { label: 'RAM', data: {!! json_encode($ram) !!}, borderColor: '#a855f7', borderWidth: 3, pointRadius: 0, tension: 0.4 }
            ]
        },
        options: { ...baseOptions, scales: { ...baseOptions.scales, y: { ...baseOptions.scales.y, max: 100 } } }
    });

    const diskChart = new Chart(document.getElementById('diskChart'), {
        type: 'line',
        data: {
            labels: {!! json_encode($labels) !!},
            datasets: [
                { label: 'Disk', data: {!! json_encode($disk) !!}, borderColor: '#64748b', borderWidth: 3, pointRadius: 0, tension: 0.4 }
            ]
        },
        options: { ...baseOptions, scales: { ...baseOptions.scales, y: { ...baseOptions.scales.y, max: 100 } } }
    });

    function formatBytes(bytes) {
        if (bytes === 0) return '0 B/s';
        const k = 1024;
        const sizes = ['B/s', 'KB/s', 'MB/s', 'GB/s'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
    }

    // State für aufgeklappte Reihen
    const expandedGroups = new Set();

    function toggleGroup(groupId) {
        if (expandedGroups.has(groupId)) {
            expandedGroups.delete(groupId);
        } else {
            expandedGroups.add(groupId);
        }
        renderTable();
    }

    let lastData = { sessions: [] };

    function renderTable() {
        const tbody = document.getElementById('session-table-body');
        
        // Gruppierung: IP + Backend Node
        const groups = {};
        lastData.sessions.forEach(s => {
            const gid = `${s.ip}_${s.server}`;
            if (!groups[gid]) {
                groups[gid] = { ip: s.ip, server: s.server, sessions: [] };
            }
            groups[gid].sessions.push(s);
        });

        const sortedGroups = Object.values(groups).sort((a, b) => b.sessions.length - a.sessions.length);

        if (sortedGroups.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="px-6 py-12 text-center text-gray-400">Keine aktiven Verbindungen</td></tr>';
            return;
        }

        let html = '';
        sortedGroups.forEach(g => {
            const gid = `${g.ip}_${g.server}`;
            const isExpanded = expandedGroups.has(gid);
            
            // Hauptzeile
            html += `
                <tr class="hover:bg-gray-50/80 transition-colors cursor-pointer" onclick="toggleGroup('${gid}')">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-700 font-mono">${g.ip}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-indigo-600 font-semibold">${g.server}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            ${g.sessions.length} aktive Ports
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-indigo-500">
                        <svg class="w-5 h-5 inline transition-transform ${isExpanded ? 'rotate-180' : ''}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </td>
                </tr>
            `;

            // Detailzeile (wenn ausgeklappt)
            if (isExpanded) {
                html += `
                    <tr class="bg-gray-50/30">
                        <td colspan="4" class="px-8 py-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                ${g.sessions.map(s => `
                                    <div class="bg-white border border-gray-100 rounded-lg p-3 text-xs shadow-sm">
                                        <div class="flex justify-between mb-1">
                                            <span class="text-gray-400">Port:</span>
                                            <span class="font-bold text-gray-700">${s.port}</span>
                                        </div>
                                        <div class="flex justify-between mb-1">
                                            <span class="text-gray-400">Proxy:</span>
                                            <span class="text-indigo-500 font-semibold">${s.frontend}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-400">Laufzeit:</span>
                                            <span class="text-gray-500">${s.age}</span>
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                        </td>
                    </tr>
                `;
            }
        });

        tbody.innerHTML = html;
    }

    async function updateLiveStats() {
        if ("{{ $range }}" !== "live") return;

        try {
            const response = await fetch('{{ route('monitoring.live') }}');
            lastData = await response.json();

            document.getElementById('stat-conn').textContent = lastData.stats.connections;
            document.getElementById('stat-bin').textContent = formatBytes(lastData.stats.bin);
            document.getElementById('stat-cpu').textContent = lastData.system.cpu + '%';
            document.getElementById('stat-ram').textContent = lastData.system.ram + '%';
            document.getElementById('stat-disk').textContent = lastData.system.disk + '%';
            document.getElementById('update-time').textContent = 'Letztes Update: ' + lastData.time;

            const maxPoints = 30;
            
            // Update charts
            [connectionsChart, trafficChart, systemChart, diskChart].forEach(chart => {
                chart.data.labels.push(lastData.time);
                if (chart.data.labels.length > maxPoints) chart.data.labels.shift();
            });

            connectionsChart.data.datasets[0].data.push(lastData.stats.connections);
            if (connectionsChart.data.datasets[0].data.length > maxPoints) connectionsChart.data.datasets[0].data.shift();
            
            trafficChart.data.datasets[0].data.push(lastData.stats.bin);
            trafficChart.data.datasets[1].data.push(lastData.stats.bout);
            if (trafficChart.data.datasets[0].data.length > maxPoints) {
                trafficChart.data.datasets[0].data.shift();
                trafficChart.data.datasets[1].data.shift();
            }

            systemChart.data.datasets[0].data.push(lastData.system.cpu);
            systemChart.data.datasets[1].data.push(lastData.system.ram);
            if (systemChart.data.datasets[0].data.length > maxPoints) {
                systemChart.data.datasets[0].data.shift();
                systemChart.data.datasets[1].data.shift();
            }

            diskChart.data.datasets[0].data.push(lastData.system.disk);
            if (diskChart.data.datasets[0].data.length > maxPoints) diskChart.data.datasets[0].data.shift();

            [connectionsChart, trafficChart, systemChart, diskChart].forEach(chart => chart.update('none'));

            document.getElementById('session-count').textContent = `${lastData.sessions.length} Sitzungen aktiv`;
            renderTable();

        } catch (e) { console.error("Update failed", e); }
    }

    if ("{{ $range }}" === "live") {
        setInterval(updateLiveStats, 2000);
    }
</script>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
</style>
@endsection
