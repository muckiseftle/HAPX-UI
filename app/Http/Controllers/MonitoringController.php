<?php

namespace App\Http\Controllers;

use App\Models\PerformanceMetric;
use App\Models\ProxyHost;
use App\Services\HAProxyService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MonitoringController extends Controller
{
    protected HAProxyService $haproxy;

    public function __construct(HAProxyService $haproxy)
    {
        $this->haproxy = $haproxy;
    }

    public function index(Request $request)
    {
        $range = $request->get('range', 'live'); // live, 24h, 7d
        
        $query = PerformanceMetric::query();

        if ($range === '24h') {
            $query->where('created_at', '>=', now()->subDay());
        } elseif ($range === '7d') {
            $query->where('created_at', '>=', now()->subDays(7));
        } else {
            // Live: Letzte 50 Datenpunkte
            $query->latest()->limit(50);
        }

        $history = $query->get();
        if ($range === 'live') {
            $history = $history->reverse();
        }

        $labels = $history->map(fn($m) => $m->created_at->format($range === 'live' ? 'H:i:s' : 'd.m H:i'))->toArray();
        $connections = $history->pluck('connections')->toArray();
        $trafficIn = $history->pluck('bytes_in')->toArray();
        $trafficOut = $history->pluck('bytes_out')->toArray();
        $cpu = $history->pluck('cpu_usage')->toArray();
        $ram = $history->pluck('ram_usage')->toArray();
        $disk = $history->pluck('disk_usage')->toArray();

        $hosts = ProxyHost::with('backends')->get();

        return view('monitoring.index', compact(
            'labels', 'connections', 'trafficIn', 'trafficOut', 
            'cpu', 'ram', 'disk', 'hosts', 'range'
        ));
    }

    public function liveData()
    {
        $system = $this->getCurrentSystemMetrics();
        return response()->json([
            'stats' => $this->haproxy->getRealtimeStats(),
            'system' => $system,
            'sessions' => $this->haproxy->getLiveSessions(),
            'time' => now()->format('H:i:s')
        ]);
    }

    protected function getCurrentSystemMetrics(): array
    {
        $load = sys_getloadavg();
        $cores = (int) shell_exec('nproc') ?: 1;
        $cpu = round(($load[0] / $cores) * 100, 2);

        $free = shell_exec('free');
        $free = (string)trim($free);
        $free_lines = explode("\n", $free);
        $ram_usage = 0;
        if (isset($free_lines[1])) {
            $mem = preg_split('/\s+/', $free_lines[1]);
            if (isset($mem[1]) && $mem[1] > 0) {
                $ram_usage = round(($mem[2] / $mem[1]) * 100, 2);
            }
        }

        $disk_total = disk_total_space('/');
        $disk_free = disk_free_space('/');
        $disk_usage = 0;
        if ($disk_total > 0) {
            $disk_usage = round((($disk_total - $disk_free) / $disk_total) * 100, 2);
        }

        return ['cpu' => $cpu, 'ram' => $ram_usage, 'disk' => $disk_usage];
    }
}
