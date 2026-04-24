<?php

namespace App\Services;

use App\Models\Certificate;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CertificateService
{
    protected string $certDir = '/etc/haproxy/certs';
    protected string $acmePath = '/home/itm/.acme.sh/acme.sh';

    /**
     * List all certificates from the database.
     */
    public function listCertificates()
    {
        return Certificate::orderBy('domain')->get();
    }

    /**
     * Create a self-signed certificate.
     */
    public function createSelfSigned(string $domain, int $days = 3650): Certificate
    {
        $tempKey = storage_path("app/{$domain}.key");
        $tempCrt = storage_path("app/{$domain}.crt");
        $finalPem = "{$this->certDir}/{$domain}.pem";

        try {
            // Generate Key and Cert
            $cmd = "openssl req -x509 -newkey rsa:2048 -keyout $tempKey -out $tempCrt -days $days -nodes -subj \"/CN=$domain\"";
            Process::run($cmd)->throw();

            // Combine to PEM
            $combined = File::get($tempKey) . "\n" . File::get($tempCrt);
            
            // Write to /tmp first then move with sudo
            $tmpPem = storage_path("app/{$domain}.pem");
            File::put($tmpPem, $combined);
            
            Process::run("sudo mv $tmpPem $finalPem")->throw();
            Process::run("sudo chmod 600 $finalPem")->throw();

            File::delete([$tempKey, $tempCrt]);

            $info = $this->getCertificateInfo($finalPem);

            return Certificate::updateOrCreate(
                ['domain' => $domain],
                [
                    'type' => 'self-signed',
                    'status' => 'active',
                    'path' => $finalPem,
                    'expires_at' => $info['expires_at'] ?? now()->addDays($days),
                    'last_renewed_at' => now(),
                    'error_message' => null,
                ]
            );
        } catch (\Exception $e) {
            Log::error("Cert creation failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Request a certificate via Let's Encrypt using DNS or HTTP challenge.
     */
    public function requestLetsEncrypt(Certificate $certificate): bool
    {
        $domain = $certificate->domain;
        $certificate->update(['status' => 'pending', 'error_message' => null]);

        try {
            if ($certificate->validation_method === 'http') {
                // acme.sh --issue --standalone --httpport 8888 -d $domain
                $cmd = "{$this->acmePath} --issue --standalone --httpport 8888 -d " . escapeshellarg($domain) . " --force";
                $process = Process::run($cmd);
            } else {
                $provider = $certificate->dns_provider;
                $creds = $certificate->dns_credentials ?? [];
                $env = [];
                foreach ($creds as $key => $value) {
                    $env[$key] = $value;
                }
                // acme.sh --issue --dns $provider -d $domain
                $cmd = "{$this->acmePath} --issue --dns {$provider} -d " . escapeshellarg($domain) . " --force";
                $process = Process::withEnvironment($env)->run($cmd);
            }

            if ($process->failed()) {
                $certificate->update([
                    'status' => 'failed',
                    'error_message' => $process->errorOutput() ?: $process->output()
                ]);
                return false;
            }

            // Deploy/Install cert to HAProxy dir
            // acme.sh --install-cert -d $domain --fullchain-file /etc/haproxy/certs/$domain.pem
            $finalPem = "{$this->certDir}/{$domain}.pem";
            $installCmd = "{$this->acmePath} --install-cert -d " . escapeshellarg($domain) . " --fullchain-file " . escapeshellarg($finalPem);
            
            // Note: acme.sh might need sudo to write to /etc/haproxy/certs if not running as root
            // We'll use a temporary file and sudo mv to be safe
            $tmpFullChain = storage_path("app/{$domain}.fullchain.pem");
            $installTmpCmd = "{$this->acmePath} --install-cert -d " . escapeshellarg($domain) . " --fullchain-file " . escapeshellarg($tmpFullChain);
            
            $installProcess = Process::run($installTmpCmd);
            
            if ($installProcess->successful() && File::exists($tmpFullChain)) {
                Process::run("sudo mv $tmpFullChain $finalPem")->throw();
                Process::run("sudo chmod 600 $finalPem")->throw();
                
                $info = $this->getCertificateInfo($finalPem);
                $certificate->update([
                    'status' => 'active',
                    'path' => $finalPem,
                    'expires_at' => $info['expires_at'],
                    'last_renewed_at' => now(),
                ]);
                return true;
            }

            $certificate->update([
                'status' => 'failed',
                'error_message' => "Installation failed: " . $installProcess->errorOutput()
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error("LE Request failed: " . $e->getMessage());
            $certificate->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Parse certificate info using openssl.
     */
    public function getCertificateInfo(string $path): array
    {
        if (!File::exists($path)) {
            return [];
        }

        try {
            $result = Process::run("openssl x509 -enddate -noout -in " . escapeshellarg($path))->throw();
            // notAfter=Apr 24 14:04:40 2036 GMT
            $output = $result->output();
            $dateStr = str_replace('notAfter=', '', trim($output));
            $expiry = Carbon::parse($dateStr);

            return [
                'expires_at' => $expiry,
            ];
        } catch (\Exception $e) {
            Log::warning("Could not parse cert $path: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Sync database with files in /etc/haproxy/certs.
     */
    public function syncWithFileSystem(): void
    {
        if (!File::exists($this->certDir)) {
            return;
        }

        $files = File::files($this->certDir);
        foreach ($files as $file) {
            if ($file->getExtension() === 'pem') {
                $domain = $file->getBasename('.pem');
                $path = $file->getRealPath();
                $info = $this->getCertificateInfo($path);

                Certificate::updateOrCreate(
                    ['domain' => $domain],
                    [
                        'path' => $path,
                        'status' => 'active',
                        'expires_at' => $info['expires_at'] ?? null,
                        'type' => Certificate::where('domain', $domain)->value('type') ?? 'self-signed',
                    ]
                );
            }
        }
    }

    /**
     * Delete a certificate.
     */
    public function delete(int $id): bool
    {
        $certificate = Certificate::find($id);
        if (!$certificate) return false;

        if ($certificate->path && File::exists($certificate->path)) {
             Process::run("sudo rm " . escapeshellarg($certificate->path));
        }
        
        return $certificate->delete();
    }
}
