<?php

namespace App\Console\Commands;

use App\Models\PerformanceMetric;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class CollectHAProxyStats extends Command
{
    protected $signature = 'hapx:stats';
    protected $description = 'Fetch stats from HAProxy socket and system metrics, then store in DB';

    public function handle()
    {
        // 1. HAProxy Stats
        $haproxyStats = $this->getHAProxyStats();
        
        // 2. System Metrics
        $systemMetrics = $this->getSystemMetrics();

        PerformanceMetric::create([
            'connections'         => $haproxyStats['connections'],
            'bytes_in'            => $haproxyStats['bytes_in'],
            'bytes_out'           => $haproxyStats['bytes_out'],
            'requests_per_second' => $haproxyStats['req_rate'],
            'avg_response_ms'     => 0,
            'cpu_usage'           => $systemMetrics['cpu'],
            'ram_usage'           => $systemMetrics['ram'],
            'disk_usage'          => $systemMetrics['disk'],
        ]);

        $this->info("Stats collected successfully.");
    }

    protected function getHAProxyStats(): array
    {
        $socketPath = '/run/haproxy/admin.sock';
        $metrics = [
            'connections' => 0,
            'bytes_in' => 0,
            'bytes_out' => 0,
            'req_rate' => 0,
        ];

        // Try socat directly, fallback to sudo if needed
        $cmd = "echo 'show stat' | socat stdio unix-connect:$socketPath";
        $result = Process::run($cmd);

        if ($result->failed()) {
            $result = Process::run("sudo " . $cmd);
        }

        if ($result->successful()) {
            $lines = explode("\n", $result->output());
            foreach ($lines as $line) {
                if (empty($line) || str_starts_with($line, '#')) continue;
                $fields = explode(',', $line);
                if (isset($fields[1]) && $fields[1] === 'FRONTEND') {
                    $metrics['connections'] += (int)($fields[4] ?? 0);
                    $metrics['bytes_in']    += (int)($fields[8] ?? 0);
                    $metrics['bytes_out']   += (int)($fields[9] ?? 0);
                    $metrics['req_rate']    += (int)($fields[33] ?? 0);
                }
            }
        }

        return $metrics;
    }

    protected function getSystemMetrics(): array
    {
        // CPU Usage (Load Average 1m / cores * 100)
        $load = sys_getloadavg();
        $cores = (int) shell_exec('nproc') ?: 1;
        $cpu = round(($load[0] / $cores) * 100, 2);

        // RAM Usage
        $free = shell_exec('free');
        $free = (string)trim($free);
        $free_lines = explode("\n", $free);
        $ram_usage = 0;
        if (isset($free_lines[1])) {
            $mem = preg_split('/\s+/', $free_lines[1]);
            // mem[1] = total, mem[2] = used
            if (isset($mem[1]) && $mem[1] > 0) {
                $ram_usage = round(($mem[2] / $mem[1]) * 100, 2);
            }
        }

        // Disk Usage
        $disk_total = disk_total_space('/');
        $disk_free = disk_free_space('/');
        $disk_usage = 0;
        if ($disk_total > 0) {
            $disk_usage = round((($disk_total - $disk_free) / $disk_total) * 100, 2);
        }

        return [
            'cpu' => $cpu,
            'ram' => $ram_usage,
            'disk' => $disk_usage,
        ];
    }
}
