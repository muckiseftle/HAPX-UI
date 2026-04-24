<?php

namespace App\Http\Controllers;

use App\Models\PerformanceMetric;
use App\Models\ProxyHost;
use App\Services\HAProxyService;
use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    protected HAProxyService $haproxy;

    public function __construct(HAProxyService $haproxy)
    {
        $this->haproxy = $haproxy;
    }

    public function index()
    {
        $history = PerformanceMetric::latest()->limit(50)->get()->reverse();
        
        $labels = $history->map(fn($m) => $m->created_at->format('H:i:s'))->toArray();
        $connections = $history->pluck('connections')->toArray();
        $trafficIn = $history->pluck('bytes_in')->toArray();
        $trafficOut = $history->pluck('bytes_out')->toArray();

        $hosts = ProxyHost::with('backends')->get();

        return view('monitoring.index', compact('labels', 'connections', 'trafficIn', 'trafficOut', 'hosts'));
    }

    /**
     * API for live data polling.
     */
    public function liveData()
    {
        return response()->json([
            'stats' => $this->haproxy->getRealtimeStats(),
            'sessions' => $this->haproxy->getLiveSessions(),
            'time' => now()->format('H:i:s')
        ]);
    }
}
