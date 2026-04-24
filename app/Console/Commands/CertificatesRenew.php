<?php

namespace App\Console\Commands;

use App\Models\Certificate;
use App\Services\CertificateService;
use Illuminate\Console\Command;

class CertificatesRenew extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'certs:renew';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Renew Let\'s Encrypt certificates nearing expiry';

    /**
     * Execute the console command.
     */
    public function handle(CertificateService $certs)
    {
        $this->info('Checking for certificates to renew...');

        $expiring = Certificate::where('type', 'letsencrypt')
            ->where(function($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '<', now()->addDays(30));
            })
            ->get();

        if ($expiring->isEmpty()) {
            $this->info('No certificates need renewal.');
            return 0;
        }

        foreach ($expiring as $cert) {
            $this->info("Renewing certificate for {$cert->domain}...");
            
            if ($certs->requestLetsEncrypt($cert)) {
                $this->info("Successfully renewed {$cert->domain}.");
            } else {
                $this->error("Failed to renew {$cert->domain}: {$cert->error_message}");
            }
        }

        return 0;
    }
}
