<?php

namespace App\Console\Commands;

use App\Models\PerformanceMetric;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class CollectHAProxyStats extends Command
{
    protected $signature = 'hapx:stats';
    protected $description = 'Fetch stats from HAProxy socket and store in DB';

    public function handle()
    {
        // Wir nutzen socat um mit dem Socket zu sprechen
        $result = Process::run("echo 'show stat' | sudo socat stdio /run/haproxy/admin.sock");
        
        if ($result->failed()) {
            $this->error("Failed to connect to HAProxy socket.");
            return;
        }

        $lines = explode("\n", $result->output());
        $metrics = [
            'connections' => 0,
            'bytes_in' => 0,
            'bytes_out' => 0,
            'req_rate' => 0,
            'avg_response' => 0,
            'count' => 0,
        ];

        foreach ($lines as $line) {
            if (empty($line) || str_starts_with($line, '#')) continue;
            
            $fields = explode(',', $line);
            
            // Wir summieren die Werte aller Frontends
            if (isset($fields[1]) && $fields[1] === 'FRONTEND') {
                $metrics['connections'] += (int)$fields[4]; // scur (current sessions)
                $metrics['bytes_in'] += (int)$fields[8];    // bin
                $metrics['bytes_out'] += (int)$fields[9];   // bout
                $metrics['req_rate'] += (int)$fields[33];   // rate
            }
        }

        PerformanceMetric::create([
            'connections' => $metrics['connections'],
            'bytes_in' => $metrics['bytes_in'],
            'bytes_out' => $metrics['bytes_out'],
            'requests_per_second' => $metrics['req_rate'],
            'avg_response_ms' => 0, // Benötigt Modus HTTP für präzise Messung
        ]);

        $this->info("Stats collected.");
    }
}
