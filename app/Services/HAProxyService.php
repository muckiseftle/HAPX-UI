<?php

namespace App\Services;

use App\Models\ProxyHost;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;

class HAProxyService
{
    protected string $configPath = '/etc/haproxy/haproxy.cfg';
    protected string $socketPath = '/run/haproxy/admin.sock';
    protected string $markerStart = '# BEGIN HAPX-UI-MANAGED';
    protected string $markerEnd = '# END HAPX-UI-MANAGED';

    public function syncConfig(): array
    {
        $hosts = ProxyHost::with('backends')->active()->get();
        $generatedContent = $this->renderConfig($hosts);
        $currentConfig = $this->readConfig();
        $newConfig = $this->updateManagedBlock($currentConfig, $generatedContent);
        
        // Write to temp file
        $tempPath = storage_path('app/haproxy.cfg.tmp');
        File::put($tempPath, $newConfig);

        // Docker check
        if (file_exists('/.dockerenv')) {
            File::copy($tempPath, $this->configPath);
        } else {
            Process::run("sudo cp {$this->configPath} {$this->configPath}.bak");
            Process::run("sudo mv {$tempPath} {$this->configPath}");
        }

        $validation = $this->validate();
        if ($validation['exit_code'] === 0) {
            $this->reload();
            return ['success' => true, 'message' => 'Configuration applied successfully.'];
        }

        return ['success' => false, 'message' => 'Validation failed: ' . $validation['output']];
    }

    protected function readConfig(): string
    {
        if (file_exists($this->configPath)) {
            return File::get($this->configPath);
        }
        return "";
    }

    public function validate(): array
    {
        $cmd = "haproxy -c -f {$this->configPath}";
        $result = Process::run($cmd);
        return ['exit_code' => $result->exitCode(), 'output' => $result->output()];
    }

    public function reload(): void
    {
        if (file_exists('/.dockerenv')) {
            // Signal-based reload for Master-Worker mode (-W)
            // We use a simple trick: acme.sh or a small helper could trigger this,
            // but the cleanest way in Docker Compose is to have HAProxy watch the config
            // or use a reload trigger. 
            // For this setup, we assume Master-Worker mode which reloads on SIGHUP to the master process.
            // Since we are in a different container, we'd need to talk to the Docker API or use a shared signal file.
            
            // Simpler for this implementation: The user can trigger a reload or we use a sidecar.
            // But let's try the 'socat' approach if the socket is shared.
            Process::run("echo 'reload' | socat stdio unix-connect:{$this->socketPath}");
        } else {
            Process::run("sudo systemctl reload haproxy");
        }
    }

    // ... (rest of the methods remain the same)
    public function getLiveSessions(): array
    {
        $result = Process::run("echo 'show sess' | socat stdio {$this->socketPath}");
        if ($result->failed()) return [];
        $lines = explode("\n", $result->output());
        $sessions = [];
        foreach ($lines as $line) {
            if (empty($line) || !str_contains($line, 'src=')) continue;
            $data = [];
            $parts = explode(' ', $line);
            foreach ($parts as $part) {
                if (str_contains($part, '=')) {
                    [$key, $value] = explode('=', $part, 2);
                    $data[$key] = $value;
                }
            }
            if (isset($data['src']) && isset($data['fe']) && isset($data['be'])) {
                if ($data['fe'] === 'GLOBAL' || $data['be'] === '<NONE>') continue;
                $srcParts = explode(':', $data['src']);
                $sessions[] = [
                    'ip' => $srcParts[0],
                    'port' => $srcParts[1] ?? 'unknown',
                    'frontend' => str_replace(['fe_http_', 'fe_tcp_'], '', $data['fe']),
                    'backend' => str_replace(['be_http_', 'be_tcp_'], '', $data['be']),
                    'server' => $data['srv'] ?? 'none',
                    'age' => $data['age'] ?? '0s'
                ];
            }
        }
        return $sessions;
    }

    public function getRealtimeStats(): array
    {
        $result = Process::run("echo 'show stat' | socat stdio {$this->socketPath}");
        if ($result->failed()) return [];
        $lines = explode("\n", $result->output());
        $data = ['connections' => 0, 'bin' => 0, 'bout' => 0, 'rps' => 0];
        foreach ($lines as $line) {
            if (empty($line) || str_starts_with($line, '#')) continue;
            $f = explode(',', $line);
            if (isset($f[1]) && $f[1] === 'FRONTEND') {
                $data['connections'] += (int)$f[4];
                $data['bin'] += (int)$f[8];
                $data['bout'] += (int)$f[9];
                $data['rps'] += (int)$f[33];
            }
        }
        return $data;
    }

    protected function renderConfig($hosts): string
    {
        $content = "";
        
        // Permanent ACME Challenge Backend
        $content .= "backend be_acme_challenge\n";
        $content .= "    mode http\n";
        $content .= "    server acme_standalone 127.0.0.1:8888\n\n";

        $groupedHosts = $hosts->groupBy(function($h) {
            return $h->listen_address . ':' . $h->listen_port . '|' . $h->mode;
        });
        foreach ($groupedHosts as $key => $group) {
            [$bind, $mode] = explode('|', $key);
            $content .= $this->renderFrontendGroup($bind, $mode, $group);
            foreach ($group as $host) {
                $content .= ($mode === 'http') ? $this->renderHttpBackend($host) : $this->renderTcpBackend($host);
            }
        }
        return $content;
    }

    protected function renderFrontendGroup(string $bind, string $mode, $hosts): string
    {
        $slug = Str::slug($bind . '_' . $mode);
        $feName = "fe_{$mode}_{$slug}";
        $certs = $hosts->filter(fn($h) => $h->tls_termination && $h->certificate_path)->pluck('certificate_path')->unique();
        $bindLine = "    bind $bind";
        if ($certs->isNotEmpty()) {
            foreach ($certs as $cert) {
                $bindLine .= " ssl crt $cert";
            }
        }
        $lines = ["frontend $feName", $bindLine];
        if ($mode === 'http') {
            $lines[] = "    mode http";
            $lines[] = "    option httplog";
            
            // ACME Challenge Rule
            $lines[] = "    acl is_acme_challenge path_beg /.well-known/acme-challenge/";
            $lines[] = "    use_backend be_acme_challenge if is_acme_challenge";

            foreach ($hosts as $host) {
                $beName = "be_http_" . Str::slug($host->name);
                if (!empty($host->hostnames)) {
                    $aclName = "is_" . Str::slug($host->name);
                    $hostsList = implode(' ', $host->hostnames);
                    $lines[] = "    acl $aclName hdr(host) -i $hostsList";
                    $lines[] = "    use_backend $beName if $aclName";
                } else {
                    $lines[] = "    default_backend $beName";
                }
            }
        } else {
            $lines[] = "    mode tcp";
            $lines[] = "    option tcplog";
            $lines[] = "    tcp-request inspect-delay 5s";
            $lines[] = "    tcp-request content accept if { req_ssl_hello_type 1 }";
            foreach ($hosts as $host) {
                $beName = "be_tcp_" . Str::slug($host->name);
                if (!empty($host->hostnames)) {
                    foreach ($host->hostnames as $hn) {
                        $lines[] = "    use_backend $beName if { req_ssl_sni -i $hn }";
                    }
                } else {
                    $lines[] = "    default_backend $beName";
                }
            }
        }
        return implode("\n", $lines) . "\n\n";
    }

    protected function renderHttpBackend(ProxyHost $host): string
    {
        $beName = "be_http_" . Str::slug($host->name);
        $lines = ["backend $beName", "    mode http", "    balance {$host->balance_algorithm}"];
        foreach ($host->backends as $backend) {
            $backup = $backend->is_backup ? " backup" : "";
            $lines[] = "    server {$backend->name} {$backend->address}:{$backend->port} check$backup";
        }
        return implode("\n", $lines) . "\n\n";
    }

    protected function renderTcpBackend(ProxyHost $host): string
    {
        $beName = "be_tcp_" . Str::slug($host->name);
        $lines = ["backend $beName", "    mode tcp"];
        foreach ($host->backends as $backend) {
            $backup = $backend->is_backup ? " backup" : "";
            $lines[] = "    server {$backend->name} {$backend->address}:{$backend->port} check$backup";
        }
        return implode("\n", $lines) . "\n\n";
    }

    protected function updateManagedBlock(string $fullConfig, string $newBlock): string
    {
        $pattern = "/{$this->markerStart}.*?{$this->markerEnd}/s";
        $replacement = "{$this->markerStart}\n{$newBlock}{$this->markerEnd}";
        return preg_match($pattern, $fullConfig) ? preg_replace($pattern, $replacement, $fullConfig) : $fullConfig . "\n\n" . $replacement;
    }
}
