<?php

namespace App\Console\Commands;

use App\Models\ProxyHost;
use App\Models\ProxyBackend;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ImportExistingConfig extends Command
{
    protected $signature = 'hapx:import';
    protected $description = 'Import existing HAProxy managed block into the database';

    public function handle()
    {
        $path = '/tmp/haproxy.cfg.tmp';
        if (!File::exists($path)) {
            $this->error("Temp HAProxy config not found at $path");
            return;
        }

        $content = File::get($path);
        
        if (preg_match('/# BEGIN HAPX-UI-MANAGED(.*)# END HAPX-UI-MANAGED/s', $content, $matches)) {
            $block = $matches[1];
            
            if (str_contains($block, 'itm-exchange-server')) {
                $host = ProxyHost::firstOrCreate([
                    'name' => 'itm-exchange-server',
                ], [
                    'mode' => 'tcp',
                    'listen_port' => 443,
                    'listen_address' => '*',
                    'is_active' => true,
                ]);

                if (preg_match('/server (.*?) ([\d\.]+):(\d+) check/', $block, $beMatches)) {
                    ProxyBackend::firstOrCreate([
                        'proxy_host_id' => $host->id,
                        'name' => $beMatches[1],
                    ], [
                        'address' => $beMatches[2],
                        'port' => $beMatches[3],
                        'is_active' => true,
                    ]);
                }
                
                $this->info("Imported itm-exchange-server successfully.");
            }
        } else {
            $this->warn("No managed block found to import.");
        }
        
        // Clean up
        File::delete($path);
    }
}
