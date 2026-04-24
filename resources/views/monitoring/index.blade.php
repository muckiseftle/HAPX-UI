@extends('layouts.app')

@section('header', 'Live Performance Dashboard')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Quick Stats Row -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Live Sitzungen</p>
        <div class="flex items-center justify-between">
            <h3 id="stat-conn" class="text-2xl font-bold text-indigo-600">0</h3>
            <div class="p-2 bg-indigo-50 rounded-lg">
                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Traffic In</p>
        <div class="flex items-center justify-between">
            <h3 id="stat-bin" class="text-2xl font-bold text-emerald-600">0 KB/s</h3>
            <div class="p-2 bg-emerald-50 rounded-lg">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path></svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Traffic Out</p>
        <div class="flex items-center justify-between">
            <h3 id="stat-bout" class="text-2xl font-bold text-amber-600">0 KB/s</h3>
            <div class="p-2 bg-amber-50 rounded-lg">
                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path></svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Status</p>
        <div class="flex items-center justify-between">
            <h3 class="text-2xl font-bold text-gray-800">Online</h3>
            <div class="flex h-3 w-3 relative">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-bold text-gray-800 mb-6">Sitzungsverlauf</h2>
        <div class="relative h-64">
            <canvas id="connectionsChart"></canvas>
        </div>
    </div>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-bold text-gray-800 mb-6">Bandbreite (Live)</h2>
        <div class="relative h-64">
            <canvas id="trafficChart"></canvas>
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
        try {
            const response = await fetch('{{ route('monitoring.live') }}');
            lastData = await response.json();

            document.getElementById('stat-conn').textContent = lastData.stats.connections;
            document.getElementById('stat-bin').textContent = formatBytes(lastData.stats.bin);
            document.getElementById('stat-bout').textContent = formatBytes(lastData.stats.bout);

            const maxPoints = 30;
            connectionsChart.data.labels.push(lastData.time);
            trafficChart.data.labels.push(lastData.time);
            if (connectionsChart.data.labels.length > maxPoints) {
                connectionsChart.data.labels.shift();
                trafficChart.data.labels.shift();
            }

            connectionsChart.data.datasets[0].data.push(lastData.stats.connections);
            if (connectionsChart.data.datasets[0].data.length > maxPoints) connectionsChart.data.datasets[0].data.shift();
            
            trafficChart.data.datasets[0].data.push(lastData.stats.bin);
            trafficChart.data.datasets[1].data.push(lastData.stats.bout);
            if (trafficChart.data.datasets[0].data.length > maxPoints) {
                trafficChart.data.datasets[0].data.shift();
                trafficChart.data.datasets[1].data.shift();
            }

            connectionsChart.update('none');
            trafficChart.update('none');

            document.getElementById('session-count').textContent = `${lastData.sessions.length} Sitzungen aktiv`;
            renderTable();

        } catch (e) { console.error("Update failed", e); }
    }

    setInterval(updateLiveStats, 2000);
</script>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
</style>
@endsection
